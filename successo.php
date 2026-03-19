<?php
// ============================================================
//  successo.php — Pagina di conferma dopo l'acquisto
//  In futuro Stripe reindirizzerà qui con ?session_id=xxx
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// --- Placeholder: leggi il session_id di Stripe quando sarà integrato ---
// $session_id = filter_input(INPUT_GET, 'session_id', FILTER_SANITIZE_STRING);

$titolo_pagina    = 'Ordine Confermato — StorieAppeseAUnFilo';
$meta_descrizione = 'Grazie per il tuo acquisto! Il tuo ricamo artigianale è in preparazione.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== PAGINA DI SUCCESSO ===== -->
<section class="successo-wrapper">

    <div class="successo-card">
        <div class="successo-icona">🎉</div>
        <h1>Ordine confermato!</h1>
        <p class="successo-testo">
            Grazie mille per il tuo acquisto.<br>
            Riceverai una email di conferma a breve con tutti i dettagli.
        </p>
        <p class="successo-nota">
            ✂️ Il tuo pezzo unico è già in preparazione — ogni punto viene cucito con cura.
        </p>

        <!-- Riepilogo ordine — si popolerà con i dati Stripe in futuro -->
        <!-- <div class="riepilogo-ordine">
                <h2>Riepilogo ordine</h2>
                <p>Prodotto: <strong>...</strong></p>
                <p>Importo pagato: <strong>...</strong></p>
            </div> -->

        <a href="<?php echo BASE_URL; ?>/index.php" class="btn-acquista" style="display:inline-block;width:auto;text-decoration:none;">
            ← Scopri altri prodotti
        </a>
    </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>