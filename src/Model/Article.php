<?php

namespace App\Model;

use PDO;

class Article
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM articles');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        return $article ?: null;
    }

    public function create($title, $content): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO articles (title, content, created_at) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, date('Y-m-d H:i:s')]);
    }

    public function update($id, $title, $content): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET title = ?, content = ? WHERE id = ?');
        $stmt->execute([$title, $content, $id]);
    }

    public function delete($id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function like($id): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET likes = likes + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
