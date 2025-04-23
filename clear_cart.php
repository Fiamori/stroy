<?php
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Если пользователь не авторизован
    exit();
}

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Удаление всех товаров из корзины
$sql = "DELETE FROM cart WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Если корзина была очищена, перенаправляем на страницу корзины
    header('Location: cart.php');
} else {
    echo "Корзина пуста или возникла ошибка при удалении товаров.";
}

$stmt->close();
$conn->close();
?>
