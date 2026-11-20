<?php
// Адмін-панель (бонуси 4–6): перегляд відповідей, вхід за логіном/паролем, $_SESSION, експорт, видалення
require_once __DIR__ . '/includes/functions.php';
admin_session_start();

$loginError = '';

// Вихід
if (isset($_GET['logout'])) {
    admin_logout();
    header('Location: admin.php');
    exit;
}

// Вхід
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (admin_login(trim($_POST['user'] ?? ''), $_POST['pass'] ?? '')) {
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Невірний логін або пароль.';
}

// Усе нижче – лише для авторизованого адміністратора
if (is_admin()) {
    // Видалення (POST + CSRF-токен)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        if (csrf_ok()) {
            delete_response((int)$_POST['delete']);
        }
        header('Location: admin.php');
        exit;
    }
    // Експорт
    if (isset($_GET['export'])) {
        $rows = all_responses();
        if ($_GET['export'] === 'csv')  { export_csv($rows);  exit; }
        if ($_GET['export'] === 'json') { export_json($rows); exit; }
    }
}

$title = 'Адмін-панель';
require __DIR__ . '/includes/header.php';
?>

<?php if (!is_admin()): ?>
    <div class="card" style="max-width:420px;margin:auto">
        <h2>Вхід для адміністратора</h2>
        <?php if ($loginError): ?><div class="errors"><?= e($loginError) ?></div><?php endif; ?>
        <form method="post" action="admin.php">
            <label class="q" for="user">Логін</label>
            <input type="text" id="user" name="user" required autofocus>
            <label class="q" for="pass">Пароль</label>
            <input type="password" id="pass" name="pass" required>
            <p><button type="submit" name="login" value="1">Увійти</button></p>
        </form>
    </div>
<?php else: $rows = all_responses(); ?>
    <div class="card">
        <h2>Відповіді на анкету (<?= count($rows) ?>)</h2>
        <p>База даних: <strong><?= e(db_name()) ?></strong>. Ви увійшли як <strong><?= e(ADMIN_USER) ?></strong> (сесія з <?= e(format_dt($_SESSION['admin_login_time'])) ?>).</p>
        <div class="actions">
            <a class="btn" href="admin.php?export=csv">Експорт CSV</a>
            <a class="btn" href="admin.php?export=json">Експорт JSON</a>
            <a class="btn" href="admin.php?logout=1">Вийти</a>
        </div>
        <div class="scroll">
        <table>
            <tr><th>ID</th><th>Дата</th><th>Ім’я</th><th>Email</th><th>Частота</th><th>Завдання</th><th>Оцінка</th><th>Пропозиція</th><th></th></tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= e(format_dt($r['submitted_at'])) ?></td>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td><?= e($r['usage_frequency']) ?></td>
                    <td><?= e($r['tasks']) ?></td>
                    <td><?= e($r['usefulness']) ?></td>
                    <td><?= e($r['improvement']) ?></td>
                    <td>
                        <form method="post" action="admin.php" onsubmit="return confirm('Видалити відповідь #<?= (int)$r['id'] ?>?');">
                            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                            <button class="danger" type="submit" name="delete" value="<?= (int)$r['id'] ?>">Видалити</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9">Відповідей ще немає.</td></tr><?php endif; ?>
        </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
