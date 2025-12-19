<?php
require_once __DIR__ . '/config.php';

$results = [
    'connection' => 'not tested',
    'errors' => [],
    'tables' => [],
];

try {
    $pdo = db();
    $results['connection'] = 'ok';

    $tables = ['users', 'masters', 'services', 'appointments'];
    foreach ($tables as $table) {
        try {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            $sample = $pdo->query("SELECT * FROM {$table} LIMIT 3")->fetchAll();
            $results['tables'][$table] = ['count' => $count, 'sample' => $sample];
        } catch (Throwable $e) {
            $results['tables'][$table] = ['error' => $e->getMessage()];
        }
    }
} catch (Throwable $e) {
    $results['connection'] = 'failed: ' . $e->getMessage();
    $results['errors'][] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Диагностика базы данных</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        pre { background: #0c1117; color: #dbe2ec; padding: 12px; border-radius: 8px; overflow-x: auto; }
        .status { display: inline-block; padding: 8px 12px; border-radius: 6px; }
        .ok { background: #c5f2d6; color: #10542d; }
        .fail { background: #ffd5d5; color: #7a0b0b; }
        .card { margin-bottom: 18px; }
    </style>
</head>
<body>
    <h1>Диагностика БД «Нефертити»</h1>
    <p>DSN: <code><?= htmlspecialchars(DB_DSN, ENT_QUOTES, 'UTF-8') ?></code></p>
    <p>Пользователь: <code><?= htmlspecialchars(DB_USER, ENT_QUOTES, 'UTF-8') ?></code></p>

    <div class="card">
        <strong>Подключение:</strong>
        <?php if ($results['connection'] === 'ok'): ?>
            <span class="status ok">OK</span>
        <?php else: ?>
            <span class="status fail">Ошибка</span>
            <div class="muted"><?= htmlspecialchars($results['connection'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <?php foreach ($results['tables'] as $table => $info): ?>
        <div class="card">
            <h3><?= htmlspecialchars($table, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if (isset($info['error'])): ?>
                <div class="status fail">Ошибка выборки</div>
                <div class="muted"><?= htmlspecialchars($info['error'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
                <div class="status ok">строк: <?= (int)$info['count'] ?></div>
                <details style="margin-top:8px;">
                    <summary>Первые строки</summary>
                    <pre><?= htmlspecialchars(json_encode($info['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></pre>
                </details>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($results['errors']): ?>
        <h3>Ошибки</h3>
        <pre><?= htmlspecialchars(implode("\n", $results['errors']), ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
</body>
</html>
