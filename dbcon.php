<?php
$config = require __DIR__ . '/config.php';
$c = $config['db'];
try {
    return new PDO(
        "mysql:host={$c['host']};dbname={$c['dbname']};charset={$c['charset']}",
        $c['user'],
        $c['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>