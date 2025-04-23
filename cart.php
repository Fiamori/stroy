<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Подключение к БД
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Получение данных пользователя
$sql = "SELECT first_name, last_name, email, phone_number, address, image_path FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Получение товаров в корзине
$sql_cart = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price 
             FROM cart c
             JOIN products p ON c.product_id = p.product_id
             WHERE c.customer_id = ?";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param('i', $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();
$cart = $result_cart->fetch_all(MYSQLI_ASSOC);
$stmt_cart->close();

// Подсчет общей суммы
$totalPrice = 0;
foreach ($cart as $product) {
    $totalPrice += $product['price'] * $product['quantity'];
}

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1";
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <style>
        /* Общие стили для страницы */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f9f9f9;
        }

        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Стили для шапки */
        header {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .header-container1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 50px;
        }

        .logo-img {
            height: 60px;
        }

        nav ul {
            display: flex;
            gap: 15px;
            list-style: none;
            padding: 0;
            margin: 0;
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

        /* Стили для основного контента */
        main {
            flex: 1;
            max-width: 1000px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        /* Стили для таблицы корзины */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        thead tr {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table input[type="number"] {
            width: 60px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        table form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* Стили для кнопок */
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .clear-cart {
            background: none;
            border: none;
            color: red;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
        }

        .clear-cart:hover {
            color: darkred;
            text-decoration: underline;
        }

        .checkout, .my-orders-btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            background-color: #D2B48C;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }

        .checkout:hover, .my-orders-btn:hover {
            background-color: #a0865f;
        }

        .buttons-container {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .delete-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        /* Стили для пустой корзины */
        .empty-cart {
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 20px;
        }

        .empty-cart p {
            font-size: 20px;
            color: #555;
        }

        /* Стили для контекстного меню */
        .context-menu {
            position: absolute;
            display: none;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            min-width: 120px;
        }

        .context-menu ul {
            list-style: none;
            margin: 0;
            padding: 5px 0;
        }

        .context-menu li {
            padding: 8px 15px;
            cursor: pointer;
        }

        .context-menu li:hover {
            background-color: #f5f5f5;
        }

        /* Стили для уведомления */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }

        /* Стили для модального окна подтверждения */
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }

        .confirm-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .confirm-buttons {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .confirm-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .confirm-yes {
            background-color: #f44336;
            color: white;
        }

        .confirm-no {
            background-color: #ccc;
        }

                /* Стили для модального окна подтверждения заказа */
                .order-confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1002;
            justify-content: center;
            align-items: center;
        }

        .order-confirm-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .order-confirm-buttons {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .order-confirm-yes {
            background-color: #D2B48C;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .order-confirm-no {
            background-color: #f44336;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        /* Стили для уведомления о успешном заказе */
        .order-success-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s forwards;
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

        .order-success-notification.hide {
            animation: slideOut 0.5s forwards;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Контекстное меню -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li onclick="confirmDelete()">Удалить</li>
        </ul>
    </div>

     <!-- Модальное окно подтверждения заказа -->
     <div id="orderConfirmModal" class="order-confirm-modal">
        <div class="order-confirm-content">
            <p>Подтвердите оформление заказа</p>
            <div class="order-confirm-buttons">
                <button id="orderConfirmYes" class="order-confirm-yes">Оформить</button>
                <button id="orderConfirmNo" class="order-confirm-no">Отмена</button>
            </div>
        </div>
    </div>

     <!-- Уведомление об успешном заказе -->
     <div id="orderSuccessNotification" class="order-success-notification">
        Заказ успешно оформлен!
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-content">
            <p>Вы точно хотите удалить этот товар?</p>
            <div class="confirm-buttons">
                <button id="confirmYes" class="confirm-yes">Удалить</button>
                <button id="confirmNo" class="confirm-no">Отмена</button>
            </div>
        </div>
    </div>

    <!-- Уведомление об успешном удалении -->
    <div id="notification" class="notification">
        Товар успешно удален
    </div>

    <!-- Шапка сайта -->
    <header class="header1">
        <div class="header-container1">
            <div class="logo">
                <button onclick="location.href='user.php'" style="background: none; border: none; color: black; font: inherit; cursor: pointer; padding: 0; font-weight: bold; font-size: 18px;">
                    ИП "Жамкоцян"
                </button>
            </div>
            <nav>
                <ul>
                    <li><button onclick="location.href='user_catalog1.php'">Каталог</button></li>
                    <li><button onclick="scrollToFooter()">Контакты</button></li>
                    <li><button onclick="location.href='cart.php'">Корзина</button></li>
                    <li>
                        <button>
                            <?php echo htmlspecialchars($user['first_name']); ?>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Основной контент -->
    <main>
        <h2>Ваша корзина</h2>
        <?php if (!empty($cart)): ?>
            <table id="cartTable">
                <thead>
                    <tr>
                        <th>Продукт</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Общая сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $product): ?>
                        <tr data-cart-id="<?php echo $product['cart_id']; ?>">
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['price']); ?> руб.</td>
                            <td>
                                <form method="POST" action="update_cart.php">
                                    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" min="1" required>
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;">
                                        <img src="images/chek.png" alt="Обновить" style="width: 20px; height: 20px;">
                                    </button>
                                </form>
                            </td>
                            <td><?php echo $product['price'] * $product['quantity']; ?> руб.</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold;">Итого:</td>
                        <td style="font-weight: bold;"><?php echo $totalPrice; ?> руб.</td>
                    </tr>
                </tbody>
            </table>

            <div class="cart-actions">
                <div>
                    <form method="POST" action="clear_cart.php">
                        <button class="clear-cart">Очистить корзину</button>
                    </form>
                </div>
                <div class="buttons-container">
                    <button button class="checkout" onclick="showOrderConfirmModal()">Оформить заказ</button>
                    <button class="my-orders-btn" onclick="location.href='my_orders.php'">Мои заказы</button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста.</p>
                <button class="my-orders-btn" onclick="location.href='my_orders.php'">Мои заказы</button>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
let selectedCartId = null;

// Показать модальное окно подтверждения заказа
function showOrderConfirmModal() {
    document.getElementById('orderConfirmModal').style.display = 'flex';
}

// Обработчики для модального окна подтверждения заказа
document.getElementById('orderConfirmNo').addEventListener('click', function() {
    document.getElementById('orderConfirmModal').style.display = 'none';
});

document.getElementById('orderConfirmYes').addEventListener('click', function() {
    document.getElementById('orderConfirmModal').style.display = 'none';
    
    // Отправка формы заказа
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'process_order.php';

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'order';
    input.value = '1';
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    
    // Показываем уведомление об успешном заказе
    showOrderSuccessNotification();
});

// Показать уведомление об успешном заказе
function showOrderSuccessNotification() {
    const notification = document.getElementById('orderSuccessNotification');
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.classList.add('hide');
        setTimeout(() => {
            notification.style.display = 'none';
            notification.classList.remove('hide');
        }, 500);
    }, 3000);
}


// Обработчик правой кнопки мыши
document.addEventListener('DOMContentLoaded', function() {
    const cartTable = document.getElementById('cartTable');
    if (cartTable) {
        cartTable.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            
            const row = e.target.closest('tr');
            if (row && row.dataset.cartId) {
                selectedCartId = row.dataset.cartId;
                
                const contextMenu = document.getElementById('contextMenu');
                contextMenu.style.display = 'block';
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.top = `${e.pageY}px`;
            }
        });
    }
});

// Закрытие контекстного меню при клике вне его
document.addEventListener('click', function() {
    document.getElementById('contextMenu').style.display = 'none';
});

function confirmDelete() {
    document.getElementById('contextMenu').style.display = 'none';
    document.getElementById('confirmModal').style.display = 'flex';
}

document.getElementById('confirmNo').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
    selectedCartId = null;
});

document.getElementById('confirmYes').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
    
    if (selectedCartId) {
        fetch("delete_from_cart.php?id=" + selectedCartId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});

function showNotification() {
    const notification = document.getElementById('notification');
    notification.style.display = 'block';
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

function processOrder() {
    if (confirm("Вы уверены, что хотите оформить заказ?")) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process_order.php';

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order';
        input.value = '1';
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}

function scrollToFooter() {
    const footer = document.getElementById("contacts");
    if (footer) {
        footer.scrollIntoView({ behavior: "smooth" });
    }
}

// Закрытие модального окна при клике вне его
window.addEventListener('click', function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php
// Подключаем подвал
include('includes/footer.php');
?>
</body>
</html>