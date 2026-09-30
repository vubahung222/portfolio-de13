<?php
/**
 * Lightweight health check endpoint.
 * Returns 200 + JSON when the DB is reachable, 503 otherwise.
 */
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../src/db.php';

try {
    db()->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'time' => date('c')]);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['status' => 'error']);
}
