<?php
session_start();

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['status' => false, 'message' => 'Ошибка входа'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка пользователя
    $stmt = $conn->prepare("SELECT customer_id, first_name FROM customers WHERE email = ? AND password = ?");
    $stmt->bind_param('ss', $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['customer_id'];
        $_SESSION['first_name'] = $user['first_name'];

        $response['status'] = true;
        $response['message'] = 'Успешный вход';
        $response['redirect'] = 'user.php'; // Редирект на личный кабинет
    } else {
        $response['message'] = 'Неверный логин или пароль';
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
