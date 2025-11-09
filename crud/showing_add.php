<?php
session_start();
$pdo = require_once __DIR__ . '/../dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

// lists
$movies = $pdo->query("SELECT movie_id, title FROM Movie ORDER BY title")->fetchAll();
$halls  = $pdo->query("SELECT hall_id, name FROM Hall ORDER BY name")->fetchAll();

// insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId = (int)($_POST['movie_id'] ?? 0);
    $hallId  = (int)($_POST['hall_id']  ?? 0);
    $start   = trim($_POST['start_time'] ?? '');
    $price   = (float)($_POST['price']   ?? 0);

    $stmt = $pdo->prepare("INSERT INTO Showing (movie_id, hall_id, start_time, price) VALUES (?,?,?,?)");
    $stmt->execute([$movieId, $hallId, $start, $price]);
    header('Location: showings_crud.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Showing</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-3xl">
    <h1 class="text-4xl font-bold mb-6">Add <span class="text-yellow-500">Showing</span></h1>

    <form method="post" class="space-y-5">
        <div>
            <label class="block mb-2 font-semibold">Movie</label>
            <select name="movie_id" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
                <?php foreach ($movies as $m): ?>
                    <option value="<?= $m['movie_id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block mb-2 font-semibold">Hall</label>
            <select name="hall_id" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
                <?php foreach ($halls as $h): ?>
                    <option value="<?= $h['hall_id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block mb-2 font-semibold">Start time</label>
            <input type="datetime-local" name="start_time" required
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Price (DKK)</label>
            <input type="number" step="0.01" name="price" required
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save</button>
            <a href="showings_crud.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>