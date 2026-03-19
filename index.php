<?php
// ============================================================
//  index.php — Home page: griglia prodotti con filtro categorie
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Recupera prodotti (dal più recente) e categorie
$prodotti  = getProdotti($conn);
$categorie = getCategorie($conn);

$titolo_pagina    = 'StorieAppeseAUnFilo — Ricami artigianali fatti a mano';
$meta_descrizione = 'Esplora ricami unici realizzati a mano. Ogni pezzo racconta una storia.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="intro">
    <h1>Creazioni uniche, fatte a mano.</h1>
    <p>Esplora i miei ricami o contattami per un lavoro personalizzato.</p>
</section>

<?php if (empty($prodotti)): ?>
    <p style="text-align:center; color:#888;">
        Nessun prodotto disponibile al momento. Torna presto! 🪡
    </p>
<?php else: ?>

    <!-- ===== FILTRI CATEGORIA ===== -->
    <div class="filtri-wrapper">
        <button class="btn-filtro attivo" data-categoria="tutti">Tutti</button>
        <?php foreach ($categorie as $cat): ?>
            <button
                class="btn-filtro"
                data-categoria="<?php echo (int)$cat['id']; ?>">
                <?php echo sanitizzaTesto($cat['nome']); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- ===== GRIGLIA PRODOTTI ===== -->
    <section class="griglia-prodotti">
        <?php foreach ($prodotti as $prodotto): ?>
            <div class="card"
                data-categoria="<?php echo (int)($prodotto['categoria_id'] ?? 0); ?>">

                <a href="<?php echo BASE_URL; ?>/prodotto.php?id=<?php echo (int)$prodotto['id']; ?>" class="card-link">
                    <div class="media-container">
                        <img
                            src="<?php echo sanitizzaTesto($prodotto['immagine']); ?>"
                            alt="<?php echo sanitizzaTesto($prodotto['nome']); ?>">
                    </div>
                </a>

                <div class="card-info">
                    <h2>
                        <a href="<?php echo BASE_URL; ?>/prodotto.php?id=<?php echo (int)$prodotto['id']; ?>">
                            <?php echo sanitizzaTesto($prodotto['nome']); ?>
                        </a>
                    </h2>
                    <p><?php echo sanitizzaTesto($prodotto['descrizione']); ?></p>
                    <span class="prezzo"><?php echo formatPrezzo((float)$prodotto['prezzo']); ?></span>

                    <button class="btn-acquista" data-id="<?php echo (int)$prodotto['id']; ?>">
                        Acquista Ora
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Messaggio se nessun prodotto corrisponde al filtro -->
    <p class="filtri-vuoti" style="display:none;">
        Nessun prodotto in questa categoria al momento. 🪡
    </p>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>