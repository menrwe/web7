<?php
/**
 * Усі PHP-функції сайту (бонус 2: PHP-код окремо від HTML).
 * Підключається у сторінках через require_once.
 */
require_once __DIR__ . '/config.php';

/** Опис питань анкети: ключ => [текст, тип, варіанти] */
function survey_questions(): array
{
    return [
        'usage_frequency' => [
            'text'    => 'Як часто ви користуєтесь ШІ-чат-ботами під час навчання?',
            'type'    => 'radio',
            'options' => ['Щодня', 'Кілька разів на тиждень', 'Рідко', 'Ніколи'],
        ],
        'tasks' => [
            'text'    => 'Для яких завдань ви їх використовуєте? (можна обрати кілька)',
            'type'    => 'checkbox',
            'options' => ['Пояснення тем', 'Написання коду', 'Переклад текстів', 'Підготовка звітів', 'Інше'],
        ],
        'usefulness' => [
            'text'    => 'Наскільки корисними для навчання ви вважаєте ШІ-помічників? (1 – зовсім ні, 5 – дуже)',
            'type'    => 'select',
            'options' => ['1', '2', '3', '4', '5'],
        ],
        'improvement' => [
            'text'    => 'Що б ви покращили у ШІ-помічниках для студентів?',
            'type'    => 'text',
            'options' => [],
        ],
    ];
}

/** Екранування виводу (захист від XSS) */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Перевірка та очищення даних, що прийшли методом POST. Повертає [помилки, чисті дані] */
function validate_submission(array $post): array
{
    $errors = [];
    $name  = trim($post['name'] ?? '');
    $email = trim($post['email'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = "Вкажіть ім’я (до 100 символів).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Вкажіть коректний email.';
    }

    $answers = [];
    foreach (survey_questions() as $key => $q) {
        $raw = $post[$key] ?? ($q['type'] === 'checkbox' ? [] : '');
        if ($q['type'] === 'checkbox') {
            $raw = is_array($raw) ? $raw : [];
            $value = array_values(array_intersect($raw, $q['options'])); // лише дозволені варіанти
            if (!$value) {
                $errors[] = 'Оберіть хоча б один варіант у питанні: «' . $q['text'] . '»';
            }
        } elseif ($q['type'] === 'text') {
            $value = trim((string)$raw);
            if ($value === '') {
                $errors[] = 'Дайте відповідь на питання: «' . $q['text'] . '»';
            }
            $value = mb_substr($value, 0, 1000);
        } else { // radio / select
            $value = (string)$raw;
            if (!in_array($value, $q['options'], true)) {
                $errors[] = 'Оберіть відповідь на питання: «' . $q['text'] . '»';
            }
        }
        $answers[$key] = $value;
    }

    return [$errors, [
        'name'         => $name,
        'email'        => $email,
        'submitted_at' => date('Y-m-d H:i:s'),
        'answers'      => $answers,
    ]];
}

/** Збереження відповіді у файл survey/survey_YYYY-MM-DD_HH-MM-SS_xxxx.json (завдання 3) */
function save_to_file(array $data): string
{
    if (!is_dir(SURVEY_DIR)) {
        mkdir(SURVEY_DIR, 0775, true);
    }
    $fileName = 'survey_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(2)) . '.json';
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents(SURVEY_DIR . '/' . $fileName, $json, LOCK_EX);
    return $fileName;
}

/** Назва СУБД, що використовується зараз ('MySQL' або 'SQLite') */
function db_name(): string
{
    db();
    return $GLOBALS['db_driver_name'];
}

/**
 * Підключення до бази даних через PDO (бонус 3).
 * Спочатку MySQL/MariaDB; якщо сервер недоступний – резервна SQLite.
 * Таблиця створюється автоматично.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        // 1) MySQL: спочатку без бази, щоб створити її, якщо немає
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . DB_NAME . '`');
        $pdo->exec('CREATE TABLE IF NOT EXISTS responses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            submitted_at DATETIME NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            usage_frequency VARCHAR(100),
            tasks TEXT,
            usefulness VARCHAR(10),
            improvement TEXT,
            file_name VARCHAR(255)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $GLOBALS['db_driver_name'] = 'MySQL';
    } catch (Throwable $e) {
        // 2) резерв: SQLite
        if (!is_dir(dirname(DB_FILE))) {
            mkdir(dirname(DB_FILE), 0775, true);
        }
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE IF NOT EXISTS responses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            submitted_at TEXT NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            usage_frequency TEXT,
            tasks TEXT,
            usefulness TEXT,
            improvement TEXT,
            file_name TEXT
        )');
        $GLOBALS['db_driver_name'] = 'SQLite';
    }
    return $pdo;
}

/** Збереження відповіді в базу даних (підготовлений запит – захист від SQL-ін’єкцій) */
function save_to_db(array $data, string $fileName): int
{
    $a = $data['answers'];
    $st = db()->prepare('INSERT INTO responses
        (submitted_at, name, email, usage_frequency, tasks, usefulness, improvement, file_name)
        VALUES (:t, :n, :e, :f, :k, :u, :i, :file)');
    $st->execute([
        ':t' => $data['submitted_at'], ':n' => $data['name'], ':e' => $data['email'],
        ':f' => $a['usage_frequency'], ':k' => implode('; ', $a['tasks']),
        ':u' => $a['usefulness'],      ':i' => $a['improvement'], ':file' => $fileName,
    ]);
    return (int)db()->lastInsertId();
}

/** Обробка відправленої анкети: валідація + запис у файл і БД. Повертає [помилки, дані] */
function process_survey(array $post): array
{
    [$errors, $data] = validate_submission($post);
    if ($errors) {
        return [$errors, null];
    }
    $fileName = save_to_file($data);
    $data['file_name'] = $fileName;
    save_to_db($data, $fileName);
    return [[], $data];
}

/** Форматування дати для користувача: 13.11.2026 14:05:33 */
function format_dt(string $sqlDate): string
{
    return date('d.m.Y H:i:s', strtotime($sqlDate));
}

function all_responses(): array
{
    return db()->query('SELECT * FROM responses ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

/** Видалення відповіді з БД та відповідного файлу */
function delete_response(int $id): void
{
    $st = db()->prepare('SELECT file_name FROM responses WHERE id = :id');
    $st->execute([':id' => $id]);
    $file = $st->fetchColumn();
    if ($file) {
        $path = SURVEY_DIR . '/' . basename($file); // basename – захист від виходу з папки
        if (is_file($path)) {
            unlink($path);
        }
    }
    db()->prepare('DELETE FROM responses WHERE id = :id')->execute([':id' => $id]);
}

/* ---------- Адмін-частина (бонуси 4–6) ---------- */

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function admin_login(string $user, string $pass): bool
{
    admin_session_start();
    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;           // вхід фіксується у $_SESSION
        $_SESSION['admin_login_time'] = date('Y-m-d H:i:s');
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        return true;
    }
    return false;
}

function is_admin(): bool
{
    admin_session_start();
    return !empty($_SESSION['admin']);
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    session_destroy();
}

function csrf_ok(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Експорт усіх відповідей у CSV (Excel-сумісний, з BOM) */
function export_csv(array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="survey_' . date('Y-m-d_H-i-s') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Дата', 'Ім’я', 'Email', 'Частота', 'Завдання', 'Корисність', 'Покращення'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['submitted_at'], $r['name'], $r['email'], $r['usage_frequency'],
                       $r['tasks'], $r['usefulness'], $r['improvement']], ';');
    }
    fclose($out);
}

function export_json(array $rows): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="survey_' . date('Y-m-d_H-i-s') . '.json"');
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
