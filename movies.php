<?php
session_start();
$pdo = require_once 'dbcon.php';
require_once 'header.php';

$movies = $pdo->query("SELECT * FROM Movie ORDER BY release_year DESC")->fetchAll();
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-5xl font-bold mb-8">All <span class="text-yellow-500">Movies</span></h1>
    
    <div class="flex gap-2 mb-8 flex-wrap">
        <button class="filter-btn bg-yellow-500 text-black px-4 py-2 rounded font-bold" data-genre="all">All</button>
        <?php
        $genres = $pdo->query("SELECT DISTINCT genre FROM Movie WHERE genre IS NOT NULL ORDER BY genre")->fetchAll();
        foreach ($genres as $g) {
            echo '<button class="filter-btn bg-gray-800 text-white px-4 py-2 rounded font-bold hover:bg-gray-700" data-genre="'.htmlspecialchars($g['genre']).'">'.htmlspecialchars($g['genre']).'</button>';
        }
        ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($movies as $movie): 
            $genre = $movie['genre'] ?: 'Uncategorized';
        ?>
            <div class="movie-card bg-gray-900 rounded-lg border border-gray-800 hover:border-yellow-500 transition overflow-hidden" 
                 data-genre="<?= htmlspecialchars($genre) ?>">
                
                <div class="aspect-[2/3] bg-gray-800 overflow-hidden">
                    <img src="<?= htmlspecialchars('public/' . ($movie['poster_url'] ?: 'placeholder.jpg')) ?>" 
                         alt="<?= htmlspecialchars($movie['title']) ?>" 
                         class="w-full h-full object-cover">
                </div>
                
                <div class="p-6">
                    <span class="text-xs text-yellow-500 border border-yellow-500 px-2 py-1 rounded font-bold mr-1"><?= htmlspecialchars($genre) ?></span>
                    <span class="text-sm text-gray-400 ml-2"><?= htmlspecialchars($movie['release_year']) ?></span>
                    <h3 class="text-xl font-bold mt-2 text-white"><?= htmlspecialchars($movie['title']) ?></h3>
                    <div class="text-sm text-gray-400 mt-2">⏱️ <?= htmlspecialchars($movie['duration_min']) ?> min</div>
                    <p class="text-sm text-gray-400 mt-3"><?= htmlspecialchars($movie['description']) ?></p>
                    <a href="movie_detail.php?id=<?= htmlspecialchars($movie['movie_id']) ?>" 
                       class="block mt-4 bg-yellow-500 text-black px-4 py-2 rounded font-bold hover:bg-yellow-600 text-center">
                       Show Detail
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-yellow-500', 'text-black');
            b.classList.add('bg-gray-800', 'text-white');
        });
        this.classList.add('bg-yellow-500', 'text-black');
        
        const selectedGenre = this.getAttribute('data-genre');
        document.querySelectorAll('.movie-card').forEach(card => {
            const movieGenre = card.getAttribute('data-genre');
            card.style.display = (selectedGenre === 'all' || movieGenre === selectedGenre) ? 'block' : 'none';
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>