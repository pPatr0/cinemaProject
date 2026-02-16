<?php
$pdo = require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../config/autoload.php';
$newsObj = new News($pdo);

require_once __DIR__ . '/../templates/header.php';
?>

<div class="py-16">
    <div class="container mx-auto px-4">
        <div class="mb-12">
            <h1 class="text-5xl font-bold mb-4">Latest <span class="text-yellow-500">News</span></h1>
            <p class="text-lg text-gray-400">Stay updated with the latest announcements, special offers, and cinema updates.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($newsObj->getLatest() as $r):
                $date = date('F j, Y', strtotime($r['created_at'])); ?>
                <div class="bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                    <div class="text-sm text-gray-400 mb-3">📅 <?= e($date) ?></div>
                    <h3 class="text-xl font-bold mb-2 text-white"><?= e($r['title']) ?></h3>
                    <p class="text-gray-400"><?= e($r['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>