<?php
$pdo = require_once __DIR__ . '/../config/dbcon.php';

/* HERO */
$hero = $pdo->prepare(
    "SELECT m.movie_id, m.title, m.description, m.poster_url, m.release_year
     FROM CompanyInfo ci
     JOIN Movie m ON m.movie_id = ci.hero_movie_id
     LIMIT 1"
);
$hero->execute();
$hero = $hero->fetch() ?: [];

// Upcoming Showings 
$upcomingStmt = $pdo->query(
    "SELECT s.showing_id,
            m.title,
            m.poster_url,
            m.release_year,
            s.start_time,
            s.price,
            h.name AS hall_name
     FROM Showing s
     JOIN Movie m ON s.movie_id = m.movie_id
     JOIN Hall h ON s.hall_id = h.hall_id
     WHERE DATE(s.start_time) >= CURDATE()
     ORDER BY s.start_time ASC"
);
$raw = $upcomingStmt->fetchAll();

$grouped = [];
foreach ($raw as $row) {
    $date = date('j. F Y', strtotime($row['start_time']));
    $grouped[$date][] = $row;
}

// Latest News
$news = $pdo->prepare(
    "SELECT n.news_id, n.title, n.body, n.created_at, u.name AS author
     FROM News n
     JOIN User u ON n.user_id = u.user_id
     ORDER BY n.created_at DESC
     LIMIT 3"
);
$news->execute();
$newsList = $news->fetchAll();

// about
$info = $pdo->query("SELECT about FROM CompanyInfo LIMIT 1")->fetch();
?>
<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- hero section -->
<section class="relative h-screen flex items-center justify-center bg-cover bg-center" 
         style="background-image:url('/images/<?= htmlspecialchars(basename($hero['poster_url'] ?? 'placeholder.jpg')) ?>')">
  <div class="absolute inset-0 bg-black/60"></div>
  <div class="container mx-auto px-4 z-10 text-white">
    <div class="max-w-2xl">
      <span class="bg-yellow-500 text-black px-3 py-1 rounded text-sm mb-4 inline-block font-bold">Best Seller</span>
      <h1 class="text-5xl font-bold mb-4"><?= htmlspecialchars($hero['title'] ?? '') ?></h1>
      <p class="text-lg mb-6"><?= htmlspecialchars($hero['description'] ?? '') ?></p>
      <div class="flex gap-4">
        <a href="movie_detail.php?id=<?= htmlspecialchars($hero['movie_id'] ?? 0) ?>" 
           class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600">View Details</a>
      </div>
    </div>
  </div>
</section>

<!-- upcoming showings -->
<section class="py-16 bg-black">
  <div class="container mx-auto px-4">
    <h2 class="text-4xl font-bold mb-8 text-white">Upcoming <span class="text-yellow-500">Showings</span></h2>

    <?php if (!$grouped): ?>
      <p class="text-gray-400">No upcoming showings.</p>
    <?php else: ?>
      <?php foreach ($grouped as $date => $dayShows): ?>
        <h3 class="text-2xl font-semibold text-white mt-10 mb-4"><?= htmlspecialchars($date) ?></h3>
        <div class="grid grid-cols-1 gap-6">
          <?php foreach ($dayShows as $show):
                $time = date('H:i', strtotime($show['start_time']));
          ?>
            <a href="reservation.php?id=<?= htmlspecialchars($show['showing_id']) ?>" class="block">
              <div class="bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
                <div class="flex gap-6">
                  <img src="/images/<?= htmlspecialchars(basename($show['poster_url'] ?? 'placeholder.jpg')) ?>" 
                       alt="<?= htmlspecialchars($show['title']) ?>" 
                       class="w-32 h-40 rounded-lg object-cover">
                  <div class="flex-1 flex items-start justify-between">
                    <div>
                      <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($show['title']) ?></h3>
                      <div class="flex items-center gap-4 text-sm text-gray-400">
                        <span>📍 <?= htmlspecialchars($show['hall_name']) ?></span>
                        <span>🕒 <?= htmlspecialchars($time) ?></span>
                        <span class="text-yellow-500 font-bold"><?= htmlspecialchars($show['price']) ?> DKK</span>
                      </div>
                    </div>
                    <div class="flex items-center">
                      <span class="bg-yellow-500 text-black px-3 py-1 rounded font-bold hover:bg-yellow-600">Book Seats</span>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Latest news -->
<section class="py-16 bg-black">
  <div class="container mx-auto px-4">
    <div class="mb-12 flex items-center justify-between">
      <div>
        <h2 class="text-5xl font-bold mb-4 text-white">Latest <span class="text-yellow-500">News</span></h2>
        <p class="text-lg text-gray-400">Stay updated with the latest announcements, special offers, and cinema updates.</p>
      </div>
      <a href="news.php" 
         class="bg-yellow-500 text-black px-6 py-3 rounded font-bold hover:bg-yellow-600 transition">
         See all news
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($newsList as $article):
            $date = date('F j, Y', strtotime($article['created_at']));
      ?>
        <div class="bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition p-6">
          <div class="text-sm text-gray-400 mb-3">📅 <?= htmlspecialchars($date) ?></div>
          <h3 class="text-xl font-bold mb-2 text-white"><?= htmlspecialchars($article['title']) ?></h3>
          <p class="text-gray-400"><?= nl2br(htmlspecialchars(substr($article['body'], 0, 200))) ?>…</p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- About -->
<section class="py-16 bg-black border-t border-gray-800">
  <div class="container mx-auto px-4">
    <h2 class="text-4xl font-bold mb-6 text-white">About <span class="text-yellow-500">Us</span></h2>
    <div class="bg-gray-900 rounded-lg border border-gray-800 p-8">
      <p class="text-gray-300 text-lg leading-relaxed">
        <?= nl2br(htmlspecialchars($info['about'] ?? 'No information available.')) ?>
      </p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>