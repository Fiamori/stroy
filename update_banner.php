<?php
// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Проверка, был ли отправлен файл
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_image']) && isset($_POST['banner_id'])) {
    $bannerId = intval($_POST['banner_id']);
    $uploadDir = 'images/';
    $uploadFile = $uploadDir . basename($_FILES['new_image']['name']);

    // Перемещение файла на сервер
    if (move_uploaded_file($_FILES['new_image']['tmp_name'], $uploadFile)) {
        // Обновление пути в базе данных
        $stmt = $conn->prepare("UPDATE banners SET image_url = ? WHERE id = ?");
        $stmt->bind_param('si', $uploadFile, $bannerId);

        if ($stmt->execute()) {
            echo "Баннер успешно обновлен.";
        } else {
            echo "Ошибка при обновлении баннера.";
        }
        $stmt->close();
    } else {
        echo "Ошибка при загрузке файла.";
    }
}

$conn->close();
?>
