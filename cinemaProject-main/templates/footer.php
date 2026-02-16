<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/dbcon.php';
}

$footerInfo = $pdo->query("SELECT about, contact_email, contact_number, opening_hours FROM CompanyInfo LIMIT 1")->fetch();

// formatting (open hours)
$hoursFooter = $footerInfo['opening_hours'] ?? '';
$hoursFormattedFooter = '';
if ($hoursFooter) {
    $parts = explode(', ', $hoursFooter);
    $monFri = isset($parts[0]) ? str_replace('Mon–Fri:', 'Monday - Friday:', $parts[0]) : '';
    $satSun = isset($parts[1]) ? str_replace('Sat–Sun:', 'Saturday - Sunday:', $parts[1]) : '';
    $hoursFormattedFooter = $monFri . '<br>' . $satSun;
}
?>
<footer class="bg-gray-900 border-t border-gray-800 mt-auto">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <a href="index.php" class="text-xl font-bold flex items-center gap-2 mb-4">
                    🎬 <span class="text-white">Cine<span class="text-yellow-500">Max</span></span>
                </a>
                <p class="text-gray-400 text-sm">
                    <?= nl2br(e($footerInfo['about'] ?? 'No information available.')) ?>
                </p>
            </div>
            <div>
                <h3 class="font-semibold mb-4 text-white">Contact Us</h3>
                <div class="space-y-2 text-sm text-gray-400">
                    <div>📧 <?= e($footerInfo['contact_email'] ?? 'Not available') ?></div>
                    <div>📞 <?= e($footerInfo['contact_number'] ?? 'Not available') ?></div>
                </div>
            </div>
            <div>
                <h3 class="font-semibold mb-4 text-white">Opening Hours</h3>
                <div class="text-sm text-gray-400">
                    <?= $hoursFormattedFooter ?: 'No hours available' ?>
                </div>
            </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm text-gray-404">
            <p>&copy; 2025 CineMax. All rights reserved.</p>
        </div>
    </div>
</footer>