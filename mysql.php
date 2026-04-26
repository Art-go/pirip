<?php

$mysqli = new mysqli("mysql-8.4.local", "root", "", "pirip");

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

function fetch_categories() {
  global $mysqli;
  return $mysqli->query("SELECT * FROM categories");
}

function fetch_items() {
  global $mysqli;
  return $mysqli->query("SELECT * FROM dishes");
}