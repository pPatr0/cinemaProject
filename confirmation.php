<?php
require_once 'header.php';
$res_id = $_GET['res'] ?? 0;
$reservation = null;
if ($res_id) {
    $q = mysqli_query($db, "SELECT r.reservation_id, r.seat_list, m.title, h.name AS hall_name, s.start_time, s.price FROM Reservation r JOIN Showing s ON r.showing_id = s.showing_id JOIN Movie m ON s.movie_id = m.movie_id JOIN Hall h ON s.hall_id = h.hall_id WHERE r.reservation_id = " . (int)$res_id);
    $reservation = mysqli_fetch_assoc($q);
}
?>
<div class="container mx-auto px-4 py-12">
    <?php if ($reservation) { 
        $seats = explode(',', $reservation['seat_list']);
        $total = count($seats) * $reservation['price'];
    ?>
    <div class="max-w-2xl mx-auto">
        <div class="bg-green-900 border border-green-500 text-green-300 px-6 py-4 rounded-lg mb-8 text-center">
            <h1 class="text-3xl font-bold mb-2">✅ Booking Confirmed!</h1>
            <p>Your reservation has been successfully created.</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-8">
            <h2 class="text-2xl font-bold mb-6 text-white">Reservation Details</h2>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-800 pb-3">
                    <span class="text-gray-400">Movie</span>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars($reservation['title'], ENT_QUOTES); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-3">
                    <span class="text-gray-400">Hall</span>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars($reservation['hall_name'], ENT_QUOTES); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-3">
                    <span class="text-gray-400">Date & Time</span>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($reservation['start_time'])), ENT_QUOTES); ?></span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-3">
                    <span class="text-gray-400">Seats</span>
                    <span class="text-yellow-500 font-semibold"><?php echo htmlspecialchars($reservation['seat_list'], ENT_QUOTES); ?></span>
                </div>
                <div class="flex justify-between text-xl font-bold pt-4">
                    <span class="text-white">Total</span>
                    <span class="text-yellow-500"><?php echo htmlspecialchars($total, ENT_QUOTES); ?> DKK</span>
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <a href="profile.php" class="flex-1 text-center bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">View Profile</a>
                <a href="index.php" class="flex-1 text-center border border-yellow-500 text-yellow-500 px-6 py-3 rounded font-bold hover:bg-yellow-500 hover:text-black">Back to Home</a>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="text-center">
        <p class="text-xl text-white">Reservation not found.</p>
        <a href="index.php" class="mt-4 inline-block bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">Back to Home</a>
    </div>
    <?php } ?>
</div>
<?php require_once 'footer.php'; ?>