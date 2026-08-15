<?php
const STAFF_NO_PATTERN = '/^CS-\d{6}$/';
const CUST_NO_PATTERN = '/^K\d{6}$/';
const CONAME_PATTERN = '/^[a-zA-Z\u4e00-\u9fff\s\.]{1,255}$/';
const NAME_PATTERN = '/^[a-zA-Z\u4e00-\u9fff\s]{1,100}$/';
const GOODS_PATTERN = '/^[\w\u4e00-\u9fff\s]{1,100}$/';
const PHONE_PATTERN = '/^[\d\-\(\)]{8,30}$/';

function check_staff_no(string $v): bool {
    return preg_match(STAFF_NO_PATTERN, $v) === 1;
}

function check_cust_no(string $v): bool {
    return preg_match(CUST_NO_PATTERN, $v) === 1;
}

function check_coname(string $v): bool {
    return preg_match(CONAME_PATTERN, $v) === 1;
}

function check_name(string $v): bool {
    return preg_match(NAME_PATTERN, $v) === 1;
}

function check_goods(string $v): bool {
    return preg_match(GOODS_PATTERN, $v) === 1;
}

function check_phone(string $v): bool {
    return preg_match(PHONE_PATTERN, $v) === 1;
}
?>