<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация для администратора</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Основной стиль страницы */
        body {
            background-color: #f4f4f4;
            color: #333;
            font-family: Arial, sans-serif;
        }

        /* Контейнер формы */
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form label {
            align-self: flex-start;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
            visibility: hidden; /* Скрываем label */
        }

        form input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fff;
            color: #333;
            outline: none;
            box-sizing: border-box;
        }

        form input:focus {
            border: 1px solid #888;
        }

        form input::placeholder {
            color: #888;
        }

        /* Кнопка входа */
        form button {
            width: calc(100% - 20px);
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color:rgb(156, 156, 156);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        form button:hover {
            background-color:rgb(104, 104, 104);
        }

        /* Кнопка назад */
        .back-btn-container {
            text-align: center;
            margin-top: 15px;
        }

        .back-btn {
            color:rgb(101, 101, 101);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-btn:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Авторизация администратора</h2>
    </div>
    <form action="admin_login.php" method="post">
        <input type="text" id="username" name="username" placeholder="Логин" required>
        <input type="password" id="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
    <div class="back-btn-container">
        <a href="index.php" class="back-btn">Назад</a>
    </div>
</div>

</body>
</html>
