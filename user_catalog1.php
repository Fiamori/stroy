<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Если пользователь не авторизован
    exit();
}

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1"; // Предполагаем, что в таблице всегда только одна запись
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

$user_id = $_SESSION['user_id'];

// Получение данных пользователя
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, phone_number, address, image_path FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

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

// Параметры пагинации
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Получаем выбранные типы из GET-параметров
$selected_types = isset($_GET['type']) ? (array)$_GET['type'] : [];

// Базовый запрос
$sql = "SELECT * FROM products WHERE category_id = 1";

// Добавляем фильтрацию по типам, если они выбраны
if (!empty($selected_types)) {
    $conditions = [];
    foreach ($selected_types as $type) {
        $safe_type = $conn->real_escape_string($type);
        $conditions[] = "product_name LIKE '$safe_type%'"; // Ищем в начале названия
    }
    $sql .= " AND (" . implode(" OR ", $conditions) . ")";
}
// Добавляем пагинацию
$sql .= " LIMIT $items_per_page OFFSET $offset";

// Выполняем запрос
$result = $conn->query($sql);

// Проверяем ошибки запроса
if (!$result) {
    die("Ошибка запроса: " . $conn->error);
}

// Запрос для подсчета общего количества товаров (с учетом фильтров)
$count_sql = "SELECT COUNT(*) AS total FROM products WHERE category_id = 1";
if (!empty($selected_types)) {
    $conditions = [];
    foreach ($selected_types as $type) {
        $safe_type = $conn->real_escape_string($type);
        $conditions[] = "product_name LIKE '$safe_type%'"; // Ищем в начале названия
    }
    $count_sql .= " AND (" . implode(" OR ", $conditions) . ")";
}

$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

$stmt->close();
$conn->close();
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
    background-color: rgba(255, 255, 255, 0.8); /* Прозрачный белый фон */
    backdrop-filter: blur(10px); /* Размытие фона */
    position: sticky; /* Шапка фиксируется вверху страницы */
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); /* Легкая тень для отделения */
    padding: 15px 0; /* Меньше отступов сверху и снизу */
}

/* Контейнер внутри шапки */
.header-container1 {
    display: flex;
    justify-content: space-between; /* Оставляем элементы с равным распределением */
    align-items: center;
    max-width: 1000px; /* Максимальная ширина шапки */
    margin: 0 auto; /* Центрируем весь контейнер */
    padding: 0 50px; /* Отступы слева и справа */
}

/* Логотип */
.logo-img {
    height: 60px; /* Увеличиваем размер логотипа */
}

.logo {
  
    flex-grow: 1; /* Растягиваем блок логотипа для балансировки */
}

nav {
    flex: 2; /* Пространство для кнопок навигации */
    display: flex;
    justify-content: center; /* Центрируем кнопки навигации */
}

/* Навигация */
nav ul {
    display: flex;
    gap: 15px; /* Расстояние между пунктами меню */
    list-style: none;
}

nav ul li button {
    background: none;
    border: none;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    padding: 10px 15px; /* Добавляем внутренние отступы */
    transition: color 0.3s;
}

nav ul li button:hover {
    color: #A0522D; /* Цвет текста при наведении */
}

.auth-btn-container {
    flex: 1; /* Пространство для профиля */
    display: flex;
    justify-content: flex-end; /* Профиль будет справа */
}


/* Стиль для кнопок пагинации */
.pagination a {
    background-color: #d3d3d3; /* Серый фон для кнопок */
    color: black; /* Цвет текста */
    padding: 8px 16px; /* Отступы вокруг текста */
    text-decoration: none; /* Убираем подчеркивание */
    margin: 0 5px; /* Отступы между кнопками */
    border-radius: 4px; /* Закругление углов */
    cursor: pointer; /* Курсор в виде указателя */
}

/* Стиль для активной страницы */
.pagination a.active {
    background-color: #bdbdbd; /* Цвет активной кнопки */
}

/* Стиль для кнопок пагинации при наведении */
.pagination a:hover {
    background-color: #F5DEB3; /* Бежевый цвет при наведении */
    color: black; /* Цвет текста при наведении */
}


/* Контейнер для всплывающего меню */
.auth-btn-container {
    position: relative;
    display: inline-block;
}

/* Скрытый контент меню */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 120px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Стили ссылок в меню */
.dropdown-content a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    display: block;
}

/* Изменение цвета при наведении */
.dropdown-content a:hover {
    background-color: #f1f1f1;
}

/* Показ меню при добавлении класса active */
.auth-btn-container .dropdown-content.active {
    display: block;
}
/* Стиль для кнопки "Добавить в корзину" */
.add-to-cart-btn {
    padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            background-color: #D2B48C;
            color: white;
            border: none;
}

.add-to-cart-btn:hover {
    background-color: #a0865f;
}

/* Стиль для модального окна (по умолчанию скрыто) */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}


/* Стиль для контента модального окна */
.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 20px;
    width: 600px; /* Ширина окна */
    box-sizing: border-box;
    display: flex; /* Используем flexbox для размещения элементов */
    align-items: center; /* Центрируем элементы по вертикали */
    gap: 20px; /* Отступ между картинкой и текстом */
}
/* Стиль для картинки */
.modal-content img {
    max-width: 300px; /* Ограничиваем ширину картинки */
    max-height: 300px; /* Ограничиваем высоту картинки */
    object-fit: cover; /* Сохраняем пропорции */
}

/* Стиль для текста */
.modal-content .text {
    flex: 1; /* Текст занимает оставшееся пространство */
    text-align: left; /* Выравниваем текст по левому краю */
}

/* Стиль для крестика закрытия */
.close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 30px;
    color: #aaa;
    cursor: pointer;
}
.modalP {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

/* Контент модального окна */
.modal-contentP {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 40%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}

/* Заголовок */
.profile-title {
    margin-bottom: 20px;
    font-size: 24px;
    color: #333;
}

/* Контейнер информации */
.profile-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

/* Фото профиля */
.profile-photo img {
    border-radius: 50%;
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 2px solid #ccc;
}

/* Детали профиля */
.profile-details {
    text-align: left;
    width: 100%;
    max-width: 300px;
}

.profile-details p {
    margin: 8px 0;
    font-size: 16px;
    color: #555;
}

/* ПЕРЕКРЫТИЕ */
.hidden {
    display: none;
}

.edit-profile-btn {

    background-color: #D2B48C;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
}

.edit-profile-btn:hover {
    background-color: #a0865f;
}

/* Кнопка "Изменить" */
.edit-profile-btn {
    margin-top: 20px;
    background-color: #D2B48C;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

.edit-profile-btn:hover {
    background-color: #a0865f;
}

/* Скрытая форма редактирования */
.hidden {
    display: none;
}

form label {
    display: block;
    margin-top: 15px;
    text-align: left;
    font-size: 14px;
    color: #333;
}

form input {
    width: 93%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

form button {
    margin-top: 20px;
    padding: 10px 20px;
    background-color:rgb(182, 169, 115);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: rgb(212, 193, 146);
}

/* Кнопка Каталог */
.dropdown {
    position: relative; /* Контекст для позиционирования подменю */
}

/* Подменю */
.dropdown-menu {
    display: none;
    position: absolute; /* Абсолютное позиционирование относительно родителя */
    top: 100%; /* Появляется под кнопкой */
    left: 0; /* Начинается с левого края кнопки */
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    list-style: none;
    padding: 10px 0;
    margin: 0;
    z-index: 10;
}

/* Стили пунктов меню */
.dropdown-menu li {
    padding: 5px 15px;
}

/* Ссылка в меню */
.dropdown-menu li a {
    text-decoration: none;
    color: black;
    display: block;
}

/* Изменение фона при наведении */
.dropdown-menu li a:hover {
    background-color: #f4f4f4;
}

/* Показываем меню при наведении */
.dropdown:hover .dropdown-menu {
    display: block;
}

/* Сброс стандартных отступов */
ul {
    margin: 0;
    padding: 0;
}

/* Кнопки в меню */
nav ul li button {
    background: none;
    border: none;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    padding: 10px 15px;
    transition: color 0.3s;
}

.popular-products h2 {
    margin-bottom: 0 0 0 30px; /* Отступ снизу */
    font-size: 35px; /* Размер шрифта */
    color: #333; /* Цвет текста */
}

    /* Стиль для кнопки "Добавить в корзину" */
    .add-to-cart-btn {
    padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            background-color: #D2B48C;
            color: white;
            border: none;
}

.add-to-cart-btn:hover {
    background-color: #a0865f;
}

    .catalog-container {
    display: flex;
    gap: 0px;
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
}

.filters {
    width: 200px; /* Уменьшили ширину */
    padding: 15px;
    background:rgb(255, 255, 255);
    position: sticky;
    border-radius: 0px;
}

.filters h3 {
    margin-top: 0;
    margin-bottom: 15px;
    position: sticky;
    font-size: 18px;
}

.filters label {
    display: flex;
    align-items: center;
    margin: 2px 0; /* Уменьшили отступы между чекбоксами */
    cursor: pointer;
    position: sticky;
    font-size: 14px;
}

.filters input[type="checkbox"] {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    position: sticky;
    cursor: pointer;
}

.filters button {
    margin-top: 10px;
    padding: 6px 12px;
    background: #333;
    position: sticky;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

/* Для товаров добавим фиксированную высоту карточек */
.product {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px;
    height: 320px; /* Фиксированная высота */
    display: flex;
    flex-direction: column;
}

.product img {
    width: 100%;
    height: 180px; /* Фиксированная высота изображения */
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 10px;
}
    </style>
</head>
<body>
<div class="wrapper">
<header class="header1">
        <div class="header-container1">
        <div class="logo">
    <button onclick="location.href='user.php'" style="
        background: none;
        border: none;
        color: black;
        font: inherit;
        cursor: pointer;
        padding: 0;
        font-weight: bold; /* Жирный шрифт */
        font-size: 18px; /* Увеличенный размер шрифта */
        
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
        <li><a href="user_catalog1.php">Полы</a></li>
        <li><a href="user_catalog2.php">Обои</a></li>
    </ul>
</li>

                 
                    <li><button onclick="scrollToFooter()">Контакты</button></li>
                    <li><button onclick="location.href='cart.php'">Корзина</button></li>
                    <li>
    <div class="auth-btn-container">
        <button id="userMenuButton">
            <?php echo htmlspecialchars($user['first_name']); ?>
        </button>
        <!-- Всплывающее меню -->
        <div id="userDropdown" class="dropdown-content">
        <a href="javascript:void(0)" onclick="openProfileModal()">Профиль</a>

            <a href="logout.php">Выход</a>
        </div>
    </div>
</li>

                </ul>
            </nav>
        </div>
    </header>

    <main>
    <div class="catalog-container">
    <aside class="filters" style="margin: 100px 0 0 0;">
                <h3>Тип покрытия</h3>
                <form id="filterForm" method="get">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1px;">
                        <?php
                        $types = ['Линолеум', 'Ламинат', 'Ковролин', 'Ковровая плитка', 'Паркет', 'Виниловое покрытие'];
                        foreach ($types as $type) {
                            $checked = in_array($type, $selected_types) ? 'checked' : '';
                            echo "<label><input type='checkbox' name='type[]' value='$type' $checked><span>$type</span></label>";
                        }
                        ?>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="submit">Применить</button>
                        <button type="button" onclick="location.href='user_catalog1.php'" style="background: #ccc; color: #333;">Сбросить</button>
                    </div>
                    <input type="hidden" name="page" value="1">
                </form>
            </aside>
        <section class="popular-products">
            <h2>Напольные покрытия</h2>
            <div class="product-list">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "
                            <div class='product'>
                                <img src='{$row['image_path']}' alt='{$row['product_name']}' 
                                     data-name='{$row['product_name']}' 
                                     data-description='{$row['description']}'
                                     data-price='{$row['price']}'
                                     data-image='{$row['image_path']}'
                                     data-id='{$row['product_id']}' 
                                     onclick='openModal(this)'>
                                <h3>{$row['product_name']}</h3>
                                <p>Цена: {$row['price']} руб./м²</p>
                            </div>
                        ";
                    }
                } else {
                    echo "<p>Товары не найдены.</p>";
                }
                ?>
            </div>

            <div class="pagination">
                <?php
                // Кнопка "Назад"
                if ($page > 1) {
                    $prev_url = 'user_catalog1.php?page=' . ($page - 1);
                    if (!empty($selected_types)) {
                        foreach ($selected_types as $type) {
                            $prev_url .= '&type[]=' . urlencode($type);
                        }
                    }
                    echo "<a href='$prev_url'>&laquo; Назад</a>";
                }

                // Показать номера страниц
                for ($i = 1; $i <= $total_pages; $i++) {
                    $page_url = 'user_catalog1.php?page=' . $i;
                    if (!empty($selected_types)) {
                        foreach ($selected_types as $type) {
                            $page_url .= '&type[]=' . urlencode($type);
                        }
                    }
                    
                    if ($i == $page) {
                        echo "<a href='$page_url' class='active'>$i</a>";
                    } else {
                        echo "<a href='$page_url'>$i</a>";
                    }
                }

                // Кнопка "Далее"
                if ($page < $total_pages) {
                    $next_url = 'user_catalog1.php?page=' . ($page + 1);
                    if (!empty($selected_types)) {
                        foreach ($selected_types as $type) {
                            $next_url .= '&type[]=' . urlencode($type);
                        }
                    }
                    echo "<a href='$next_url'>Далее &raquo;</a>";
                }
                ?>
            </div>
        </section>
    </div>
</main>
    </div>


<!-- Модальное окно -->
<div id="productModal" class="modal" onclick="handleModalClick(event)" style="background-color: rgba(65, 63, 58, 0.7);">
    <div class="modal-content" style="background-color: rgba(255, 255, 255, 0.9);">
        <div class="modal-image" style="width: 300px; height: 300px; overflow: hidden;">
            <img id="modalImg" src="" alt="Изображение товара" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
        </div>
        <div class="modal-details">
            <h3 id="modalName"></h3> <!-- Название товара -->
            <p id="modalDescription"></p> <!-- Описание товара -->
            <p id="modalPrice"></p> <!-- Цена товара -->

            <!-- Форма для добавления в корзину -->
            <form method="POST" action="add_to_cart.php">
                <!-- Скрытое поле для передачи ID товара -->
                <input type="hidden" id="product_id" name="product_id" value="">
                <!-- Поле для ввода количества -->
                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                <button type="submit" class="add-to-cart-btn" >Добавить в корзину</button>
               
            </form>

            <p id="cartMessage" style="color: green;"></p> <!-- Сообщение пользователю -->
        </div>
    </div>
    <span class="close" onclick="closeModal()">&times;</span>
</div>

<!-- Модальное окно профиля -->
<div id="profileModal" class="modalP">
    <div class="modal-contentP">
    <span class="closeP" id="closeProfileModal" style="
    position: absolute; 
    top: 10px; 
    right: 10px; 
    font-size: 24px; 
    cursor: pointer;">
    &times;
</span>

        <h2 class="profile-title">Информация о пользователе</h2>
        <div class="profile-container">
            <div class="profile-photo">
                <?php 
                $userPhoto = !empty($user['image_path']) && file_exists($user['image_path']) 
                    ? htmlspecialchars($user['image_path']) 
                    : 'images/profil.jpg';
                ?>
                <img src="<?php echo $userPhoto; ?>" alt="Фото пользователя">
            </div>
            <div class="profile-details">
                <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['first_name'] ?? 'Не указано'); ?></p>
                <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($user['last_name'] ?? 'Не указано'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Не указано'); ?></p>
                <p><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? 'Не указано'); ?></p>
                <p><strong>Адрес:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Не указано'); ?></p>
            </div>
        </div>

        <!-- Кнопка "Изменить" -->
        <button id="editProfileBtn" class="edit-profile-btn">Изменить</button>

        <!-- Форма редактирования -->
        <form id="editProfileForm" class="hidden" method="POST" action="update_profile.php" enctype="multipart/form-data">
            <label for="first_name">Имя:</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>

            <label for="last_name">Фамилия:</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

            <label for="phone_number">Телефон:</label>
            <input type="tel" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" pattern="\d{10,15}" required>

            <label for="address">Адрес:</label>
            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>

            <label for="image_path">Фото:</label>
            <input type="file" name="image_path" id="image_path" accept="image/*">

            <button type="submit">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для авторизации -->
<div id="authModal" class="modal1">
    <div class="modal-content1">
        <span class="close" onclick="closeAuthModal()">&times;</span>
        <h2>Авторизация</h2>
        <form action="auth.php" method="POST">
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Войти</button>
        </form>
        <p>Нет аккаунта? <button onclick="openRegisterModal()" style="border: none; background: none; color: #0066cc; cursor: pointer;">Зарегистрироваться</button></p>
    </div>
</div>

<!-- Модальное окно для регистрации -->
<div id="registerModal" class="modal1">
    <div class="modal-content1">
        <span class="close" onclick="closeRegisterModal()">&times;</span>
        <h2>Регистрация</h2>
        <form id="registerForm" action="register.php" method="POST" onsubmit="return validateRegistrationForm()">
            <label for="first_name">Имя:</label>
            <input type="text" id="first_name" name="first_name" required>
            <label for="last_name">Фамилия:</label>
            <input type="text" id="last_name" name="last_name" required>
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" required>
            <label for="phone_number">Телефон:</label>
            <input type="tel" id="phone_number" name="phone_number" pattern="\d{10,15}" required>
            <label for="address">Адрес:</label>
            <input type="text" id="address" name="address" required>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password">Подтверждение пароля:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p id="successMessage" style="display: none; color: green;">Регистрация прошла успешно!</p>
    </div>
</div>

<script>
    // Функции для работы с модальными окнами
    function openModal(element) {
        var productId = element.getAttribute('data-id');
        var productName = element.getAttribute('data-name');
        var productDescription = element.getAttribute('data-description');
        var productPrice = element.getAttribute('data-price');
        var productImage = element.getAttribute('data-image');

        document.getElementById('modalName').innerText = productName;
        document.getElementById('modalDescription').innerText = productDescription;
        document.getElementById('modalPrice').innerText = "Цена: " + productPrice + " руб.";
        document.getElementById('modalImg').src = productImage;
        document.getElementById('product_id').value = productId;

        document.getElementById('productModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function openProfileModal() {
        document.getElementById('profileModal').style.display = 'block';
    }

    function closeProfileModal() {
        document.getElementById('profileModal').style.display = 'none';
    }

    function closeAuthModal() {
        document.getElementById('authModal').style.display = 'none';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').style.display = 'none';
    }

    // Закрытие модальных окон при клике вне их области
    window.addEventListener('click', function(event) {
        if (event.target === document.getElementById('productModal')) {
            closeModal();
        }
        if (event.target === document.getElementById('profileModal')) {
            closeProfileModal();
        }
        if (event.target === document.getElementById('authModal')) {
            closeAuthModal();
        }
        if (event.target === document.getElementById('registerModal')) {
            closeRegisterModal();
        }
    });

    // Закрытие модальных окон по клавише Esc
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeProfileModal();
            closeAuthModal();
            closeRegisterModal();
        }
    });

    // Остальные функции
    function scrollToFooter() {
        const footer = document.getElementById("contacts");
        footer.scrollIntoView({ behavior: "smooth" });
    }

    function openAuthModal() {
        document.getElementById('authModal').style.display = 'flex';
    }

    function openRegisterModal() {
        document.getElementById('authModal').style.display = 'none';
        document.getElementById('registerModal').style.display = 'flex';
    }

    function validateRegistrationForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password').value;
        const phoneNumber = document.getElementById('phone_number').value;

        if (password !== confirmPassword) {
            alert('Пароли не совпадают.');
            return false;
        }

        if (!/^[0-9]+$/.test(phoneNumber)) {
            alert('Номер телефона должен содержать только цифры.');
            return false;
        }

        alert('Регистрация прошла успешно!');
        document.getElementById('successMessage').style.display = 'block';
        return true;
    }

    // Работа с меню пользователя
    document.addEventListener('DOMContentLoaded', function () {
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');

        userMenuButton.addEventListener('click', function () {
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function (event) {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove('active');
            }
        });
    });

    // Редактирование профиля
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileForm = document.getElementById('editProfileForm');
    const profileDetails = document.querySelector('.profile-details');

    editProfileBtn.addEventListener('click', () => {
        if (editProfileForm.classList.contains('hidden')) {
            editProfileForm.classList.remove('hidden');
            profileDetails.style.display = 'none';
            editProfileBtn.textContent = 'Отмена';
        } else {
            editProfileForm.classList.add('hidden');
            profileDetails.style.display = 'block';
            editProfileBtn.textContent = 'Изменить';
        }
    });
</script>

<?php
// Подключаем подвал
include('includes/footer.php');
?>

</body>
</html>