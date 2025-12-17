<?php
class Reservation {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getTakenSeats(int $showingId): array {
        $stmt = $this->pdo->prepare("SELECT seat_list FROM Reservation WHERE showing_id = ?");
        $stmt->execute([$showingId]);
        $taken = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $list) {
            foreach (explode(',', $list) as $s) $taken[trim($s)] = true;
        }
        return $taken;
    }

    public function book(int $userId, int $showingId, string $seatList): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO Reservation (user_id, showing_id, seat_list) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$userId, $showingId, $seatList]);
    }
}