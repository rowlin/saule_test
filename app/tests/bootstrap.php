<?php

require __DIR__ . '/../constants.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Reset test user data to known state
try {
    $config = require __DIR__ . '/../config.php';
    $db = $config['db'];
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4;",
        $db['user'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $eur = \Enums\Currency::EUR->value;

    $pdo->exec("UPDATE users SET default_currency = '$eur' WHERE login = 'john_doe'");
    $pdo->exec("DELETE FROM balance_logs");
    $pdo->exec("DELETE FROM bets");
    $pdo->exec("DELETE FROM balances WHERE user_id = (SELECT id FROM users WHERE login = 'john_doe')");
    $pdo->exec("INSERT INTO balances (user_id, currency, amount) VALUES ((SELECT id FROM users WHERE login = 'john_doe'), '$eur', 1000)");
} catch (\Exception $e) {
    // Silently skip if DB is not available (e.g. in unit-test-only context)
}
