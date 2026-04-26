<?php
require_once("template.php");
require_once("mysql.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cartJSON'])) {
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $cart    = json_decode($_POST['cartJSON'] ?? '[]', true);

        if (!$phone || !$address || empty($cart)) {
            header('Location: checkout.php');
            exit;
        }

        $id = insert_order($phone, $address, $comment, $cart);
        header("Location: order.php?id=$id");
        exit;
    }

    if (isset($_POST['pay'])) {
        // фейк оплата
        $id = (int)($_POST['id'] ?? 0);
        if ($id) pay_order($id);
        header("Location: order.php?id=$id");
        exit;
    }
}
