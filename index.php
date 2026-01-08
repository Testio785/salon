<?php
require_once __DIR__ . '/config.php';
ensure_session();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Салон красоты "Нефертити" — Информационная система</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="topbar">
    <div class="container flex-between">
        <div class="brand">
            <span class="brand-mark">N</span>
            <div>
                <div class="brand-name">Нефертити</div>
                <div class="brand-sub">Премиальный салон красоты</div>
            </div>
        </div>
        <nav class="nav">
            <a href="#services">Услуги</a>
            <a href="#booking">Записаться</a>
            <a href="#team">Мастера</a>
            <a href="#contacts">Контакты</a>
        </nav>
        <div class="nav-actions">
            <?php if ($user): ?>
                <span class="user-pill">👤 <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
                <button class="ghost" id="logoutBtn">Выйти</button>
                <?php if (($user['role'] ?? 'client') === 'admin'): ?>
                    <a class="cta ghost" href="admin/dashboard.php">Дашборд</a>
                <?php endif; ?>
            <?php else: ?>
                <button class="ghost" id="openLogin">Войти</button>
                <button class="cta" id="openRegister">Регистрация</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <p class="eyebrow">Информационная система салона</p>
                <h1>Современный веб-интерфейс для вашего идеального образа</h1>
                <p class="lede">Удобная онлайн-запись с контролем занятости мастеров, личный кабинет клиента и дашборд администратора с аналитикой доходов.</p>
                <div class="hero-actions">
                    <a class="cta" href="#booking">Записаться онлайн</a>
                    <a class="ghost" href="#services">Посмотреть услуги</a>
                </div>
                <div class="stats">
                    <div><span class="stat-number">25+</span><span class="stat-label">Услуг премиум класса</span></div>
                    <div><span class="stat-number">8</span><span class="stat-label">Сертифицированных мастеров</span></div>
                    <div><span class="stat-number">4.9★</span><span class="stat-label">Средний рейтинг клиентов</span></div>
                </div>
            </div>
            <div class="hero-card">
                <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80" alt="Салон красоты" class="hero-img">
                <div class="overlay-card">
                    <p>Прямое управление расписанием мастеров</p>
                    <div class="pill-row">
                        <span class="pill">Онлайн календарь</span>
                        <span class="pill">Уведомления</span>
                        <span class="pill">Контроль занятости</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="section">
        <div class="container section-header">
            <div>
                <p class="eyebrow">Портфолио услуг</p>
                <h2>Топовые бьюти-процедуры в одном месте</h2>
                <p class="muted">Собрали востребованные услуги с подробным описанием, длительностью и ценой. Цены указаны в российских рублях.</p>
            </div>
            <div class="legend">
                <span class="pill">Профессиональные материалы</span>
                <span class="pill">SPA зона</span>
                <span class="pill">VIP кабинеты</span>
            </div>
        </div>
        <div class="container" id="servicesGrid">
            <!-- Cards rendered by JS -->
        </div>
    </section>

    <section id="booking" class="section panel">
        <div class="container two-col">
            <div>
                <p class="eyebrow">Онлайн-запись</p>
                <h2>Выберите услугу, мастера и удобное время</h2>
                <p class="muted">Система показывает только свободные слоты. После регистрации вы сможете управлять своими записями и отменять их при необходимости.</p>
                <ul class="checklist">
                    <li>Календарь с контролем занятости мастеров</li>
                    <li>Автоматический расчет стоимости услуги</li>
                    <li>Уведомление в личном кабинете</li>
                </ul>
            </div>
            <div class="card form-card">
                <h3>Запись на обслуживание</h3>
                <form id="bookingForm">
                    <label>Выберите услугу
                        <select name="service_id" id="serviceSelect" required></select>
                    </label>
                    <label>Мастер
                        <select name="master_id" id="masterSelect" required></select>
                    </label>
                    <label>Дата и время
                        <input type="datetime-local" name="appointment_date" id="datetimeInput" required>
                    </label>
                    <div class="muted" id="slotHint">Выберите дату — система проверит свободные слоты мастера.</div>
                    <button type="submit" class="cta" id="bookBtn">Записаться</button>
                    <div id="bookingMessage" class="form-message"></div>
                </form>
            </div>
        </div>
    </section>

    <section id="team" class="section">
        <div class="container section-header">
            <div>
                <p class="eyebrow">Команда</p>
                <h2>Мастера с опытом более 5 лет</h2>
            </div>
            <div class="legend">
                <span class="pill">Визаж</span>
                <span class="pill">Парикмахерское искусство</span>
                <span class="pill">Нейл-дизайн</span>
            </div>
        </div>
        <div class="container" id="mastersGrid"></div>
    </section>

    <section class="section panel">
        <div class="container two-col">
            <div>
                <p class="eyebrow">Личный кабинет</p>
                <h2>Ваши предстоящие визиты</h2>
                <p class="muted">Все записи доступны в один клик. Можно отменить визит до его начала.</p>
            </div>
            <div class="card" id="appointmentsCard">
                <div class="card-header">Записи</div>
                <div id="appointmentsList" class="list"></div>
            </div>
        </div>
    </section>

    <section id="contacts" class="section">
        <div class="container two-col">
            <div>
                <p class="eyebrow">Контакты</p>
                <h2>Приходите в салон "Нефертити"</h2>
                <p class="muted">Москва, ул. Арбат, 27. Ежедневно с 9:00 до 21:00</p>
                <div class="contact-grid">
                    <div><strong>Телефон:</strong> +7 (495) 123-45-67</div>
                    <div><strong>Telegram:</strong> @nefertiti_beauty</div>
                    <div><strong>Email:</strong> hello@nefertiti.ru</div>
                    <div><strong>Соцсети:</strong> Instagram*, VK</div>
                </div>
            </div>
            <div class="card map-card">
                <img src="https://api.mapbox.com/styles/v1/mapbox/light-v11/static/37.5977,55.7520,13,0/600x360?access_token=pk.eyJ1IjoidGVzdCIsImEiOiJja2ZqOTMybWswMHFzMnpsd2J5Mm13dGFxIn0.Gp7x3jRN2MJ1q1YqubWNoA" alt="Карта салона" />
            </div>
        </div>
    </section>
</main>

<div class="modal" id="authModal">
    <div class="modal-content">
        <button class="close" id="closeModal">×</button>
        <div class="tabs">
            <button class="tab active" data-tab="login">Вход</button>
            <button class="tab" data-tab="register">Регистрация</button>
        </div>
        <div class="tab-panel" data-panel="login">
            <form id="loginForm">
                <label>Email<input type="email" name="email" required></label>
                <label>Пароль<input type="password" name="password" required></label>
                <button type="submit" class="cta">Войти</button>
                <div class="form-message" id="loginMessage"></div>
            </form>
        </div>
        <div class="tab-panel hidden" data-panel="register">
            <form id="registerForm">
                <label>Имя<input type="text" name="name" required></label>
                <label>Email<input type="email" name="email" required></label>
                <label>Телефон<input type="tel" name="phone"></label>
                <label>Пароль<input type="password" name="password" required></label>
                <button type="submit" class="cta">Создать аккаунт</button>
                <div class="form-message" id="registerMessage"></div>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container flex-between">
        <div>
            <div class="brand-name">Нефертити</div>
            <p class="muted">Информационная система салона красоты. Онлайн-запись, управление персоналом и аналитика доходов.</p>
        </div>
        <div class="footer-links">
            <a href="#services">Услуги</a>
            <a href="#booking">Записаться</a>
            <a href="#team">Команда</a>
            <a href="admin/dashboard.php">Админ-доступ</a>
        </div>
    </div>
</footer>

<script>window.__USER__ = <?php echo json_encode($user ?? null); ?>;</script>
<script src="assets/js/app.js"></script>
</body>
</html>
