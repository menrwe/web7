# ЛР №7 — Динамічне оновлення даних за допомогою AJAX

## Запуск
У папці проєкту: `php -S localhost:8000`, далі:
- http://localhost:8000/jokes.html — випадковий жарт із jokes.txt (XMLHttpRequest)
- http://localhost:8000/index.php — анкета, що надсилається через XMLHttpRequest (обробник submit.php)
- http://localhost:8000/table.html — таблиця з API на jQuery (кнопки: оновити, сортувати за name / affiliation)
- http://localhost:8000/admin.php — адмінка з ЛР №6 (admin / admin123)

HTML-сторінки з AJAX потрібно відкривати через сервер (http://), а не подвійним кліком по файлу.
Для таблиці потрібен доступ до Інтернету. Якщо браузер блокує прямий запит до lab.vntu.org (CORS), table.js автоматично використає proxy.php.
jQuery лежить локально: js/jquery.min.js (3.7.1). У папках survey/ та data/ — тестові відповіді.

## База даних (MySQL)
Основна база — MySQL/MariaDB. Найпростіше: встановіть XAMPP, запустіть у XAMPP Control Panel модуль **MySQL** (користувач root, пароль порожній — це значення за замовчуванням у includes/config.php). Базу `survey_db` і таблицю `responses` скрипт створює сам; те саме можна зробити вручну, імпортувавши database.sql у phpMyAdmin.
У php.ini увімкніть `extension=pdo_mysql`. Якщо MySQL не запущено — сайт автоматично працює з резервною SQLite, а в адмін-панелі видно, яка база використовується.
