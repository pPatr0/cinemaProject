<?php
session_start();

$pdo = require_once __DIR__ . '/../config/dbcon.php';

// login control
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = isset($_GET['success']);

// Reservations
$lastReservation = null;
if ($success) {
    $stmt = $pdo->prepare("
        SELECT r.reservation_id, r.seat_list, m.title, s.start_time, s.price, h.name AS hall_name
        FROM Reservation r
        JOIN Showing s ON r.showing_id = s.showing_id
        JOIN Movie m ON s.movie_id = m.movie_id
        JOIN Hall h ON s.hall_id = h.hall_id
        WHERE r.user_id = ?
        ORDER BY r.reservation_id DESC
        LIMIT 1
    ");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $lastReservation = $stmt->fetch();
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <?php if ($success && $lastReservation): ?>
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500 rounded-full mb-4">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Booking Confirmed!</h1>
                <p class="text-gray-400 text-lg">Your tickets have been successfully reserved</p>
            </div>

            <!-- Booking Details -->
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 text-white border-b border-gray-800 pb-2">Booking Details</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Confirmation Number</span>
                        <span class="font-mono font-bold text-yellow-500">#<?= str_pad($lastReservation['reservation_id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Movie</span>
                        <span class="font-semibold text-white"><?= htmlspecialchars($lastReservation['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Hall</span>
                        <span class="font-semibold text-white"><?= htmlspecialchars($lastReservation['hall_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Date & Time</span>
                        <span class="font-semibold text-white"><?= date('l, F j, Y - H:i', strtotime($lastReservation['start_time'])) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Seats</span>
                        <span class="font-semibold text-yellow-500"><?= htmlspecialchars($lastReservation['seat_list'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </div>
                    <?php 
                    $seats_count = count(explode(',', $lastReservation['seat_list']));
                    $total = $seats_count * (int)$lastReservation['price'];
                    ?>
                    <div class="flex justify-between pt-4 border-t border-gray-800">
                        <span class="text-gray-400 font-semibold">Total Paid</span>
                        <span class="font-bold text-xl text-yellow-500"><?= $total ?> DKK</span>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-yellow-900 border border-yellow-500 text-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-bold mb-2">Important Information</h3>
                <ul class="text-sm space-y-1 list-disc list-inside">
                    <li>Please arrive 15 minutes before the showing time</li>
                    <li>Bring this confirmation number to the cinema</li>
                    <li>You can view this booking in your profile anytime</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a href="profile.php" class="flex-1 bg-gray-700 text-white py-3 rounded hover:bg-gray-600 font-bold transition text-center">
                    View My Bookings
                </a>
                <a href="index.php" class="flex-1 bg-yellow-500 text-black py-3 rounded hover:bg-yellow-600 font-bold transition text-center">
                    Back to Home
                </a>
            </div>

        <?php else: ?>
            <!-- No Booking Found -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-800 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">No Booking Found</h1>
                <p class="text-gray-400 text-lg mb-6">We couldn't find any recent booking information.</p>
                <a href="index.php" class="inline-block bg-yellow-500 text-black px-6 py-3 rounded hover:bg-yellow-600 font-bold transition">
                    Browse Movies
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>