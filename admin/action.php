<?php
require_once '../mysql.php';

require_once '../template.php';
validate_admin();

$action = $_POST['action'] ?? '';
$table  = $_POST['table']  ?? '';
$id     = (int)($_POST['id'] ?? 0);

$allowed = ['categories', 'dishes', 'orders'];
if (!in_array($table, $allowed)) { exit('Invalid table'); }

if ($action === 'delete') {
    delete_row($table, $id);
    header("Location: index.php#$table");
    exit;
}

if ($action === 'save') {
    if ($table === 'categories') {
        save_category($id, $_POST['name'] ?? '');

    } elseif ($table === 'dishes') {
        save_dish($id, $_POST['name'] ?? '', $_POST['cat_id'] ?? 0, $_POST['price'] ?? 0, $_POST['img'] ?? '');

    } elseif ($table === 'orders') {
        save_order($id, $_POST['phone'] ?? '', $_POST['address'] ?? '', $_POST['comment'] ?? '', $_POST['user_id'] ?? null, $_POST['status'] ?? 'pending', $_POST['receipt_url'] ?? null);
    }

    header("Location: index.php#$table");
    exit;
}

header('Location: index.php');