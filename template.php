<?php
function gen_header($title, $head_inject = "", $header_inject = "") {
  return <<<HTML

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>{$title}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    {$head_inject}
</head>
<body>

<header class="header">
    <a href="/">
      <div class="logo">
          <i class="fas fa-utensils"></i>
          <span>Хинкальня</span>
      </div>
    </a>
    {$header_inject}
</header>
HTML;
}