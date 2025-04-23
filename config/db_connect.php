<?php
$servername = "127.0.0.1"; // или 'localhost'
$username = "root"; // ваш пользователь базы данных
$password = "root"; // ваш пароль базы данных
$dbname = "store_coverings";

// Создаем подключение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем подключение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>