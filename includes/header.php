<?php
// Подключение к базе данных
include('config/db_connect.php');

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1";
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

// Запрос на получение баннеров
$query = "SELECT image_url FROM banners";
$result = $conn->query($query);

// Проверяем, есть ли баннеры
$banners = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banners[] = $row['image_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин Обоев</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Общий стиль для шапки */
        .header1 {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        /* Контейнер внутри шапки */
        .header-container1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Логотип */
        .logo-img {
            height: 60px;
        }

        .logo {
            flex-grow: 1;
        }

        nav {
            flex: 2;
            display: flex;
            justify-content: center;
        }

        /* Навигация */
        nav ul {
            display: flex;
            gap: 15px;
            list-style: none;
        }

        nav ul li button {
            background: none;
            border: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            padding: 10px 15px;
            transition: color 0.3s;
        }

        nav ul li button:hover {
            color: #A0522D;
        }

        /* Уменьшаем расстояние между шапкой и баннером */
        main {
            margin-top: 1px;
        }

        /* Размеры баннера */
        .banner {
            width: 950px;
            margin: 0 auto;
        }

        /* Стиль для кнопок пагинации */
        .pagination a {
            background-color: #d3d3d3;
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            margin: 0 5px;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Стиль для активной страницы */
        .pagination a.active {
            background-color: #bdbdbd;
        }

        /* Стиль для кнопок пагинации при наведении */
        .pagination a:hover {
            background-color: #F5DEB3;
            color: black;
        }

        /* Стиль для модального окна (по умолчанию скрыто) */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        /* Стиль для контента модального окна */
        .modal-content {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 600px;
            max-width: 90%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        /* Стиль для текста */
        .modal-content .text {
            flex: 1;
            text-align: left;
        }

        /* Стиль для модального окна (по умолчанию скрыто) */
        .modal1 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        /* Стиль для контента модального окна */
        .modal-content1 {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 400px;
            max-width: 90%;
            box-sizing: border-box;
            position: relative;
        }

        /* Стиль для изображения */
        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Стиль для крестика закрытия */
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: #aaa;
            cursor: pointer;
            z-index: 1001;
        }

        /* Стиль для кнопки "Войти как администратор" */
        .modal-content1 a {
            text-decoration: none;
            color: black;
        }

        /* Стиль для формы */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popular-products h2 {
            margin-top: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 35px;
            color: #333;
        }

        .popular-products {
            border-radius: 10px;
        }

        /* Стили для навигации */
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }

        nav li {
            position: relative;
        }

        /* Скрытое меню по умолчанию */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            list-style: none;
            padding: 10px 0;
            margin: 0;
            z-index: 10;
            width: 150px;
        }

        /* Стили пунктов меню */
        .dropdown-menu li {
            padding: 5px 15px;
        }

        .dropdown-menu li a {
            text-decoration: none;
            color: black;
            display: block;
        }

        .dropdown-menu li a:hover {
            background-color: #f4f4f4;
        }

        /* Показать меню при наведении */
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        /* Стили для уведомлений */
        .notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notification {
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.5s forwards;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 300px;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }

        .notification.warning {
            background-color: #ff9800;
        }

        .notification.info {
            background-color: #2196F3;
        }

        .notification-close {
            cursor: pointer;
            margin-left: 15px;
            font-weight: bold;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .notification.hide {
            animation: slideOut 0.5s forwards;
        }
    </style>
</head>
<body>
<div class="wrapper">
<!-- Контейнер для уведомлений -->
<div class="notification-container" id="notificationContainer"></div>

<header class="header1">
    <div class="header-container1">
        <div class="logo">
            <button onclick="location.href='index.php'" style="
                background: none;
                border: none;
                color: black;
                font: inherit;
                cursor: pointer;
                padding: 0;
                font-weight: bold;
                font-size: 18px;
            ">
                ИП "Жамкоцян"
            </button>
        </div>
        <nav>
            <ul>
            <li><button href="#"></button></li>
            <li><button href="#"></button></li>
                <li class="dropdown">
                    <button class="dropdown-btn">Каталог</button>
                    <ul class="dropdown-menu">
                        <li><a href="ingex_catalog1.php">Полы</a></li>
                        <li><a href="ingex_catalog2.php">Обои</a></li>
                    </ul>
                </li>
                <li><button onclick="scrollToFooter()">Контакты</button></li>
                <li><button onclick="checkAuthorization()">Корзина</button></li>
                <li>
                    <div class="auth-btn-container">
                        <button class="auth-btn" onclick="openAuthModal()">Войти</button>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</header>