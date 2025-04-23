<?php
session_start();
session_destroy(); // Удаляем данные сессии
header('Location: index.php');
exit();
?>
