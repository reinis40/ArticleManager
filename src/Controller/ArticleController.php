<?php

namespace App\Controller;

use PDO;
use Twig\Environment;
use Monolog\Logger;

class ArticleController
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

    public function list(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM articles');
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($articles as &$article) {
            $article['likes'] = $this->getLikesCount($article['id'], 'article');
        }

        return [
              'template' => 'article_list.twig',
              'data' => ['articles' => $articles]
        ];
    }

    public function show($id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return [
                  'template' => '404.twig',
                  'data' => []
            ];
        }

        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE article_id = ?');
        $stmt->execute([$id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $article['likes'] = $this->getLikesCount($article['id'], 'article');

        foreach ($comments as &$comment) {
            $comment['likes'] = $this->getLikesCount($comment['id'], 'comment');
        }

        return [
              'template' => 'article.twig',
              'data' => ['article' => $article, 'comments' => $comments]
        ];
    }

    public function like($id): void
    {
        $this->addLike($id, 'article');
        header('Location: /articles/' . $id);
    }

    public function addComment($id): void
    {
        $comment = $_POST['comment'];
        $name = $_POST['name'];

        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, name, comment, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id, $name, $comment, date('Y-m-d H:i:s')]);

        $this->logger->info("New comment added", ['article_id' => $id, 'name' => $name, 'comment' => $comment, 'timestamp' => date('Y-m-d H:i:s')]);

        header('Location: /articles/' . $id);
    }

    public function createForm(): array
    {
        return [
              'template' => 'create_article.twig',
              'data' => []
        ];
    }

    public function create(): void
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $created_at = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare('INSERT INTO articles (title, content, created_at) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $created_at]);

        $this->logger->info("New article created", ['title' => $title, 'content' => $content, 'timestamp' => $created_at]);

        header('Location: /articles');
    }

    public function edit($id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return [
                  'template' => '404.twig',
                  'data' => []
            ];
        }

        return [
              'template' => 'edit_article.twig',
              'data' => ['article' => $article]
        ];
    }

    public function update($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $content = $_POST['content'];

            $stmt = $this->pdo->prepare('UPDATE articles SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$title, $content, $id]);

            $this->logger->info("Article updated", ['id' => $id, 'title' => $title, 'content' => $content, 'timestamp' => date('Y-m-d H:i:s')]);

            header('Location: /articles/' . $id);
            exit;
        }
    }

    public function delete($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = ?');
            $stmt->execute([$id]);

            $this->logger->info("Article deleted", ['id' => $id, 'timestamp' => date('Y-m-d H:i:s')]);

            header('Location: /articles');
            exit;
        }
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

