<?php
session_start();
$pdo = require_once __DIR__ . '/../dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

/* save */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mid = (int)($_POST['hero_movie_id'] ?? 0);
    $pdo->prepare("UPDATE CompanyInfo SET hero_movie_id = ? LIMIT 1")->execute([$mid]);
    $saved = true;
}

/* read */
$info   = $pdo->query("SELECT hero_movie_id FROM CompanyInfo LIMIT 1")->fetch();
$current = $info['hero_movie_id'] ?? 0;

$movies = $pdo->query("SELECT movie_id, title FROM Movie ORDER BY title")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hero Movie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-2xl">
    <h1 class="text-4xl font-bold mb-6">Select <span class="text-yellow-500">Best-Seller Movie</span></h1>

    <?php if (isset($saved)): ?>
        <div class="bg-green-900 border border-green-500 text-green-300 px-4 py-3 rounded mb-6">✅ Saved.</div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
        <select name="hero_movie_id" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
            <?php foreach ($movies as $m): ?>
                <option value="<?= $m['movie_id'] ?>" <?= $m['movie_id']==$current?'selected':'' ?>>
                    <?= htmlspecialchars($m['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save</button>
            <a href="admin.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">Back</a>
        </div>
    </form>
</div>
</body>
</html>