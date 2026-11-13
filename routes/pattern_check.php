<?php
const STAFF_NO_PATTERN = '/^CS-\d{6}$/';
const CUST_NO_PATTERN = '/^K\d{6}$/';
// 限制貨物名字的長度在1-100，不可以帶有任何文字以外的符號
const GOODS_PATTERN = '/^[\w\x{4e00}-\x{9fff}\s]{1,100}$/u';
// 電話可以有+、-、()、長度在8-30，中間可以有空白格
const PHONE_PATTERN = '/^[\d\+\-\(\) ]{8,30}$/';

function check_staff_no(string $v): bool {
    return preg_match(STAFF_NO_PATTERN, $v) === 1;
}

function check_cust_no(string $v): bool {
    return preg_match(CUST_NO_PATTERN, $v) === 1;
}

// 限制公司名字的長度在1-255
function check_coname(string $v): bool {
    $len = mb_strlen($v);
    return $len >= 1 && $len <= 255;
}

// 限制人類名字的長度在1-100
function check_name(string $v): bool {
    $len = mb_strlen($v);
    return $len >= 1 && $len <= 100;
}

function check_goods(string $v): bool {
    return preg_match(GOODS_PATTERN, $v) === 1;
}

function check_phone(string $v): bool {
    return preg_match(PHONE_PATTERN, $v) === 1;
}
?>