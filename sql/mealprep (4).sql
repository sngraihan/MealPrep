-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 14, 2025 at 02:53 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mealprep`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_meal` (IN `p_name` VARCHAR(100), IN `p_description` TEXT, IN `p_price` DECIMAL(10,2))   BEGIN
    INSERT INTO meals (name, description, price) VALUES (p_name, p_description, p_price);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cancel_order` (IN `p_order_id` INT)   BEGIN
    DELETE FROM order_items WHERE order_id = p_order_id;
    DELETE FROM orders WHERE id = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_meal` (IN `p_id` INT)   BEGIN
    DELETE FROM meals WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_all_meals` ()   BEGIN
    SELECT * FROM meals ORDER BY name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_all_orders` ()   BEGIN
    SELECT o.id, u.username, o.order_date, o.total_price, o.status, o.created_at,
           GROUP_CONCAT(CONCAT(m.name, ' (', oi.quantity, ')') SEPARATOR ', ') as meals
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN meals m ON oi.meal_id = m.id
    GROUP BY o.id
    ORDER BY o.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_available_meals` ()   BEGIN
    SELECT * FROM meals WHERE available = 1 ORDER BY name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_meal_by_id` (IN `p_id` INT)   BEGIN
    SELECT * FROM meals WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_recent_orders` (IN `p_limit` INT)   BEGIN
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT p_limit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_user_by_id` (IN `p_id` INT)   BEGIN
    SELECT id, username, email, role, created_at FROM users WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_user_by_username_or_email` (IN `p_username` VARCHAR(100))   BEGIN
    SELECT id, username, email, password, role FROM users 
    WHERE username = p_username OR email = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_user_orders` (IN `p_user_id` INT)   BEGIN
    SELECT o.id AS order_id, o.order_date, o.total_price, o.status,
           m.name AS meal_name, oi.quantity, oi.subtotal
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN meals m ON oi.meal_id = m.id
    WHERE o.user_id = p_user_id
    ORDER BY o.order_date DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `place_order` (IN `p_user_id` INT, IN `p_order_date` DATE, IN `p_meal_id` INT, IN `p_quantity` INT)   BEGIN
    DECLARE v_order_id INT;
    DECLARE v_price DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);

    START TRANSACTION;

    -- Cek apakah user sudah punya order hari ini
    SELECT id INTO v_order_id
    FROM orders
    WHERE user_id = p_user_id AND order_date = p_order_date
    LIMIT 1;

    -- Jika belum ada, buat order baru
    IF v_order_id IS NULL THEN
        INSERT INTO orders (user_id, order_date)
        VALUES (p_user_id, p_order_date);
        SET v_order_id = LAST_INSERT_ID();
    END IF;

    -- Ambil harga makanan
    SELECT price INTO v_price
    FROM meals
    WHERE id = p_meal_id AND available = TRUE;

    SET v_subtotal = v_price * p_quantity;

    -- Tambahkan item ke order_items
    INSERT INTO order_items (order_id, meal_id, quantity, subtotal)
    VALUES (v_order_id, p_meal_id, p_quantity, v_subtotal);

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `register_user` (IN `p_username` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(255), IN `p_role` VARCHAR(10))   BEGIN
    INSERT INTO users (username, email, password, role) VALUES (p_username, p_email, p_password, p_role);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_meal` (IN `p_id` INT, IN `p_name` VARCHAR(100), IN `p_description` TEXT, IN `p_price` DECIMAL(10,2), IN `p_available` TINYINT)   BEGIN
    UPDATE meals 
    SET name = p_name, description = p_description, price = p_price, available = p_available 
    WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_order_status` (IN `p_order_id` INT, IN `p_status` VARCHAR(20))   BEGIN
    UPDATE orders SET status = p_status WHERE id = p_order_id;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `calculate_order_total` (`p_order_id` INT) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT SUM(subtotal) INTO total
    FROM order_items
    WHERE order_id = p_order_id;
    RETURN IFNULL(total, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `check_email_exists` (`p_email` VARCHAR(100)) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE result BOOLEAN;
    SELECT EXISTS(SELECT 1 FROM users WHERE email = p_email) INTO result;
    RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `check_username_exists` (`p_username` VARCHAR(50)) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE result BOOLEAN;
    SELECT EXISTS(SELECT 1 FROM users WHERE username = p_username) INTO result;
    RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `count_all_orders` () RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders;
    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_available_meals_count` () RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM meals WHERE available = 1;
    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_today_orders_count` () RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders WHERE DATE(created_at) = CURDATE();
    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_total_orders_by_user` (`p_user_id` INT) RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders WHERE user_id = p_user_id;
    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_total_spent` (`p_user_id` INT) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT SUM(total_price) INTO total FROM orders WHERE user_id = p_user_id;
    RETURN IFNULL(total, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_total_users` () RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM users WHERE role = 'user';
    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `is_admin` (`p_user_id` INT) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE result BOOLEAN;
    SELECT role = 'admin' INTO result FROM users WHERE id = p_user_id;
    RETURN result;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `name`, `description`, `price`, `available`, `created_at`) VALUES
(1, 'Nasi Ayam Bakar', 'Nasi putih dengan ayam bakar bumbu kecap, dilengkapi lalapan segar dan sambal', 25000.00, 1, '2025-06-11 15:14:20'),
(3, 'Gado-Gado Betawi', 'Sayuran segar dengan bumbu kacang khas Betawi, dilengkapi kerupuk dan emping', 20000.00, 1, '2025-06-11 15:14:20'),
(5, 'Soto Ayam Lamongan', 'Soto ayam kuah bening dengan telur, tauge, dan bihun, disajikan dengan nasi', 22000.00, 1, '2025-06-11 15:14:20'),
(7, 'Rendang Daging Sapi', 'Rendang daging sapi empuk dengan bumbu rempah tradisional, disajikan dengan nasi', 35000.00, 1, '2025-06-11 15:14:20'),
(9, 'Pecel Lele Sambal Matah', 'Lele goreng crispy dengan sambal matah khas Bali dan lalapan segar', 18000.00, 1, '2025-06-11 15:14:20'),
(11, 'Ayam Geprek Bensu', 'Ayam goreng geprek dengan sambal pedas level 1-5, dilengkapi nasi dan lalapan', 23000.00, 1, '2025-06-11 15:14:20'),
(13, 'Nasi Gudeg Yogya', 'Gudeg khas Yogyakarta dengan ayam, telur, dan krecek, disajikan dengan nasi', 28000.00, 1, '2025-06-11 15:14:20'),
(15, 'Bakso Malang Jumbo', 'Bakso daging sapi jumbo dengan mie, tahu, dan pangsit dalam kuah kaldu segar', 24000.00, 1, '2025-06-11 15:14:20'),
(17, 'Mie Ayam Ceker', 'Mie ayam dengan topping ceker ayam, pangsit, dan bakso, kuah kaldu gurih', 21000.00, 1, '2025-06-11 15:14:20'),
(19, 'Nasi Padang Komplit', 'Nasi dengan rendang, ayam pop, sayur nangka, dan sambal hijau khas Padang', 32000.00, 1, '2025-06-11 15:14:20'),
(21, 'Sate Kambing Madura', 'Sate kambing empuk dengan bumbu kacang khas Madura, dilengkapi lontong', 30000.00, 1, '2025-06-11 15:14:20'),
(23, 'Es Teh Manis', 'Minuman teh manis dingin segar', 5000.00, 1, '2025-06-11 15:14:20'),
(25, 'Es Jeruk Nipis', 'Minuman jeruk nipis segar dengan es batu', 8000.00, 1, '2025-06-11 15:14:20'),
(27, 'Jus Alpukat', 'Jus alpukat segar dengan susu kental manis', 12000.00, 1, '2025-06-11 15:14:20'),
(29, 'Salad Buah Segar', 'Campuran buah-buahan segar dengan dressing yogurt', 15000.00, 1, '2025-06-11 15:14:20'),
(31, 'Ayam Pak Gembus', 'Ayam goreng dengan sambal kacang yang enak', 25000.00, 1, '2025-06-11 15:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `status`, `created_at`) VALUES
(1, 3, '2025-06-11', 53000.00, 'confirmed', '2025-06-11 08:20:00'),
(2, 5, '2025-06-11', 49000.00, 'pending', '2025-06-11 08:22:00'),
(3, 3, '2025-06-10', 36000.00, 'delivered', '2025-06-10 05:10:00'),
(21, 13, '2025-06-13', 215000.00, 'delivered', '2025-06-11 15:46:50'),
(23, 13, '2025-06-11', 23000.00, 'confirmed', '2025-06-11 15:51:06'),
(25, 13, '2025-07-10', 23000.00, 'delivered', '2025-06-13 09:54:34'),
(27, 13, '2025-06-26', 8000.00, 'pending', '2025-06-13 10:08:15'),
(29, 13, '2025-06-30', 105000.00, 'pending', '2025-06-13 10:08:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `meal_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `meal_id`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 1, 25000.00),
(2, 1, 23, 2, 10000.00),
(3, 1, 29, 1, 18000.00),
(4, 2, 5, 2, 44000.00),
(5, 2, 23, 1, 5000.00),
(6, 3, 11, 1, 23000.00),
(7, 3, 25, 1, 8000.00),
(8, 3, 23, 1, 5000.00),
(9, 21, 31, 2, 50000.00),
(11, 23, 11, 1, 23000.00),
(13, 21, 31, 1, 25000.00),
(15, 25, 11, 1, 23000.00),
(17, 21, 3, 7, 140000.00),
(19, 27, 25, 1, 8000.00),
(21, 29, 17, 5, 105000.00);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_calculate_subtotal` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE meal_price DECIMAL(10,2);
    
    SELECT price INTO meal_price 
    FROM meals 
    WHERE id = NEW.meal_id;
    
    SET NEW.subtotal = meal_price * NEW.quantity;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_order_total` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    UPDATE orders 
    SET total_price = calculate_order_total(NEW.order_id)
    WHERE id = NEW.order_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validate_meal_availability` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE meal_available TINYINT DEFAULT 0;
    
    SELECT available INTO meal_available 
    FROM meals 
    WHERE id = NEW.meal_id;
    
    IF meal_available = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Cannot order unavailable meal';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@mealprep.com', 'admin', '2025-06-11 15:14:20'),
(3, 'john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', 'user', '2025-06-11 15:14:20'),
(5, 'jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@example.com', 'user', '2025-06-11 15:14:20'),
(7, 'mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike@example.com', 'user', '2025-06-11 15:14:20'),
(9, 'sarah_johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah@example.com', 'user', '2025-06-11 15:14:20'),
(11, 'david_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'david@example.com', 'user', '2025-06-11 15:14:20'),
(13, 'koron', '$2y$10$/t3n84gWwhVEO0BJItiXo.PeDzQDq6dwNFX.nOHTexCZdJJAf/nkO', 'koron@gmail.com', 'user', '2025-06-11 15:45:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
