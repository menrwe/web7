<?php
// Обробник AJAX-запиту анкети: приймає POST, повертає JSON (без перезавантаження сторінки)
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Дозволено лише метод POST.']], JSON_UNESCAPED_UNICODE);
    exit;
}

[$errors, $data] = process_survey($_POST);

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success'      => true,
    'name'         => $data['name'],
    'submitted_at' => format_dt($data['submitted_at']),
    'file'         => 'survey/' . $data['file_name'],
], JSON_UNESCAPED_UNICODE);
