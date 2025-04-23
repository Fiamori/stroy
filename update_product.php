<?php
// Обновление данных товара в базе данных
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $imagePath = $_POST['image_path'];

    // Соединение с базой данных
    $conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Запрос для обновления данных товара
    $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, image_path = ? WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdsi', $productName, $description, $price, $imagePath, $productId);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>
