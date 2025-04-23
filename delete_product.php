<?php
// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Получаем ID товара для удаления
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Удаляем товар
$sql = "DELETE FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

header('Content-Type: application/json');

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Товар успешно удален']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении товара']);
}

$stmt->close();
$conn->close();
?>
