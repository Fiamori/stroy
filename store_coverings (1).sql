-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 15 2025 г., 01:48
-- Версия сервера: 8.0.19
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `store_coverings`
--
CREATE DATABASE IF NOT EXISTS `store_coverings` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `store_coverings`;

-- --------------------------------------------------------

--
-- Структура таблицы `administrators`
--

DROP TABLE IF EXISTS `administrators`;
CREATE TABLE `administrators` (
  `admin_id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `administrators`
--

INSERT INTO `administrators` (`admin_id`, `username`, `password`, `email`, `full_name`) VALUES
(1, 'admin', '123QWEasd', 'chitamadrid@gmail.com', 'Администратор');

-- --------------------------------------------------------

--
-- Структура таблицы `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` int NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `banners`
--

INSERT INTO `banners` (`id`, `image_url`) VALUES
(1, 'images/banner1.jpg'),
(2, 'images/banner2.jpg'),
(3, 'images/banner3.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `product_id`, `quantity`, `total_price`) VALUES
(145, 1, 8, 1, 550.00),
(172, 5, 4, 1, 810.00);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Напольные покрытия', 'Все виды напольных покрытий для вашего дома'),
(2, 'Обои', 'Разные обои для любого интерьера');

-- --------------------------------------------------------

--
-- Структура таблицы `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text,
  `password` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `phone_number`, `address`, `password`, `image_path`, `verification_code`, `verification_expiry`, `is_verified`) VALUES
(1, 'Олег', 'Марков', 'oleg.markov@example.com', '79248374637', 'Чита, ул. Ленина, 10', '123', 'images/image_673ca32d0e1af.jpg', NULL, NULL, 0),
(5, 'Евгения', 'Никольская', 'enikolskaa.ev@gmail.com', '89144567230', 'Журавлева, 116', '123', 'images/image_681ad99fa8604.jpg', NULL, NULL, 0),
(20, 'Евгения', 'Никольская', 'enikolskaa.ev@mail.ru', '89144567230', '672040, Забайкальский край, г. Чита, ул. Журавлева, д. 106, кв. 10', '123', NULL, '123456', '2025-04-28 04:09:37', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('В обработке','Принят','В пути','Доставлен') DEFAULT 'В обработке',
  `status_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `total_price`, `status`, `status_read`) VALUES
(12, 1, '2025-03-23 22:44:42', 3160.99, 'В пути', 1),
(32, 5, '2025-04-15 17:41:05', 810.00, 'Принят', 1),
(34, 1, '2025-04-27 17:34:08', 810.00, 'В обработке', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `order_details`
--

DROP TABLE IF EXISTS `order_details`;
CREATE TABLE `order_details` (
  `order_detail_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(15, 12, 4, 1, 810.00),
(16, 12, 5, 1, 900.00),
(17, 12, 66, 1, 1450.99),
(48, 32, 4, 1, 810.00),
(50, 34, 4, 1, 810.00);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `description`, `price`, `stock_quantity`, `image_path`) VALUES
(3, 'Обои Floral Design', 2, 'Обои с цветочным узором, идеальны для гостиных и спален.', 500.00, 20, 'images/floral_design.jpg'),
(4, 'Обои Modern Style', 2, 'Современные обои с геометрическими узорами для стильных интерьеров.', 810.00, 25, 'images/modern_style.jpg'),
(5, 'Обои Antique Elegance', 2, 'Роскошные обои с бархатным рисунком, подходящие для классических интерьеров.', 900.00, 30, 'images/antique_elegance.jpg'),
(6, 'Обои Classic Lines', 2, 'Обои с классическими полосами для стильных и элегантных комнат.', 600.00, 20, 'images/classic_lines.jpg'),
(7, 'Обои Ocean Breeze', 2, 'Обои в стиле морской тематики для создания уюта в ванной и спальне.', 450.00, 10, 'images/ocean_breeze.jpg'),
(8, 'Обои Retro Vibe', 2, 'Обои в ретро-стиле с яркими цветами для создания уникальной атмосферы.', 550.00, 25, 'images/retro_vibe.jpg'),
(9, 'Обои Tropical Paradise', 2, 'Яркие обои с экзотическими растениями для создания живого и свежего интерьера.', 650.00, 20, 'images/tropical_paradise.jpg'),
(10, 'Обои Urban Chic', 2, 'Современные обои с эффектом металлик для создания шикарных городских интерьеров.', 810.00, 30, 'images/urban_chic.jpg'),
(11, 'Обои Rustic Charm', 2, 'Обои с древесным узором для создания уютной атмосферы в доме.', 400.00, 10, 'images/rustic_charm.jpg'),
(12, 'Обои Marble Touch', 2, 'Обои с эффектом мрамора для создания изысканных интерьеров.', 750.00, 25, 'images/marble_touch.jpg'),
(13, 'Обои Blossom Garden', 2, 'Обои с изображением сада цветов для создания яркого и уютного интерьера.', 850.00, 10, 'images/blossom_garden.jpg'),
(14, 'Обои Vintage Lace', 2, 'Обои с узорами в винтажном стиле для спальни или гостиной.', 650.00, 15, 'images/vintage_lace.jpg'),
(15, 'Обои Abstract Fusion', 2, 'Обои с абстрактным рисунком для создания креативных интерьеров.', 700.00, 30, 'images/abstract_fusion.jpg'),
(16, 'Обои Coastal Living', 2, 'Обои в морском стиле с изображениями лодок и пляжа для комнат в стиле лофт.', 450.00, 25, 'images/coastal_living.jpg'),
(18, 'Обои Art Deco', 2, 'Обои в стиле ар-деко для создания роскошных интерьеров с элементами ретро.', 950.00, 20, 'images/art_deco.jpg'),
(19, 'Обои Soft Pastels', 2, 'Нежные обои пастельных оттенков для спальни и детской комнаты.', 600.00, 10, 'images/soft_pastels.jpg'),
(20, 'Обои Diamond Texture', 2, 'Обои с эффектом алмазной текстуры для современных интерьеров.', 800.00, 30, 'images/diamond_texture.jpg'),
(21, 'Обои Sakura Blossom', 2, 'Обои с изображением сакуры для создания японского интерьера.', 650.00, 20, 'images/sakura_blossom.jpg'),
(22, 'Обои Midnight Night', 2, 'Обои с темным фоном и золотыми узорами для создания элегантной атмосферы.', 700.00, 15, 'images/midnight_night.jpg'),
(23, 'Обои Garden Escape', 2, 'Обои с изображениями садов и цветов для создания свежего и воздушного интерьера.', 500.00, 10, 'images/garden_escape.jpg'),
(24, 'Обои Royal Gold', 2, 'Элегантные обои с золотыми узорами для создания роскошного интерьера.', 1200.00, 30, 'images/royal_gold.jpg'),
(25, 'Обои Vintage Flowers', 2, 'Обои с винтажными цветами для создания романтической атмосферы.', 550.00, 30, 'images/vintage_flowers.jpg'),
(26, 'Обои Subtle Elegance', 2, 'Обои с тонкими, но элегантными узорами для стильных и современных интерьеров.', 750.00, 15, 'images/subtle_elegance.jpg'),
(27, 'Обои Exotic Jungle', 2, 'Обои с изображением джунглей для создания экзотической атмосферы в вашем доме.', 700.00, 20, 'images/exotic_jungle.jpg'),
(28, 'Обои Industrial Chic', 2, 'Обои с индустриальными мотивами для стильных офисов и современных домов.', 900.00, 25, 'images/industrial_chic.jpg'),
(29, 'Обои Soft Waves', 2, 'Обои с плавными волнистыми линиями для создания спокойной атмосферы.', 600.00, 10, 'images/soft_waves.jpg'),
(30, 'Обои Urban Jungle', 2, 'Яркие обои с изображением городского джунгеля для создания динамичного интерьера.', 650.00, 30, 'images/urban_jungle.jpg'),
(31, 'Обои Glamor Chic', 2, 'Роскошные обои с гламурным дизайном для стильных интерьеров.', 950.00, 10, 'images/glamor_chic.jpg'),
(32, 'Обои Earth Tones', 2, 'Обои в земных оттенках для создания спокойной и уютной атмосферы.', 550.00, 25, 'images/earth_tones.jpg'),
(33, 'Обои City Lights', 2, 'Обои с изображением огней города для создания вечерней атмосферы.', 800.00, 20, 'images/city_lights.jpg'),
(34, 'Обои Midnight Blue', 2, 'Обои с темным синим фоном для создания стильной атмосферы.', 650.00, 15, 'images/midnight_blue.jpg'),
(35, 'Обои Dream Catcher', 2, 'Обои с изображением ловца снов для создания мистической атмосферы в комнате.', 700.00, 30, 'images/dream_catcher.jpg'),
(36, 'Обои Bohemian Dreams', 2, 'Яркие обои в бохо-стиле для создания креативного интерьера.', 600.00, 25, 'images/bohemian_dreams.jpg'),
(37, 'Обои Crystal Glow', 2, 'Обои с эффектом свечения для создания необычного и яркого интерьера.', 850.00, 10, 'images/crystal_glow.jpg'),
(38, 'Обои Velvet Touch', 2, 'Обои с бархатным эффектом для создания роскошной и уютной атмосферы.', 950.00, 15, 'images/velvet_touch.jpg'),
(39, 'Обои Minimalist', 2, 'Обои с простым, но стильным минималистичным дизайном для современных интерьеров.', 550.00, 20, 'images/minimalist.jpg'),
(40, 'Обои Fresh Breeze', 2, 'Обои с изображениями свежих фруктов и овощей для создания яркой кухни.', 650.00, 30, 'images/fresh_breeze.jpg'),
(41, 'Обои Natural Wood', 2, 'Обои с текстурой дерева для создания теплой и уютной атмосферы.', 760.00, 10, 'images/natural_wood.jpg'),
(42, 'Обои Geometric Spark', 2, 'Обои с геометрическими фигурами для создания современного интерьера.', 800.00, 20, 'images/geometric_spark.jpg'),
(43, 'Обои Winter Dream', 2, 'Обои с зимним пейзажем для создания уюта в холодное время года.', 500.00, 10, 'images/winter_dream.jpg'),
(44, 'Обои Sky High', 2, 'Обои с изображением неба и облаков для создания легкого и воздушного интерьера.', 600.00, 25, 'images/sky_high.jpg'),
(45, 'Обои Vintage Brick', 2, 'Обои с изображением кирпичной стены для создания индустриального стиля.', 700.00, 30, 'images/vintage_brick.jpg'),
(46, 'Обои Forest Path', 2, 'Обои с изображением лесной тропы для создания уюта и спокойствия в комнате.', 650.00, 15, 'images/forest_path.jpg'),
(47, 'Обои Serene Landscape', 2, 'Обои с изображением спокойного пейзажа для создания расслабляющей атмосферы.', 750.00, 10, 'images/serene_landscape.jpg'),
(48, 'Обои Coastal Breeze', 2, 'Обои с изображением морского побережья для создания уюта в любом интерьере.', 600.00, 25, 'images/coastal_breeze.jpg'),
(49, 'Обои флизелиновые 1,06 1075-06ОАВ', 2, 'Фактура камня Горная порода Под мрамор C антивандальным эффектом Моющиеся От производителя Метровые 1,06х10м', 1300.50, 20, 'images/oboi1.jpg'),
(50, 'Mondeco Большие бабочка', 2, 'Обои флизелиновые Большие бабочки на стену', 1630.00, 5, 'images/fliz_babochka.jpg'),
(61, 'Ламинат дуб тёмный', 1, 'Элегантный ламинат для офисов.', 1399.99, 10, 'images/laminate_dark_oak.jpg'),
(62, 'Паркет ёлочка', 1, 'Высококачественный паркет для гостиных.', 2599.50, 30, 'images/herringbone_parquet.jpg'),
(63, 'Виниловое покрытие серый', 1, 'Износостойкое покрытие для кухни.', 899.99, 20, 'images/vinyl_gray.jpg'),
(64, 'Ковровая плитка красная', 1, 'Модульная ковровая плитка для офисов.', 599.50, 25, 'images/carpet_tile_red.jpg'),
(65, 'Ковровая плитка синяя', 1, 'Модульная ковровая плитка для офисов.', 599.50, 30, 'images/carpet_tile_blue.jpg'),
(66, 'Ламинат дуб светлый 8 мм', 1, 'Ламинат 8 мм, износостойкий, для жилых помещений.', 1450.99, 20, 'images/laminate_light_oak_8mm.jpg'),
(67, 'Ламинат дуб белый', 1, 'Ламинат в белом цвете, отлично подходит для современных интерьеров.', 1699.99, 10, 'images/laminate_white_oak.jpg'),
(68, 'Пробковое покрытие', 1, 'Экологичное пробковое покрытие для помещений.', 2999.50, 20, 'images/cork_flooring.jpg'),
(69, 'Виниловое покрытие дерево', 1, 'Виниловое покрытие с имитацией дерева для кухни и коридора.', 1099.00, 20, 'images/vinyl_wood.jpg'),
(70, 'Паркет из дуба', 1, 'Изысканный дубовый паркет для роскошных интерьеров.', 4599.99, 15, 'images/oak_wood_parquet.jpg'),
(71, 'Ковролин для офиса', 1, 'Устойчивый ковролин для офисных помещений.', 749.99, 10, 'images/office_carpet.jpg'),
(72, 'Ламинат с дубовой текстурой', 1, 'Ламинат с текстурой дуба, идеально подходит для домашних интерьеров.', 1499.00, 25, 'images/laminate_oak_texture.jpg'),
(73, 'Ламинат с фаской', 1, 'Ламинат с фаской, имитирующий паркет для вашего дома.', 1799.00, 10, 'images/laminate_with_bevel.jpg'),
(74, 'Ковровая плитка коричневая', 1, 'Универсальная ковровая плитка для жилых помещений.', 899.00, 15, 'images/carpet_tile_brown.jpg'),
(75, 'Ковровая плитка зелёная', 1, 'Ковровая плитка для офиса или магазина.', 849.99, 30, 'images/carpet_tile_green.jpg'),
(76, 'Ламинат с текстурой бетона', 1, 'Ламинат с текстурой бетона для современного дизайна.', 1599.00, 25, 'images/laminate_concrete_texture.jpg'),
(77, 'Виниловое покрытие черное', 1, 'Виниловое покрытие с гладкой черной текстурой.', 999.99, 10, 'images/vinyl_black.jpg'),
(78, 'Ламинат с каменной текстурой', 1, 'Ламинат с текстурой натурального камня.', 1799.00, 20, 'images/laminate_stone_texture.jpg'),
(79, 'Пробковое покрытие для кухни', 1, 'Пробковое покрытие для кухни, устойчиво к влаге.', 3499.99, 10, 'images/cork_kitchen.jpg'),
(80, 'Ковровая плитка с рисунком', 1, 'Ковровая плитка с геометрическим рисунком для офисов.', 1099.99, 30, 'images/carpet_tile_pattern.jpg'),
(81, 'Ковролин с защитой от пятен', 1, 'Ковролин с защитой от пятен для жилых помещений.', 1199.50, 20, 'images/stain_resistant_carpet.jpg'),
(82, 'Ламинат вишня', 1, 'Ламинат с вишневым оттенком для стильных интерьеров.', 1699.00, 15, 'images/cherry_laminate.jpg'),
(83, 'Паркет из ясеня', 1, 'Паркет из ясеня, экологичный и долговечный.', 3899.99, 25, 'images/ash_parquet.jpg'),
(84, 'Ламинат с эффектом состаренного дерева', 1, 'Ламинат с эффектом состаренного дерева для загородных домов.', 1599.99, 25, 'images/distressed_wood_laminate.jpg'),
(85, 'Ламинат в стиле ретро', 1, 'Ламинат с ретро текстурой для уникальных интерьеров.', 1499.99, 30, 'images/retro_style_laminate.jpg'),
(86, 'Ковровая плитка с антискользящей поверхностью', 1, 'Ковровая плитка с антискользящей поверхностью для безопасных помещений.', 1099.99, 15, 'images/anti_slip_carpet_tile.jpg'),
(87, 'Паркет из тикового дерева', 1, 'Высококачественный паркет из тикового дерева.', 4999.99, 15, 'images/teak_parquet.jpg'),
(88, 'Виниловое покрытие с текстурой плитки', 1, 'Виниловое покрытие с текстурой плитки для ванной.', 1199.00, 25, 'images/vinyl_tile_texture.jpg'),
(89, 'Ламинат для кухни', 1, 'Ламинат с водоотталкивающей текстурой, идеален для кухни.', 1799.99, 10, 'images/kitchen_laminate.jpg'),
(90, 'Ламинат дуб с фаской', 1, 'Ламинат с фаской, имитирующий дубовые доски.', 1699.50, 30, 'images/oak_laminate_bevel.jpg'),
(91, 'Паркет вишня', 1, 'Паркет вишневого цвета для стильных интерьеров.', 3999.99, 20, 'images/cherry_parquet.jpg'),
(92, 'Ламинат серый дуб', 1, 'Ламинат с серой текстурой дуба для современного дизайна.', 1399.00, 25, 'images/gray_oak_laminate.jpg'),
(93, 'Ковровая плитка для офиса', 1, 'Универсальная ковровая плитка для офисных помещений.', 799.00, 20, 'images/office_carpet_tile.jpg'),
(94, 'Ламинат с подкладкой для звукоизоляции', 1, 'Ламинат с подкладкой для улучшения звукоизоляции.', 1799.00, 15, 'images/soundproof_laminate.jpg'),
(95, 'Пробковое покрытие для спальни', 1, 'Пробковое покрытие с мягким, приятным на ощупь материалом для спальни.', 3999.99, 30, 'images/cork_bedroom.jpg'),
(96, 'Ламинат дуб темный', 1, 'Темный дубовый ламинат для классических интерьеров.', 1599.50, 25, 'images/dark_oak_laminate.jpg'),
(98, 'Виниловое покрытие с текстурой кожи', 1, 'Виниловое покрытие с текстурой кожи для стильных интерьеров.', 1199.00, 15, 'images/vinyl_leather_texture.jpg'),
(99, 'Ковролин для дачи', 1, 'Ковролин для дачи, устойчивый к загрязнениям и легкий в уходе.', 799.99, 20, 'images/cottage_carpet.jpg'),
(100, 'Ламинат с эффектом старинного дерева', 1, 'Ламинат с эффектом старинного дерева для интерьеров в винтажном стиле.', 1699.50, 30, 'images/antique_wood_laminate.jpg'),
(101, 'Ламинат с текстурой дуба', 1, 'Ламинат с натуральной текстурой дуба для любого помещения.', 1499.00, 10, 'images/oak_texture_laminate.jpg'),
(102, 'Паркет ясеня', 1, 'Высококачественный паркет из ясеня, долговечный и стильный.', 3999.99, 20, 'images/ash_wood_parquet.jpg'),
(103, 'Ламинат в цвете орех', 1, 'Ламинат с текстурой ореха для уютных интерьеров.', 1399.00, 10, 'images/walnut_laminate.jpg'),
(107, 'Паркет из ореха', 1, 'Элитный паркет из ореха для дорогих интерьеров.', 5499.00, 15, 'images/67403ad5c032b.jpg'),
(115, 'Линолеум Stimul Pegas', 1, 'Бытовой линолеум Stimul Pegas разработан специально для использования с теплыми полами', 2242.00, 30, 'images/stimul_pegas.jpg'),
(116, 'Линолеум Tarkett Favorit Stobo', 1, 'Обладает высокой износостойкостью за счёт защитного лака Extreme protection', 4210.00, 20, 'images/photo_2025-03-31_15-43-13.jpg'),
(117, 'Линолеум Comfort Bengal', 1, 'Серый линолеум с реалистичным рельефом паркетной доски', 4703.00, 10, 'images/photo_2025-03-31_15-45-37.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `store_info`
--

DROP TABLE IF EXISTS `store_info`;
CREATE TABLE `store_info` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `inn` varchar(12) NOT NULL,
  `ogrnip` varchar(15) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `owner_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `store_info`
--

INSERT INTO `store_info` (`id`, `name`, `address`, `inn`, `ogrnip`, `phone`, `email`, `owner_name`) VALUES
(1, 'ИП «Жамкоцян Айкануш Ованесовна»', 'Забайкальский край, г. Чита, ул. Инженерная, д. 5', '753620510880', '323750000009800', '+7(924) 505 25 25', 'chitamadrid@mail.ru', 'Жамкоцян Айкануш Ованесовна');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Индексы таблицы `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status_read` (`status_read`);

--
-- Индексы таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `store_info`
--
ALTER TABLE `store_info`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `administrators`
--
ALTER TABLE `administrators`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT для таблицы `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT для таблицы `store_info`
--
ALTER TABLE `store_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Ограничения внешнего ключа таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
