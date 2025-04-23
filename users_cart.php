<?php
session_start();

// Подключение к базе данных
include('config/db_connect.php');

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обновление статуса
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        
        $update_query = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
        if ($conn->query($update_query) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
        exit;
    }
    
    // Удаление заказа
    if (isset($_POST['delete_order']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        
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
        exit;
    }

    // Получение полной информации о заказе
    if (isset($_POST['get_order_details'])) {
        $order_id = $_POST['order_id'];
        
        $order_query = "SELECT o.*, c.* FROM orders o 
                       JOIN customers c ON o.customer_id = c.customer_id
                       WHERE o.order_id = $order_id";
        $order_result = $conn->query($order_query);
        $order_data = $order_result->fetch_assoc();
        
        $products_query = "SELECT p.product_name, p.price, od.quantity 
                          FROM order_details od
                          JOIN products p ON od.product_id = p.product_id
                          WHERE od.order_id = $order_id";
        $products_result = $conn->query($products_query);
        $products = [];
        while ($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
        
        echo json_encode([
            'order' => $order_data,
            'products' => $products
        ]);
        exit;
    }
}

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1";
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

// Получаем все заказы
$query = "SELECT o.order_id, o.customer_id, o.order_date, o.total_price, o.status, c.first_name, c.last_name 
          FROM `orders` o 
          JOIN customers c ON o.customer_id = c.customer_id
          ORDER BY o.order_date DESC";
$result = $conn->query($query);

if (!$result) {
    die("Ошибка выполнения запроса: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(255, 255, 255);
            color: #333;
            font-family: Arial, sans-serif;
        }
        
        .header1 {
            background-color: #aaa;
            color: white;
            padding: 4px 20px;
        }
        
        .header-container1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 36px;
            font-weight: bold;
            color: white;
        }
        
        nav ul {
            display: flex;
            list-style-type: none;
            gap: 10px;
            margin: 0;
            padding: 0;
        }
        
        nav ul li {
            display: flex;
            align-items: center;
        }
        
        nav button {
            background-color: transparent;
            color: white !important;
            font-size: 18px;
            padding: 2px 8px;
            border: none;
            cursor: pointer;
            transition: color 0.3s, transform 0.3s;
        }
        
        nav button:hover {
            color: #aaa;
        }
        
        nav .auth-btn {
            background-color: transparent;
            color: #aaa !important;
        }
        
        nav .auth-btn:hover {
            color: #aaa;
        }
        
        .auth-btn-container {
            display: flex;
            align-items: center;
        }
        
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: white;
            cursor: pointer;
            background: none;
            border: none;
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
            background-color: #C0C0C0;
            color: black;
        }
        
        footer {
            padding: 20px;
            background-color: #ddd;
            color: #333;
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
            color: #333;
            text-decoration: none;
        }
        
        footer a:hover {
            text-decoration: underline;
        }
        
        footer div.flex-container {
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            text-align: left;
        }
        
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        
        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }

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

        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 3% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 70%;
            max-width: 700px;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        }

        .modal-header {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
            position: relative;
        }

        .modal-title {
            font-size: 1.5em;
            font-weight: bold;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .close-modal:hover {
            color: black;
            background: #e1e1e1;
        }

        .btn {
            background-color: #6c757d !important;
            color: white !important;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #5a6268 !important;
        }

        .order-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .order-details-table th, 
        .order-details-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-details-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<header class="header1">
    <div class="header-container1">
        <div class="logo">
            <button onclick="location.href='admin.php'" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0;">Администратор</button>
        </div>
        <nav>
            <ul>
                <li><button><a href="users_cart.php" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0;">Заказы</a></button></li>
                <li><button onclick="scrollToFooter()">Контакты</button></li>
                <li><button><a href="add.php" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0;">Добавить запись</a></button></li>
                <li><div class="auth-btn-container"><button class="auth-btn" onclick="openAuthModal()"><a href="index.php">Выйти</a></button></div></li>
            </ul>
        </nav>
    </div>
</header>

<h1 style="text-align: center;">Список заказов</h1>

<table>
    <thead>
        <tr>
            <th>Номер заказа</th>
            <th>Имя клиента</th>
            <th>Дата заказа</th>
            <th>Общая цена</th>
            <th>Статус</th>
            <th>Изменить статус</th>
            <th>Товары</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($order = $result->fetch_assoc()): ?>
            <tr class="order-row" data-order-id="<?php echo $order['order_id']; ?>">
                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                <td><?php echo htmlspecialchars($order['total_price']); ?> руб.</td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form class="status-form">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="status" class="status-select" required>
                            <option value="В обработке" <?php echo ($order['status'] == 'В обработке') ? 'selected' : ''; ?>>В обработке</option>
                            <option value="Принят" <?php echo ($order['status'] == 'Принят') ? 'selected' : ''; ?>>Принят</option>
                            <option value="В пути" <?php echo ($order['status'] == 'В пути') ? 'selected' : ''; ?>>В пути</option>
                            <option value="Доставлен" <?php echo ($order['status'] == 'Доставлен') ? 'selected' : ''; ?>>Доставлен</option>
                        </select>
                    </form>
                </td>
                <td>
                    <?php
                    $product_conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
                    if ($product_conn->connect_error) {
                        die("Ошибка подключения: " . $product_conn->connect_error);
                    }
                    
                    $order_id = $order['order_id'];
                    $query_products = "SELECT p.product_name 
                                     FROM order_details od 
                                     JOIN products p ON od.product_id = p.product_id 
                                     WHERE od.order_id = $order_id";
                    $products_result = $product_conn->query($query_products);
                    $products = [];
                    if ($products_result && $products_result->num_rows > 0) {
                        while ($product = $products_result->fetch_assoc()) {
                            $products[] = $product['product_name'];
                        }
                    }
                    
                    echo implode(', ', $products);
                    $product_conn->close();
                    ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Модальное окно для просмотра информации о заказе -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-modal"></span>
            <h2 class="modal-title">Информация о заказе #<span id="modalOrderId"></span></h2>
        </div>
        <div class="modal-body">
            <h3>Информация о клиенте</h3>
            <p><strong>Имя:</strong> <span id="customerName"></span></p>
            <p><strong>Телефон:</strong> <span id="customerPhone"></span></p>
            <p><strong>Email:</strong> <span id="customerEmail"></span></p>
            <p><strong>Адрес:</strong> <span id="customerAddress"></span></p>
            
            <h3>Детали заказа</h3>
            <p><strong>Дата заказа:</strong> <span id="orderDate"></span></p>
            <p><strong>Статус:</strong> <span id="orderStatus"></span></p>
            <p><strong>Общая сумма:</strong> <span id="orderTotal"></span> руб.</p>
            
            <h3>Товары</h3>
            <table class="order-details-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody id="orderProducts">
                    <!-- Сюда будут добавлены товары через JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button id="closeModal" class="btn">Закрыть</button>
        </div>
    </div>
</div>

<!-- Контекстное меню -->
<div class="context-menu" id="contextMenu">
    <div class="context-menu-item delete-option" id="deleteOrderOption">Удалить заказ</div>
</div>

<!-- Диалог подтверждения -->
<div class="confirmation-dialog" id="confirmationDialog">
    <p>Вы уверены, что хотите удалить этот заказ?</p>
    <div style="display: flex; justify-content: space-around; margin-top: 15px;">
        <button id="confirmDelete" style="background-color: #d9534f; color: white; border: none; padding: 5px 15px; border-radius: 3px;">Удалить</button>
        <button id="cancelDelete" style="background-color: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 3px;">Отмена</button>
    </div>
</div>

<!-- Уведомление -->
<div class="notification" id="statusNotification">Статус обновлен!</div>

<script>
    // Глобальные переменные для хранения выбранного заказа
    let selectedOrderId = null;
    let selectedOrderRow = null;
    
    // Получаем элементы модального окна
    const modal = document.getElementById("orderModal");
    const closeModal = document.getElementsByClassName("close-modal")[0];
    const closeModalBtn = document.getElementById("closeModal");
    
    // Обработчик DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        const orderRows = document.querySelectorAll('.order-row');
        const contextMenu = document.getElementById('contextMenu');
        const deleteOrderOption = document.getElementById('deleteOrderOption');
        const confirmationDialog = document.getElementById('confirmationDialog');
        const confirmDelete = document.getElementById('confirmDelete');
        const cancelDelete = document.getElementById('cancelDelete');
        
        // Обработчик клика по строке заказа (левая кнопка мыши)
        orderRows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Проверяем, был ли клик по select или его элементам
                const clickedElement = e.target;
                if (clickedElement.closest('.status-form') || clickedElement.closest('.status-select')) {
                    return; // Не открываем модальное окно при клике на форму статуса
                }
                
                const orderId = this.getAttribute('data-order-id');
                fetchOrderDetails(orderId);
            });
        });
        
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
        
        // Обработчик выбора "Удалить" в контекстном меню
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
                    showNotification('Заказ успешно удален');
                } else {
                    alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении заказа');
            });
        });
        
        // Отмена удаления
        cancelDelete.addEventListener('click', function() {
            confirmationDialog.style.display = 'none';
        });
        
        // Остальные обработчики (обновление статуса и т.д.)
        const statusSelects = document.querySelectorAll('select[name="status"]');
        const notification = document.getElementById('statusNotification');
        
        statusSelects.forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.closest('form').querySelector('input[name="order_id"]').value;
                const newStatus = this.value;
                
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Статус обновлен!');
                        
                        // Обновляем статус в таблице
                        const statusCell = this.closest('td').previousElementSibling;
                        statusCell.textContent = newStatus;
                    } else if (data.error) {
                        alert('Ошибка: ' + data.error);
                        // Возвращаем предыдущее значение
                        this.value = statusCell.textContent;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при обновлении статуса');
                });
            });
        });
        
        // Закрытие модального окна
        closeModal.addEventListener('click', function() {
            modal.style.display = "none";
        });
        
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = "none";
        });
        
        // Закрытие при клике вне модального окна
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    });
    
    // Функция для получения деталей заказа
    function fetchOrderDetails(orderId) {
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `get_order_details=true&order_id=${orderId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.order) {
                displayOrderDetails(data);
            } else {
                alert('Не удалось получить информацию о заказе');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при получении информации о заказе');
        });
    }
    
    // Функция для отображения деталей заказа в модальном окне
    function displayOrderDetails(data) {
        const order = data.order;
        const products = data.products;
        
        // Заполняем информацию о заказе
        document.getElementById('modalOrderId').textContent = order.order_id;
        document.getElementById('customerName').textContent = order.first_name + ' ' + order.last_name;
        document.getElementById('customerPhone').textContent = order.phone_number || 'Не указан';
        document.getElementById('customerEmail').textContent = order.email || 'Не указан';
        document.getElementById('customerAddress').textContent = order.address || 'Не указан';
        document.getElementById('orderDate').textContent = order.order_date;
        document.getElementById('orderStatus').textContent = order.status;
        document.getElementById('orderTotal').textContent = order.total_price;
        
        // Заполняем таблицу товаров
        const productsTable = document.getElementById('orderProducts');
        productsTable.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            const total = product.price * product.quantity;
            
            row.innerHTML = `
                <td>${product.product_name}</td>
                <td>${product.price} руб.</td>
                <td>${product.quantity}</td>
                <td>${total} руб.</td>
            `;
            
            productsTable.appendChild(row);
        });
        
        // Показываем модальное окно
        modal.style.display = "block";
    }
    
    function showNotification(message) {
        const notification = document.getElementById('statusNotification');
        notification.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    
    function scrollToFooter() {
        const footer = document.getElementById("contacts");
        footer.scrollIntoView({ behavior: "smooth" });
    }
</script>

<footer id="contacts" style="padding: 20px; background-color: #f1f1f1; width: 100%; color: black;">
    <?php if ($store_info): ?>
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; text-align: left; gap: 20px;">
            <div>
                <p><strong><?php echo htmlspecialchars($store_info['name']); ?></strong></p>
                <p><?php echo htmlspecialchars($store_info['address']); ?></p>
                <p>ИНН: <?php echo htmlspecialchars($store_info['inn']); ?></p>
                <p>ОГРНИП: <?php echo htmlspecialchars($store_info['ogrnip']); ?></p>
            </div>
            <div>
                <p>Телефон: <?php echo htmlspecialchars($store_info['phone']); ?></p>
                <p>Email: <a href="mailto:<?php echo htmlspecialchars($store_info['email']); ?>"><?php echo htmlspecialchars($store_info['email']); ?></a></p>
                <p>Владелица: <?php echo htmlspecialchars($store_info['owner_name']); ?></p>
            </div>
        </div>
        <div style="text-align: center; margin-bottom: 10px;">
            <p>&copy; 2024 Магазин Обоев. Все права защищены.</p>
        </div>
        <button id="editFooterButton" style="padding: 5px 10px; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <a href="edit_store_info.php">Изменить</a>
        </button>
    <?php else: ?>
        <p style="text-align: center;">Информация о магазине временно недоступна.</p>
    <?php endif; ?>
</footer>
</body>
</html>