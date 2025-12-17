<?php
class Movie {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getAllOrderedByYear(): array {
        return $this->pdo->query("SELECT * FROM Movie ORDER BY release_year DESC")->fetchAll();
    }

    public function getGenres(): array {
        return $this->pdo->query("SELECT DISTINCT genre FROM Movie WHERE genre IS NOT NULL ORDER BY genre")->fetchAll();
    }
}