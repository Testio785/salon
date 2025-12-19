<?php
// Database configuration and shared helpers for the Nefertiti salon information system.

const DB_DSN = 'mysql:host=localhost;dbname=nefertiti_salon;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASSWORD = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user(): ?array {
    ensure_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        http_response_code(401);
        echo json_encode(['error' => 'Требуется авторизация']);
        exit;
    }
}

function require_admin(): void {
    $user = current_user();
    if (!$user || ($user['role'] ?? 'client') !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Недостаточно прав']);
        exit;
    }
}

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}
