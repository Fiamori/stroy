<?php
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Проверяем, были ли выбраны товары для удаления
if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
    $product_ids = $_POST['product_ids'];

    // Удаляем выбранные товары из корзины
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $sql = "DELETE FROM cart WHERE cart_id IN ($placeholders) AND customer_id = ?";
    $stmt = $conn->prepare($sql);
    
    // Массив параметров для привязки
    $params = array_merge($product_ids, [$user_id]);
    $stmt->bind_param(str_repeat('i', count($product_ids)) . 'i', ...$params);
    
    if ($stmt->execute()) {
        echo "<p>Товары успешно удалены из корзины.</p>";
    } else {
        echo "<p>Ошибка при удалении товаров.</p>";
    }

    $stmt->close();
}

$conn->close();

// Перенаправляем обратно в корзину
header('Location: cart.php');
exit();
?>
