<?php
session_start();

// Подключение к базе данных
include('config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение ID пользователя
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;

    if ($customer_id > 0) {
        // Удаление всех товаров из корзины пользователя
        $query = "DELETE FROM cart WHERE customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Корзина пользователя очищена.";
        } else {
            $_SESSION['error'] = "Ошибка при очистке корзины.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Некорректный ID пользователя.";
    }
}

// Перенаправление обратно на страницу с корзинами
header("Location: users_cart.php");
exit();
