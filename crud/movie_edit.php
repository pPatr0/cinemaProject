<?php
session_start();
$pdo = require_once __DIR__ . '/../dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: movies_crud.php'); exit; }

// update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']       ?? '');
    $year  = (int)($_POST['release_year'] ?? 0);
    $dur   = (int)($_POST['duration_min'] ?? 0);
    $genre = trim($_POST['genre']       ?? '');
    $desc  = trim($_POST['description'] ?? '');

    // img upload
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['poster']['tmp_name'];
        $name = basename($_FILES['poster']['name']);
        $target = __DIR__ . '/../public/' . $name;
        if (move_uploaded_file($tmp, $target)) {
            $posterName = $name;
        } else {
            $posterName = $movie['poster_url'];
        }
    } else {
        $posterName = $_POST['old_poster'] ?? '';
    }

    $stmt = $pdo->prepare("UPDATE Movie 
                           SET title=?, release_year=?, duration_min=?, genre=?, description=?, poster_url=?
                           WHERE movie_id=?");
    $stmt->execute([$title,$year,$dur,$genre,$desc,$posterName,$id]);
    header('Location: movies_crud.php');
    exit;
}

// read
$movie = $pdo->prepare("SELECT * FROM Movie WHERE movie_id=?");
$movie->execute([$id]);
$movie = $movie->fetch();
if (!$movie) { header('Location: movies_crud.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Movie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-3xl">
    <h1 class="text-4xl font-bold mb-6">Edit <span class="text-yellow-500">Movie</span></h1>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
        <input type="text" name="title" placeholder="Title" required value="<?= htmlspecialchars($movie['title']) ?>"
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">

        <div class="grid grid-cols-3 gap-4">
            <input type="number" name="release_year" placeholder="Year" required value="<?= htmlspecialchars($movie['release_year']) ?>"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
            <input type="number" name="duration_min" placeholder="Duration (min)" required value="<?= htmlspecialchars($movie['duration_min']) ?>"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
            <input type="text" name="genre" placeholder="Genre" value="<?= htmlspecialchars($movie['genre']) ?>"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Current poster</label>
            <?php if ($movie['poster_url']): ?>
                <img src="../public/<?= htmlspecialchars($movie['poster_url']) ?>" class="h-32 rounded mb-2">
            <?php endif; ?>
            <label class="block mb-2 font-semibold">Upload new (optional)</label>
            <input type="file" name="poster" accept="image/*"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-yellow-500 file:text-black hover:file:bg-yellow-600">
            <input type="hidden" name="old_poster" value="<?= htmlspecialchars($movie['poster_url']) ?>">
        </div>

        <textarea name="description" rows="5" placeholder="Description" required
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"><?= htmlspecialchars($movie['description']) ?></textarea>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save</button>
            <a href="movies_crud.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>