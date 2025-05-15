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
            /* Прозрачный белый фон */
            backdrop-filter: blur(10px);
            /* Размытие фона */
            position: sticky;
            /* Шапка фиксируется вверху страницы */
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            /* Легкая тень для отделения */
            padding: 15px 0;
            /* Меньше отступов сверху и снизу */
        }

        /* Контейнер внутри шапки */
        .header-container1 {
            display: flex;
            justify-content: space-between;
            /* Оставляем элементы с равным распределением */
            align-items: center;
            max-width: 1000px;
            /* Максимальная ширина шапки */
            margin: 0 auto;
            /* Центрируем весь контейнер */
            padding: 0 50px;
            /* Отступы слева и справа */
        }

        /* Логотип */
        .logo-img {
            height: 60px;
            /* Увеличиваем размер логотипа */
        }

        .logo {
            flex-grow: 1;
            /* Растягиваем блок логотипа для балансировки */
        }

        nav {
            flex: 2;
            /* Пространство для кнопок навигации */
            display: flex;
            justify-content: center;
            /* Центрируем кнопки навигации */
        }

        /* Навигация */
        nav ul {
            display: flex;
            gap: 15px;
            /* Расстояние между пунктами меню */
            list-style: none;
        }

        nav ul li button {
            background: none;
            border: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            padding: 10px 15px;
            /* Добавляем внутренние отступы */
            transition: color 0.3s;
        }

        nav ul li button:hover {
            color: #A0522D;
            /* Цвет текста при наведении */
        }

        /* Стиль для кнопок пагинации */
        .pagination a {
            background-color: #d3d3d3;
            /* Серый фон для кнопок */
            color: black;
            /* Цвет текста */
            padding: 8px 16px;
            /* Отступы вокруг текста */
            text-decoration: none;
            /* Убираем подчеркивание */
            margin: 0 5px;
            /* Отступы между кнопками */
            border-radius: 4px;
            /* Закругление углов */
            cursor: pointer;
            /* Курсор в виде указателя */
        }

        /* Стиль для активной страницы */
        .pagination a.active {
            background-color: #bdbdbd;
            /* Цвет активной кнопки */
        }

        /* Стиль для кнопок пагинации при наведении */
        .pagination a:hover {
            background-color: #F5DEB3;
            /* Бежевый цвет при наведении */
            color: black;
            /* Цвет текста при наведении */
        }

        /* Стиль для модального окна (по умолчанию скрыто) */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            display: none;
        }

        /* Стиль для контента модального окна */
        .modal-content {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 600px;
            /* Ширина окна */
            max-width: 90%;
            box-sizing: border-box;
            display: flex;
            /* Используем flexbox для размещения элементов */
            align-items: center;
            /* Центрируем элементы по вертикали */
            gap: 20px;
            /* Отступ между картинкой и текстом */
            position: relative;
        }

        /* Стиль для картинки */
        .modal-content img {
            max-width: 300px;
            /* Ограничиваем ширину картинки */
            max-height: 300px;
            /* Ограничиваем высоту картинки */
            object-fit: cover;
            /* Сохраняем пропорции */
        }

        /* Стиль для текста */
        .modal-content .text {
            flex: 1;
            /* Текст занимает оставшееся пространство */
            text-align: left;
            /* Выравниваем текст по левому краю */
        }

        /* Стиль для модального окна (по умолчанию скрыто) */
        .modal1 {
            display: none;
            /* Модальное окно скрыто по умолчанию */
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
            /* Закругление углов */
            padding: 20px;
            width: 400px;
            /* Ширина окна */
            max-width: 90%;
            /* Ограничение максимальной ширины */
            box-sizing: border-box;
            /* Учитывать padding в расчете ширины */
            position: relative;
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
            /* Убираем подчеркивание */
            color: black;
            /* Черный цвет текста */
        }

        /* Стиль для формы */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Отступы между элементами формы */
        }
        
        .popular-products h2 {
            margin-bottom: 30px;
            /* Отступ снизу */
            text-align: center;
            /* Дополнительно: выравнивание по центру */
            font-size: 35px;
            /* Размер шрифта */
            color: #333;
            /* Цвет текста */
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

        /* Кнопка Каталог */

        /* Скрытое меню по умолчанию */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            /* Появляется под кнопкой */
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

        .catalog-container {
            display: flex;
            gap: 0px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .filters {
            width: 200px;
            /* Уменьшили ширину */
            padding: 15px;
            background: rgb(255, 255, 255);
            border-radius: 0px;
        }

        .filters h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .filters label {
            display: flex;
            align-items: center;
            margin: 2px 0;
            /* Уменьшили отступы между чекбоксами */
            cursor: pointer;
            font-size: 14px;
        }

        .filters input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
        }

        .filters button {
            margin-top: 10px;
            padding: 6px 12px;
            background: #333;
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
            height: 320px;
            /* Фиксированная высота */
            display: flex;
            flex-direction: column;
        }

        .product img {
            width: 100%;
            height: 180px;
            /* Фиксированная высота изображения */
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
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
                    <button onclick="location.href='index.php'" style="background: none; border: none; color: black; font: inherit; cursor: pointer; padding: 0; font-weight: bold; font-size: 18px;">
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

        <main>
            <div class="catalog-container">
                <aside class="filters" style="margin: 100px 0 0 0;">
                    <h3>Тип покрытия</h3>
                    <form id="filterForm" method="get" action="ingex_catalog1.php">
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
                            <button type="button" onclick="location.href='ingex_catalog1.php'">Сбросить</button>
                        </div>
                        <input type="hidden" name="page" value="1">
                    </form>
                </aside>

                <section class="popular-products">
                    <h2>Напольные покрытия</h2>
                    <div class="product-list">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "
                                <div class='product'>
                                    <img src='{$row['image_path']}' alt='{$row['product_name']}' 
                                         data-name='{$row['product_name']}' 
                                         data-description='{$row['description']}'
                                         data-price='{$row['price']}'
                                         data-image='{$row['image_path']}'
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
                            $prev_url = 'ingex_catalog1.php?page=' . ($page - 1);
                            if (!empty($selected_types)) {
                                foreach ($selected_types as $type) {
                                    $prev_url .= '&type[]=' . urlencode($type);
                                }
                            }
                            echo "<a href='$prev_url'>&laquo; Назад</a>";
                        }

                        // Показать номера страниц
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $page_url = 'ingex_catalog1.php?page=' . $i;
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
                            $next_url = 'ingex_catalog1.php?page=' . ($page + 1);
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

        <!-- Модальное окно товара -->
        <div id="productModal" class="modal" style="background-color: rgba(65, 63, 58, 0.7);">
            <div class="modal-content" style="background-color: rgba(255, 255, 255, 0.9);">
                <div class="modal-image" style="width: 300px; height: 300px; overflow: hidden;">
                    <img id="modalImg" src="" alt="Изображение товара" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                </div>
                <div class="modal-details">
                    <h3 id="modalName"></h3>
                    <p id="modalDescription"></p>
                    <p id="modalPrice"></p>
                    <button id="addToCartButton" class="add-to-cart-btn" onclick="addToCart()">Добавить в корзину</button>
                </div>
                <span class="close" onclick="closeProductModal()">&times;</span>
            </div>
        </div>

        <!-- Модальное окно для авторизации -->
        <div id="authModal" class="modal1">
            <div class="modal-content1">
                <span class="close" onclick="closeModal('authModal')">&times;</span>
                <h2>Авторизация</h2>
                <form id="authForm" action="auth.php" method="POST">
                    <div style="text-align: center; width: 100%;">
                        <label for="email" style="display: none;">Почта:</label>
                        <input type="email" id="email" name="email" placeholder="Почта" required style="width: 90%; padding: 10px; margin-bottom: 15px;">

                        <label for="password" style="display: none;">Пароль:</label>
                        <input type="password" id="password" name="password" placeholder="Пароль" required style="width: 90%; padding: 10px; margin-bottom: 15px;">

                        <button type="submit" style="border: none; background-color: #D2B48C; color: white; cursor: pointer; width: 100%;">Войти</button>

                    </div>
                </form>
                <p>Нет аккаунта? <button onclick="openRegisterModal()" style="border: none; background: none; color: #0066cc; cursor: pointer;">Зарегистрироваться</button></p>
            </div>
        </div>

        <!-- Модальное окно для регистрации -->
        <div id="registerModal" class="modal1">
        <div class="modal-content1">
            <span class="close" onclick="closeModal('registerModal')">&times;</span>
            <h2>Регистрация</h2>
            <form id="registerForm" action="registration.php" method="POST" enctype="multipart/form-data" onsubmit="return validateRegistrationForm()">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; width: 95%;">
                    <div style="flex: 1; min-width: 300px;">
                        <input type="text" id="first_name" name="first_name" placeholder="Имя" required>
                        <input type="text" id="last_name" name="last_name" placeholder="Фамилия" required>
                        <input type="tel" id="phone_number" name="phone_number" placeholder="Телефон" pattern="\d{10,15}" required>
                        <input type="email" id="email" name="email" placeholder="Почта" required>
                        <input type="text" id="address" name="address" placeholder="Адрес" required>
                        <input type="password" id="password" name="password" placeholder="Пароль" required>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Подтверждение пароля" required>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                </div>
                <button type="submit" style="background-color: #D2B48C; color: white; cursor: pointer; width: 100%;">Зарегистрироваться</button>
            </form>
        </div>
    </div>

        <!-- Футер -->
        <?php
        // Подключаем подвал
        include('includes/footer.php');
        ?>
    </div>

    <script>
        // Функция для отображения уведомлений
        function showNotification(message, type = 'info', duration = 5000) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
            <span>${message}</span>
            <span class="notification-close">&times;</span>
        `;

            container.appendChild(notification);

            // Закрытие по клику
            notification.querySelector('.notification-close').addEventListener('click', () => {
                notification.classList.add('hide');
                setTimeout(() => notification.remove(), 500);
            });

            // Автоматическое закрытие
            if (duration > 0) {
                setTimeout(() => {
                    notification.classList.add('hide');
                    setTimeout(() => notification.remove(), 500);
                }, duration);
            }

            return notification;
        }

        // Функция для открытия модального окна товара
        function openModal(element) {
            document.getElementById('modalImg').src = element.getAttribute('data-image');
            document.getElementById('modalName').innerText = element.getAttribute('data-name');
            document.getElementById('modalDescription').innerText = element.getAttribute('data-description');
            document.getElementById('modalPrice').innerText = 'Цена: ' + element.getAttribute('data-price') + ' руб.';
            document.getElementById('productModal').style.display = 'flex';
        }

        // Функция для закрытия модального окна товара
        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Закрытие модального окна товара при клике вне его содержимого
        document.getElementById('productModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeProductModal();
            }
        });

        // Закрытие всех модальных окон при нажатии Esc
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProductModal();
                document.querySelectorAll('.modal1').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Функция добавления товара в корзину
        function addToCart() {
            const productName = document.getElementById('modalName').innerText;
            const productPrice = document.getElementById('modalPrice').innerText.replace('Цена: ', '').replace(' руб.', '');

            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            const existingProductIndex = cart.findIndex(product => product.name === productName);
            if (existingProductIndex !== -1) {
                cart[existingProductIndex].quantity += 1;
            } else {
                cart.push({
                    name: productName,
                    price: productPrice,
                    quantity: 1
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            showNotification('Вы не авторизованы! Пожалуйста, войдите в систему.', 'warning');
            closeProductModal();
        }

        // Функция для прокрутки к футеру
        function scrollToFooter() {
            document.getElementById('contacts').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Обработчики форм после загрузки DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Обработчик для формы авторизации
            document.getElementById('authForm')?.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(this);

                fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showNotification('Авторизация успешна', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Ошибка при авторизации', 'error');
                        console.error('Ошибка:', error);
                    });
            });

            // Обработчик для формы регистрации
            document.getElementById('registerForm')?.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(this);

                fetch('registration.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showNotification('Регистрация успешна! Теперь вы можете войти.', 'success');
                            setTimeout(() => {
                                document.getElementById('registerModal').style.display = 'none';
                                document.getElementById('authModal').style.display = 'flex';
                            }, 1500);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Ошибка при регистрации', 'error');
                        console.error('Ошибка:', error);
                    });
            });
        });

        // Функции для работы с модальными окнами авторизации/регистрации
        function openAuthModal() {
            document.getElementById('authModal').style.display = 'flex';
        }

        function openRegisterModal() {
            document.getElementById('authModal').style.display = 'none';
            document.getElementById('registerModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Валидация формы регистрации
        function validateRegistrationForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const phoneNumber = document.getElementById('phone_number').value;

            if (password !== confirmPassword) {
                showNotification('Пароли не совпадают', 'error');
                return false;
            }

            if (!/^[0-9]+$/.test(phoneNumber)) {
                showNotification('Номер телефона должен содержать только цифры', 'error');
                return false;
            }

            return true;
        }

        // Функция проверки авторизации
        function checkAuthorization() {
            const isAuthorized = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            if (isAuthorized) {
                location.href = 'cart.php';
            } else {
                showNotification('Вы не авторизованы! Пожалуйста, войдите в систему.', 'warning');
                openAuthModal();
            }
        }

        // Закрытие модальных окон по клику вне области
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal1')) {
                closeModal(event.target.id);
            }
        });

        // Показать уведомления из PHP сессии, если они есть
        <?php if (isset($_SESSION['notification'])): ?>
            showNotification('<?php echo $_SESSION['notification']['message']; ?>', '<?php echo $_SESSION['notification']['type']; ?>');
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>
    </script>
</body>

</html>