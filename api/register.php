<?php
require_once __DIR__ . '/../config.php';
ensure_session();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'Метод не поддерживается'], 405);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        json_response(['error' => 'Заполните все обязательные поля'], 422);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (:name, :email, :phone, :password_hash, "client")');
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    $_SESSION['user'] = [
        'id' => (int)db()->lastInsertId(),
        'name' => $name,
        'email' => $email,
        'role' => 'client',
    ];
    json_response(['message' => 'Регистрация успешна', 'user' => $_SESSION['user']]);
} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        json_response(['error' => 'Пользователь с таким email уже существует'], 409);
    } else {
        json_error_response($e, 500);
    }
} catch (Throwable $e) {
    json_error_response($e, 500);
}
