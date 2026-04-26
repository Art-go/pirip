<?php

function gen_head($title, $head_inject) {
  return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>{$title}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
    {$head_inject}
</head>
<body>
HTML;
}

function gen_header($title, $head_inject = "", $header_inject = "") {
  return gen_head($title, $head_inject) . <<<HTML

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

function gen_admin_header($title, $head_inject = "") {
  return gen_head($title, $head_inject) . <<<HTML

<header class="header">
  <a href="/admin">
  <div class="logo">
    <i class="fas fa-utensils"></i>
    <span>Хинкальня <small>admin</small></span>
  </div>
  </a>
  <div class="header-right">
    <a class="site-link" href="/" target="_blank"><i class="fas fa-external-link-alt"></i> Сайт</a>
    <form method="POST" action="logout.php" style="display:inline">
      <button class="logout-btn"><i class="fas fa-sign-out-alt"></i> Выйти</button>
    </form>
  </div>
</header>
HTML;
}

function gen_table($table_icon, $table_id, $table_name, $columns, $table, $render_row, $add=true){
  $count = count($table);
  echo <<<HTML
  <div class="panel" id="{$table_id}">
      <div class="panel-head">
      <div class="panel-title">
        <i class="fas $table_icon"></i> {$table_name}
        <span class="count-badge">{$count}</span>
      </div>
    </div>
    <table class="adm-table">
      <thead><tr>
  HTML;

  foreach ($columns as $col) {
      echo "<th>{$col}</th>";
  }
  echo '<th style="width:120px;text-align:right">Действия</th></tr></thead><tbody>';

  if (empty($table)) {
      $colspan = count($columns) + 1;
      echo "<tr class=\"empty-row\"><td colspan=\"{$colspan}\"> Таблица пуста! </td></tr>";
  } else {
      foreach ($table as $row) {
          echo "<tr>";
          $render_row($row);
          $row_name = isset($row['name']) ? ("«" . htmlspecialchars($row['name']) . "»") : "#{$row['id']}?";
          echo <<<HTML
          <td>
          <div class="row-actions">
              <a class="act-btn act-edit" href="form.php?table=$table_id&id={$row['id']}">
              <i class="fas fa-pen"></i> Изменить
              </a>
              <form method="POST" action="action.php" onsubmit="return confirm('Удалить {$row_name}?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="table" value="$table_id">
              <input type="hidden" name="id" value="{$row['id']}">
              <button class="act-btn act-del" type="submit"><i class="fas fa-trash"></i> Удалить</button>
              </form>
          </div>
          </td>
          HTML;
          echo "</tr>";
      }
  }

  if ($add) echo <<<HTML
      </tbody>
    </table>
    <div class="add-row">
      <a class="btn-primary" href="form.php?table={$table_id}"><i class="fas fa-plus"></i> Добавить</a>
    </div>
  </div>
  HTML;
}