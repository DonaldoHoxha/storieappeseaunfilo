<?php
// ============================================================
//  functions.php — Funzioni di utilità del sito
//  Uso: require_once __DIR__ . '/includes/functions.php';
//  Richiede che $conn (da db.php) sia già disponibile.
// ============================================================


// ------------------------------------------------------------
//  PRODOTTI
// ------------------------------------------------------------

/**
 * Restituisce tutti i prodotti visibili, ordinati per data di inserimento.
 *
 * @param  mysqli $conn  Connessione al database
 * @return array         Array associativo di prodotti
 */
function getProdotti(mysqli $conn): array
{
    $sql  = "SELECT * FROM prodotti WHERE visibile = 1 ORDER BY creato_il DESC";
    $result = $conn->query($sql);

    if (!$result) {
        error_log('Errore getProdotti: ' . $conn->error);
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Restituisce un singolo prodotto dato il suo ID.
 * Ritorna null se non trovato o non visibile.
 *
 * @param  mysqli $conn
 * @param  int    $id
 * @return array|null
 */
function getProdottoById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare(
        "SELECT * FROM prodotti WHERE id = ? AND visibile = 1 LIMIT 1"
    );

    if (!$stmt) {
        error_log('Errore prepare getProdottoById: ' . $conn->error);
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prodotto = $result->fetch_assoc();
    $stmt->close();

    return $prodotto ?: null;
}


// ------------------------------------------------------------
//  CATEGORIE
// ------------------------------------------------------------

/**
 * Restituisce tutte le categorie, ordinate per nome.
 *
 * @param  mysqli $conn
 * @return array
 */
function getCategorie(mysqli $conn): array
{
    $result = $conn->query("SELECT * FROM categorie ORDER BY nome ASC");

    if (!$result) {
        error_log('Errore getCategorie: ' . $conn->error);
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


// ------------------------------------------------------------
//  IMMAGINI PRODOTTO
// ------------------------------------------------------------

/**
 * Restituisce tutte le immagini di un prodotto, ordinate per 'ordinamento'.
 *
 * @param  mysqli $conn
 * @param  int    $prodotto_id
 * @return array  Es: [['percorso' => 'assets/images/foto1.jpg'], ...]
 */
function getImmaginiProdotto(mysqli $conn, int $prodotto_id): array
{
    $stmt = $conn->prepare(
        "SELECT percorso FROM prodotto_immagini
         WHERE prodotto_id = ? ORDER BY ordinamento ASC"
    );

    if (!$stmt) {
        error_log('Errore prepare getImmaginiProdotto: ' . $conn->error);
        return [];
    }

    $stmt->bind_param('i', $prodotto_id);
    $stmt->execute();
    $risultati = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $risultati;
}


// ------------------------------------------------------------
//  ORDINI
// ------------------------------------------------------------

/**
 * Salva un ordine nel database.
 *
 * @param  mysqli      $conn
 * @param  string      $nome
 * @param  string      $email
 * @param  string      $messaggio
 * @param  string|null $immagine_path   Percorso file caricato dall'utente
 * @param  int|null    $prodotto_ref_id ID prodotto scelto come riferimento
 * @return int|false   ID del nuovo ordine, false in caso di errore
 */
function salvaOrdine(
    mysqli $conn,
    string $nome,
    string $email,
    string $messaggio,
    ?string $immagine_path = null,
    ?int $prodotto_ref_id  = null
): int|false {
    $nome      = sanitizzaTesto($nome);
    $messaggio = sanitizzaTesto($messaggio);
    $email     = sanitizzaEmail($email);

    if (!$email || $nome === '' || $messaggio === '') {
        return false;
    }

    $stmt = $conn->prepare(
        "INSERT INTO ordini
            (nome_cliente, email_cliente, messaggio, immagine_path, prodotto_riferimento_id, stato, creato_il)
         VALUES (?, ?, ?, ?, ?, 'nuovo', NOW())"
    );

    if (!$stmt) {
        error_log('Errore prepare salvaOrdine: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('ssssi', $nome, $email, $messaggio, $immagine_path, $prodotto_ref_id);

    if (!$stmt->execute()) {
        error_log('Errore execute salvaOrdine: ' . $stmt->error);
        $stmt->close();
        return false;
    }

    $nuovo_id = $conn->insert_id;
    $stmt->close();

    return $nuovo_id;
}

/**
 * Invia la notifica email all'admin quando arriva un ordine.
 * Usa la funzione mail() di PHP — su XAMPP configura php.ini oppure
 * sostituisci con PHPMailer per la produzione.
 *
 * @param  array $ordine  Dati dell'ordine (nome, email, messaggio, ecc.)
 * @return bool
 */
function inviaEmailOrdine(array $ordine): bool
{
    $a        = EMAIL_ADMIN;
    $oggetto  = '🪡 Nuovo ordine da ' . $ordine['nome_cliente'] . ' — ' . NOME_SITO;
    $prodotto = !empty($ordine['prodotto_nome']) ? $ordine['prodotto_nome'] : '(nessuno selezionato)';
    $immagine = !empty($ordine['immagine_path'])  ? $ordine['immagine_path']  : '(nessuna)';

    $corpo = "Hai ricevuto un nuovo ordine personalizzato!\n\n"
        . "--- CLIENTE ---\n"
        . "Nome:    {$ordine['nome_cliente']}\n"
        . "Email:   {$ordine['email_cliente']}\n\n"
        . "--- ORDINE ---\n"
        . "Messaggio:\n{$ordine['messaggio']}\n\n"
        . "Prodotto di riferimento: {$prodotto}\n"
        . "Immagine allegata:       {$immagine}\n\n"
        . "--- RIEPILOGO ---\n"
        . "ID ordine: #{$ordine['id']}\n"
        . "Ricevuto il: " . date('d/m/Y H:i') . "\n\n"
        . "Accedi al pannello admin per gestire l'ordine.";

    $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n"
        . "Reply-To: {$ordine['email_cliente']}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($a, $oggetto, $corpo, $headers);
}

/**
 * Gestisce l'upload di un'immagine di riferimento per l'ordine.
 * Restituisce il percorso relativo salvato nel DB, oppure null se nessun file.
 *
 * @param  array  $file  Elemento di $_FILES
 * @return string|null
 * @throws RuntimeException  Se il file non è valido
 */
function uploadImmagineOrdine(array $file): ?string
{
    // Nessun file caricato — non è un errore
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Errore durante il caricamento del file.');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Il file supera la dimensione massima consentita (5 MB).');
    }

    // Verifica tipo MIME reale (non solo l'estensione)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!in_array($mime, TIPI_IMMAGINE_OK, true)) {
        throw new RuntimeException('Tipo file non consentito. Carica un\'immagine JPG, PNG, WEBP o GIF.');
    }

    // Crea la cartella se non esiste
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Nome univoco per evitare sovrascritture
    $estensione  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome_file   = uniqid('ordine_', true) . '.' . strtolower($estensione);
    $percorso    = UPLOAD_DIR . $nome_file;

    if (!move_uploaded_file($file['tmp_name'], $percorso)) {
        throw new RuntimeException('Impossibile salvare il file. Controlla i permessi della cartella uploads/.');
    }

    return UPLOAD_URL . $nome_file;
}



// ------------------------------------------------------------
//  UTILITÀ GENERALI
// ------------------------------------------------------------

/**
 * Pulisce una stringa di testo per output HTML sicuro.
 *
 * @param  string $testo
 * @return string
 */
function sanitizzaTesto(string $testo): string
{
    return htmlspecialchars(trim($testo), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida e sanitizza un indirizzo email.
 * Restituisce la email pulita oppure null se non valida.
 *
 * @param  string $email
 * @return string|null
 */
function sanitizzaEmail(string $email): ?string
{
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

/**
 * Formatta un prezzo in euro con il simbolo €.
 * Esempio: formatPrezzo(45) → "€ 45,00"
 *
 * @param  float $prezzo
 * @return string
 */
function formatPrezzo(float $prezzo): string
{
    return '€ ' . number_format($prezzo, 2, ',', '.');
}

/**
 * Reindirizza a un URL e termina lo script.
 *
 * @param  string $url
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
