<?php
session_start();
$pdo = require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../config/autoload.php';

$showing_id = (int)($_GET['id'] ?? 0);
$error      = '';

$showing = $pdo->prepare("
    SELECT s.showing_id, m.title, s.start_time, s.price, h.name AS hall_name
    FROM Showing s
    JOIN Movie m ON s.movie_id = m.movie_id
    JOIN Hall h ON s.hall_id = h.hall_id
    WHERE s.showing_id = ?
");
$showing->execute([$showing_id]);
$showing = $showing->fetch();
if (!$showing) {
    http_response_code(404);
    exit('Showing not found');
}

// OOP booked seats
$resObj   = new Reservation($pdo);
$reserved = $resObj->getTakenSeats($showing_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }

    if (!isset($_SESSION['user_id'])) {
        $error = 'You must be logged in to book.';
    } else {
        $raw   = $_POST['seats'] ?? '';
        $seats = array_filter(array_map('trim', explode(',', $raw)));
        if (!$seats) {
            $error = 'No seats selected.';
        } elseif (count($seats) > 5) {
            $error = 'Maximum 5 seats per booking.';
        } else {
            $conflict = array_intersect($seats, array_keys($reserved));
            if ($conflict) {
                $error = 'Seats already taken: ' . implode(', ', $conflict);
            } else {
                // Save to session
                $_SESSION['pending_reservation'] = [
                    'showing_id' => $showing_id,
                    'seats'      => $seats
                ];
                header("Location: payment.php");
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <?php if ($error): ?>
        <div class="bg-red-900 border border-red-500 text-red-300 px-4 py-3 rounded mb-6">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($showing): ?>
        <h1 class="text-5xl font-bold mb-8 text-white">Select Your <span class="text-yellow-500">Seats</span></h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                    <h2 class="text-xl font-bold mb-4 text-white">Cinema Hall</h2>
                    <div class="flex items-center gap-4 text-sm mb-6">
                        <div class="flex items-center gap-2"><div class="w-6 h-6 rounded bg-gray-800 border border-gray-700"></div><span class="text-gray-400">Available</span></div>
                        <div class="flex items-center gap-2"><div class="w-6 h-6 rounded bg-yellow-500"></div><span class="text-gray-400">Selected</span></div>
                        <div class="flex items-center gap-2"><div class="w-6 h-6 rounded bg-red-600"></div><span class="text-gray-400">Booked</span></div>
                    </div>
                    <div class="mb-6 text-center"><div class="h-2 bg-yellow-500 rounded-full mb-2"></div><p class="text-sm text-gray-400">SCREEN</p></div>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="seats" id="seatsInput">
                        <table class="w-full border-separate" style="border-spacing: 4px;">
<?php
$rows = ['A','B','C','D','E','F','G','H'];
foreach ($rows as $row) {
    echo '<tr>';
    echo '<td class="text-center font-bold w-8 text-yellow-500">' . $row . '</td>';
    for ($j = 1; $j <= 12; $j++) {
        $seat = $row . $j;
        $disabled = isset($reserved[$seat]) ? 'disabled' : '';
        $bg       = $disabled ? 'bg-red-600 cursor-not-allowed' : 'bg-gray-800 hover:bg-yellow-500 cursor-pointer';
        echo '<td><button type="button" class="seat w-8 h-8 rounded text-xs ' . $bg . ' text-white transition" data-seat="' . e($seat) . '" ' . $disabled . '></button></td>';
    }
    echo '<td class="text-center font-bold w-8 text-yellow-500">' . $row . '</td>';
    echo '</tr>';
}
?>
                        </table>
                        <div class="mt-6 text-sm text-gray-400"><p>Maximum 5 seats per booking</p></div>
                        <div class="mt-6"><div class="font-medium text-white">Selected Seats: <span id="selectedSeats" class="text-yellow-500">None</span></div></div>
                        <button type="submit" class="mt-6 w-full bg-yellow-500 text-black py-3 rounded hover:bg-yellow-600 font-bold transition" id="confirmBtn" disabled>Proceed to Payment</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-4 text-white">Booking Summary</h2>
                    <div class="space-y-4 mb-6">
                        <div><p class="text-sm text-gray-400 mb-2">Movie</p><p class="font-medium text-white"><?= e($showing['title']) ?></p></div>
                        <div><p class="text-sm text-gray-400 mb-2">Hall</p><p class="font-medium text-white"><?= e($showing['hall_name']) ?></p></div>
                        <div><p class="text-sm text-gray-400 mb-2">Time</p><p class="font-medium text-white"><?= date('Y-m-d H:i', strtotime($showing['start_time'])) ?></p></div>
                        <div class="pt-4 border-t border-gray-800">
                            <div class="flex justify-between mb-2"><span class="text-gray-400">Seats (<span id="seatCount">0</span>)</span><span class="font-semibold text-white"><span id="totalPrice">0</span> DKK</span></div>
                            <div class="flex justify-between text-lg font-bold"><span class="text-white">Total</span><span class="text-yellow-500"><span id="finalPrice">0</span> DKK</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-xl text-white">Showing not found.</p>
    <?php endif; ?>
</div>

<script>
var selected = [];
var price = <?= (int)$showing['price'] ?>;
document.querySelectorAll('.seat:not([disabled])').forEach(function(btn) {
    btn.onclick = function() {
        var seat = this.getAttribute('data-seat');
        if (selected.indexOf(seat) === -1) {
            if (selected.length >= 5) return;
            selected.push(seat);
            this.classList.remove('bg-gray-800', 'hover:bg-yellow-500');
            this.classList.add('bg-yellow-500');
        } else {
            selected = selected.filter(function(s) { return s !== seat; });
            this.classList.remove('bg-yellow-500');
            this.classList.add('bg-gray-800', 'hover:bg-yellow-500');
        }
        document.getElementById('selectedSeats').textContent = selected.length ? selected.join(', ') : 'None';
        document.getElementById('seatsInput').value = selected.join(',');
        document.getElementById('seatCount').textContent = selected.length;
        document.getElementById('totalPrice').textContent = selected.length * price;
        document.getElementById('finalPrice').textContent = selected.length * price;
        document.getElementById('confirmBtn').disabled = selected.length === 0;
    };
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>