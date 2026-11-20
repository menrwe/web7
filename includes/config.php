<?php
// Налаштування сайту
date_default_timezone_set('Europe/Kyiv');

define('SURVEY_DIR', __DIR__ . '/../survey');          // папка для текстових (JSON) файлів відповідей
// --- База даних (бонус 3) ---
// Основна СУБД – MySQL/MariaDB (XAMPP: користувач root, пароль порожній). Базу й таблицю скрипт створює сам.
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'survey_db');
define('DB_USER', 'root');
define('DB_PASS', '');
// Резервний варіант: якщо MySQL недоступний, використовується файл SQLite
define('DB_FILE', __DIR__ . '/../data/survey.sqlite');

// Адміністратор (бонус 5). Пароль за замовчуванням: admin123
// Пароль зберігається у вигляді хеша (password_hash), а не відкритим текстом
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$e5MsbaJTXoyj./w60JPTc.JhGMLIkefzFJZU3plgKo7RbPg9WiE8G');
