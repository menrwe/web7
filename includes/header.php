<?php /** Шаблон: початок сторінки. Змінна $title задається перед підключенням. */ ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Анкета') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="top">
    <h1>Веб-технології · ЛР №7 (AJAX)</h1>
    <nav><a href="index.php">Анкета</a> <a href="jokes.html">Жарти</a> <a href="table.html">Таблиця (jQuery)</a> <a href="admin.php">Адмін</a></nav>
</header>
<main>
