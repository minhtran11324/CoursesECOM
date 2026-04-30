<?php
require_once __DIR__ . '/../../backend/config/database.php';

$newsItems = [];
$db = $pdo;

if ($db instanceof PDO) {
    try {
        $stmt = $db->prepare(
            'SELECT post_id, title, slug, content, thumbnail, category, created_at
             FROM posts
             WHERE status = 1 AND category = :category
             ORDER BY created_at DESC
             LIMIT 3'
        );
        $stmt->execute(['category' => 'news']);
        $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $newsItems = [];
    }
}
?>