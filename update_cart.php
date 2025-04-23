<?php
session_start();

// Проверка содержимого корзины перед обновлением
echo '<pre>';
print_r($_SESSION['cart']); // Выводим содержимое корзины
echo '</pre>';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Подключение к базе данных
$host = '127.0.0.1'; // Ваш хост
$dbname = 'store_coverings'; // Ваше имя базы данных
$username = 'root'; // Ваше имя пользователя
$password = 'root'; // Ваш пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из POST-запроса
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Выводим полученные данные
    echo "Product ID: " . $product_id . "<br>";
    echo "Quantity: " . $quantity . "<br>";

    // Проверка, что количество товара больше 0
    if ($quantity > 0) {
        // Получаем цену товара из таблицы products
        $stmt = $pdo->prepare('SELECT price FROM products WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $total_price = $product['price'] * $quantity; // Рассчитываем полную цену для товара в корзине

            // Проверяем, что товар уже есть в корзине
            $stmt = $pdo->prepare('SELECT * FROM cart WHERE customer_id = :customer_id AND product_id = :product_id');
            $stmt->execute(['customer_id' => $_SESSION['user_id'], 'product_id' => $product_id]);
            $product_in_cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product_in_cart) {
                // Если товар есть, обновляем количество и цену
                $stmt = $pdo->prepare('UPDATE cart SET quantity = :quantity, total_price = :total_price WHERE customer_id = :customer_id AND product_id = :product_id');
                $stmt->execute([
                    'quantity' => $quantity,
                    'total_price' => $total_price,
                    'customer_id' => $_SESSION['user_id'],
                    'product_id' => $product_id
                ]);
            } else {
                // Если товара нет, добавляем его в корзину
                $stmt = $pdo->prepare('INSERT INTO cart (customer_id, product_id, quantity, total_price) VALUES (:customer_id, :product_id, :quantity, :total_price)');
                $stmt->execute([
                    'customer_id' => $_SESSION['user_id'],
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'total_price' => $total_price
                ]);
            }
        } else {
            echo "Товар с ID $product_id не найден.";
        }
    } else {
        // Если количество товара <= 0, удаляем товар из корзины
        $stmt = $pdo->prepare('DELETE FROM cart WHERE customer_id = :customer_id AND product_id = :product_id');
        $stmt->execute(['customer_id' => $_SESSION['user_id'], 'product_id' => $product_id]);
    }

    // Перенаправляем обратно на страницу корзины
    header('Location: cart.php');
    exit();
}
?>
