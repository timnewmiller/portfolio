<?php
// config.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_file = __DIR__ .'/inventory.sqlite';
$dsn = 'sqlite:' . $database_file;
$username = null;
$password = null;

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS lists (
        list_id INTEGER PRIMARY KEY AUTOINCREMENT,
        list_name TEXT NOT NULL,
        list_url TEXT NOT NULL UNIQUE
    )");
    
    $pdo-exec("CREATE TABLE IF NOT EXISTS items (
        item_id INTEGER PRIMARY KEY AUTOINCREMENT,
        list_id INTEGER NOT NULL,
        item_name TEXT NOT NULL,
        quantity INTEGER DEFAULT 1,
        FOREIGN KEY (list_id) REFERENCES lists(list_id) ON DELETE CASCADE
    )");
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
