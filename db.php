<?php
function get_connection(): PDO {
    $host = getenv("SERVER");
    $port = getenv("PORT");
    $user = getenv("USERNAME");
    $password = getenv("PASSWORD");
    $dbname= getenv("DBNAME");
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}