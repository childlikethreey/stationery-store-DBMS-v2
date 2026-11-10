<?php
require_once __DIR__ .'/../db.php';

/* 
方便分辨不同error
- NotFoundError: 不存在
- AuthError: 沒有權限
- ValidationError: 驗證錯誤
*/
class NotFoundError extends Exception {}
class AuthError extends Exception {}
class ValidationError extends Exception {}
class OtherError extends Exception {}

// CORS setting
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3000");
header('Content-Type: application/json; charset=utf-8');

// 處理CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 處理error的回傳結果
set_exception_handler(function (Throwable $e) {
    if ($e instanceof NotFoundError) {
        http_response_code(404);
    } elseif ($e instanceof AuthError) {
        http_response_code(403);
    } elseif ($e instanceof ValidationError || $e instanceof InvalidArgumentException || $e instanceof OtherError) {
        http_response_code(400);
    } elseif ($e instanceof PDOException) {
        http_response_code(500);
    } else {
        http_response_code(500);
    }
    $error_msg = [
        "error" => $e->getMessage(),
        "status code" =>$e->getCode(),
        ];
    echo json_encode($error_msg, JSON_UNESCAPED_UNICODE);
});