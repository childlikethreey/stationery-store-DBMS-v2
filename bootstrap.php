<?php
require_once __DIR__ .'/db.php';

class NotFoundError extends Exception {}
class AuthError extends Exception {}
class ValidationError extends Exception {}

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $e) {
    if ($e instanceof NotFoundError) {
        http_response_code(404);
    } elseif ($e instanceof AuthError) {
        http_response_code(403);
    } elseif ($e instanceof ValidationError || $e instanceof InvalidArgumentException) {
        http_response_code(400);
    } elseif ($e instanceof PDOException) {
        http_response_code(500);
    } else {
        http_response_code(500);
    }
    echo json_encode(["error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
});