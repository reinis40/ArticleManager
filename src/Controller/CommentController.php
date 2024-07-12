<?php

namespace App\Controller;

use PDO;
use Twig\Environment;
use Monolog\Logger;

class CommentController
{
    private Environment $twig;
    private PDO $pdo;
    private Logger $logger;

    public function __construct(PDO $pdo, Environment $twig, Logger $logger)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function addComment($articleId): void
    {
        $comment = $_POST['comment'];
        $name = $_POST['name'];

        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, name, comment, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$articleId, $name, $comment, date('Y-m-d H:i:s')]);

        $this->logger->info("New comment added", ['article_id' => $articleId, 'name' => $name, 'comment' => $comment, 'timestamp' => date('Y-m-d H:i:s')]);

        header('Location: /articles/' . $articleId);
    }

    public function likeComment($id): void
    {
        $this->addLike($id, 'comment');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    private function getLikesCount($targetId, $targetType): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM likes WHERE target_id = ? AND target_type = ?');
        $stmt->execute([$targetId, $targetType]);
        return (int)$stmt->fetchColumn();
    }

    private function addLike($targetId, $targetType): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO likes (target_id, target_type, created_at) VALUES (?, ?, ?)');
        $stmt->execute([$targetId, $targetType, date('Y-m-d H:i:s')]);

        $this->logger->info("Like added", ['target_id' => $targetId, 'target_type' => $targetType, 'timestamp' => date('Y-m-d H:i:s')]);
    }
}
