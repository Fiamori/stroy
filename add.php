<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить запись</title>
    <link rel="icon" type="image/x-icon" href="images/favicon1.ico">
    <link rel="stylesheet" href="css/style.css">
    <style>
               
       /* Стили для страницы добавления */
.container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    background-color: #ffffff; /* Белый фон */
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); /* Легкая тень */
}

.header {
    text-align: center;
    margin-bottom: 20px;
    color: #333; /* Темно-серый текст */
}

form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #444; /* Чуть темнее серый */
}

form input,
form textarea,
form select,
form button {
    width: 100%;
    padding: 8px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f0f0f0; /* Светло-серый фон полей */
    color: #333; /* Темный текст */
}

form button {
    background-color: #777; /* Серый цвет кнопки */
    color: #fff;
    border: none;
    cursor: pointer;
}

form button:hover {
    background-color: #666; /* Темнее при наведении */
}

.back-btn {
    display: inline-block;
    text-align: center;
    color: #666; /* Серый текст */
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    border: none;
    background: none;
    margin: 0 auto;
}

.back-btn-container {
    text-align: center;
}

.back-btn:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Добавить запись</h2>
    </div>
    <form action="add_product.php" method="post" enctype="multipart/form-data">
    <label for="product_name">Название:</label>
    <input type="text" id="product_name" name="product_name" required>

    <label for="price">Цена:</label>
    <input type="number" id="price" name="price" step="0.01" required>

    <label for="category">Категория:</label>
    <select id="category" name="category_id" required>
        <option value="">Выберите категорию</option>
        <?php
        // Подключаемся к базе данных для получения списка категорий
        $db = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
        $result = $db->query("SELECT category_id, category_name FROM categories");

        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
        }

        $db->close();
        ?>
    </select>

    <label for="description">Описание:</label>
    <textarea id="description" name="description" rows="4" required></textarea>

    <label for="stock_quantity">Количество на складе:</label>
    <input type="number" id="stock_quantity" name="stock_quantity" required>

    <label for="image">Изображение:</label>
    <input type="file" id="image" name="image" accept="image/*" required>

    <button type="submit">Сохранить</button>
</form>

    <a href="admin.php" class="back-btn">Назад</a>
</div>

</body>
</html>

