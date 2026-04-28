<?php
session_start();
require_once("mysql.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cartJSON'])) {
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $cart    = json_decode($_POST['cartJSON'] ?? '[]', true);
        $user_id = (int)($_SESSION['user']['id'] ?? 0);

        if (!$phone || !$address || empty($cart) || !$user_id) {
            header('Location: checkout.php');
            exit;
        }

        $id = insert_order($phone, $address, $comment, $cart, $user_id);
        header("Location: order.php?id=$id");
        exit;
    }

    if (isset($_POST['pay'])) {
        // фейк оплата
        $id = (int)($_POST['id'] ?? 0);
        $user_id = (int)($_SESSION['user']['id'] ?? 0);
        echo 'user';
        if ($id) pay_order($id, $user_id);
        header("Location: order.php?id=$id");
        exit;
    }
}
header("Location: index.php");
