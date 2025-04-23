<?php
// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1";
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

// Количество товаров на странице
$items_per_page = 12;

// Получаем текущую страницу из параметров URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Определяем смещение для запроса
$offset = ($page - 1) * $items_per_page;

// Инициализация параметров поиска
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Формируем SQL-запрос в зависимости от поиска
if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE product_name LIKE '%$search%' LIMIT $items_per_page OFFSET $offset";
    $count_sql = "SELECT COUNT(*) AS total FROM products WHERE product_name LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products LIMIT $items_per_page OFFSET $offset";
    $count_sql = "SELECT COUNT(*) AS total FROM products";
}

// Выполняем запрос на получение товаров
$result = $conn->query($sql);

// Выполняем запрос для подсчета общего количества товаров
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(255, 255, 255);
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

        .modal-dialog {
            max-width: 500px;
            margin: auto;
        }

        .modal-content {
            width: 100%;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #f0f0f0;
            color: #333;
        }

        .modal-image img {
            max-height: 250px;
            object-fit: contain;
            margin: 0 auto;
        }

        .modal-body {
            text-align: center;
            padding: 10px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
        }

        .product {
            width: 250px;
            height: 320px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px;
            background-color: #fff;
            color: #333;
            text-align: center;
            position: relative;
        }

        .product img {
            width: 100%;
            height: 150px;
        }

        .product h3 {
            font-size: 18px;
            margin: 10px 0 5px;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .product p {
            font-size: 16px;
        }

        .actions {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .delete-btn {
            color: red;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .edit-btn {
            display: inline-block;
            color: #666;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            border: none;
            background: none;
            margin: 0;
            float: right;
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
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .confirm-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirm-buttons button {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .confirm-yes {
            background-color: #f44336;
            color: white;
        }

        .confirm-no {
            background-color: #ccc;
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
    animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
    </style>
</head>
<body>
<div class="wrapper">
    <header class="header1">
        <div class="header-container1">
            <div class="logo">
                <button onclick="location.href='admin.php'" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0;">
                    Администратор
                </button>
            </div>
            <nav>
                <ul>
                    <li>
                        <div class="search-container">
                            <form action="admin.php" method="get">
                                <input type="text" name="search" placeholder="Введите запрос" class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit">
                                    <img src="images/search-icon.png" alt="Search" style="width: 40px; height: 20px;">
                                </button>
                            </form>
                        </div>
                    </li>
                    <li>
                        <button>
                            <a href="users_cart.php" style="color: inherit; text-decoration: none;">Заказы</a>
                        </button>
                    </li>
                    <li><button onclick="scrollToFooter()">Контакты</button></li>
                    <li>
                        <button>
                            <a href="add.php" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; padding: 0;">Добавить запись</a>
                        </button>
                    </li>
                    <li>
                        <div class="auth-btn-container">
                            <button class="auth-btn" onclick="openAuthModal()">
                                <a href="index.php">Выйти</a>
                            </button>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="popular-products">
            <div class="product-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                            <div class='product'>
                                <img src='{$row['image_path']}' alt='{$row['product_name']}'>
                                <h3>{$row['product_name']}</h3>
                                <p>Цена: {$row['price']} руб./м²</p>
                                <div class='actions'>
                                    <a href='#' class='delete-btn' onclick='confirmDelete({$row['product_id']})'>Удалить</a>
                                    <a href='edit_product.php?id={$row['product_id']}' class='edit-btn'>Редактировать</a>
                                </div>
                            </div>
                        ";
                    }
                } else {
                    echo "<p>Товары не найдены.</p>";
                }
                ?>
            </div>

            <!-- Пагинация -->
            <div class="pagination">
                <nav>
                    <ul class="pagination">
                        <?php
                        if ($page > 1) {
                            echo "<li><a href='admin.php?page=" . ($page - 1) . "&search=" . urlencode($search) . "'>&laquo; Назад</a></li>";
                        }

                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active = $i == $page ? "class='active'" : "";
                            echo "<li $active><a href='admin.php?page=$i&search=" . urlencode($search) . "'>$i</a></li>";
                        }

                        if ($page < $total_pages) {
                            echo "<li><a href='admin.php?page=" . ($page + 1) . "&search=" . urlencode($search) . "'>Далее &raquo;</a></li>";
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </section>

        <!-- Модальное окно для деталей товара -->
        <div id="productModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Детали товара</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-image">
                            <img id="modalImg" src="" alt="Изображение товара" class="img-fluid">
                        </div>
                        <h3 id="modalName"></h3>
                        <p id="modalDescription"></p>
                        <p id="modalPrice"></p>
                        <p id="modalStock_quantity"></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
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

<!-- Подключаем Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let productIdToDelete = null;

    function confirmDelete(productId) {
        productIdToDelete = productId;
        document.getElementById('confirmModal').style.display = 'flex';
    }

    document.getElementById('confirmNo').addEventListener('click', function() {
        document.getElementById('confirmModal').style.display = 'none';
        productIdToDelete = null;
    });

    document.getElementById('confirmYes').addEventListener('click', function() {
        document.getElementById('confirmModal').style.display = 'none';
        
        fetch("delete_product.php?id=" + productIdToDelete)
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
    });

    function showNotification() {
    const notification = document.getElementById('notification');
    notification.style.display = 'block';
    notification.style.animation = 'slideIn 0.5s';
    
    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.5s forwards';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 500);
    }, 2500);
}

    function openModal(element) {
        const name = element.getAttribute('data-name');
        const description = element.getAttribute('data-description');
        const price = element.getAttribute('data-price');
        const image = element.getAttribute('data-image');
        const stock_quantity = element.getAttribute('data-stock_quantity');

        document.getElementById('modalName').textContent = name;
        document.getElementById('modalDescription').textContent = description;
        document.getElementById('modalPrice').textContent = "Цена: " + price + " руб.";
        document.getElementById('modalImg').src = image;
        document.getElementById('modalStock_quantity').textContent = "Количество на складе: " + stock_quantity;

        $('#productModal').modal('show');
    }

    function scrollToFooter() {
        const footer = document.getElementById("contacts");
        footer.scrollIntoView({ behavior: "smooth" });
    }

    // Для закрытия модального окна при клике вне его
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('confirmModal');
        if (event.target == modal) {
            modal.style.display = 'none';
            productIdToDelete = null;
        }
    });
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