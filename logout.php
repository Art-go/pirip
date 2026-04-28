<?php
session_start();
session_destroy();
if (isset($_GET["redirect"])){
    header("Location: {$_GET["redirect"]}");
    exit;
}
header('Location: login.php');
