<?php
session_start();
$pdo = require_once 'dbcon.php';
?>

<?php require_once 'header.php';?>

<div class="py-16">
    <div class="container mx-auto px-4">
        <div class="mb-12">
            <h1 class="text-5xl font-bold mb-4">Latest <span class="text-yellow-500">News</span></h1>
            <p class="text-lg text-gray-400">Stay updated with the latest announcements, special offers, and cinema updates.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php
$q = mysqli_query($db, "SELECT news_id, title, body, created_at FROM News ORDER BY created_at DESC");
while ($r = mysqli_fetch_assoc($q)) {
    $date = date('F j, Y', strtotime($r['created_at']));
    echo '<div class="bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">';
    echo '<div class="text-sm text-gray-400 mb-3">📅 ' . htmlspecialchars($date, ENT_QUOTES) . '</div>';
    echo '<h3 class="text-xl font-bold mb-2 text-white">' . htmlspecialchars($r['title'], ENT_QUOTES) . '</h3>';
    echo '<p class="text-gray-400">' . htmlspecialchars($r['body'], ENT_QUOTES) . '</p>';
    echo '</div>';
}
?>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>