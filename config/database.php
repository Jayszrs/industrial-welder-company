<?php
/**
 * Database Connection (PDO)
 * -------------------------
 * Default XAMPP credentials. Change these when deploying.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'industrial_company');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL / path helpers (auto-detected, works when the folder is placed
// directly under htdocs, e.g. http://localhost/industrial-welder-company/)
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak credentials or raw DB errors to visitors.
            http_response_code(500);
            die('Database connection failed. Please check /config/database.php and make sure the "industrial_company" database has been imported via phpMyAdmin.');
        }
    }

    return $pdo;
}
