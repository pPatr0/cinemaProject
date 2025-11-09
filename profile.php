<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$pdo = require_once 'dbcon.php';

$user_id = $_SESSION['user_id'];

// user i
$userStmt = $pdo->prepare("SELECT name, email FROM User WHERE user_id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch();

// user reservations
$resStmt = $pdo->prepare("
    SELECT r.reservation_id, m.title, h.name AS hall_name, s.start_time, r.seat_list, s.price
    FROM Reservation r
    JOIN Showing s ON r.showing_id = s.showing_id
    JOIN Movie m ON s.movie_id = m.movie_id
    JOIN Hall h ON s.hall_id = h.hall_id
    WHERE r.user_id = ?
    ORDER BY s.start_time DESC
");
$resStmt->execute([$user_id]);
$reservations = $resStmt->fetchAll();

require_once 'header.php';
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-5xl font-bold mb-8 text-white">My <span class="text-yellow-500">Profile</span></h1>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h2 class="text-xl font-bold mb-6 text-white">Account Information</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-yellow-500 flex items-center justify-center text-2xl">👤</div>
                        <div>
                            <p class="font-semibold text-white"><?= htmlspecialchars($user['name']) ?></p>
                            <p class="text-sm text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-800">
                        <span class="text-yellow-500">📧</span>
                        <span class="text-gray-400"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="pt-4 border-t border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Total Bookings</span>
                            <span class="bg-yellow-500 text-black px-3 py-1 rounded font-bold"><?= count($reservations) ?></span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-800">
                        <a href="logout.php" class="w-full text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded font-bold block">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h2 class="text-xl font-bold mb-6 text-white">Booking History</h2>
                <div class="space-y-4">
                    <?php if (!$reservations): ?>
                        <p class="text-gray-400">No bookings yet.</p>
                    <?php else: ?>
                        <?php foreach ($reservations as $r):
                            $date = date('Y-m-d H:i', strtotime($r['start_time']));
                            $seats = explode(',', $r['seat_list']);
                            $total = count($seats) * $r['price'];
                        ?>
                            <div class="bg-gray-800 rounded-lg border border-gray-700 hover:border-yellow-500 transition p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="space-y-3 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-yellow-500">🎟️</span>
                                            <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($r['title']) ?></h3>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                            <div class="flex items-center gap-2 text-gray-400">
                                                <span class="text-yellow-500">📍</span>
                                                <span><?= htmlspecialchars($r['hall_name']) ?></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-gray-400">
                                                <span class="text-yellow-500">📅</span>
                                                <span><?= htmlspecialchars($date) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-yellow-500 border border-yellow-500 px-2 py-1 rounded">Seats: <?= htmlspecialchars($r['seat_list']) ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-yellow-500"><?= htmlspecialchars($total) ?> DKK</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>