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

    // Сначала проверяем, не администратор ли это
    $admin_stmt = $conn->prepare("SELECT admin_id FROM administrators WHERE email = ? AND password = ?");
    $admin_stmt->bind_param('ss', $email, $password);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin_result->num_rows > 0) {
        // Это администратор
        $admin = $admin_result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['is_admin'] = true;

        $response['status'] = true;
        $response['message'] = 'Успешный вход как администратор';
        $response['redirect'] = 'admin.php'; // Редирект в админ-панель
        
        $admin_stmt->close();
    } else {
        // Если не администратор, проверяем как обычного пользователя
        $user_stmt = $conn->prepare("SELECT customer_id, first_name FROM customers WHERE email = ? AND password = ?");
        $user_stmt->bind_param('ss', $email, $password);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $_SESSION['user_id'] = $user['customer_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['is_admin'] = false;

            $response['status'] = true;
            $response['message'] = 'Успешный вход';
            $response['redirect'] = 'user.php'; // Редирект в личный кабинет
        } else {
            $response['message'] = 'Неверный логин или пароль';
        }

        $user_stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>