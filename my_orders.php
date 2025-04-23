<?php
session_start();

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}

$user_id = $_SESSION['user_id'];

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Удаление заказа
    if (isset($_POST['delete_order']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        
        // Проверяем статус заказа
        $status_check = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND customer_id = ?");
        $status_check->bind_param("ii", $order_id, $user_id);
        $status_check->execute();
        $status_result = $status_check->get_result();
        
        if ($status_result->num_rows > 0) {
            $order = $status_result->fetch_assoc();
            if ($order['status'] == 'В обработке') {
                // Начинаем транзакцию
                $conn->begin_transaction();
                
                try {
                    // Сначала удаляем из order_details
                    $delete_details = "DELETE FROM order_details WHERE order_id = $order_id";
                    if (!$conn->query($delete_details)) {
                        throw new Exception("Ошибка удаления деталей заказа: " . $conn->error);
                    }
                    
                    // Затем удаляем сам заказ
                    $delete_order = "DELETE FROM orders WHERE order_id = $order_id";
                    if (!$conn->query($delete_order)) {
                        throw new Exception("Ошибка удаления заказа: " . $conn->error);
                    }
                    
                    // Если все успешно - коммитим
                    $conn->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    // Откатываем в случае ошибки
                    $conn->rollback();
                    echo json_encode(['error' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['error' => 'Отменить заказ можно только на стадии обработки']);
            }
        } else {
            echo json_encode(['error' => 'Заказ не найден или не принадлежит вам']);
        }
        exit;
    }
}

// Получаем заказы пользователя с группировкой по order_id
$query = "SELECT o.order_id, o.status, 
                 GROUP_CONCAT(p.product_name SEPARATOR ', ') as products,
                 GROUP_CONCAT(od.quantity SEPARATOR ', ') as quantities,
                 GROUP_CONCAT(od.price_at_purchase SEPARATOR ', ') as prices
          FROM orders o
          JOIN order_details od ON o.order_id = od.order_id
          JOIN products p ON od.product_id = p.product_id
          WHERE o.customer_id = ?
          GROUP BY o.order_id
          ORDER BY o.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Получение данных о магазине
$store_query = "SELECT * FROM store_info LIMIT 1";
$store_result = $conn->query($store_query);
$store_info = $store_result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #555;
        }

        /* Контент */
        .order-container {
        max-width: 900px;
        margin: 50px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .table-wrapper {
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #a0865f;
        color: white;
    }

    .no-orders {
        font-size: 18px;
        color: #666;
    }

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
/* Уменьшаем расстояние между шапкой и баннером */
main {
    margin-top: 1px; /* Уменьшаем отступ между шапкой и баннером */
}

/* Размеры баннера */
.banner {
    width: 950px;  /* Пример фиксированной ширины */
    margin: 0 auto;  /* Центрирование баннера */
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
        background-color: #f5deb3; /* Бежевый цвет */
        border: none;
        color: #000; /* Чёрный текст */
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease; /* Плавное изменение цвета */
    }

    /* Стиль для кнопки при наведении */
    .add-to-cart-btn:hover {
        background-color: #8b4513; /* Коричневый цвет */
        color: #fff; /* Белый текст для контраста */
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


/* Стили модального окна профиля*/
/* Основное модальное окно */
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
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
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
    background-color:rgb(207, 182, 131);
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

.edit-profile-btn:hover {
    background-color:#7a6e4a;
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
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

form button {
    margin-top: 20px;
    padding: 10px 20px;
    background-color:#cfb683;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: #a0865f;
}

/* ПОДВАЛ */
footer {
    padding: 20px;
    background-color: #f1f1f1;
    color: black;
    font-family: Arial, sans-serif;
}

footer div {
    max-width: 1200px;
    margin: 0 auto;
}

footer div p {
    margin: 5px 0;
}

footer a {
    color: black;
    text-decoration: none;
}

footer a:hover {
    text-decoration: underline;
}

footer div.flex-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
    text-align: left;
}

/* Кнопка Каталог */

/* Убедимся, что родитель имеет позицию для контекста */
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
    margin-top: 40px; /* Отступ сверху */
    margin-bottom: 30px; /* Отступ снизу */
    font-size: 35px; /* Размер шрифта */
    color: #333; /* Цвет текста */
}

.back-to-cart {
        display: inline-block;
        padding: 8px 15px;
        background-color: #cfb683;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }

    .back-to-cart:hover {
        background-color: #7a6e4a;
    }

/* Добавляем стили для контекстного меню */
.order-row {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .order-row:hover {
            background-color: #f1f1f1;
        }
        
        .context-menu {
            position: absolute;
            display: none;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .context-menu-item {
            padding: 8px 15px;
            cursor: pointer;
        }
        
        .context-menu-item:hover {
            background-color: #f5f5f5;
        }
        
        .delete-option {
            color: #d9534f;
        }
        
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            z-index: 1001;
        }
        
        .notification {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
        }
        
        .notification.error {
            background-color: #d9534f;
        }

    </style>
</head>
<body>

<!-- Шапка -->
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
                    <li><button onclick="location.href='user.php'">Главная</button></li>

                </ul>
            </nav>
        </div>
    </header>

    <div class="order-container">
    <h2>Ваши заказы</h2>
   
    <?php if ($result->num_rows > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Номер заказа</th>
                        <th>Товары</th>
                        <th>Количество</th>
                        <th>Цены</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="order-row" data-order-id="<?php echo $row['order_id']; ?>">
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['products']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantities']); ?></td>
                            <td><?php echo htmlspecialchars($row['prices']); ?> руб.</td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="no-orders">У вас нет заказов.</p>
    <?php endif; ?>

    <!-- Кнопка "Назад в корзину" -->
    <div style="text-align: right; margin-top: 20px;">
        <a href="cart.php" class="back-to-cart">⬅ Назад в корзину</a>
    </div>
</div>

<!-- Контекстное меню -->
<div class="context-menu" id="contextMenu">
    <div class="context-menu-item delete-option" id="deleteOrderOption">Отменить заказ</div>
</div>

<!-- Диалог подтверждения -->
<div class="confirmation-dialog" id="confirmationDialog">
    <p>Вы уверены, что хотите отменить этот заказ?</p>
    <div style="display: flex; justify-content: space-around; margin-top: 15px;">
        <button id="confirmDelete" style="background-color: #d9534f; color: white; border: none; padding: 5px 15px; border-radius: 3px;">Отменить</button>
        <button id="cancelDelete" style="background-color: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 3px;">Нет</button>
    </div>
</div>

<!-- Уведомление -->
<div class="notification" id="statusNotification"></div>

<script>
    // Глобальные переменные для хранения выбранного заказа
    let selectedOrderId = null;
    let selectedOrderRow = null;
    
    // Обработчик правой кнопки мыши
    document.addEventListener('DOMContentLoaded', function() {
        const orderRows = document.querySelectorAll('.order-row');
        const contextMenu = document.getElementById('contextMenu');
        const deleteOrderOption = document.getElementById('deleteOrderOption');
        const confirmationDialog = document.getElementById('confirmationDialog');
        const confirmDelete = document.getElementById('confirmDelete');
        const cancelDelete = document.getElementById('cancelDelete');
        const notification = document.getElementById('statusNotification');
        
        // Показываем контекстное меню по правой кнопке мыши
        orderRows.forEach(row => {
            row.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                
                // Запоминаем выбранный заказ
                selectedOrderId = this.getAttribute('data-order-id');
                selectedOrderRow = this;
                
                // Позиционируем меню
                contextMenu.style.display = 'block';
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.top = `${e.pageY}px`;
            });
        });
        
        // Скрываем меню при клике вне его
        document.addEventListener('click', function() {
            contextMenu.style.display = 'none';
        });
        
        // Обработчик выбора "Отменить заказ" в контекстном меню
        deleteOrderOption.addEventListener('click', function() {
            contextMenu.style.display = 'none';
            confirmationDialog.style.display = 'block';
        });
        
        // Подтверждение удаления
        confirmDelete.addEventListener('click', function() {
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `delete_order=true&order_id=${selectedOrderId}`
            })
            .then(response => response.json())
            .then(data => {
                confirmationDialog.style.display = 'none';
                
                if (data.success) {
                    // Удаляем строку из таблицы
                    selectedOrderRow.remove();
                    
                    // Показываем уведомление
                    showNotification('Заказ успешно отменен');
                } else {
                    showNotification(data.error || 'Неизвестная ошибка', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Произошла ошибка при отмене заказа', 'error');
            });
        });
        
        // Отмена удаления
        cancelDelete.addEventListener('click', function() {
            confirmationDialog.style.display = 'none';
        });
        
        function showNotification(message, type = 'success') {
            notification.textContent = message;
            notification.className = 'notification ' + (type === 'error' ? 'error' : '');
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    });
    
    function scrollToFooter() {
        const footer = document.getElementById("contacts");
        footer.scrollIntoView({ behavior: "smooth" });
    }
</script>


<!-- Подвал -->
<footer class="footer">
<?php
// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1"; // Предполагаем, что в таблице всегда только одна запись
$result = $conn->query($query);

$store_info = null;
if ($result && $result->num_rows > 0) {
    $store_info = $result->fetch_assoc();
} else {
    echo "Ошибка при получении информации о магазине.";
}?>

<footer id="contacts" style="padding: 20px; background-color: #f1f1f1; width: 100%; color: black;">
    <?php if ($store_info): ?>
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; text-align: left; gap: 20px;">
            <!-- Левый блок -->
            <div>
                <p><strong><?php echo htmlspecialchars($store_info['name']); ?></strong></p>
                <p><?php echo htmlspecialchars($store_info['address']); ?></p>
                <p>ИНН: <?php echo htmlspecialchars($store_info['inn']); ?></p>
                <p>ОГРНИП: <?php echo htmlspecialchars($store_info['ogrnip']); ?></p>
            </div>
            <!-- Правый блок -->
            <div>
                <p>Телефон: <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $store_info['phone'])); ?>" 
                              style="color: black; text-decoration: none; border-bottom: 1px dashed #666;"
                              onmouseover="this.style.color='#A0522D'; this.style.textDecoration='underline';" 
                              onmouseout="this.style.color='black'; this.style.textDecoration='none';">
                        <?php echo htmlspecialchars($store_info['phone']); ?>
                    </a>
                </p>
                <p>Email: <a href="mailto:<?php echo htmlspecialchars($store_info['email']); ?>" 
                            style="color: black; text-decoration: none; border-bottom: 1px dashed #666;"
                            onmouseover="this.style.color='#A0522D'; this.style.textDecoration='underline';" 
                            onmouseout="this.style.color='black'; this.style.textDecoration='none';">
                        <?php echo htmlspecialchars($store_info['email']); ?>
                    </a>
                </p>
                <p>Владелица: <?php echo htmlspecialchars($store_info['owner_name']); ?></p>
            </div>
        </div>
        <div style="text-align: center; margin-bottom: 10px;">
            <p>&copy; 2024 Магазин Обоев. Все права защищены</p>
        </div>
    <?php else: ?>
        <p style="text-align: center;">Информация о магазине временно недоступна.</p>
    <?php endif; ?>
</footer>

</body>
</html>

<?php
// Закрытие соединения с БД
$conn->close();
?>
