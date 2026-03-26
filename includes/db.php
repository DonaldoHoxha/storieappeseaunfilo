<?php
// ============================================================
//  db.php — Connessione al database MySQL (XAMPP/WAMP)
//  Includi questo file in ogni pagina che legge dal database:
//      require_once __DIR__ . '/includes/db.php';
// ============================================================

// --- Configurazione generale ---
require_once __DIR__ . '/config.php';

// --- URL base del progetto (funziona in qualsiasi sottocartella) ---
// Es: localhost/web/storieappese → BASE_URL = '/web/storieappese'
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// --- Credenziali ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // utente di default su XAMPP/WAMP
define('DB_PASS', '');           // password vuota di default su XAMPP/WAMP
define('DB_NAME', 'storieappese'); // nome del tuo database

// --- Connessione con mysqli ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Controllo errore ---
if ($conn->connect_error) {
    // In produzione NON mostrare mai l'errore reale all'utente
    error_log('Errore connessione DB: ' . $conn->connect_error);
    die('Servizio temporaneamente non disponibile. Riprova più tardi.');
}

// --- Encoding UTF-8 ---
$conn->set_charset('utf8mb4');
