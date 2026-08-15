<?php
function loadenv(string $env): void {
    if(!file_exists($env)) {
        throw new Exception(".env is not exist\n");
    }
    $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        putenv("{$key}={$value}");
    }
}

loadenv(__DIR__ . '/../.env');
$host = getenv("SERVER_INIT");
$port = getenv('PORT_INIT');
$rootUser = getenv('ROOT_USERNAME');
$rootPW = getenv('ROOT_PASSWORD');
$DBName = getenv('DBNAME');
 
$ddlFile  = __DIR__ . '/../ddl.sql';
$seedFile = __DIR__ . '/seed_data.json';
 
function ClearSeedData(PDO $pdo): void {
    $tables = [
        'Staff', 'Customer', 'Supplier', 'Invertory',
        'Purchase', 'Order', 'Sequences', 'Order_details', 
        'Purchase_details', 'login_info',
    ];
 
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `{$table}`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}
 
function SeedData(PDO $pdo, string $seedFile): void {
    $data = json_decode(file_get_contents($seedFile), true);
    $refMap = [];
    foreach ($data as $table => $rows) {
        foreach ($rows as $row) {
            $ref = $row['_ref'] ?? null;
            unset($row['_ref']);
 
            foreach ($row as $key => $val) {
                if (is_string($val) && str_starts_with($val, '@')) {
                    $refKey = substr($val, 1);
                    $row[$key] = $refMap[$refKey];
                }
            }
 
            // Special Case: login_info
            if ($table === 'login_info' && isset($row['password'])) {
                $pw = $row['password'];
                unset($row['password']);
                $row['pw_hash'] = password_hash($pw, PASSWORD_BCRYPT);
            }
 
            $columns = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($row)));
            $values_hold = implode(', ', array_fill(0, count($row), '?'));
 
            $stmt = $pdo->prepare("INSERT INTO `{$table}` ({$columns}) VALUES ({$values_hold})");
            $stmt->execute(array_values($row));
 
            // Special Case: 有業務編號的 Tables
            $newId = (int) $pdo->lastInsertId();
 
            if ($table === 'Staff') {
                $staffNo = sprintf('CS-%06d', $newId);
                $pdo->prepare('UPDATE `Staff` SET staff_no = ? WHERE staff_id = ?')
                    ->execute([$staffNo, $newId]);
            } elseif ($table === 'Order') {
                $orderNo = sprintf('INV-2026%06d', $newId);
                $pdo->prepare('UPDATE `Order` SET order_no = ? WHERE order_id = ?')
                    ->execute([$orderNo, $newId]);
            } elseif ($table === 'Customer') {
                $custNo = sprintf('K%06d', $newId);
                $pdo->prepare('UPDATE `Customer` SET cust_no = ? WHERE cust_id = ?')
                    ->execute([$custNo, $newId]);
            } elseif ($table === 'Purchase') {
                $puNo = sprintf('P-2026%06d', $newId);
                $pdo->prepare('UPDATE `Purchase` SET pu_no = ? WHERE pu_id = ?')
                    ->execute([$puNo, $newId]);
            }
 
            if ($ref) {
                $refMap[$ref] = $newId;
            }
        }
    }
}
 
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $rootUser,
        $rootPW,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
 
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DBName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$DBName}`");
 
    if (file_exists($ddlFile)) {
        $pdo->exec(file_get_contents($ddlFile));
        echo "Create tables successed: {$ddlFile}\n";
    } else {
        echo "ddl is not exist\n";
    }
 
    if (file_exists($seedFile)) {
        ClearSeedData($pdo);
    }

    if (file_exists($seedFile)) {
        SeedData($pdo, $seedFile);
        echo "Import seed data successed\n";
    } else {
        echo "seed file is not exist\n";
    }
} catch (PDOException $e) {
    echo 'Error occurred: ' . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo 'Error occurred: ' . $e->getMessage() . "\n";
    exit(1);
}
?>