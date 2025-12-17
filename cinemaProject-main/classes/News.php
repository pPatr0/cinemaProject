<?php
class News {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getLatest(int $limit = 999): array {
        $stmt = $this->pdo->query("SELECT news_id, title, body, created_at FROM News ORDER BY created_at DESC LIMIT $limit");
        return $stmt->fetchAll();
    }
}