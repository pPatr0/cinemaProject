<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'use_strict_mode' => true,  
]);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/dbcon.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineMax</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
    <header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="text-xl font-bold flex items-center gap-2">🎬 <span class="text-white">Cine<span class="text-yellow-500">Max</span></span></a>

                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="../admin/index.php" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold transition">Admin</a>
                <?php endif; ?>

                <nav class="hidden md:flex gap-6">
                    <a href="index.php" class="hover:text-yellow-500 transition">Home</a>
                    <a href="movies.php" class="hover:text-yellow-500 transition">Movies</a>
                    <a href="news.php" class="hover:text-yellow-500 transition">News</a>
                    <a href="contact.php" class="hover:text-yellow-500 transition">Contact</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="hover:text-yellow-500 transition">Profile</a>
                        <a href="logout.php" class="hover:text-yellow-500 transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-yellow-500 transition">Login</a>
                    <?php endif; ?>
                </nav>

                <button id="menuBtn" class="md:hidden text-2xl">☰</button>
            </div>
            <nav id="mobileMenu" class="hidden md:hidden border-t border-gray-800 py-4 flex-col gap-3">
                <a href="index.php" class="block hover:text-yellow-500 transition">Home</a>
                <a href="movies.php" class="block hover:text-yellow-500 transition">Movies</a>
                <a href="news.php" class="block hover:text-yellow-500 transition">News</a>
                <a href="profile.php" class="block hover:text-yellow-500 transition">Profile</a>
                <a href="contact.php" class="block hover:text-yellow-500 transition">Contact</a>
            </nav>
        </div>
    </header>
    <script>
        document.getElementById('menuBtn').onclick = function() {
            var menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
        };
    </script>