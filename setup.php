<?php

require 'vendor/autoload.php';

use Medoo\Medoo;

try {
    // Ensure the storage directory exists
    if (!file_exists('storage')) {
        mkdir('storage', 0777, true);
    }

    // Initialize the database connection
    $database = new Medoo([
        'type' => 'sqlite',
        'database' => 'storage/database.sqlite'
    ]);

    // Create users table
    $database->query("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            balance REAL NOT NULL
        )
    ");

    // Create wallets table
    $database->query("
        CREATE TABLE IF NOT EXISTS wallets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            symbol TEXT NOT NULL,
            amount REAL NOT NULL,
            average_price REAL NOT NULL,
            user_id INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Create transactions table
    $database->query("
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            kind TEXT NOT NULL,
            symbol TEXT NOT NULL,
            price REAL NOT NULL,
            quantity REAL NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    echo "Database schema initialized.\n";

} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo 'General error: ' . $e->getMessage() . "\n";
}
