<?php
// ============================================================
//  header.php — Intestazione comune a tutte le pagine
//  Uso: require_once __DIR__ . '/includes/header.php';
//
//  Variabili opzionali da definire PRIMA di includere:
//      $titolo_pagina  → titolo nel <title> (default: 'StorieAppeseAUnFilo')
//      $meta_descrizione → meta description SEO (opzionale)
// ============================================================

$titolo_pagina    = $titolo_pagina    ?? 'StorieAppeseAUnFilo';
$meta_descrizione = $meta_descrizione ?? 'Ricami artigianali unici, fatti a mano con amore.';

// Classe body automatica dal nome del file (es: prodotto.php → body class="pagina-prodotto")
$nome_file   = basename($_SERVER['PHP_SELF'], '.php'); // "prodotto", "index", "successo"...
$classe_body = 'pagina-' . $nome_file;
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_descrizione); ?>">
    <title><?php echo htmlspecialchars($titolo_pagina); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>

<body class="<?php echo $classe_body; ?>">

    <header class="site-header">
        <a href="<?php echo BASE_URL; ?>/index.php" class="logo" style="text-decoration:none;">🪡 StorieAppeseAUnFilo</a>
        <nav>
            <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
            <a href="#">Chi Sono</a>
            <a href="#">Contatti</a>
        </nav>
    </header>

    <main class="container">