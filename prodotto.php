<?php
// ============================================================
//  prodotto.php — Pagina di dettaglio di un singolo prodotto
//  URL: /prodotto.php?id=1
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// --- Leggi e valida l'ID dall'URL ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    // ID assente o non valido → torna alla home
    redirect(BASE_URL . '/index.php');
}

// --- Recupera il prodotto dal database ---
$prodotto = getProdottoById($conn, $id);

if (!$prodotto) {
    // Prodotto non esistente o non visibile
    http_response_code(404);
    $titolo_pagina = 'Prodotto non trovato — StorieAppeseAUnFilo';
    require_once __DIR__ . '/includes/header.php';
?>
    <section class="intro">
        <h1>Prodotto non trovato 😔</h1>
        <p>Il prodotto che cerchi non esiste o non è più disponibile.</p>
        <a href="<?php echo BASE_URL; ?>/index.php" class="btn-acquista" style="display:inline-block;width:auto;margin-top:20px;text-decoration:none;">
            ← Torna alla Home
        </a>
    </section>
<?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// --- Imposta variabili per header ---
$titolo_pagina    = sanitizzaTesto($prodotto['nome']) . ' — StorieAppeseAUnFilo';
$meta_descrizione = sanitizzaTesto($prodotto['descrizione']);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== DETTAGLIO PRODOTTO ===== -->
<div class="prodotto-dettaglio">

    <!-- Carousel immagini -->
    <?php
    $immagini = getImmaginiProdotto($conn, $prodotto['id']);
    // Se non ci sono immagini extra, usa la foto di copertina
    if (empty($immagini)) {
        $immagini = [['percorso' => $prodotto['immagine']]];
    }
    ?>
    <div class="prodotto-media">
        <div class="carousel" data-autoplay="5000">

            <!-- Freccia sinistra -->
            <button class="carousel-freccia carousel-prev" aria-label="Immagine precedente">&#8592;</button>

            <!-- Slide -->
            <div class="carousel-track">
                <?php foreach ($immagini as $i => $img): ?>
                    <div class="carousel-slide <?php echo $i === 0 ? 'attiva' : ''; ?>">
                        <img
                            src="<?php echo BASE_URL . '/' . sanitizzaTesto($img['percorso']); ?>"
                            alt="<?php echo sanitizzaTesto($prodotto['nome']); ?> — foto <?php echo $i + 1; ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Freccia destra -->
            <button class="carousel-freccia carousel-next" aria-label="Immagine successiva">&#8594;</button>

            <!-- Puntini -->
            <?php if (count($immagini) > 1): ?>
                <div class="carousel-dots">
                    <?php foreach ($immagini as $i => $img): ?>
                        <button
                            class="carousel-dot <?php echo $i === 0 ? 'attivo' : ''; ?>"
                            data-indice="<?php echo $i; ?>"
                            aria-label="Vai alla foto <?php echo $i + 1; ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Informazioni prodotto -->
    <div class="prodotto-info">
        <h1><?php echo sanitizzaTesto($prodotto['nome']); ?></h1>
        <p class="prodotto-descrizione"><?php echo sanitizzaTesto($prodotto['descrizione']); ?></p>
        <span class="prezzo"><?php echo formatPrezzo((float)$prodotto['prezzo']); ?></span>

        <p class="prodotto-nota">✂️ Ogni pezzo è unico e realizzato interamente a mano.</p>

        <!-- Bottone acquisto (placeholder — in futuro chiamerà Stripe) -->
        <button
            class="btn-acquista"
            data-id="<?php echo (int)$prodotto['id']; ?>">
            Acquista Ora
        </button>

        <a href="<?php echo BASE_URL; ?>/index.php" class="link-torna">← Torna a tutti i prodotti</a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>