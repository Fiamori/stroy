<?php
// Подключение к базе данных
include 'config/db_connect.php'; // Убедитесь, что подключение работает

session_start();
$userId = $_SESSION['user_id']; // Предполагается, что ID пользователя хранится в сессии

// Проверяем, что форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $phoneNumber = htmlspecialchars($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);
    $imagePath = '';

    // Проверка и обработка загружаемого файла
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        $fileName = basename($_FILES['image_path']['name']);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Генерация уникального имени файла
        $newFileName = uniqid('image_') . '.' . $fileExtension;
        $uploadFilePath = $uploadDir . $newFileName;

        // Проверяем тип файла (например, только изображения)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            // Перемещаем файл в папку
            if (move_uploaded_file($_FILES['image_path']['tmp_name'], $uploadFilePath)) {
                $imagePath = $uploadFilePath; // Путь к файлу для базы данных
            } else {
                die('Ошибка при загрузке файла.');
            }
        } else {
            die('Недопустимый формат файла.');
        }
    }

    // Обновление данных пользователя в базе
    $sql = "UPDATE customers SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone_number = ?, 
                address = ?, 
                image_path = IF(? != '', ?, image_path)
            WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sssssssi',
        $firstName,
        $lastName,
        $email,
        $phoneNumber,
        $address,
        $imagePath,
        $imagePath,
        $userId
    );

    if ($stmt->execute()) {
        echo "Профиль успешно обновлен.";
        header('Location: user.php'); // Перенаправление на профиль
        exit;
    } else {
        echo "Ошибка: " . $stmt->error;
    }
}
