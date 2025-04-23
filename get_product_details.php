<?php
// Получаем ID товара из запроса
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0) {
    // Соединение с базой данных
    $conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Запрос на получение данных о товаре
    $sql = "SELECT product_name, image_path, description, price FROM products WHERE id = $productId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product); // Отправляем данные в формате JSON
    } else {
        echo json_encode(["error" => "Товар не найден"]);
    }

    // Закрытие соединения
    $conn->close();
} else {
    echo json_encode(["error" => "Неверный ID товара"]);
}
?>
