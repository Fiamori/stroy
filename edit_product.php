<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование товара</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    background-color: #f8f8f8; /* Светло-серый фон */
    color: #333; /* Темно-серый текст */
}

.edit-container {
    background: #ffffff; /* Белый фон контейнера */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
}

.edit-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #444; /* Темно-серый цвет заголовка */
}

.form-group label {
    color: #444; /* Темно-серые лейблы */
}

.btn-primary {
    width: 100%;
    background-color: #e0e0e0; /* Светло-серый цвет кнопки */
    border: none;
    color: #333; /* Темный текст */
}

.btn-primary:hover {
    background-color: #d0d0d0; /* Серый при наведении */
}

.btn-secondary {
    display: inline-block;
    text-align: center;
    color: #555;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    border: none;
    background: none;
    margin: 0 auto;
}

.btn-secondary:hover {
    text-decoration: underline;
}

.form-control, .form-control-file {
    background-color: #f0f0f0; /* Светло-серый фон полей ввода */
    border: 1px solid #ccc; /* Светло-серые границы */
    color: #333; /* Темный текст */
}

.form-control:focus {
    background-color: #ffffff; /* Белый фон при фокусе */
    border-color: #bbb;
}

.form-text {
    color: #666; /* Серый текст подсказок */
}

    </style>
</head>
<body>
<div class="edit-container">
    <h2>Редактирование товара</h2>
    
    <?php
    // Соединение с базой данных
    $conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');

    // Проверка соединения
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Проверка, передан ли ID товара через GET
    if (isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];

        // Получение данных о товаре
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        } else {
            echo "<div class='alert alert-danger'>Товар не найден.</div>";
            exit();
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>ID товара не указан.</div>";
        exit();
    }

    // Обработка формы при отправке
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];

        // Обработка загруженного файла
        $image_path = $product['image_path']; // Сохраняем старое изображение, если новое не загружено

        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
            $image_name = $_FILES['image_path']['name'];
            $image_tmp_name = $_FILES['image_path']['tmp_name'];
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_path = "images/" . uniqid() . "." . $image_extension;
            
            // Перемещаем файл в папку images
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                // Файл успешно загружен, обновляем ссылку в базе данных
            } else {
                echo "<div class='alert alert-danger'>Ошибка загрузки изображения.</div>";
            }
        }

        // Обновление данных в базе
        $update_sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock_quantity = ?, image_path = ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdiss", $product_name, $description, $price, $stock_quantity, $image_path, $product_id);

        if ($update_stmt->execute()) {
            echo "<div class='alert alert-success'>Товар успешно обновлен.</div>";
        } else {
            echo "<div class='alert alert-danger'>Ошибка обновления товара: " . $conn->error . "</div>";
        }
        $update_stmt->close();
    }

    // Закрытие соединения
    $conn->close();
    ?>

    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Название товара</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Цена</label>
            <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="stock_quantity">Количество</label>
            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
        </div>
        <div class="form-group">
            <label for="image_path">Изображение товара</label>
            <input type="file" class="form-control-file" id="image_path" name="image_path">
            <small class="form-text text-muted">Если вы хотите изменить изображение, выберите новый файл.</small>
        </div>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <a href="admin.php" class="btn-secondary">Назад</a>
    </form>
</div>

</body>
</html>
