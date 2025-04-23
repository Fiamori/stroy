<?php
session_start();

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user_id'])) {
    $_SESSION['return_url'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: login.php');
    exit();
}

$host = '127.0.0.1';
$dbname = 'store_coverings';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $return_url = $_POST['return_url'] ?? ($_SERVER['HTTP_REFERER'] ?? 'user.php');

    if ($quantity > 0) {
        // Получаем цену товара
        $stmt = $pdo->prepare('SELECT price FROM products WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Вычисляем общую стоимость
            $total_price = $product['price'] * $quantity;

            // Проверяем, есть ли товар в корзине
            $stmt = $pdo->prepare('SELECT * FROM cart WHERE customer_id = :customer_id AND product_id = :product_id');
            $stmt->execute([
                'customer_id' => $_SESSION['user_id'],
                'product_id' => $product_id
            ]);
            $product_in_cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product_in_cart) {
                // Обновляем существующий товар в корзине
                $stmt = $pdo->prepare('UPDATE cart SET quantity = :quantity, total_price = :total_price WHERE customer_id = :customer_id AND product_id = :product_id');
                $stmt->execute([
                    'quantity' => $quantity,
                    'total_price' => $total_price,
                    'customer_id' => $_SESSION['user_id'],
                    'product_id' => $product_id
                ]);
            } else {
                // Добавляем новый товар в корзину
                $stmt = $pdo->prepare('INSERT INTO cart (customer_id, product_id, quantity, total_price) VALUES (:customer_id, :product_id, :quantity, :total_price)');
                $stmt->execute([
                    'customer_id' => $_SESSION['user_id'],
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'total_price' => $total_price
                ]);
            }
        } else {
            $_SESSION['error'] = "Товар с ID $product_id не найден.";
            header("Location: $return_url");
            exit();
        }
    } else {
        // Удаляем товар из корзины, если количество <= 0
        $stmt = $pdo->prepare('DELETE FROM cart WHERE customer_id = :customer_id AND product_id = :product_id');
        $stmt->execute([
            'customer_id' => $_SESSION['user_id'],
            'product_id' => $product_id
        ]);
    }

    // Перенаправляем пользователя обратно на страницу, с которой он пришел
    $_SESSION['success'] = "Товар успешно добавлен в корзину!";
    header("Location: $return_url");
    exit();
}
?>
