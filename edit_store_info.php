<?php
// Подключаемся к базе данных
include 'config/db_connect.php';

// Получаем данные магазина из базы
$query = "SELECT * FROM store_info WHERE id = 1"; // Запрос для получения информации о магазине
$result = $conn->query($query);

if ($result) {
    $store_info = $result->fetch_assoc(); // Получаем данные как ассоциативный массив
} else {
    echo "Ошибка при получении данных: " . $conn->error;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = $_POST['name'];
    $address = $_POST['address'];
    $inn = $_POST['inn'];
    $ogrnip = $_POST['ogrnip'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $owner_name = $_POST['owner_name'];

    // Обновляем таблицу
    $query = "UPDATE store_info SET 
                name = ?, 
                address = ?, 
                inn = ?, 
                ogrnip = ?, 
                phone = ?, 
                email = ?, 
                owner_name = ? 
              WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssss', $name, $address, $inn, $ogrnip, $phone, $email, $owner_name);

    if ($stmt->execute()) {
        header('Location: admin.php'); // Перенаправляем обратно на главную страницу
        exit;
    } else {
        echo "Ошибка обновления: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование информации о магазине</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f8f8f8; /* Светло-серый фон */
    margin: 0;
    padding: 0;
}

.container {
    width: 100%;
    max-width: 800px;
    margin: 50px auto;
    background-color: #ffffff; /* Белый фон контейнера */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); /* Легкая тень */
}

h1 {
    text-align: center;
    color: #333; /* Темно-серый текст */
}

form {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 8px;
    color: #444; /* Чуть темнее серый */
}

input[type="text"],
input[type="email"],
input[type="phone"],
input[type="number"] {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
    background-color: #f0f0f0; /* Светло-серый фон полей ввода */
    color: #333; /* Темный текст */
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="phone"]:focus,
input[type="number"]:focus {
    border-color: #999; /* Серый цвет фокуса */
    outline: none;
}

.btn {
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #777; /* Серый цвет кнопки */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #666; /* Темно-серый при наведении */
}

.form-group {
    margin-bottom: 15px;
}

.form-group input[type="submit"] {
    background-color: #888; /* Серый цвет кнопки отправки */
    color: #fff;
}

.form-group input[type="submit"]:hover {
    background-color: #777; /* Темнее при наведении */
}

.form-group a {
    color: #666; /* Серый цвет ссылок */
    text-decoration: none;
}

.form-group a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>

<div class="container">
    <h1>Редактирование информации о магазине</h1>
    <form action="edit_store_info.php" method="POST">
        <div class="form-group">
            <label for="name">Название магазина:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($store_info['name']); ?>">
        </div>

        <div class="form-group">
            <label for="address">Адрес:</label>
            <input type="text" id="address" name="address" required value="<?php echo htmlspecialchars($store_info['address']); ?>">
        </div>

        <div class="form-group">
            <label for="inn">ИНН:</label>
            <input type="text" id="inn" name="inn" required value="<?php echo htmlspecialchars($store_info['inn']); ?>">
        </div>

        <div class="form-group">
            <label for="ogrnip">ОГРНИП:</label>
            <input type="text" id="ogrnip" name="ogrnip" required value="<?php echo htmlspecialchars($store_info['ogrnip']); ?>">
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($store_info['phone']); ?>">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($store_info['email']); ?>">
        </div>

        <div class="form-group">
            <label for="owner_name">ФИО владельца:</label>
            <input type="text" id="owner_name" name="owner_name" required value="<?php echo htmlspecialchars($store_info['owner_name']); ?>">
        </div>

        <div class="form-group">
            <input type="submit" value="Сохранить изменения" class="btn">
            <button id="editFooterButton" class="btn"><a href="admin.php">Назад</a>
        </div>

        
    
    </form>
</div>

</body>
</html>
