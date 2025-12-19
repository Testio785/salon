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
        ensure_schema_and_seed($pdo);
    }
    return $pdo;
}

/**
 * Build core tables if they are missing and seed demo data so the site always has
 * an admin, мастеров и базовый каталог услуг даже если init.sql не был применен вручную.
 */
function ensure_schema_and_seed(PDO $pdo): void {
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (\n" .
        "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
        "  name VARCHAR(120) NOT NULL,\n" .
        "  email VARCHAR(160) NOT NULL UNIQUE,\n" .
        "  phone VARCHAR(40),\n" .
        "  password_hash VARCHAR(255) NOT NULL,\n" .
        "  role ENUM('client','admin') DEFAULT 'client',\n" .
        "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n" .
        ");"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS masters (\n" .
        "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
        "  name VARCHAR(120) NOT NULL,\n" .
        "  specialty VARCHAR(160) NOT NULL,\n" .
        "  bio TEXT,\n" .
        "  photo_url VARCHAR(255),\n" .
        "  phone VARCHAR(40),\n" .
        "  email VARCHAR(160),\n" .
        "  hourly_rate DECIMAL(10,2) DEFAULT 0,\n" .
        "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n" .
        ");"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS services (\n" .
        "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
        "  name VARCHAR(160) NOT NULL,\n" .
        "  description TEXT,\n" .
        "  duration_minutes INT DEFAULT 60,\n" .
        "  price DECIMAL(10,2) NOT NULL,\n" .
        "  photo_url VARCHAR(255),\n" .
        "  active TINYINT(1) DEFAULT 1,\n" .
        "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n" .
        ");"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS appointments (\n" .
        "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
        "  user_id INT NOT NULL,\n" .
        "  master_id INT NOT NULL,\n" .
        "  service_id INT NOT NULL,\n" .
        "  appointment_date DATETIME NOT NULL,\n" .
        "  status ENUM('booked','cancelled','completed') DEFAULT 'booked',\n" .
        "  total_amount DECIMAL(10,2) NOT NULL,\n" .
        "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n" .
        "  CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n" .
        "  CONSTRAINT fk_master FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE CASCADE,\n" .
        "  CONSTRAINT fk_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,\n" .
        "  CONSTRAINT uniq_master_slot UNIQUE (master_id, appointment_date)\n" .
        ");"
    );

    // Ensure admin account exists with the documented credentials
    $adminExists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@nefertiti.ru'")->fetchColumn();
    if ($adminExists === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, phone, password_hash, role) VALUES (:name, :email, :phone, :password_hash, :role)'
        );
        $stmt->execute([
            ':name' => 'Администратор',
            ':email' => 'admin@nefertiti.ru',
            ':phone' => '+7 (495) 123-45-67',
            ':password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            ':role' => 'admin',
        ]);
    }

    // Seed masters if directory is empty
    $mastersCount = (int) $pdo->query('SELECT COUNT(*) FROM masters')->fetchColumn();
    if ($mastersCount === 0) {
        $masters = [
            [
                'Екатерина Смирнова',
                'Парикмахер-стилист',
                'Эксперт по блондированию и сложному окрашиванию. Более 8 лет опыта.',
                'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
                '+7 (495) 222-10-01',
                'katya@nefertiti.ru',
                2500,
            ],
            [
                'Марианна Власова',
                'Визажист',
                'Создает безупречный тон и экспресс-макеап для съемок и мероприятий.',
                'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
                '+7 (495) 222-10-02',
                'masha@nefertiti.ru',
                2000,
            ],
            [
                'Антон Громов',
                'Мастер маникюра',
                'Аппаратный маникюр, долговременное покрытие и сложный нейл-арт.',
                'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=crop&w=800&q=80',
                '+7 (495) 222-10-03',
                'anton@nefertiti.ru',
                1800,
            ],
            [
                'Виктория Ким',
                'Косметолог',
                'SPA-уходы, лифтинг-программы и безинъекционное омоложение.',
                'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=800&q=80',
                '+7 (495) 222-10-04',
                'viktoria@nefertiti.ru',
                3000,
            ],
        ];

        $stmt = $pdo->prepare('INSERT INTO masters (name, specialty, bio, photo_url, phone, email, hourly_rate) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($masters as $master) {
            $stmt->execute($master);
        }
    }

    // Seed services if empty
    $servicesCount = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($servicesCount === 0) {
        $services = [
            [
                'Окрашивание AirTouch',
                'Мягкие переливы цвета с минимальной нагрузкой на волосы. Включено восстановление.',
                180,
                9200,
                'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'Укладка Hollywood waves',
                'Голливудские волны, стойкие до 24 часов. Термозащита и фиксация включены.',
                75,
                3800,
                'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'Вечерний макияж',
                'Идеален для съемок, свадеб и торжеств. Подбор средств под тип кожи.',
                60,
                4500,
                'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'Маникюр с дизайном',
                'Аппаратный маникюр + выравнивание + сложный дизайн по эскизу.',
                90,
                3200,
                'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'SPA-уход "Золотой шелк"',
                'Детокс массаж лица, маска на основе шелка и LED-терапия.',
                70,
                6100,
                'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $stmt = $pdo->prepare('INSERT INTO services (name, description, duration_minutes, price, photo_url, active) VALUES (?, ?, ?, ?, ?, 1)');
        foreach ($services as $service) {
            $stmt->execute($service);
        }
    }

    $initialized = true;
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

function json_error_response(Throwable $e, int $status = 500): void {
    json_response(['error' => $e->getMessage()], $status);
}
