<?php
// Подключение к базе данных
include('config/db_connect.php');

// Получение данных о магазине
$query = "SELECT * FROM store_info LIMIT 1"; // Предполагаем, что в таблице всегда только одна запись
$result = $conn->query($query);
$store_info = $result->fetch_assoc();

// Запрос на получение баннеров
$query = "SELECT image_url FROM banners";
$result = $conn->query($query);

// Проверяем, есть ли баннеры
$banners = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banners[] = $row['image_url'];
    }
}
?>
<style>
/* ПОДВАЛ */
footer {
    padding: 20px;
    background-color: #f1f1f1;
    color: black;
    font-family: Arial, sans-serif;
    margin-top: auto; /* Футер прижимается вниз */
    width: 100%;
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
</style>

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