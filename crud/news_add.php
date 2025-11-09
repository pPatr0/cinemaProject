<?php
session_start();
$pdo = require_once __DIR__ . '/../dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

// insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body']  ?? '');
    $userId = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO News (title, body, user_id, created_at) VALUES (?,?,?, NOW())");
    $stmt->execute([$title, $body, $userId]);
    header('Location: news_crud.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add News</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-3xl">
    <h1 class="text-4xl font-bold mb-6">Add <span class="text-yellow-500">Article</span></h1>

    <form method="post" class="space-y-5">
        <input type="text" name="title" placeholder="Title" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">

        <textarea name="body" rows="8" placeholder="Article body" required
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"></textarea>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save</button>
            <a href="news_crud.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>