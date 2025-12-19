<?php
require_once __DIR__ . '/../config.php';
ensure_session();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $services = db()->query('SELECT id, name, description, duration_minutes, price, photo_url FROM services WHERE active = 1 ORDER BY price DESC')->fetchAll();
    json_response(['services' => $services]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $data = $_POST;
    $stmt = db()->prepare('INSERT INTO services (name, description, duration_minutes, price, photo_url, active) VALUES (:name, :description, :duration_minutes, :price, :photo_url, 1)');
    $stmt->execute([
        ':name' => $data['name'] ?? '',
        ':description' => $data['description'] ?? '',
        ':duration_minutes' => $data['duration_minutes'] ?? 60,
        ':price' => $data['price'] ?? 0,
        ':photo_url' => $data['photo_url'] ?? '',
    ]);
    json_response(['message' => 'Услуга добавлена', 'id' => db()->lastInsertId()]);
    exit;
}

json_response(['error' => 'Метод не поддерживается'], 405);
