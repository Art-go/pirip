<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require_once '../mysql.php';

$action = $_POST['action'] ?? '';
$table  = $_POST['table']  ?? '';
$id     = (int)($_POST['id'] ?? 0);

// Allowlist tables
$allowed = ['categories', 'dishes', 'orders'];
if (!in_array($table, $allowed)) { exit('Invalid table'); }

if ($action === 'delete') {
    $mysqli->query("DELETE FROM `$table` WHERE id = $id");
    header("Location: index.php#$table");
    exit;
}

if ($action === 'save') {
    if ($table === 'categories') {
        $name = $mysqli->real_escape_string($_POST['name']);
        if ($id) {
            $mysqli->query("UPDATE categories SET name='$name' WHERE id=$id");
        } else {
            $mysqli->query("INSERT INTO categories (name) VALUES ('$name')");
        }

    } elseif ($table === 'dishes') {
        $name   = $mysqli->real_escape_string($_POST['name']);
        $cat_id = (int)$_POST['cat_id'];
        $price  = (float)$_POST['price'];
        $img    = $mysqli->real_escape_string($_POST['img']);
        if ($id) {
            $mysqli->query("UPDATE dishes SET name='$name', cat_id=$cat_id, price=$price, img='$img' WHERE id=$id");
        } else {
            $mysqli->query("INSERT INTO dishes (name, cat_id, price, img) VALUES ('$name', $cat_id, $price, '$img')");
        }

    } elseif ($table === 'orders') {
        $phone   = $mysqli->real_escape_string($_POST['phone']);
        $address = $mysqli->real_escape_string($_POST['address']);
        $comment = $mysqli->real_escape_string($_POST['comment']);
        $cart    = $mysqli->real_escape_string($_POST['cart_json']);
        if ($id) {
            $mysqli->query("UPDATE orders SET phone='$phone', address='$address', comment='$comment', cart_json='$cart' WHERE id=$id");
        } else {
            $mysqli->query("INSERT INTO orders (phone, address, comment, cart_json) VALUES ('$phone','$address','$comment','$cart')");
        }
    }

    header("Location: index.php#$table");
    exit;
}

header('Location: index.php');
