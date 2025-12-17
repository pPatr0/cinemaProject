<?php
session_start();
$pdo = require_once __DIR__ . '/../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /views/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
    <div class="container mx-auto px-4 py-12">
        <h1 class="text-5xl font-bold mb-8">Admin <span class="text-yellow-500">Dashboard</span></h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="company/info.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">Company Info</h2>
                <p class="text-gray-400 text-sm">Edit description, opening hours & contact.</p>
            </a>

            <a href="movies/index.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">Movies</h2>
                <p class="text-gray-400 text-sm">Add, edit or delete movies.</p>
            </a>

            <a href="showings/index.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">Showings</h2>
                <p class="text-gray-400 text-sm">Manage daily screenings.</p>
            </a>

            <a href="company/hero.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">Hero Showing</h2>
                <p class="text-gray-400 text-sm">Pick which showing appears on the front page.</p>
            </a>

            <a href="news/index.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">News</h2>
                <p class="text-gray-400 text-sm">Publish or edit news articles.</p>
            </a>

            <a href="bookings/index.php" class="block bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <h2 class="text-xl font-bold mb-2">Bookings & Invoices</h2>
                <p class="text-gray-400 text-sm">View all bookings and generate invoices.</p>
            </a>

            <a href="../views/index.php" class="block bg-yellow-500 text-black rounded-lg p-6 hover:bg-yellow-600 transition">
                <h2 class="text-xl font-bold mb-2">← Back to CineMax</h2>
                <p class="text-black/80 text-sm">Return to the public site.</p>
            </a>
        </div>
    </div>
</body>
</html>