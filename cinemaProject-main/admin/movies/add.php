<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

// insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']       ?? '');
    $year  = (int)($_POST['release_year'] ?? 0);
    $dur   = (int)($_POST['duration_min'] ?? 0);
    $genre = trim($_POST['genre']       ?? '');
    $desc  = trim($_POST['description'] ?? '');

    // img upload
    $posterName = '';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['poster']['tmp_name'];

        // basic dimension check
        [$w, $h] = @getimagesize($tmp);
        if ($w && $h) {

            // img ration check
            $ratio = max($w, $h) / max(1, min($w, $h));
            if ($ratio <= 3.0) {

                // generate safe filename
                $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $ext = 'jpg';
                }
                $safeName = 'poster_' . bin2hex(random_bytes(6)) . '.' . $ext;

                $target = __DIR__ . '/../../public/images/' . $safeName;
                if (move_uploaded_file($tmp, $target)) {
                    $posterName = $safeName;
                }
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO Movie 
                           (title,release_year,duration_min,genre,description,poster_url)
                           VALUES (?,?,?,?,?,?)");
    $stmt->execute([$title,$year,$dur,$genre,$desc,$posterName]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Movie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-3xl">
    <h1 class="text-4xl font-bold mb-6">Add <span class="text-yellow-500">Movie</span></h1>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
        <input type="text" name="title" placeholder="Title" required
               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">

        <div class="grid grid-cols-3 gap-4">
            <input type="number" name="release_year" placeholder="Year" required
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
            <input type="number" name="duration_min" placeholder="Duration (min)" required
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
            <input type="text" name="genre" placeholder="Genre"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Poster image</label>
            <input type="file" name="poster" accept="image/*"
                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-yellow-500 file:text-black hover:file:bg-yellow-600">
        </div>

        <textarea name="description" rows="5" placeholder="Description" required
                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"></textarea>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save</button>
            <a href="index.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>