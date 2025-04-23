<?php
session_start();

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключаемся к базе данных
    $db = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');

    // Проверка соединения
    if ($db->connect_error) {
        die("Ошибка подключения: " . $db->connect_error);
    }

    // Получаем данные из формы
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Подготовка SQL-запроса для поиска администратора
    $stmt = $db->prepare("SELECT admin_id, password FROM administrators WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Если результат не пустой, проверяем пароль
    if ($result->num_rows > 0) {
        // Если пользователь найден, получаем данные
        $row = $result->fetch_assoc();

        // Проверяем, совпадает ли введенный пароль с тем, что хранится в базе данных
        if ($password === $row['password']) {
            // Пароль верный, авторизация прошла успешно
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $username;
            header("Location: admin.php");  // Редирект на страницу администратора
            exit;
        } else {
            // Неверный пароль, перенаправляем обратно на страницу логина
            header("Location: admin_avto.php?error=wrong_password");
            exit;
        }
    } else {
        // Если администратора с таким именем не найдено, перенаправляем обратно на страницу логина
        header("Location: admin_avto.php?error=user_not_found");
        exit;
    }

    // Закрываем соединение
    $stmt->close();
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка авторизации</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Ошибка авторизации</h2>
    </div>
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 'wrong_password'): ?>
            <p style="color: red;">Неверный пароль!</p>
        <?php elseif ($_GET['error'] == 'user_not_found'): ?>
            <p style="color: red;">Пользователь не найден!</p>
        <?php endif; ?>
    <?php endif; ?>
    <a href="admin_avto.php" class="back-btn">Попробовать снова</a>
</div>

</body>
</html>
