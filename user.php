<?php
session_start();

// Проверка авторизации пользователя
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
$query = "SELECT * FROM store_info LIMIT 1";
$result = $conn->query($query);

$store_info = null;
if ($result && $result->num_rows > 0) {
    $store_info = $result->fetch_assoc();
} else {
    echo "Ошибка при получении информации о магазине.";
}

// Получение данных пользователя
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, phone_number, address, image_path FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = null;
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Ошибка при получении данных пользователя.";
}

// Запрос на получение баннеров
$query = "SELECT image_url FROM banners";
$result = $conn->query($query);

$banners = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banners[] = $row['image_url'];
    }
}

// Закрытие соединения с базой данных
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
    align-items: center;
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
    align-items: flex-start; /* Изменено на flex-start для прокрутки */
    padding-top: 20px;
    padding-bottom: 20px;
    overflow-y: auto; /* Добавлена возможность прокрутки */
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
    margin: auto; /* Центрирование */
    max-height: 90vh; /* Ограничение высоты */
    overflow-y: auto; /* Прокрутка внутри окна */
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
    width: calc(100% - 16px); /* Учитываем padding */
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
    background-color: #ff4444;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    <section class="banner">
        <div class="banner-slider">
            <?php foreach ($banners as $banner): ?>
                <div class="slide">
                    <img src="<?php echo htmlspecialchars($banner); ?>" alt="Баннер">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="slider-controls">
            <button class="prev">❮</button>
            <button class="next">❯</button>
        </div>
    </section>

    <section class="popular-products">
        <h2>Популярные обои</h2>
        <div class="product-list">
            <?php
                $conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql_wallpapers = "SELECT * FROM products WHERE category_id = 2 LIMIT 6";
                $result_wallpapers = $conn->query($sql_wallpapers);

                if ($result_wallpapers->num_rows > 0) {
                    while ($row = $result_wallpapers->fetch_assoc()) {
                        echo "
                            <div class='product' style='border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin: 10px;'>
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
                    echo "<p>Популярные обои не найдены.</p>";
                }
            ?>
        </div>
    </section>

    <section class="popular-products">
        <h2>Популярные напольные покрытия</h2>
        <div class="product-list">
            <?php
                $sql_floorings = "SELECT * FROM products WHERE category_id = 1 LIMIT 6";
                $result_floorings = $conn->query($sql_floorings);

                if ($result_floorings->num_rows > 0) {
                    while ($row = $result_floorings->fetch_assoc()) {
                        echo "
                            <div class='product' style='border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin: 10px;'>
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
                    echo "<p>Популярные напольные покрытия не найдены.</p>";
                }

                $conn->close();
            ?>
        </div>
    </section>
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
            <form method="POST" action="add_to_cart.php">
                <input type="hidden" id="product_id" name="product_id" value="">
                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
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
function showNotification(message) {
    const notification = document.getElementById('notification');
    const messageElement = notification.querySelector('.notification-message');
    
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

// Слайдер баннеров
let currentIndex = 0;
const slides = document.querySelectorAll('.banner-slider .slide');
const totalSlides = slides.length;

function showSlide(index) {
    const slider = document.querySelector('.banner-slider');
    const offset = -index * 100;
    slider.style.transform = `translateX(${offset}%)`;
}

document.querySelector('.next').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % totalSlides;
    showSlide(currentIndex);
});

document.querySelector('.prev').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    showSlide(currentIndex);
});

setInterval(() => {
    currentIndex = (currentIndex + 1) % totalSlides;
    showSlide(currentIndex);
}, 5000);
</script>
</body>
</html>