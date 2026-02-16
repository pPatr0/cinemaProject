<?php
$pdo = require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../templates/header.php';

$info = $pdo->query("SELECT about, contact_email, contact_number, opening_hours, address FROM CompanyInfo LIMIT 1")->fetch();

$messageSent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }

    $name    = htmlspecialchars(trim($_POST['name']   ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $email   = htmlspecialchars(trim($_POST['email']  ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $subject = htmlspecialchars(trim($_POST['subject']?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if ($name && $email && $subject && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $to      = 'netflixucet25@gmail.com';
        $headers = "From: $email\r\nContent-Type: text/html; charset=UTF-8";
        $body    = "
            <h3>Cinema Contact Form</h3>
            <p><b>Name:</b> $name</p>
            <p><b>Email:</b> $email</p>
            <p><b>Message:</b><br>$message</p>
        ";
        $messageSent = mail($to, $subject, $body, $headers);
    } else {
        $messageSent = false;
    }
}

$hours = $info['opening_hours'] ?? '';
$hoursFormatted = '';
if ($hours) {
    $parts = explode(', ', $hours);
    $monFri = isset($parts[0]) ? str_replace('Mon–Fri:', 'Monday-Friday:', $parts[0]) : '';
    $satSun = isset($parts[1]) ? str_replace('Sat–Sun:', 'Saturday-Sunday:', $parts[1]) : '';
    $hoursFormatted = $monFri . '<br>' . $satSun;
}
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-5xl font-bold mb-8">Contact <span class="text-yellow-500">Us</span></h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2 bg-gray-900 p-6 rounded-lg border border-gray-800">
            <h3 class="text-2xl font-bold text-yellow-500 mb-4">Send us a message</h3>

            <?php if ($messageSent): ?>
                <div class="bg-green-900 text-green-200 p-4 rounded mb-4">
                    ✅ Message sent successfully! We'll get back to you soon.
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="contact_form" value="1">

                <div>
                    <label class="block text-gray-300 mb-2">Your Name:</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Your Email:</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Subject:</label>
                    <input type="text" name="subject" required
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Message:</label>
                    <textarea name="message" rows="5" required
                              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded text-white"></textarea>
                </div>
                <button type="submit"
                        class="w-full bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">
                    Send Message
                </button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="flex flex-col gap-8">
            <div class="bg-gray-900 p-6 rounded-lg border border-gray-800 flex-1">
                <h3 class="text-2xl font-bold text-yellow-500 mb-4">Contact Information</h3>
                <div class="space-y-4 text-gray-300">
                    <div>
                        <span class="text-yellow-500 font-bold">Email:</span><br>
                        <a href="mailto:<?= e($info['contact_email']) ?>"
                           class="hover:text-yellow-500">
                            <?= e($info['contact_email']) ?>
                        </a>
                    </div>
                    <div>
                        <span class="text-yellow-500 font-bold">Phone:</span><br>
                        <?= e($info['contact_number']) ?>
                    </div>
                    <div>
                        <span class="text-yellow-500 font-bold">Address:</span><br>
                        <?= nl2br(e($info['address'] ?? 'No address available.')) ?>
                    </div>
                </div>
            </div>
            <div class="bg-gray-900 p-6 rounded-lg border border-gray-800 flex-1">
                <h3 class="text-2xl font-bold text-yellow-500 mb-4">Opening Hours</h3>
                <p class="text-gray-300">
                    <?= $hoursFormatted ?: 'No hours available.' ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>