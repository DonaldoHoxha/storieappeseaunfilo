<?php
// ============================================================
//  ordine.php — Pagina per creare un ordine personalizzato
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$prodotti = getProdotti($conn);  // per la selezione del riferimento
$errore   = null;
$successo = false;

// Preseleziona il prodotto se arriva da ?ref=ID (link da prodotto.php)
$ref_id = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);

// --- Gestione invio form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome      = trim($_POST['nome']      ?? '');
        $email     = trim($_POST['email']     ?? '');
        $messaggio = trim($_POST['messaggio'] ?? '');
        $prod_ref  = !empty($_POST['prodotto_riferimento']) ? (int)$_POST['prodotto_riferimento'] : null;

        // Validazioni base
        if ($nome === '')      throw new RuntimeException('Inserisci il tuo nome.');
        if ($messaggio === '') throw new RuntimeException('Descrivi cosa vorresti ordinare.');
        if (!sanitizzaEmail($email)) throw new RuntimeException('Inserisci un indirizzo email valido.');

        // Upload immagine (opzionale)
        $immagine_path = null;
        if (!empty($_FILES['immagine_upload']['name'])) {
            $immagine_path = uploadImmagineOrdine($_FILES['immagine_upload']);
        }

        // Se l'utente ha scelto un prodotto come riferimento ma NON ha caricato un file,
        // usiamo l'immagine di copertina del prodotto
        if ($immagine_path === null && $prod_ref) {
            $prod = getProdottoById($conn, $prod_ref);
            if ($prod) $immagine_path = $prod['immagine'];
        }

        // Salva nel DB
        $ordine_id = salvaOrdine($conn, $nome, $email, $messaggio, $immagine_path, $prod_ref);
        if (!$ordine_id) throw new RuntimeException('Errore nel salvataggio. Riprova più tardi.');

        // Invia email all'admin
        $prod_nome = null;
        if ($prod_ref) {
            $p = getProdottoById($conn, $prod_ref);
            $prod_nome = $p['nome'] ?? null;
        }

        inviaEmailOrdine([
            'id'              => $ordine_id,
            'nome_cliente'    => $nome,
            'email_cliente'   => $email,
            'messaggio'       => $messaggio,
            'immagine_path'   => $immagine_path,
            'prodotto_nome'   => $prod_nome,
        ]);

        $successo = true;
    } catch (RuntimeException $e) {
        $errore = $e->getMessage();
    }
}

$titolo_pagina    = 'Crea il tuo ordine — ' . NOME_SITO;
$meta_descrizione = 'Commissiona un ricamo personalizzato. Descrivi la tua idea e riceverai una risposta personalizzata.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="ordine-wrapper">

    <?php if ($successo): ?>
        <!-- ===== CONFERMA ===== -->
        <div class="ordine-successo">
            <div class="successo-icona">🎉</div>
            <h1>Ordine inviato!</h1>
            <p>Grazie! Ho ricevuto la tua richiesta e ti risponderò al più presto sulla tua email.</p>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn-acquista"
                style="display:inline-block;width:auto;text-decoration:none;margin-top:20px;">
                ← Torna alla Home
            </a>
        </div>

    <?php else: ?>
        <!-- ===== FORM ===== -->
        <div class="ordine-card">
            <h1>✂️ Crea il tuo ordine</h1>
            <p class="ordine-intro">
                Descrivi cosa vorresti — un ricamo personalizzato, un'idea, un regalo speciale.
                Ti risponderò via email con disponibilità e preventivo.
            </p>

            <?php if ($errore): ?>
                <div class="ordine-errore">⚠️ <?php echo sanitizzaTesto($errore); ?></div>
            <?php endif; ?>

            <form class="ordine-form" method="POST" enctype="multipart/form-data">

                <!-- Nome e Email -->
                <div class="form-riga">
                    <div class="form-gruppo">
                        <label for="nome">Il tuo nome *</label>
                        <input type="text" id="nome" name="nome"
                            value="<?php echo sanitizzaTesto($_POST['nome'] ?? ''); ?>"
                            placeholder="Es: Maria Rossi" required>
                    </div>
                    <div class="form-gruppo">
                        <label for="email">La tua email *</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo sanitizzaTesto($_POST['email'] ?? ''); ?>"
                            placeholder="Es: maria@email.it" required>
                    </div>
                </div>

                <!-- Messaggio -->
                <div class="form-gruppo">
                    <label for="messaggio">Descrivi il tuo ordine *</label>
                    <textarea id="messaggio" name="messaggio" rows="5"
                        placeholder="Es: Vorrei un ricamo con il nome 'Sofia' su una felpa rosa, stile floreale..."
                        required><?php echo sanitizzaTesto($_POST['messaggio'] ?? ''); ?></textarea>
                </div>

                <!-- Immagine di riferimento -->
                <div class="form-gruppo">
                    <label>Immagine di riferimento <span class="label-opzionale">(opzionale)</span></label>

                    <div class="riferimento-tabs">
                        <button type="button" class="tab-btn attivo" data-tab="upload">
                            📁 Carica un'immagine
                        </button>
                        <button type="button" class="tab-btn" data-tab="prodotti">
                            🪡 Scegli tra i miei prodotti
                        </button>
                    </div>

                    <!-- Tab upload -->
                    <div class="tab-contenuto attivo" id="tab-upload">
                        <div class="upload-area" id="upload-area">
                            <input type="file" id="immagine_upload" name="immagine_upload"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                class="upload-input">
                            <div class="upload-label">
                                <span class="upload-icona">🖼️</span>
                                <span class="upload-testo">Clicca o trascina un'immagine qui</span>
                                <span class="upload-nota">JPG, PNG, WEBP o GIF — max 5 MB</span>
                            </div>
                            <div class="upload-preview" id="upload-preview" style="display:none;">
                                <img id="preview-img" src="" alt="Anteprima">
                                <button type="button" id="rimuovi-preview">✕ Rimuovi</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab selezione prodotto -->
                    <div class="tab-contenuto" id="tab-prodotti">
                        <input type="hidden" name="prodotto_riferimento" id="prodotto_riferimento"
                            value="<?php echo (int)($_POST['prodotto_riferimento'] ?? $ref_id ?? 0); ?>">

                        <?php if (empty($prodotti)): ?>
                            <p style="color:#888; text-align:center; padding:20px;">
                                Nessun prodotto disponibile al momento.
                            </p>
                        <?php else: ?>
                            <div class="prodotti-selezione">
                                <?php foreach ($prodotti as $p): ?>
                                    <div class="prodotto-thumb <?php
                                                                $selected_id = (int)($_POST['prodotto_riferimento'] ?? $ref_id ?? 0);
                                                                echo ($selected_id === (int)$p['id']) ? 'selezionato' : '';
                                                                ?>"
                                        data-id="<?php echo (int)$p['id']; ?>">
                                        <?php if (!empty($p['immagine'])): ?>
                                            <img src="<?php echo BASE_URL . '/' . sanitizzaTesto($p['immagine']); ?>"
                                                alt="<?php echo sanitizzaTesto($p['nome']); ?>">
                                        <?php else: ?>
                                            <div class="thumb-placeholder">🪡</div>
                                        <?php endif; ?>
                                        <span><?php echo sanitizzaTesto($p['nome']); ?></span>
                                        <div class="thumb-check">✓</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-acquista" style="margin-top:10px;">
                    Invia il tuo ordine ✉️
                </button>

            </form>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>