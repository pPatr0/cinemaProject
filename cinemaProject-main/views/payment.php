<?php
session_start();
$pdo = require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../config/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['pending_reservation'])) {
    header('Location: index.php');
    exit;
}

$pending    = $_SESSION['pending_reservation'];
$showing_id = $pending['showing_id'];
$seats      = $pending['seats'];
$user_id    = (int)$_SESSION['user_id'];

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
    unset($_SESSION['pending_reservation']);
    header('Location: index.php');
    exit;
}

$total_price = count($seats) * (int)$showing['price'];
$error = '';
$success = false;

// payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }

    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_name   = trim($_POST['card_name']   ?? '');
    $expiry      = trim($_POST['expiry']      ?? '');
    $cvv         = trim($_POST['cvv']         ?? '');

    if (strlen($card_number) !== 16 || !ctype_digit($card_number)) {
        $error = 'Invalid card number. Please enter 16 digits.';
    } elseif (empty($card_name)) {
        $error = 'Please enter cardholder name.';
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
        $error = 'Invalid expiry date. Use MM/YY format.';
    } elseif (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $error = 'Invalid CVV. Please enter 3 digits.';
    } else {
        // OOP booking check
        $resObj   = new Reservation($pdo);
        $reserved = $resObj->getTakenSeats($showing_id);
        $conflict = array_intersect($seats, array_keys($reserved));
        if ($conflict) {
            $error = 'Sorry, some seats were just booked: ' . implode(', ', $conflict);
        } else {
            // OOP save
            $seat_list = implode(',', $seats);
            if ($resObj->book($user_id, $showing_id, $seat_list)) {
                unset($_SESSION['pending_reservation']);
                header("Location: confirmation.php?success=1");
                exit;
            } else {
                $error = 'Booking failed. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold mb-8 text-white">Complete Your <span class="text-yellow-500">Payment</span></h1>
        
        <?php if ($error): ?>
            <div class="bg-red-900 border border-red-500 text-red-300 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                    <h2 class="text-xl font-bold mb-6 text-white">Payment Information</h2>
                    
                    <div class="bg-yellow-900 border border-yellow-500 text-yellow-200 px-4 py-3 rounded mb-6 text-sm">
                        <strong>Demo Mode:</strong> This is a mock payment system for testing purposes only. No real payment will be processed.
                        <br>Use any 16-digit number (e.g., 4111 1111 1111 1111)
                    </div>

                    <form method="POST" action="" id="paymentForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        
                        <div class="mb-4">
                            <label class="block text-gray-400 text-sm mb-2" for="card_number">Card Number</label>
                            <input type="text" 
                                   id="card_number" 
                                   name="card_number" 
                                   class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:border-yellow-500"
                                   placeholder="1234 5678 9012 3456"
                                   maxlength="19"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-400 text-sm mb-2" for="card_name">Cardholder Name</label>
                            <input type="text" 
                                   id="card_name" 
                                   name="card_name" 
                                   class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:border-yellow-500"
                                   placeholder="John Doe"
                                   required>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-400 text-sm mb-2" for="expiry">Expiry Date</label>
                                <input type="text" 
                                       id="expiry" 
                                       name="expiry" 
                                       class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:border-yellow-500"
                                       placeholder="MM/YY"
                                       maxlength="5"
                                       required>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm mb-2" for="cvv">CVV</label>
                                <input type="text" 
                                       id="cvv" 
                                       name="cvv" 
                                       class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:border-yellow-500"
                                       placeholder="123"
                                       maxlength="3"
                                       required>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <a href="reservation.php?id=<?= $showing_id ?>" 
                               class="flex-1 bg-gray-700 text-white py-3 rounded hover:bg-gray-600 font-bold transition text-center">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="flex-1 bg-yellow-500 text-black py-3 rounded hover:bg-yellow-600 font-bold transition">
                                Pay <?= $total_price ?> DKK
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 bg-gray-900 rounded-lg border border-gray-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Secure checkout - Your data is safe with us</span>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-4 text-white">Order Summary</h2>
                    <div class="space-y-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Movie</p>
                            <p class="font-medium text-white"><?= htmlspecialchars($showing['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Hall</p>
                            <p class="font-medium text-white"><?= htmlspecialchars($showing['hall_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Time</p>
                            <p class="font-medium text-white"><?= date('Y-m-d H:i', strtotime($showing['start_time'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Seats</p>
                            <p class="font-medium text-yellow-500"><?= implode(', ', $seats) ?></p>
                        </div>
                        <div class="pt-4 border-t border-gray-800">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-400">Seats (<?= count($seats) ?>)</span>
                                <span class="font-semibold text-white"><?= $total_price ?> DKK</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-white">Total</span>
                                <span class="text-yellow-500"><?= $total_price ?> DKK</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>