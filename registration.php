<?php
header('Content-Type: application/json');

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['status' => false, 'message' => 'Ошибка регистрации'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из формы
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $image_path = null;

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        $response['message'] = 'Пароли не совпадают';
        echo json_encode($response);
        exit;
    }

    // Проверка уникальности email
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = 'Пользователь с таким email уже существует';
        echo json_encode($response);
        exit;
    }

    // Обработка изображения
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Создаем директорию, если она отсутствует
        }
        $file_name = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path; // Сохраняем путь к изображению
        } else {
            $response['message'] = 'Ошибка загрузки изображения';
            echo json_encode($response);
            exit;
        }
    }

    // Добавление нового пользователя в базу данных
    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone_number, address, password, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', $first_name, $last_name, $email, $phone_number, $address, $password, $image_path);

    if ($stmt->execute()) {
        $response['status'] = true;
        $response['message'] = 'Регистрация успешна';
    } else {
        $response['message'] = 'Ошибка при регистрации';
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
