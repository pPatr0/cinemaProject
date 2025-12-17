<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /views/index.php');
    exit;
}

// Fetch all reservations
$stmt = $pdo->query(
    "SELECT 
        r.reservation_id,
        r.created_at,
        r.seat_list,
        u.name AS user_name,
        u.email AS user_email,
        m.title AS movie_title,
        s.start_time,
        s.price,
        h.name AS hall_name
     FROM Reservation r
     JOIN User u ON r.user_id = u.user_id
     JOIN Showing s ON r.showing_id = s.showing_id
     JOIN Movie m ON s.movie_id = m.movie_id
     JOIN Hall h ON s.hall_id = h.hall_id
     ORDER BY r.created_at DESC"
);
$reservations = $stmt->fetchAll();

// Calculation
foreach ($reservations as &$res) {
    $seats = explode(',', $res['seat_list']);
    $seatCount = count($seats);
    $res['total_price'] = $seatCount * $res['price'];
    $res['seat_count'] = $seatCount;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-4xl font-bold">Bookings <span class="text-yellow-500">Overview</span></h1>
        <a href="../index.php" class="border border-gray-600 text-gray-300 px-4 py-2 rounded hover:bg-gray-800">← Back</a>
    </div>

    <div class="mb-6 bg-gray-900 border border-gray-800 rounded p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-3xl font-bold text-yellow-500"><?= count($reservations) ?></div>
                <div class="text-sm text-gray-400">Total Bookings</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-green-500">
                    <?php
                    $totalRevenue = array_sum(array_column($reservations, 'total_price'));
                    echo number_format($totalRevenue, 2);
                    ?>
                </div>
                <div class="text-sm text-gray-400">Total Revenue (DKK)</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-blue-500">
                    <?= array_sum(array_column($reservations, 'seat_count')) ?>
                </div>
                <div class="text-sm text-gray-400">Total Seats Booked</div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-gray-900 border border-gray-800 rounded">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left">Booking ID</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Movie</th>
                    <th class="px-4 py-3 text-left">Showing</th>
                    <th class="px-4 py-3 text-left">Seats</th>
                    <th class="px-4 py-3 text-left">Price</th>
                    <th class="px-4 py-3 text-left">Booked</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$reservations): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-gray-400 text-center">No bookings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800">
                            <td class="px-4 py-3 font-mono text-sm">#<?= htmlspecialchars($r['reservation_id']) ?></td>
                            <td class="px-4 py-3">
                                <div class="font-semibold"><?= htmlspecialchars($r['user_name']) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($r['user_email']) ?></div>
                            </td>
                            <td class="px-4 py-3"><?= htmlspecialchars($r['movie_title']) ?></td>
                            <td class="px-4 py-3">
                                <div><?= htmlspecialchars($r['hall_name']) ?></div>
                                <div class="text-xs text-gray-400">
                                    <?= htmlspecialchars(date('d.m.Y H:i', strtotime($r['start_time']))) ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-mono"><?= htmlspecialchars($r['seat_list']) ?></div>
                                <div class="text-xs text-gray-400"><?= $r['seat_count'] ?> seat(s)</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold"><?= number_format($r['total_price'], 2) ?> DKK</div>
                                <div class="text-xs text-gray-400"><?= number_format($r['price'], 2) ?> DKK/seat</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                <?= htmlspecialchars(date('d.m.Y H:i', strtotime($r['created_at']))) ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="invoice.php?id=<?= $r['reservation_id'] ?>" 
                                       class="bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-1 rounded text-sm font-semibold">
                                        Invoice
                                    </a>
                                    <a href="delete.php?id=<?= $r['reservation_id'] ?>" 
                                       class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                                       onclick="return confirm('Delete this booking?')">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>