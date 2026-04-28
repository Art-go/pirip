<?php

$mysqli = new mysqli("localhost", "root", "", "pirip");

function get_users() {
  global $mysqli;
  return $mysqli->query("SELECT * FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);
}

function get_user($id) {
  global $mysqli;
  $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc() ?? [];
}

function validate_user($username, $pass){
  global $mysqli;
  $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE username=?");
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc() ?? [];

  if (!$user) return ["status" => false];

  $res = ["status" => password_verify($pass, $user["pass_hash"]), "user" => $user];
  $res["user"]["pass_hash"] = "";

  return $res;
}

function reg_user($username, $password, $confirm_password){
  global $mysqli;

  //check if vars are not empty
  if (!$username || !$password || !$confirm_password) return ["status" => false, "error" => ""];
  

  //password checks
  if ($password != $confirm_password) return ["status" => false, "error" => "Пароль должен совпадать!"];

  $pattern = '/^[A-Za-zА-Яа-яЁё0-9!"#$%&\'()*+,\-.:;<=>?@\[\]\\^_`{|}~]{8,100}$/u';

  if (!preg_match($pattern, $password))
    return ["status" => false, "error" => "Пароль может иметь только латиницу, кириллицу, цифры и спец символы, длина 8–100 символов!"];



  // check if user exists
  $stmt = $mysqli->prepare("SELECT * FROM `users` WHERE username=?");
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc() ?? false;

  if ($user) return ["status" => false, "error" => "Такой пользователь уже есть!"];

  //regging user
  $stmt_ins = $mysqli->prepare("INSERT INTO `users`(`username`, `pass_hash`) VALUES (?, ?)");
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt_ins->bind_param('ss', $username, $password_hash);
  $stmt_ins->execute();

  //giving user back
  $res = validate_user($username, $password);
  $res["error"]='';
  return $res; 
}

function get_categories() {
  global $mysqli;
  return $mysqli->query("SELECT * FROM categories ORDER BY id")->fetch_all(MYSQLI_ASSOC);
}

function get_dishes() {
  global $mysqli;
  return $mysqli->query("SELECT d.*, c.name AS cat_name FROM dishes d LEFT JOIN categories c ON d.cat_id=c.id ORDER BY d.id")->fetch_all(MYSQLI_ASSOC);
}

function get_orders($date = null) {
  global $mysqli;
  $date = $date ?: date('Y-m-d');
  $stmt = $mysqli->prepare("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE DATE(o.created_at) = ?  
    GROUP BY o.id
    ORDER BY o.created_at DESC
  ");
  $stmt->bind_param('s', $date);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_order_items($order_id) {
  global $mysqli;
  $stmt = $mysqli->prepare("
    SELECT oi.quantity, d.name, d.price, d.img, (oi.quantity * d.price) AS subtotal
    FROM order_items oi
    JOIN dishes d ON oi.dish_id = d.id
    WHERE oi.order_id = ?
  ");
  $stmt->bind_param('i', $order_id);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function save_order($id, $phone, $address, $comment, $user_id, $status, $receipt_url) {
  global $mysqli;
  if (!$id) return;
  $allowed = ['pending','paid','delivered'];
  if (!in_array($status, $allowed)) $status = 'pending';

  $stmt = $mysqli->prepare("
    SELECT COALESCE(SUM(oi.quantity * d.price), 0)
    FROM order_items oi JOIN dishes d ON oi.dish_id = d.id
    WHERE oi.order_id = ?
  ");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $total = $stmt->get_result()->fetch_row()[0];

  $stmt = $mysqli->prepare("UPDATE orders SET phone=?, address=?, comment=?, user_id=?, status=?, receipt_url=?, total=? WHERE id=?");
  $stmt->bind_param('sssissdi', $phone, $address, $comment, $user_id, $status, $receipt_url, $total, $id);
  $stmt->execute();
}

function get_row($table, $id) {
  global $mysqli;
  $stmt = $mysqli->prepare("SELECT * FROM `$table` WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc() ?? [];
}

function delete_row($table, $id) {
  global $mysqli;
  $stmt = $mysqli->prepare("DELETE FROM `$table` WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
}

function save_category($id, $name) {
  global $mysqli;
  if ($id) {
      $stmt = $mysqli->prepare("UPDATE categories SET name=? WHERE id=?");
      $stmt->bind_param('si', $name, $id);
  } else {
      $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
      $stmt->bind_param('s', $name);
  }
  $stmt->execute();
}

function save_dish($id, $name, $cat_id, $price, $img) {
  global $mysqli;
  $cat_id = (int)$cat_id;
  $price  = (float)$price;
  if ($id) {
      $stmt = $mysqli->prepare("UPDATE dishes SET name=?, cat_id=?, price=?, img=? WHERE id=?");
      $stmt->bind_param('sidsi', $name, $cat_id, $price, $img, $id);
  } else {
      $stmt = $mysqli->prepare("INSERT INTO dishes (name, cat_id, price, img) VALUES (?,?,?,?)");
      $stmt->bind_param('sids', $name, $cat_id, $price, $img);
  }
  $stmt->execute();
}

function insert_order($phone, $address, $comment, $cart, $user_id) {
  global $mysqli;
  $stmt = $mysqli->prepare(
      "INSERT INTO orders (phone, address, comment, user_id, status, total) VALUES (?, ?, ?, ?, 'pending', 0)"
  );
  $stmt->bind_param('sssi', $phone, $address, $comment, $user_id);
  $stmt->execute();
  $order_id = $mysqli->insert_id;

  $ins = $mysqli->prepare(
      "INSERT INTO order_items (order_id, dish_id, quantity) VALUES (?, ?, ?)"
  );
  foreach ($cart as $item) {
      $dish_id  = (int)$item['id'];
      $quantity = (int)($item['quantity'] ?? 1);
      $ins->bind_param('iii', $order_id, $dish_id, $quantity);
      $ins->execute();
  }

  $upd = $mysqli->prepare("
      UPDATE orders SET total = (
          SELECT COALESCE(SUM(oi.quantity * d.price), 0)
          FROM order_items oi JOIN dishes d ON oi.dish_id = d.id
          WHERE oi.order_id = ?
      ) WHERE id = ?
  ");
  $upd->bind_param('ii', $order_id, $order_id);
  $upd->execute();

  return $order_id;
}

function pay_order($id, $user_id) {
  global $mysqli;
  $stmt = $mysqli->prepare(
      "SELECT * FROM `orders` WHERE id=?"
  );
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $order = $stmt->get_result()->fetch_assoc()[0];
  if($order["user_id"]!=$user_id) return;
  
  $stmt = $mysqli->prepare(
      "UPDATE orders SET status='paid' WHERE id=? AND status='pending'"
  );
  $stmt->bind_param('i', $id);
  $stmt->execute();
}

function get_order($id) {
  global $mysqli;
  $stmt = $mysqli->prepare("SELECT * FROM orders WHERE id=?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc();
}