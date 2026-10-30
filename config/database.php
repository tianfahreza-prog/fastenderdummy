<?php
/**
 * Fastender Pre-Order Platform
 * File Konfigurasi Database (PDO) - Supabase PostgreSQL via Transaction Pooler
 * config/database.php
 */

require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST', 'aws-0-ap-northeast-2.pooler.supabase.com'));
define('DB_PORT', envInt('DB_PORT', 5432));
define('DB_NAME', env('DB_NAME', 'postgres'));
define('DB_USER', env('DB_USER', 'postgres.xotrxfmsrrtvestvggrw'));
define('DB_PASS', env('DB_PASSWORD', ''));

function getDbConnection(): ?PDO {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB connection error: " . $e->getMessage());
        return null;
    }
}
