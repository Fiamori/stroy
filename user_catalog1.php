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
    padding: 0 50px;
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

.auth-btn-container {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

main {
    margin-top: 1px;
}

.banner {
    width: 950px;
    margin: 0 auto;
}

.pagination a {
    background-color: #d3d3d3;
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    margin: 0 5px;
    border-radius: 4px;
    cursor: pointer;
}

.pagination a.active {
    background-color: #bdbdbd;
}

.pagination a:hover {
    background-color: #F5DEB3;
    color: black;
}

.auth-btn-container {
    position: relative;
    display: inline-block;
}

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

.dropdown-content a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.auth-btn-container .dropdown-content.active {
    display: block;
}

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

/* Модальные окна */
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

.modal-content {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    width: 600px;
    max-width: 90%;
    box-sizing: border-box;
    position: relative;
}

.modal-content img {
    max-width: 300px;
    max-height: 300px;
    object-fit: cover;
}

.close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 30px;
    color: #aaa;
    cursor: pointer;
    z-index: 1001;
}

/* Модальное окно профиля */
.modalP {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: flex-start;
    padding-top: 20px;
    padding-bottom: 20px;
    overflow-y: auto;
}

.modal-contentP {
    background-color: #fff;
    position: relative;
    padding: 15px;
    border-radius: 8px;
    width: 90%;
    max-width: 340px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    margin: auto;
    max-height: 90vh;
    overflow-y: auto;
}

.profile-title {
    margin-bottom: 12px;
    font-size: 18px;
    color: #333;
}

.profile-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.profile-photo img {
    border-radius: 50%;
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 2px solid #ccc;
}

.profile-details {
    text-align: left;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    padding: 0 5px;
}

.profile-details p {
    margin: 5px 0;
    font-size: 13px;
    color: #555;
}

/* Стили для формы редактирования */
#editProfileForm {
    width: 100%;
    box-sizing: border-box;
    padding: 0 5px;
}

#editProfileForm label {
    margin-top: 8px;
    font-size: 13px;
}

#editProfileForm input {
    width: calc(100% - 16px);
    padding: 6px 8px;
    margin-top: 3px;
    font-size: 13px;
}

#editProfileForm button {
    margin-top: 15px;
    padding: 8px 15px;
    font-size: 14px;
}

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
    border-radius: 5px;
}

.edit-profile-btn:hover {
    background-color: #a0865f;
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
    background-color: #D2B48C;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: #a0865f;
}

.dropdown {
    position: relative;
}

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
}

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

.dropdown:hover .dropdown-menu {
    display: block;
}

ul {
    margin: 0;
    padding: 0;
}

.popular-products h2 {
    margin-top: 40px;
    margin-bottom: 30px;
    font-size: 35px;
    color: #333;
}

/* Уведомление */
.notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    display: none;
    animation: slideIn 0.5s forwards;
}

.notification-content {
    background-color: #ff9800;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.notification-success .notification-content {
    background-color: #4CAF50;
}

.notification-warning .notification-content {
    background-color: #ff9800;
}

.notification-error .notification-content {
    background-color: #f44336;
}

.notification-message {
    flex-grow: 1;
}

.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    margin-left: 15px;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.notification.hide {
    animation: slideOut 0.5s forwards;
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Стили для количества товара */
.stock-info {
    margin: 10px 0;
    font-weight: bold;
}

.stock-high {
    color: #4CAF50;
}

.stock-medium {
    color: #FFC107;
}

.stock-low {
    color: #f44336;
}

.quantity-input {
    width: 60px;
    padding: 5px;
    margin-right: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
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
    margin: 2px 0;
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

.product {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px;
    height: 320px;
    display: flex;
    flex-direction: column;
}

.product img {
    width: 100%;
    height: 180px;
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
                font-weight: bold;
                font-size: 18px;">
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
                                     data-stock='{$row['stock_quantity']}'
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

<!-- Модальное окно товара -->
<div id="productModal" class="modal">
    <div class="modal-content" style="background-color: rgba(255, 255, 255, 0.9);">
        <span class="close" onclick="closeProductModal()">&times;</span>
        <div class="modal-image" style="width: 300px; height: 300px; overflow: hidden;">
            <img id="modalImg" src="" alt="Изображение товара" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
        </div>
        <div class="modal-details">
            <h3 id="modalName"></h3>
            <p id="modalDescription"></p>
            <p id="modalPrice"></p>
            <p id="modalStock" class="stock-info"></p>
            <form id="addToCartForm" method="POST" onsubmit="addToCart(event)">
                <input type="hidden" id="product_id" name="product_id" value="">
                <input type="number" id="quantity" name="quantity" value="1" min="1" class="quantity-input" required>
                <button type="submit" class="add-to-cart-btn">Добавить в корзину</button>
            </form>
            <p id="cartMessage" style="color: green;"></p>
        </div>
    </div>
</div>

<!-- Модальное окно профиля -->
<div id="profileModal" class="modalP">
    <div class="modal-contentP">
        <span class="close" onclick="closeProfileModal()">&times;</span>
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
        <button id="editProfileBtn" class="edit-profile-btn">Изменить</button>
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

<!-- Уведомление -->
<div id="notification" class="notification">
    <div class="notification-content">
        <span class="notification-message"></span>
        <button class="notification-close" onclick="hideNotification()">&times;</button>
    </div>
</div>

<?php
// Подключаем подвал
include('includes/footer.php');
?>

<script>
// Управление модальным окном товара
function openModal(element) {
    document.getElementById('modalImg').src = element.getAttribute('data-image');
    document.getElementById('modalName').innerText = element.getAttribute('data-name');
    document.getElementById('modalDescription').innerText = element.getAttribute('data-description');
    document.getElementById('modalPrice').innerText = "Цена: " + element.getAttribute('data-price') + " руб.";
    document.getElementById('product_id').value = element.getAttribute('data-id');
    
    // Отображение количества товара на складе
    const stock = parseInt(element.getAttribute('data-stock'));
    const stockElement = document.getElementById('modalStock');
    
    if (stock > 50) {
        stockElement.className = 'stock-info stock-high';
        stockElement.textContent = 'В наличии: ' + stock + ' шт.';
    } else if (stock > 10) {
        stockElement.className = 'stock-info stock-medium';
        stockElement.textContent = 'В наличии: ' + stock + ' шт.';
    } else if (stock > 0) {
        stockElement.className = 'stock-info stock-low';
        stockElement.textContent = 'Осталось мало: ' + stock + ' шт.';
    } else {
        stockElement.className = 'stock-info stock-low';
        stockElement.textContent = 'Нет в наличии';
    }
    
    document.getElementById('productModal').style.display = 'flex';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Управление модальным окном профиля
function openProfileModal() {
    document.getElementById('profileModal').style.display = 'flex';
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

// Редактирование профиля
document.getElementById('editProfileBtn').addEventListener('click', () => {
    const form = document.getElementById('editProfileForm');
    const details = document.querySelector('.profile-details');
    
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        details.style.display = 'none';
        document.getElementById('editProfileBtn').textContent = 'Отмена';
    } else {
        form.classList.add('hidden');
        details.style.display = 'block';
        document.getElementById('editProfileBtn').textContent = 'Изменить';
    }
});

// Закрытие модальных окон при клике вне их области
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('productModal')) {
        closeProductModal();
    }
    if (event.target === document.getElementById('profileModal')) {
        closeProfileModal();
    }
});

// Закрытие модальных окон по клавише Esc
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeProductModal();
        closeProfileModal();
    }
});

// Управление выпадающим меню пользователя
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');

    userMenuButton.addEventListener('click', function() {
        userDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function(event) {
        if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('active');
        }
    });
});

// Управление уведомлениями
function showNotification(message, type = 'warning') {
    const notification = document.getElementById('notification');
    const messageElement = notification.querySelector('.notification-message');
    
    // Устанавливаем класс в зависимости от типа уведомления
    notification.className = 'notification';
    if (type === 'success') {
        notification.classList.add('notification-success');
    } else if (type === 'error') {
        notification.classList.add('notification-error');
    } else {
        notification.classList.add('notification-warning');
    }
    
    messageElement.textContent = message;
    notification.classList.remove('hide');
    notification.style.display = 'flex';
    
    setTimeout(() => {
        hideNotification();
    }, 5000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.classList.add('hide');
    
    setTimeout(() => {
        notification.style.display = 'none';
        notification.classList.remove('hide');
    }, 500);
}

// Прокрутка к футеру
function scrollToFooter() {
    document.getElementById('contacts').scrollIntoView({ behavior: 'smooth' });
}

// Функция добавления товара в корзину
function addToCart(event) {
    event.preventDefault();
    
    const productId = document.getElementById('product_id').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    const stock = parseInt(document.getElementById('modalStock').textContent.match(/\d+/)?.[0] || 0);
    
    // Проверка наличия товара
    if (stock <= 0) {
        showNotification('Этот товар закончился на складе', 'error');
        return;
    }
    
    // Проверка количества
    if (quantity > stock) {
        showNotification('Недостаточно товара на складе. Доступно: ' + stock + ' шт.', 'error');
        return;
    }
    
    // Отправка данных на сервер
    const formData = new FormData(document.getElementById('addToCartForm'));
    
    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар добавлен в корзину', 'success');
            closeProductModal();
        } else {
            showNotification(data.message || 'Ошибка при добавлении товара', 'error');
        }
    })
    .catch(error => {
        showNotification('Товар добавлен в корзину', 'success');
        console.error('Error:', error);
    });
}
</script>
</body>
</html>