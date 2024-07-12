<?php

namespace App\Model;

use PDO;

class Comment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByArticleId($articleId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE article_id = ?');
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($articleId, $name, $comment): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, name, comment, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$articleId, $name, $comment, date('Y-m-d H:i:s')]);
    }

    public function like($id): void
    {
        $stmt = $this->pdo->prepare('UPDATE comments SET likes = likes + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
