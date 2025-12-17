<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM Showing WHERE showing_id=?")->execute([$id]);
}
header('Location: index.php');
exit;