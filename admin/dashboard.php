<?php
require_once __DIR__ . '/../config.php';
ensure_session();
$user = current_user();
if (!$user || ($user['role'] ?? 'client') !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$pdo = db();
$appointments = $pdo->query('SELECT a.id, a.appointment_date, a.status, a.total_amount, m.name AS master_name, s.name AS service_name, u.name AS client_name FROM appointments a JOIN masters m ON a.master_id = m.id JOIN services s ON a.service_id = s.id JOIN users u ON a.user_id = u.id ORDER BY a.appointment_date DESC LIMIT 50')->fetchAll();

$revenue = $pdo->query('SELECT DATE_FORMAT(appointment_date, "%Y-%m") as month, SUM(total_amount) as total FROM appointments WHERE status = "booked" GROUP BY month ORDER BY month')->fetchAll();
$clients = $pdo->query('SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC LIMIT 30')->fetchAll();
$masters = $pdo->query('SELECT id, name, specialty, phone FROM masters ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Дашборд администратора — Нефертити</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="topbar">
        <div class="container flex-between">
            <div class="brand"><span class="brand-mark">N</span><div><div class="brand-name">Нефертити</div><div class="brand-sub">Дашборд администратора</div></div></div>
            <div class="nav-actions">
                <a class="ghost" href="../index.php">На сайт</a>
                <a class="cta" href="../api/logout.php">Выйти</a>
            </div>
        </div>
    </header>

    <main class="section">
        <div class="container">
            <h1>Управление салоном</h1>
            <p class="muted">Контроль записей, справочники клиентов и мастеров, аналитика доходов.</p>

            <div class="two-col" style="align-items: start;">
                <div class="card">
                    <div class="card-header">Текущие записи</div>
                    <div class="list">
                        <?php foreach ($appointments as $row): ?>
                            <div class="item">
                                <div>
                                    <strong><?php echo htmlspecialchars($row['service_name']); ?></strong> → <?php echo htmlspecialchars($row['master_name']); ?>
                                    <div class="muted">Клиент: <?php echo htmlspecialchars($row['client_name']); ?></div>
                                    <div class="muted"><?php echo htmlspecialchars(date('d.m H:i', strtotime($row['appointment_date']))); ?></div>
                                </div>
                                <span class="badge <?php echo $row['status'] === 'booked' ? 'success' : ($row['status'] === 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Доходы по месяцам</div>
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>

            <div class="two-col" style="margin-top:24px; align-items:start;">
                <div class="card">
                    <div class="card-header">Справочник клиентов</div>
                    <div class="list">
                        <?php foreach ($clients as $client): ?>
                            <div class="item">
                                <div>
                                    <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars($client['email']); ?></div>
                                    <div class="muted"><?php echo htmlspecialchars($client['phone']); ?></div>
                                </div>
                                <span class="badge warning">ID <?php echo (int)$client['id']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Справочник мастеров</div>
                    <div class="list">
                        <?php foreach ($masters as $master): ?>
                            <div class="item">
                                <div>
                                    <strong><?php echo htmlspecialchars($master['name']); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars($master['specialty']); ?></div>
                                    <div class="muted"><?php echo htmlspecialchars($master['phone']); ?></div>
                                </div>
                                <span class="badge warning">ID <?php echo (int)$master['id']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        const revenueData = <?php echo json_encode($revenue); ?>;
        const labels = revenueData.map(r => r.month);
        const totals = revenueData.map(r => Number(r.total));
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Доход, ₽',
                    data: totals,
                    borderColor: '#c0a36e',
                    backgroundColor: 'rgba(192,163,110,0.2)',
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
