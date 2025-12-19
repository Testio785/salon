<?php
require_once __DIR__ . '/../config.php';
ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Метод не поддерживается'], 405);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = db()->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['error' => 'Неверный email или пароль'], 401);
    exit;
}

$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
];

json_response(['message' => 'Вход выполнен', 'user' => $_SESSION['user']]);
