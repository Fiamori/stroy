<?php
session_start();

// Подключение к базе данных
$conn = new mysqli('127.0.0.1', 'root', 'root', 'store_coverings');
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Ошибка подключения к базе данных."]);
    exit();
}

// Проверка сессионной переменной
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Пользователь не авторизован."]);
    exit();
}

// ID клиента (получаем из сессии)
$user_id = $_SESSION['user_id'];

// Проверка наличия товаров в корзине
$sql_cart = "SELECT * FROM cart WHERE customer_id = ?";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

if ($result_cart->num_rows > 0) {
    // Переменная для общей суммы
    $total_price = 0;

    // Создание записи в таблице Orders
    $order_date = date('Y-m-d H:i:s');
    $status = "В обработке";  // Используем допустимое значение статуса

    // Создание записи в таблице orders с общей суммой
    $sql_order = "INSERT INTO orders (customer_id, order_date, total_price, status) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("isds", $user_id, $order_date, $total_price, $status);
    if (!$stmt_order->execute()) {
        echo json_encode(["success" => false, "message" => "Ошибка при создании заказа: " . $stmt_order->error]);
        exit();
    }

    // Получение ID нового заказа
    $order_id = $conn->insert_id; // Теперь это будет работать правильно!

    // Перенос данных из корзины в Order_Details
    while ($row_cart = $result_cart->fetch_assoc()) {
        $product_id = $row_cart['product_id'];
        $quantity = $row_cart['quantity'];
        $price_at_purchase = $row_cart['total_price'] / $quantity;

        // Проверка корректности данных для вставки
        if ($quantity <= 0 || $price_at_purchase <= 0) {
            echo json_encode(["success" => false, "message" => "Некорректные данные в корзине."]);
            exit();
        }

        // Добавляем цену товара в общую сумму
        $total_price += $price_at_purchase * $quantity;

        // Вставка данных в таблицу order_details
        $sql_order_details = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
        $stmt_order_details = $conn->prepare($sql_order_details);
        $stmt_order_details->bind_param("iiid", $order_id, $product_id, $quantity, $price_at_purchase);
        if (!$stmt_order_details->execute()) {
            echo json_encode(["success" => false, "message" => "Ошибка при добавлении товара в заказ: " . $stmt_order_details->error]);
            exit();
        }
    }

    // Обновляем запись в таблице orders с правильной суммой
    $sql_update_total_price = "UPDATE orders SET total_price = ? WHERE order_id = ?";
    $stmt_update_total_price = $conn->prepare($sql_update_total_price);
    $stmt_update_total_price->bind_param("di", $total_price, $order_id);
    if (!$stmt_update_total_price->execute()) {
        echo json_encode(["success" => false, "message" => "Ошибка при обновлении общей суммы заказа: " . $stmt_update_total_price->error]);
        exit();
    }

    // Очистка корзины
    $sql_clear_cart = "DELETE FROM cart WHERE customer_id = ?";
    $stmt_clear_cart = $conn->prepare($sql_clear_cart);
    $stmt_clear_cart->bind_param("i", $user_id);
    if (!$stmt_clear_cart->execute()) {
        echo json_encode(["success" => false, "message" => "Ошибка при очистке корзины: " . $stmt_clear_cart->error]);
        exit();
    }

    // Перенаправление на страницу корзины с сообщением
    header("Location: cart.php?message=order_success");
    exit();
} else {
    header("Location: cart.php?message=cart_empty");
    exit();
}

$conn->close();
?>
