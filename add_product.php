
    
    <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    $categoryId = $_POST['category_id'];
    $description = $_POST['description'];
    $stockQuantity = $_POST['stock_quantity'];  // Получаем количество товара

    // Проверяем, был ли файл изображения загружен
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        // Проверяем успешность загрузки файла
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imagePathDB = 'images/' . $imageName;  // Путь для хранения в базе данных

            // Подключаемся к базе данных
            $db = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');

            // Проверяем соединение с базой данных
            if ($db->connect_error) {
                die("Ошибка подключения: " . $db->connect_error);
            }

            // Подготовка SQL-запроса
            $stmt = $db->prepare("INSERT INTO products (product_name, price, category_id, description, image_path, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsssi", $productName, $price, $categoryId, $description, $imagePathDB, $stockQuantity);

            // Выполнение запроса
            if ($stmt->execute()) {
                // Перенаправление на главную страницу админки
    header("Location: admin.php");
    exit; // Завершаем выполнение скрипта после перенаправления
            } else {
                echo "Ошибка при добавлении записи: " . $stmt->error;
            }

            // Закрытие подключения
            $stmt->close();
            $db->close();
        } else {
            echo "Ошибка при загрузке изображения.";
        }
    } else {
        echo "Ошибка при загрузке файла.";
    }
} else {
    echo "Некорректный запрос.";
}
?>
