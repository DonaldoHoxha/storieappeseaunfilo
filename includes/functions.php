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
//  ORDINI  (placeholder — pronto per Stripe in futuro)
// ------------------------------------------------------------

/**
 * Salva un ordine nel database dopo il pagamento.
 * Attualmente è un placeholder: si popolerà quando integrerete Stripe.
 *
 * @param  mysqli $conn
 * @param  int    $prodotto_id
 * @param  string $nome_cliente
 * @param  string $email_cliente
 * @return int|false   ID del nuovo ordine, oppure false in caso di errore
 */
function salvaOrdine(mysqli $conn, int $prodotto_id, string $nome_cliente, string $email_cliente): int|false
{
    $nome_cliente  = sanitizzaTesto($nome_cliente);
    $email_cliente = sanitizzaEmail($email_cliente);

    if (!$email_cliente) {
        return false; // email non valida
    }

    $stmt = $conn->prepare(
        "INSERT INTO ordini (prodotto_id, nome_cliente, email_cliente, stato, creato_il)
         VALUES (?, ?, ?, 'in_attesa', NOW())"
    );

    if (!$stmt) {
        error_log('Errore prepare salvaOrdine: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('iss', $prodotto_id, $nome_cliente, $email_cliente);

    if (!$stmt->execute()) {
        error_log('Errore execute salvaOrdine: ' . $stmt->error);
        $stmt->close();
        return false;
    }

    $nuovo_id = $conn->insert_id;
    $stmt->close();

    return $nuovo_id;
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
