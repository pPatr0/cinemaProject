<?php
$pdo = require_once __DIR__ . '/../config/dbcon.php';

$movie_id = (int)($_GET['id'] ?? 0);

//info
$movie = $pdo->prepare("
    SELECT m.movie_id, m.title, m.duration_min, m.release_year,
           m.poster_url, m.description, m.genre
    FROM Movie m
    WHERE m.movie_id = ?
");
$movie->execute([$movie_id]);
$movie = $movie->fetch();

// showings
$showings = $pdo->prepare("
    SELECT s.showing_id, s.start_time, s.price, h.name AS hall_name
    FROM Showing s
    JOIN Hall h ON s.hall_id = h.hall_id
    WHERE s.movie_id = ? AND s.start_time >= NOW()
    ORDER BY s.start_time ASC
");
$showings->execute([$movie_id]);
$showings = $showings->fetchAll();

// date grouping
$grouped = [];
foreach ($showings as $show) {
    $date = date('j. F Y', strtotime($show['start_time']));
    $grouped[$date][] = $show;
}

require_once __DIR__ . '/../templates/header.php';
?>

<?php if ($movie): ?>
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <img src="../public/images/<?= htmlspecialchars(basename($movie['poster_url'] ?? 'placeholder.jpg')) ?>"
                     alt="<?= htmlspecialchars($movie['title']) ?>"
                     class="w-full rounded-lg shadow-2xl object-cover">
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xs text-yellow-500 border border-yellow-500 px-2 py-1 rounded font-bold">
                    <?= htmlspecialchars($movie['genre'] ?? 'Uncategorized') ?>
                </span>
                <span class="text-gray-400"><?= htmlspecialchars($movie['release_year']) ?></span>
            </div>

            <h1 class="text-5xl font-bold mb-4 text-white"><?= htmlspecialchars($movie['title']) ?></h1>

            <div class="flex items-center gap-2 text-gray-400 mb-6">
                <span class="text-yellow-500">⏱️</span>
                <span class="text-lg"><?= htmlspecialchars($movie['duration_min']) ?> min</span>
            </div>

            <p class="text-lg text-gray-400 leading-relaxed mb-8">
                <?= nl2br(htmlspecialchars($movie['description'])) ?>
            </p>

            <div>
                <h2 class="text-2xl font-bold mb-6 text-white">Upcoming <span class="text-yellow-500">Showings</span></h2>

                <?php if (!$grouped): ?>
                    <p class="text-gray-400">No upcoming showings.</p>
                <?php else: ?>
                    <?php foreach ($grouped as $date => $dayShowings): ?>
                        <h3 class="text-xl font-semibold text-white mt-6 mb-3"><?= htmlspecialchars($date) ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($dayShowings as $show):
                                $time = date('H:i', strtotime($show['start_time']));
                            ?>
                                <div class="bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-yellow-500">📍</span>
                                        <span class="font-semibold text-white"><?= htmlspecialchars($show['hall_name']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="text-yellow-500">🕒</span>
                                        <span class="text-gray-400"><?= htmlspecialchars($time) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-bold text-yellow-500"><?= htmlspecialchars($show['price']) ?> DKK</span>
                                        <a href="reservation.php?id=<?= htmlspecialchars($show['showing_id']) ?>"
                                           class="bg-yellow-500 text-black px-4 py-2 rounded hover:bg-yellow-600 font-bold transition"
                                           onclick="event.stopPropagation()">Book Seats</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="container mx-auto px-4 py-12">
    <p class="text-xl text-white">Movie not found</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>