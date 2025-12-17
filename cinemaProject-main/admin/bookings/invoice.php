<?php
session_start();
$pdo = require_once __DIR__ . '/../../config/dbcon.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /views/index.php');
    exit;
}

$reservationId = (int)($_GET['id'] ?? 0);
if (!$reservationId) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT 
        r.reservation_id, r.created_at, r.seat_list,
        u.name AS user_name, u.email AS user_email,
        m.title AS movie_title, m.duration_min,
        s.start_time, s.price AS ticket_price,
        h.name AS hall_name,
        ci.contact_email, ci.contact_number, ci.address
     FROM Reservation r
     JOIN User u ON r.user_id = u.user_id
     JOIN Showing s ON r.showing_id = s.showing_id
     JOIN Movie m ON s.movie_id = m.movie_id
     JOIN Hall h ON s.hall_id = h.hall_id
     LEFT JOIN CompanyInfo ci ON 1=1
     WHERE r.reservation_id = ?
     LIMIT 1"
);
$stmt->execute([$reservationId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit;
}

$seats = explode(',', $booking['seat_list']);
$seatCount = count($seats);
$totalPrice = $seatCount * $booking['ticket_price'];
$invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($invoiceNumber) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @media print {
            body { margin: 0; padding: 12mm; font-size: 12pt; }
            .no-print { display: none !important; }
            .invoice { padding: 0; box-shadow: none; }
            h1 { font-size: 24pt; margin: 0 0 6mm; }
            h2 { font-size: 14pt; margin: 4mm 0; }
            table { font-size: 11pt; }
            .header { margin-bottom: 6mm; }
            .section { margin: 4mm 0; }
            p { margin: 2mm 0; }
        }
        @page { size: A4; margin: 10mm; }
    </style>
</head>
<body class="bg-black text-white">
<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <!-- Buttons -->
    <div class="no-print flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Invoice <span class="text-yellow-500">#<?= htmlspecialchars($invoiceNumber) ?></span></h1>
        <div class="flex gap-2">
            <button onclick="downloadPDF()" class="bg-yellow-500 text-black px-4 py-2 rounded font-bold hover:bg-yellow-600">
                Download PDF
            </button>
            <button onclick="window.print()" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-600">
                Print
            </button>
            <a href="index.php" class="border border-gray-600 text-gray-300 px-4 py-2 rounded hover:bg-gray-800">← Back</a>
        </div>
    </div>

    <!-- Invoice -->
    <div id="invoice" class="invoice bg-white text-black p-10 rounded">
        
        <!-- Header -->
        <div class="header flex justify-between border-b-2 pb-5 mb-5">
            <div>
                <h1 class="text-3xl font-bold">CineMax</h1>
                <p class="text-sm text-gray-600">Premium Cinema Experience</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-yellow-600">INVOICE</h2>
                <p class="text-sm">No: <?= htmlspecialchars($invoiceNumber) ?></p>
                <p class="text-sm">Date: <?= date('d.m.Y') ?></p>
            </div>
        </div>

        <!-- From/To -->
        <div class="section grid grid-cols-2 gap-6 mb-5">
            <div>
                <h2 class="font-bold mb-2">From:</h2>
                <p class="font-semibold">CineMax Cinema</p>
                <p class="text-sm"><?= htmlspecialchars($booking['address'] ?? 'N/A') ?></p>
                <p class="text-sm"><?= htmlspecialchars($booking['contact_email'] ?? 'N/A') ?></p>
            </div>
            <div>
                <h2 class="font-bold mb-2">Bill To:</h2>
                <p class="font-semibold"><?= htmlspecialchars($booking['user_name']) ?></p>
                <p class="text-sm"><?= htmlspecialchars($booking['user_email']) ?></p>
                <p class="text-sm">Booked: <?= date('d.m.Y H:i', strtotime($booking['created_at'])) ?></p>
            </div>
        </div>

        <!-- Showing Details -->
        <div class="section bg-gray-100 p-4 rounded mb-5">
            <p><strong>Movie:</strong> <?= htmlspecialchars($booking['movie_title']) ?> (<?= $booking['duration_min'] ?> min)</p>
            <p><strong>Showing:</strong> <?= date('d.m.Y H:i', strtotime($booking['start_time'])) ?> - <?= htmlspecialchars($booking['hall_name']) ?></p>
            <p><strong>Seats:</strong> <?= htmlspecialchars($booking['seat_list']) ?> (<?= $seatCount ?> tickets)</p>
        </div>

        <!-- Items Table -->
        <table class="w-full border border-gray-300 mb-5">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="px-4 py-3"><?= htmlspecialchars($booking['movie_title']) ?></td>
                    <td class="px-4 py-3 text-center"><?= $seatCount ?></td>
                    <td class="px-4 py-3 text-right"><?= number_format($booking['ticket_price'], 2) ?> DKK</td>
                    <td class="px-4 py-3 text-right font-bold"><?= number_format($totalPrice, 2) ?> DKK</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-5">
            <div class="w-64">
                <div class="flex justify-between py-2 font-bold text-lg bg-yellow-100 px-2 rounded">
                    <span>Total:</span>
                    <span><?= number_format($totalPrice, 2) ?> DKK</span>
                </div>
                <p class="text-xs text-gray-500 mt-2 text-right">VAT (25%) included: <?= number_format($totalPrice * 0.25 / 1.25, 2) ?> DKK</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t pt-4 text-center text-sm text-gray-600">
            <p><strong>Payment Status: ✓ PAID</strong></p>
            <p class="mt-2">Thank you for choosing CineMax Cinema!</p>
        </div>
    </div>
</div>

<script>
function downloadPDF() {
    const el = document.getElementById('invoice');
    html2pdf().set({
        margin: 10,
        filename: 'invoice-<?= htmlspecialchars($invoiceNumber) ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(el).save();
}
</script>
</body>
</html>