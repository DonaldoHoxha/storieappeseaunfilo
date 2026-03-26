<?php
// ============================================================
//  config.php — Configurazione generale del sito
//  Incluso automaticamente da db.php
// ============================================================

// --- Email che riceve gli ordini ---
define('EMAIL_ADMIN',   'donaldo.hoxha06@email.it');
define('NOME_SITO',     'StorieAppeseAUnFilo');

// --- Cartella upload immagini ordini ---
define('UPLOAD_DIR',    __DIR__ . '/../uploads/ordini/');
define('UPLOAD_URL',    'uploads/ordini/');   // percorso relativo per il DB

// --- Dimensione massima immagine upload (in byte) ---
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5 MB

// --- Tipi di file immagine accettati ---
define('TIPI_IMMAGINE_OK', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
