<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Необходима авторизация']));
}

$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Ошибка подключения к БД']));
}

$cart_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "DELETE FROM cart WHERE cart_id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $cart_id, $_SESSION['user_id']);

header('Content-Type: application/json');

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Товар удален из корзины']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении товара']);
}

$stmt->close();
$conn->close();
?>