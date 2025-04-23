<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');

// Проверка подключения
if ($conn->connect_error) {
    die(json_encode(['error' => 'Ошибка подключения: ' . $conn->connect_error]));
}

// Проверка наличия параметра id в GET-запросе
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die(json_encode(['error' => 'ID товара не передан']));
}

// Получаем и приводим к числу параметр id
$product_id = (int)$_GET['id'];

// Подготовленный запрос для получения товара
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");

if (!$stmt) {
    die(json_encode(['error' => 'Ошибка подготовки запроса: ' . $conn->error]));
}

$stmt->bind_param('i', $product_id);

// Выполняем запрос и проверяем на ошибки
if (!$stmt->execute()) {
    die(json_encode(['error' => 'Ошибка выполнения запроса: ' . $stmt->error]));
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Делаем выборку данных
    $row = $result->fetch_assoc();
    echo json_encode($row); // Отправляем данные в формате JSON
} else {
    echo json_encode(['error' => 'Товар не найден']);
}

// Закрытие соединения
$stmt->close();
$conn->close();
?>
