<?php
require_once __DIR__ ."/helpers.php";
require_once __DIR__ ."/bootstrap.php";
require_once __DIR__ ."/../db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    show_all_results();
}

function show_all_results() {
    $pdo = get_connection();
    $result = $pdo->query('select cust_id, cust_no, co_name, contact_name, C.phone, staff_no
    from `Customer` C join `Staff` using (staff_id)');
    echo json_encode($result->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
}
?>