<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /views/index.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT s.showing_id,
            m.title,
            h.name AS hall_name,
            s.start_time,
            s.price
     FROM Showing s
     JOIN Movie m ON s.movie_id = m.movie_id
     JOIN Hall h ON s.hall_id  = h.hall_id
     WHERE s.start_time >= NOW()
     ORDER BY s.start_time ASC"
);
$showings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Showings CRUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-bold">Showings <span class="text-yellow-500">Management</span></h1>
        <a href="../index.php" class="border border-gray-600 text-gray-300 px-4 py-2 rounded hover:bg-gray-800">← Back</a>
    </div>

    <div class="mb-4">
        <a href="add.php" class="bg-yellow-500 text-black px-4 py-2 rounded font-bold hover:bg-yellow-600">+ Add new showing</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-gray-900 border border-gray-800 rounded">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left">Movie</th>
                    <th class="px-4 py-3 text-left">Hall</th>
                    <th class="px-4 py-3 text-left">Start time</th>
                    <th class="px-4 py-3 text-left">Price (DKK)</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$showings): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-gray-400">No upcoming showings.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($showings as $s): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800">
                            <td class="px-4 py-3"><?= htmlspecialchars($s['title']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($s['hall_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($s['start_time']))) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($s['price']) ?></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="edit.php?id=<?= $s['showing_id'] ?>" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm">Edit</a>
                                <a href="delete.php?id=<?= $s['showing_id'] ?>" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm" onclick="return confirm('Delete this showing?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>