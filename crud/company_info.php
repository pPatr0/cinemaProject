<?php
session_start();
$pdo = require_once __DIR__ . '/../dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

// update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $about   = trim($_POST['about']   ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $hours   = trim($_POST['hours']   ?? '');
    $address = trim($_POST['address'] ?? '');

    $stmt = $pdo->prepare("UPDATE CompanyInfo 
                           SET about = ?, contact_email = ?, contact_number = ?, opening_hours = ?, address = ?
                           LIMIT 1");
    $stmt->execute([$about, $email, $phone, $hours, $address]);
    $saved = true;
}

// read
$info = $pdo->query("SELECT * FROM CompanyInfo LIMIT 1")->fetch();
if (!$info) {
    $pdo->exec("INSERT INTO CompanyInfo (about,contact_email,contact_number,opening_hours,address)
                VALUES ('','','','','')");
    $info = $pdo->query("SELECT * FROM CompanyInfo LIMIT 1")->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12 max-w-4xl">
    <h1 class="text-4xl font-bold mb-6">Company <span class="text-yellow-500">Information</span></h1>

    <?php if (isset($saved)): ?>
        <div class="bg-green-900 border border-green-500 text-green-300 px-4 py-3 rounded mb-6">✅ Changes saved.</div>
    <?php endif; ?>

    <form method="post" class="space-y-6">
        <div>
            <label class="block mb-2 font-semibold">Description</label>
            <textarea name="about" rows="5" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"><?= htmlspecialchars($info['about']) ?></textarea>
        </div>

        <div>
            <label class="block mb-2 font-semibold">Contact e-mail</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($info['contact_email']) ?>" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Contact phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($info['contact_number']) ?>" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Address</label>
            <textarea name="address" rows="2" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"><?= htmlspecialchars($info['address']) ?></textarea>
        </div>

        <div>
            <label class="block mb-2 font-semibold">Opening hours<br><span class="text-sm text-gray-400">Format: Mon–Fri: 10:00–22:00, Sat–Sun: 12:00–23:00</span></label>
            <input type="text" name="hours" required value="<?= htmlspecialchars($info['opening_hours']) ?>" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
        </div>

        <div class="flex items-center gap-4">
            <button class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Save changes</button>
            <a href="admin.php" class="border border-gray-600 text-gray-300 px-6 py-3 rounded hover:bg-gray-800">← Back</a>
        </div>
    </form>
</div>
</body>
</html>