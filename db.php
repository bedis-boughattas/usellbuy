<?php
/* ============================================================
   db.php — Connexion PDO à la base de données MySQL
   UsellBuy — ENSI 2025/2026
   ============================================================ */

define('DB_HOST', 'localhost');
define('DB_NAME', 'usellbuy');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retourne une instance PDO connectée à la base usellbuy.
 * Lance une exception si la connexion échoue.
 */
function getConnection(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die('<p style="color:red;font-family:sans-serif;padding:20px;">
             <strong>Database connection error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>');
    }
}
?>
