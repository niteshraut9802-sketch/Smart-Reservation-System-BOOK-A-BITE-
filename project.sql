-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2026 at 03:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@bookabite.com', '$2y$10$pi/nUyKZaUHb0qra0Q1NNeyJ89NJMhtr.h0cHDJ6I5mbaVQqMLHEu', '2026-08-24 01:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category`, `name`, `description`, `image_url`, `price`, `created_at`) VALUES
(1, 'Starters', 'Spring Rolls', 'Crispy rolls filled with fresh vegetables.', 'image/menu/menu_1787711024_8471.jpg', '160', '2026-08-26 02:23:44'),
(2, 'Starters', 'Veg Momo', 'Steamed dumplings filled with fresh vegetables.', 'image/menu/menu_1787711098_6557.jpg', '140', '2026-08-26 02:24:58'),
(3, 'Starters', 'Chicken Momo', 'Soft dumplings filled with seasoned chicken.', 'image/menu/menu_1787711123_3416.jpg', '180', '2026-08-26 02:25:23'),
(4, 'Starters', 'French fries', 'Crispy golden fries served with dipping sauce.', 'image/menu/menu_1787745853_5816.jpg', '150', '2026-08-26 02:25:45'),
(5, 'Starters', 'Crispy chicken', 'Crunchy fried chicken pieces with flavorful seasoning.', 'image/menu/menu_1787711166_8836.jpg', '220', '2026-08-26 02:26:06'),
(6, 'Starters', 'Paneer Chilli', 'Soft paneer tossed with spicy peppers and onions.', 'image/menu/menu_1787711200_1125.jpg', '200', '2026-08-26 02:26:40'),
(7, 'Starters', 'Chicken Wings', 'Juicy chicken wings with a tasty spicy coating.', 'image/menu/menu_1787711225_7286.jpg', '240', '2026-08-26 02:27:05'),
(8, 'Starters', 'Garlic Bread', 'Toasted bread topped with buttery garlic and herbs.', 'image/menu/menu_1787711245_9851.jpg', '130', '2026-08-26 02:27:25'),
(9, 'Main Course', 'Chicken Chowmein', 'Stir-fried noodles with chicken and fresh vegetables.', 'image/menu/menu_1787711826_7789.jpg', '220', '2026-08-26 02:37:06'),
(10, 'Main Course', 'Veg Chowmein', 'Stir-fried noodles with fresh vegetables and herbs.', 'image/menu/menu_1787712713_2004.jpg', '180', '2026-08-26 02:51:53'),
(11, 'Main Course', 'Chicken Fried Rice', 'Fragrant rice tossed with chicken, eggs, and vegetables.', 'image/menu/menu_1787712768_3642.jpg', '220', '2026-08-26 02:52:48'),
(12, 'Main Course', 'Veg Fried Rice', 'Fluffy rice cooked with fresh vegetables and herbs.', 'image/menu/menu_1787712968_5868.jpg', '180', '2026-08-26 02:56:08'),
(13, 'Main Course', 'Chicken Thukpa', 'Warm noodle soup with chicken and fresh vegetables.', 'image/menu/menu_1787713391_8650.jpg', '230', '2026-08-26 03:03:11'),
(14, 'Main Course', 'Chicken Briyani', 'Aromatic basmati rice layered with spiced chicken.', 'image/menu/menu_1787713419_3081.jpg', '320', '2026-08-26 03:03:39'),
(15, 'Main Course', 'Veg Biryani', 'Fragrant basmati rice cooked with vegetables and spices', 'image/menu/menu_1787713522_5311.jpg', '250', '2026-08-26 03:04:03'),
(16, 'Main Course', 'Chicken Curry with rice', 'Tender chicken cooked in a rich, flavorful curry', 'image/menu/menu_1787713554_1771.jpg', '280', '2026-08-26 03:05:54'),
(17, 'Main Course', 'Paneer Butter Masala with Naan', 'Creamy tomato-based paneer served with soft naan.', 'image/menu/menu_1787713581_4829.jpg', '280', '2026-08-26 03:06:21'),
(18, 'Main Course', 'Dal Bhat Set', 'Traditional rice, lentils, vegetables, and flavorful sides.', 'image/menu/menu_1787713599_7511.jpg', '300', '2026-08-26 03:06:39'),
(19, 'Beverages', 'Fresh Lemonade', 'Refreshing lemon drink with a hint of sweetness.', 'image/menu/menu_1787746327_9780.jpg', '120', '2026-08-26 12:12:07'),
(20, 'Beverages', 'Mango Lassi', 'Creamy yogurt drink blended with ripe mangoes.', 'image/menu/menu_1787746350_6441.jpg', '160', '2026-08-26 12:12:30'),
(21, 'Beverages', 'Sweet Lassi', 'Smooth and chilled yogurt drink with a touch of sweetness.', 'image/menu/menu_1787746371_8553.jpg', '140', '2026-08-26 12:12:51'),
(22, 'Beverages', 'Masala Chai', 'Warm tea brewed with aromatic spices and milk.', 'image/menu/menu_1787746391_9166.jpg', '100', '2026-08-26 12:13:11'),
(24, 'Beverages', 'Cold Chocolate', 'Creamy chilled chocolate drink topped with foam', 'image/menu/menu_1787746447_6949.jpg', '190', '2026-08-26 12:14:07'),
(25, 'Beverages', 'Fresh Orange Juice', 'Freshly squeezed orange juice served chilled.', 'image/menu/menu_1787746475_5075.jpg', '160', '2026-08-26 12:14:35'),
(26, 'Beverages', 'Watermelon Cooler', 'Fresh watermelon blended into a refreshing cooler.', 'image/menu/menu_1787746498_4407.jpg', '150', '2026-08-26 12:14:58'),
(27, 'Beverages', 'Mint Mojito', 'Fizzy lime drink with fresh mint and crushed ice.', 'image/menu/menu_1787746516_2886.jpg', '170', '2026-08-26 12:15:16'),
(28, 'Beverages', 'Strawberry Milkshake', 'Thick milkshake blended with fresh strawberries.', 'image/menu/menu_1787746539_4341.jpg', '220', '2026-08-26 12:15:39'),
(29, 'Beverages', 'CocoCola', 'Chilled Coca-Cola served cold', 'image/menu/menu_1787746596_8479.jpg', '100', '2026-08-26 12:16:36'),
(30, 'Desserts', 'Chocolate Lava Cake', 'Warm chocolate cake with a rich molten center.', 'image/menu/menu_1787747182_1014.jpg', '250', '2026-08-26 12:26:22'),
(31, 'Desserts', 'Brownie with Ice Cream', 'Warm chocolate brownie served with creamy vanilla ice cream.', 'image/menu/menu_1787747210_8375.jpg', '280', '2026-08-26 12:26:50'),
(32, 'Desserts', 'Gulab Jamun', 'Soft milk dumplings soaked in sweet aromatic syrup.', 'image/menu/menu_1787747236_9565.jpg', '150', '2026-08-26 12:27:16'),
(33, 'Desserts', 'Rasmalai', 'Soft cheese dumplings served in sweet saffron milk.', 'image/menu/menu_1787747255_1669.jpg', '180', '2026-08-26 12:27:35'),
(34, 'Desserts', 'Mango Cheesecake', 'Creamy cheesecake topped with fresh mango flavor.', 'image/menu/menu_1787747274_7314.jpg', '280', '2026-08-26 12:27:54'),
(35, 'Desserts', 'Chocolate Mousse', 'Light and creamy chocolate dessert with a rich flavor.', 'image/menu/menu_1787747302_9397.jpg', '220', '2026-08-26 12:28:22'),
(36, 'Desserts', 'Vanilla Ice Cream', 'Classic smooth vanilla ice cream served chilled.', 'image/menu/menu_1787747330_1851.jpg', '150', '2026-08-26 12:28:50'),
(37, 'Desserts', 'Fruit Cream', 'Fresh seasonal fruits mixed with sweet cream.', 'image/menu/menu_1787747348_1427.jpg', '200', '2026-08-26 12:29:08'),
(38, 'Desserts', 'Caramel Custard', 'Smooth baked custard topped with golden caramel.', 'image/menu/menu_1787747371_7538.jpg', '180', '2026-08-26 12:29:31'),
(39, 'Desserts', 'Sizzling Brownie', 'Warm chocolate brownie served on a sizzling plate with ice cream.', 'image/menu/menu_1787747397_8383.jpg', '300', '2026-08-26 12:29:57'),
(40, 'Coffee', 'Espresso', 'Strong, rich coffee served in a small cup.', 'image/menu/menu_1787748994_4664.jpg', '120', '2026-08-26 12:56:34'),
(41, 'Coffee', 'Americano', 'Smooth espresso topped with hot water.', 'image/menu/menu_1787749017_9433.jpg', '140', '2026-08-26 12:56:57'),
(42, 'Coffee', 'Cappuccino', 'Espresso topped with steamed milk and creamy foam.', 'image/menu/menu_1787749037_4685.jpg', '180', '2026-08-26 12:57:17'),
(43, 'Coffee', 'Cafe Latte', 'Smooth espresso blended with steamed milk.', 'image/menu/menu_1787749062_8437.jpg', '190', '2026-08-26 12:57:42'),
(44, 'Coffee', 'Mocha', 'Rich espresso mixed with chocolate and steamed milk.', 'image/menu/menu_1787749077_4682.jpg', '210', '2026-08-26 12:57:57'),
(45, 'Coffee', 'Caramel Latte', 'Creamy latte with a sweet caramel flavor.', 'image/menu/menu_1787749110_3662.jpg', '220', '2026-08-26 12:58:30'),
(46, 'Coffee', 'Iced Coffee', 'Chilled coffee blended with milk and ice.', 'image/menu/menu_1787749133_7899.jpg', '180', '2026-08-26 12:58:53'),
(47, 'Pizza & Burger', 'Margherita Pizza', 'Classic pizza topped with tomato, mozzarella, and fresh basil.', 'image/menu/menu_1787749601_8351.jpg', '350', '2026-08-26 13:06:41'),
(48, 'Pizza & Burger', 'Chicken Pizza', 'Savory pizza topped with seasoned chicken and melted cheese.', 'image/menu/menu_1787749629_9318.jpg', '450', '2026-08-26 13:07:09'),
(49, 'Pizza & Burger', 'BBQ Chicken Pizza', 'Tender chicken, BBQ sauce, onions, and melted cheese on a crispy crust.', 'image/menu/menu_1787749650_8861.jpg', '480', '2026-08-26 13:07:30'),
(50, 'Pizza & Burger', 'Veggie Pizza', 'Fresh vegetables, tomato sauce, and mozzarella baked on a crispy crust.', 'image/menu/menu_1787749677_4543.jpg', '400', '2026-08-26 13:07:57'),
(51, 'Pizza & Burger', 'Chicken Burger', 'Juicy chicken patty with lettuce, tomato, and our signature sauce.', 'image/menu/menu_1787749718_9782.jpg', '320', '2026-08-26 13:08:38'),
(52, 'Pizza & Burger', 'Crispy Chicken Burger', 'Crispy fried chicken with fresh lettuce and creamy sauce.', 'image/menu/menu_1787749741_9005.jpg', '350', '2026-08-26 13:09:01'),
(53, 'Pizza & Burger', 'Veggie Burger', 'Flavorful vegetable patty with lettuce, tomato, and house sauce.', 'image/menu/menu_1787749770_4737.jpg', '280', '2026-08-26 13:09:30');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` varchar(20) NOT NULL,
  `guests` varchar(20) NOT NULL,
  `seating` varchar(20) NOT NULL,
  `occasion` varchar(50) NOT NULL,
  `table_no` varchar(20) NOT NULL,
  `preorder` enum('yes','no') DEFAULT 'no',
  `total_amount` int(11) DEFAULT 0,
  `status` enum('confirmed','completed','cancelled') DEFAULT 'confirmed',
  `grilled_chicken_qty` int(11) DEFAULT 0,
  `chicken_pasta_qty` int(11) DEFAULT 0,
  `chocolate_cake_qty` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `phone_no`, `reservation_date`, `reservation_time`, `guests`, `seating`, `occasion`, `table_no`, `preorder`, `total_amount`, `status`, `grilled_chicken_qty`, `chicken_pasta_qty`, `chocolate_cake_qty`, `created_at`) VALUES
(1, 1, '9813159263', '2026-09-17', '01:00 PM', '2 Guests', 'Rooftop', 'Date', 'Table 02', 'yes', 0, 'confirmed', 1, 2, 2, '2026-08-24 03:00:36'),
(3, 2, '9813159263', '2026-08-31', '06:00 PM', '5 Guests', 'Indoor', 'Birthday', 'Table 05', 'yes', 0, 'confirmed', 2, 0, 0, '2026-08-25 03:11:06'),
(4, 1, '9813159263', '2026-10-27', '02:00 PM', '4 Guests', 'Rooftop', 'Friends get together', 'Table 03', 'yes', 0, 'confirmed', 0, 4, 2, '2026-08-25 03:39:44'),
(5, 1, '9813159263', '2026-09-24', '02:00 PM', '6 Guests', 'Rooftop', 'Friends get together', 'Table 05', 'yes', 0, 'confirmed', 0, 0, 0, '2026-08-25 03:56:50'),
(7, 1, '69420', '2026-11-21', '12:00 PM', '8 Guests', 'Rooftop', 'Anniversary', 'Table 05', 'yes', 294000, 'confirmed', 0, 0, 0, '2026-08-26 01:17:44'),
(8, 3, '9841404098', '2026-08-30', '07:00 PM', '4 Guests', 'Rooftop', 'Business meeting', 'Rooftop - Table 03', 'yes', 2520, 'confirmed', 0, 0, 0, '2026-08-27 00:56:40');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_items`
--

CREATE TABLE `reservation_items` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_items`
--

INSERT INTO `reservation_items` (`id`, `reservation_id`, `menu_item_id`, `quantity`) VALUES
(25, 8, 41, 2),
(26, 8, 42, 1),
(27, 8, 43, 1),
(28, 8, 48, 1),
(29, 8, 49, 1),
(30, 8, 1, 1),
(31, 8, 3, 2),
(32, 8, 5, 1),
(33, 8, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone_no`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Nitesh', 'Niteshraut804@gmail.com', '9813159645', '$2y$10$K2vJGD3UGnmnP7Jc6sPfgOYGrlZXYeT7jP.wbFJ0msGRGqC1jwixO', '2026-08-23 10:52:18', '2026-08-23 10:52:18'),
(2, 'Akriti Sharma', 'niraj@niraj.com', '9851166047', '$2y$10$gsZf6bEMLuSYaGvkZj5clu305aSe0Ea4r4bJ2bjXNQEbWg/i.exta', '2026-08-25 03:08:15', '2026-08-25 03:08:15'),
(3, 'Reedam Shrestha', 'reedamstha@gmail.com', '9841404098', '$2y$10$gwBPed1a7NLAfXX8KLckheMMGBaSAejlBqQGhV/qGjDwRn4ycY3W6', '2026-08-27 00:49:11', '2026-08-27 00:49:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reservation_items`
--
ALTER TABLE `reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD CONSTRAINT `reservation_items_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
