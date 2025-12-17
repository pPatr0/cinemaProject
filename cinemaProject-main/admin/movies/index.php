<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /views/index.php');
    exit;
}

$movies = $pdo->query("SELECT * FROM Movie ORDER BY title")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movies CRUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-bold">Movies <span class="text-yellow-500">Management</span></h1>
        <a href="../index.php" class="border border-gray-600 text-gray-300 px-4 py-2 rounded hover:bg-gray-800">← Back</a>
    </div>

    <div class="mb-4">
        <a href="add.php" class="bg-yellow-500 text-black px-4 py-2 rounded font-bold hover:bg-yellow-600">+ Add new movie</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-gray-900 border border-gray-800 rounded">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left">Title</th>
                    <th class="px-4 py-3 text-left">Year</th>
                    <th class="px-4 py-3 text-left">Duration</th>
                    <th class="px-4 py-3 text-left">Genre</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $m): ?>
                    <tr class="border-b border-gray-800 hover:bg-gray-800">
                        <td class="px-4 py-3"><?= htmlspecialchars($m['title']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($m['release_year']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($m['duration_min']) ?> min</td>
                        <td class="px-4 py-3"><?= htmlspecialchars($m['genre'] ?? '-') ?></td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="edit.php?id=<?= $m['movie_id'] ?>" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm">Edit</a>
                            <a href="delete.php?id=<?= $m['movie_id'] ?>" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm" onclick="return confirm('Delete this movie?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>