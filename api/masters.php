<?php
require_once __DIR__ . '/../config.php';
ensure_session();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $masters = db()->query('SELECT id, name, specialty, bio, photo_url, phone, email, hourly_rate FROM masters ORDER BY name')->fetchAll();
    json_response(['masters' => $masters]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $data = $_POST;
    $stmt = db()->prepare('INSERT INTO masters (name, specialty, bio, photo_url, phone, email, hourly_rate) VALUES (:name, :specialty, :bio, :photo_url, :phone, :email, :hourly_rate)');
    $stmt->execute([
        ':name' => $data['name'] ?? '',
        ':specialty' => $data['specialty'] ?? '',
        ':bio' => $data['bio'] ?? '',
        ':photo_url' => $data['photo_url'] ?? '',
        ':phone' => $data['phone'] ?? '',
        ':email' => $data['email'] ?? '',
        ':hourly_rate' => $data['hourly_rate'] ?? 0,
    ]);
    json_response(['message' => 'Мастер добавлен', 'id' => db()->lastInsertId()]);
    exit;
}

json_response(['error' => 'Метод не поддерживается'], 405);
