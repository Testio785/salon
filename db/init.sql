CREATE DATABASE IF NOT EXISTS nefertiti_salon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nefertiti_salon;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(40),
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('client','admin') DEFAULT 'client',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS masters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  specialty VARCHAR(160) NOT NULL,
  bio TEXT,
  photo_url VARCHAR(255),
  phone VARCHAR(40),
  email VARCHAR(160),
  hourly_rate DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  description TEXT,
  duration_minutes INT DEFAULT 60,
  price DECIMAL(10,2) NOT NULL,
  photo_url VARCHAR(255),
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  master_id INT NOT NULL,
  service_id INT NOT NULL,
  appointment_date DATETIME NOT NULL,
  status ENUM('booked','cancelled','completed') DEFAULT 'booked',
  total_amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_master FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE CASCADE,
  CONSTRAINT fk_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  CONSTRAINT uniq_master_slot UNIQUE (master_id, appointment_date)
);

INSERT INTO users (name, email, phone, password_hash, role) VALUES
('Администратор', 'admin@nefertiti.ru', '+7 (495) 123-45-67', '$2y$10$e0NRyMlGrCVloBXXtCvSOOrHltMgQs6i80x71NBOhr9uzJ5Fmyc/K', 'admin');

INSERT INTO masters (name, specialty, bio, photo_url, phone, email, hourly_rate) VALUES
('Екатерина Смирнова', 'Парикмахер-стилист', 'Эксперт по блондированию и сложному окрашиванию. Более 8 лет опыта.', 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80', '+7 (495) 222-10-01', 'katya@nefertiti.ru', 2500),
('Марианна Власова', 'Визажист', 'Создает безупречный тон и экспресс-макеап для съемок и мероприятий.', 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80', '+7 (495) 222-10-02', 'masha@nefertiti.ru', 2000),
('Антон Громов', 'Мастер маникюра', 'Аппаратный маникюр, долговременное покрытие и сложный нейл-арт.', 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=crop&w=800&q=80', '+7 (495) 222-10-03', 'anton@nefertiti.ru', 1800),
('Виктория Ким', 'Косметолог', 'SPA-уходы, лифтинг-программы и безинъекционное омоложение.', 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=800&q=80', '+7 (495) 222-10-04', 'viktoria@nefertiti.ru', 3000);

INSERT INTO services (name, description, duration_minutes, price, photo_url) VALUES
('Окрашивание AirTouch', 'Мягкие переливы цвета с минимальной нагрузкой на волосы. Включено восстановление.', 180, 9200, 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80'),
('Укладка Hollywood waves', 'Голливудские волны, стойкие до 24 часов. Термозащита и фиксация включены.', 75, 3800, 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80'),
('Вечерний макияж', 'Идеален для съемок, свадеб и торжеств. Подбор средств под тип кожи.', 60, 4500, 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80'),
('Маникюр с дизайном', 'Аппаратный маникюр + выравнивание + сложный дизайн по эскизу.', 90, 3200, 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=800&q=80'),
('SPA-уход "Золотой шелк"', 'Детокс массаж лица, маска на основе шелка и LED-терапия.', 70, 6100, 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=crop&w=800&q=80');
