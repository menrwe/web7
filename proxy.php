<?php
// Серверний «міст» до API лабораторії (резервний варіант для table.js).
// Потрібен, якщо браузер блокує прямий запит до lab.vntu.org (CORS або змішаний http/https-контент).
// Адреса зафіксована в коді, тому скрипт не можна використати для запитів на довільні сайти.
define('API_URL', getenv('LAB7_API_URL') ?: 'http://lab.vntu.org/api-server/lab7.php');

function fetch_url(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true]);
        $body = curl_exec($ch);
        curl_close($ch);
        if ($body !== false) {
            return $body;
        }
    }
    $ctx  = stream_context_create(['http' => ['timeout' => 8]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}

$body = fetch_url(API_URL);
if ($body === null) {
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Не вдалося отримати дані з API'], JSON_UNESCAPED_UNICODE);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
echo $body;
