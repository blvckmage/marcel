<?php
session_start();

// Уничтожаем сессию
session_unset();
session_destroy();

// Перенаправляем на форум
header('Location: forum.php');
exit;
?>
