<?php
// Старт сессии
session_start();

// Подключаем шапку
include('includes/header.php');
?>

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
                // Соединение с базой данных
                $conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Запрос для популярных обоев (категория 2)
                $sql_wallpapers = "SELECT * FROM products WHERE category_id = 2 LIMIT 6";
                $result_wallpapers = $conn->query($sql_wallpapers);

                // Выводим обои
                if ($result_wallpapers->num_rows > 0) {
                    while ($row = $result_wallpapers->fetch_assoc()) {
                        echo "
                            <div class='product' style='border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin: 10px;'>
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
                    echo "<p>Популярные обои не найдены.</p>";
                }
            ?>
        </div>
    </section>

    <section class="popular-products">
        <h2>Популярные напольные покрытия</h2>
        <div class="product-list">
            <?php
                // Запрос для популярных напольных покрытий (категория 1)
                $sql_floorings = "SELECT * FROM products WHERE category_id = 1 LIMIT 6";
                $result_floorings = $conn->query($sql_floorings);

                // Выводим напольные покрытия
                if ($result_floorings->num_rows > 0) {
                    while ($row = $result_floorings->fetch_assoc()) {
                        echo "
                            <div class='product' style='border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin: 10px;'>
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
                    echo "<p>Популярные напольные покрытия не найдены.</p>";
                }

                // Закрытие соединения
                $conn->close();
            ?>
        </div>
    </section>

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
            <form action="auth.php" method="POST">
                <div style="text-align: center; width: 100%;">
                    <label for="email" style="display: none;">Почта:</label>
                    <input type="email" id="email" name="email" placeholder="Почта" required style="width: 90%; padding: 10px; margin-bottom: 15px;">

                    <label for="password" style="display: none;">Пароль:</label>
                    <input type="password" id="password" name="password" placeholder="Пароль" required style="width: 90%; padding: 10px; margin-bottom: 15px;">

                    <button type="submit" style="border: none; background-color: #D2B48C; color: white; cursor: pointer; width: 100%;">Войти</button>

                    <button type="submit" style="border: none; background-color: #D2B48C; color: white; cursor: pointer; width: 100%;"><a href="admin_avto.php" style="text-decoration: none; color: white;">Войти как администратор</a></button>
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
</main>

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
        showNotification('Товар добавлен в корзину', 'success');
        closeProductModal();
    }

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

    // Прокрутка к контактам
    function scrollToFooter() {
        const footer = document.getElementById("contacts");
        footer.scrollIntoView({ behavior: "smooth" });
    }

    // Проверка авторизации
    const isAuthorized = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    function checkAuthorization() {
        if (isAuthorized) {
            location.href = 'cart.php';
        } else {
            showNotification('Вы не авторизованы! Пожалуйста, войдите в систему.', 'warning');
            openAuthModal();
        }
    }

    // AJAX для форм
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('form').addEventListener('submit', function(event) {
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
            });
        });

        document.getElementById('registerForm').onsubmit = function(event) {
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
        };
    });

    // Показать уведомления из PHP сессии, если они есть
    <?php if (isset($_SESSION['notification'])): ?>
        showNotification('<?php echo $_SESSION['notification']['message']; ?>', '<?php echo $_SESSION['notification']['type']; ?>');
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
</script>

<?php
// Подключаем подвал
include('includes/footer.php');
?>

</body>
</html>