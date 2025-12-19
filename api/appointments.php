<?php
require_once __DIR__ . '/../config.php';
ensure_session();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $masterId = isset($_GET['master_id']) ? (int)$_GET['master_id'] : null;
        $date = $_GET['date'] ?? null;
        $user = current_user();

        // Admin can view all, client only own
        if ($user && ($user['role'] ?? 'client') === 'admin') {
            $query = 'SELECT a.id, a.appointment_date, a.status, a.total_amount, m.name as master_name, s.name as service_name, u.name as client_name, u.phone, u.email FROM appointments a JOIN masters m ON a.master_id = m.id JOIN services s ON a.service_id = s.id JOIN users u ON a.user_id = u.id ORDER BY a.appointment_date DESC';
            $appointments = db()->query($query)->fetchAll();
            json_response(['appointments' => $appointments]);
            exit;
        }

        if ($masterId && $date) {
            $stmt = db()->prepare('SELECT TIME(appointment_date) as time FROM appointments WHERE master_id = :master_id AND DATE(appointment_date) = :date AND status = "booked"');
            $stmt->execute([':master_id' => $masterId, ':date' => $date]);
            $times = $stmt->fetchAll(PDO::FETCH_COLUMN);
            json_response(['booked' => $times]);
            exit;
        }

        require_login();
        $stmt = db()->prepare('SELECT a.id, a.appointment_date, a.status, a.total_amount, m.name as master_name, s.name as service_name FROM appointments a JOIN masters m ON a.master_id = m.id JOIN services s ON a.service_id = s.id WHERE a.user_id = :user_id ORDER BY a.appointment_date DESC');
        $stmt->execute([':user_id' => $user['id']]);
        json_response(['appointments' => $stmt->fetchAll()]);
        exit;
    }

    if ($method === 'POST') {
        require_login();
        $input = $_POST;
        $user = current_user();
        $serviceId = (int)($input['service_id'] ?? 0);
        $masterId = (int)($input['master_id'] ?? 0);
        $dateTime = $input['appointment_date'] ?? '';

        if (!$serviceId || !$masterId || !$dateTime) {
            json_response(['error' => 'Заполните данные записи'], 422);
            exit;
        }

        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime) ?: DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
        if (!$dt) {
            json_response(['error' => 'Неверный формат даты'], 422);
            exit;
        }

        $check = db()->prepare('SELECT COUNT(*) FROM appointments WHERE master_id = :master_id AND appointment_date = :appointment_date AND status = "booked"');
        $check->execute([':master_id' => $masterId, ':appointment_date' => $dt->format('Y-m-d H:i:s')]);
        if ($check->fetchColumn() > 0) {
            json_response(['error' => 'Мастер занят в это время'], 409);
            exit;
        }

        $serviceStmt = db()->prepare('SELECT price FROM services WHERE id = :id');
        $serviceStmt->execute([':id' => $serviceId]);
        $servicePrice = $serviceStmt->fetchColumn();

        if ($servicePrice === false) {
            json_response(['error' => 'Услуга не найдена'], 404);
            exit;
        }

        $insert = db()->prepare('INSERT INTO appointments (user_id, master_id, service_id, appointment_date, status, total_amount) VALUES (:user_id, :master_id, :service_id, :appointment_date, "booked", :total_amount)');
        $insert->execute([
            ':user_id' => $user['id'],
            ':master_id' => $masterId,
            ':service_id' => $serviceId,
            ':appointment_date' => $dt->format('Y-m-d H:i:s'),
            ':total_amount' => $servicePrice,
        ]);

        json_response(['message' => 'Запись создана', 'id' => db()->lastInsertId()]);
        exit;
    }

    if ($method === 'PATCH') {
        ensure_session();
        parse_str(file_get_contents('php://input'), $body);
        $appointmentId = (int)($body['id'] ?? 0);
        $user = current_user();

        if (!$appointmentId) {
            json_response(['error' => 'Не передан идентификатор'], 422);
            exit;
        }

        $query = 'SELECT user_id FROM appointments WHERE id = :id';
        $stmt = db()->prepare($query);
        $stmt->execute([':id' => $appointmentId]);
        $ownerId = $stmt->fetchColumn();

        if ($ownerId === false) {
            json_response(['error' => 'Запись не найдена'], 404);
            exit;
        }

        if (!$user || ($user['role'] !== 'admin' && $ownerId != $user['id'])) {
            json_response(['error' => 'Недостаточно прав'], 403);
            exit;
        }

        $update = db()->prepare('UPDATE appointments SET status = "cancelled" WHERE id = :id');
        $update->execute([':id' => $appointmentId]);
        json_response(['message' => 'Запись отменена']);
        exit;
    }

    json_response(['error' => 'Метод не поддерживается'], 405);
} catch (Throwable $e) {
    json_error_response($e, 500);
}
