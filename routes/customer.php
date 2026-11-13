<?php
require_once __DIR__ ."/pattern_check.php";
require_once __DIR__ ."/bootstrap.php";
require_once __DIR__ ."/../db.php";

$method = $_SERVER['REQUEST_METHOD'];

if($method == 'GET' && isset($_GET['id'])) {
    show_one_result((int)$_GET['id']);    
} elseif($method == 'GET') {
    show_all_results(); 
} elseif($method == 'POST') {
    create_cust();
} elseif($method == 'PUT' && isset($_GET['id'])) {
    update_cust((int)$_GET['id']);
}

// show all customers' info.
function show_all_results() {
    $pdo = get_connection();
    $instr = $pdo->query('
    select cust_no, co_name, contact_name, C.phone, staff_no
    from `Customer` C join `Staff` using (staff_id)
    ');
    echo json_encode($instr->fetchAll(), JSON_UNESCAPED_UNICODE);
}

// show one customer's info.
function show_one_result(int $id) {
    $pdo = get_connection();
    $instr = $pdo->prepare('
    select cust_no, co_name, contact_name, C.phone, staff_no
    from `Customer` C join `Staff` using (staff_id)
    where cust_id = :id
    ');
    $instr->execute(['id'=>$id]);
    $result = $instr->fetch();

    // 處理error: id不存在
    if(!$result) {
        throw new NotFoundError("該客戶ID不存在");
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

// check that required columns exist and all input formats are valid
function InputCheck (array $data): array {
    $errors = [];
    // required cols
    if(empty($data['co_name']) || !check_coname($data['co_name'])) {
        $errors['co_name'] = '公司名字沒有填寫或包含非法字元';
    }
    if(empty($data['phone']) || !check_phone($data['phone'])) {
        $errors['phone'] = '電話號碼沒有填寫或包含非數字字元';
    }
    if(empty($data['staff_no']) || !check_staff_no($data['staff_no'])) {
        $errors['staff_no'] = '員工ID沒有填寫或格式錯誤';
    }
    // not required cols
    if(!empty($data['contact_name']) && !check_name($data['contact_name'])) {
        $errors['contact_name'] = '聯絡人名字包含非法字元';
    }
    return $errors;
}

// create a new customer
function create_cust() {
    /*
    checklist:
    1. do input check
    2. firm staff_no exists
    3. firm staff active
    4. firm staff dept.
    */
    $data = json_decode(file_get_contents("php://input"), true);
    // 處理空白字元
    foreach($data as $key => $value) {
        if(is_string($value)) {
            $data[$key] = trim($value);
        }
    }

    $errors = InputCheck($data);
    if(!empty($errors)) {
        throw new ValidationError(json_encode($errors, JSON_UNESCAPED_UNICODE));
    }

    $pdo = get_connection();
    $pdo->beginTransaction();
    try {
        // firm staff_no exists, active, dept.
        $instr = $pdo->prepare('select * from `Staff` where staff_no = :staff_no');
        $instr->execute(['staff_no'=>$data['staff_no']]);
        $staff = $instr->fetch();
        if(!$staff) {
            throw new NotFoundError("該員工ID不存在");
        }
        if(!$staff['is_active']) {
            throw new OtherError('該員工已離職');
        }
        if($staff['dept'] == "清潔部門") {
            throw new OtherError('該員工非業務人員');
        }
        

        // start to insert
        $instr = $pdo->prepare('insert into `Customer` (co_name, contact_name, phone, staff_id)
        values (:co_name, :contact_name, :phone, :staff_id)');
        $instr->execute([
            'co_name'=>$data['co_name'],
            'contact_name'=>$data['cotact_name'] ?? null,
            'phone'=>$data['phone'],
            'staff_id'=>$staff['staff_id'],
        ]);

        // cust_no要額外處理
        $newId = (int)$pdo->lastInsertId();
        $cust_no = sprintf("K%06d", $newId);
        $stmt = $pdo->prepare("update `Customer` set cust_no = :cust_no where cust_id = :id");
        $stmt->execute(['cust_no' => $cust_no, 'id' => $newId]);
        $pdo->commit();

        http_response_code(201);
        echo json_encode(["message" => "create successed", "cust_no" => $cust_no], JSON_UNESCAPED_UNICODE);
    
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function update_cust(int $id) {
    /*
    checklist:
    1. do input check
    2. firm cust_id exists
    3. firm staff_no exists if staff_no had been change
    4. firm staff active if staff_no had been change
    */
    $data = json_decode(file_get_contents("php://input"), true);
    // 處理空白字元
    foreach($data as $key => $value) {
        if(is_string($value)) {
            $data[$key] = trim($value);
        }
    }
    
    $errors = InputCheck($data);
    if(!empty($errors)) {
        throw new ValidationError(json_encode($errors, JSON_UNESCAPED_UNICODE));
    }
    
    $pdo = get_connection();
    $pdo->beginTransaction();
    try {
        // firm cust_id exists
        $instr = $pdo->prepare('select * from `Customer` where cust_id = :cust_id');
        $instr->execute(['cust_id'=>$id]);
        $cust = $instr->fetch();
        if(!$cust) {
            throw new NotFoundError("該客戶ID不存在");
        }

        // firm staff_no exists, active, dept.
        $instr = $pdo->prepare('select * from `Staff` where staff_no = :staff_no');
        $instr->execute(['staff_no'=>$data['staff_no']]);
        $staff = $instr->fetch();
        if(!$staff) {
            throw new NotFoundError("該員工ID不存在");
        }
        if(!$staff['is_active']) {
            throw new OtherError('該員工已離職');
        }
        if($staff['dept'] == "清潔部門") {
            throw new OtherError('該員工非業務人員');
        }

        // start to update
        $instr = $pdo->prepare('update `Customer` set 
        co_name=:co_name, contact_name=:contact_name, phone=:phone, staff_id=:staff_id');
        $instr->execute([
            'co_name'=>$data['co_name'],
            'contact_name'=>$data['cotact_name'] ?? null,
            'phone'=>$data['phone'],
            'staff_id'=>$staff['staff_id'],
        ]);
        $pdo->commit();

        http_response_code(201);
        echo json_encode(["message" => "update successed"], JSON_UNESCAPED_UNICODE);
    
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>