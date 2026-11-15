-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2026 at 06:42 AM
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
-- Database: `little_treats_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'Admin', 'admin@littletreats.com', '$2y$10$eb4zLlFbzhGgMb6xfOvjBOxH6WHTn638xz0gZou08RBcOT/KWUx7O');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Cakes'),
(2, 'Cupcakes'),
(3, 'Cookies'),
(4, 'Desserts'),
(5, 'Brownies & Bars');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'John', 'john@gmail.com', 'Cake Order', 'I would like to know about your chocolate cakes.', 'Read', '2026-08-12 20:42:38'),
(2, 'John', 'john@gmail.com', 'Cake Order', 'I would like to know about your chocolate cakes.', 'Read', '2026-08-12 20:43:42'),
(3, 'John', 'john@gmail.com', 'Cake Order', 'I would like to know about your chocolate cakes.', 'Unread', '2026-08-12 20:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty`
--

CREATE TABLE `loyalty` (
  `loyalty_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `loyalty_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`loyalty_id`, `user_id`, `points`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_redemptions`
--

CREATE TABLE `loyalty_redemptions` (
  `redemption_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_used` int(11) NOT NULL,
  `redemption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_redemptions`
--

INSERT INTO `loyalty_redemptions` (`redemption_id`, `user_id`, `reward_id`, `points_used`, `redemption_date`, `status`) VALUES
(1, 1, 1, 10, '2026-08-12 18:58:25', 'Collected'),
(2, 1, 1, 10, '2026-08-14 09:51:27', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(100) NOT NULL,
  `points_required` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_rewards`
--

INSERT INTO `loyalty_rewards` (`reward_id`, `reward_name`, `points_required`, `description`) VALUES
(1, '6-Piece Brownie Box', 10, 'A box containing 6 delicious brownies');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `order_status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `order_status`, `created_at`, `payment_method`, `payment_status`) VALUES
(1, 1, '2026-08-12 09:20:47', 5000.00, 'Pending', 'Preparing', '2026-08-12 16:08:40', 'Cash on Delivery', 'Pending'),
(2, 1, '2026-08-12 09:20:56', 8000.00, 'Pending', 'Completed', '2026-08-12 16:08:40', 'Cash on Delivery', 'Pending'),
(3, 1, '2026-08-12 09:21:07', 8000.00, 'Pending', 'Completed', '2026-08-12 16:08:40', 'Cash on Delivery', 'Pending'),
(4, 1, '2026-08-12 09:21:59', 11000.00, 'Pending', 'Completed', '2026-08-12 16:08:40', 'Cash on Delivery', 'Pending'),
(5, 1, '2026-08-12 09:22:45', 500.00, 'Pending', 'Completed', '2026-08-12 16:08:40', 'Cash on Delivery', 'Pending'),
(6, 1, '2026-08-12 09:24:39', 3500.00, 'Pending', 'Confirmed', '2026-08-12 16:08:40', 'Cash on Delivery', 'Paid'),
(7, 1, '2026-08-12 16:00:40', 3300.00, 'Pending', 'Cancelled', '2026-08-12 16:08:40', 'Cash on Delivery', 'Cancelled'),
(8, 1, '2026-08-12 17:12:54', 3000.00, 'Pending', 'Pending', '2026-08-12 17:12:54', 'Cash on Delivery', 'Pending'),
(9, 1, '2026-08-12 18:09:27', 4000.00, 'Pending', 'Pending', '2026-08-12 18:09:27', 'Cash on Delivery', 'Pending'),
(10, 1, '2026-08-12 18:35:57', 1000.00, 'Pending', 'Ready', '2026-08-12 18:35:57', 'Cash on Delivery', 'Paid'),
(11, 1, '2026-08-12 18:38:40', 5500.00, 'Pending', 'Preparing', '2026-08-12 18:38:40', 'Cash on Delivery', 'Paid'),
(12, 1, '2026-08-12 20:34:15', 300.00, 'Pending', 'Confirmed', '2026-08-12 20:34:15', 'Cash on Delivery', 'Failed'),
(13, 1, '2026-08-12 20:38:35', 300.00, 'Pending', 'Ready', '2026-08-12 20:38:35', 'Cash on Delivery', 'Pending'),
(14, 1, '2026-08-14 09:33:32', 600.00, 'Pending', 'Pending', '2026-08-14 09:33:32', 'Cash on Delivery', 'Pending'),
(15, 1, '2026-09-23 15:48:16', 2500.00, 'Pending', 'Preparing', '2026-09-23 15:48:16', 'Bank Transfer', 'Pending'),
(16, 1, '2026-09-23 15:52:20', 300.00, 'Pending', 'Preparing', '2026-09-23 15:52:20', 'Cash on Delivery', 'Paid'),
(17, 1, '2026-10-18 04:10:33', 3000.00, 'Pending', 'Pending', '2026-10-18 04:10:33', 'Cash on Delivery', 'Pending'),
(18, 1, '2026-11-08 05:41:56', 12000.00, 'Pending', 'Pending', '2026-11-08 05:41:56', 'Card Payment', 'Pending'),
(19, 1, '2026-11-11 17:40:17', 300.00, 'Pending', 'Pending', '2026-11-11 17:40:17', 'Card Payment', 'Pending'),
(20, 1, '2026-11-15 05:32:56', 6000.00, 'Pending', 'Pending', '2026-11-15 05:32:56', 'Card Payment', 'Pending'),
(21, 1, '2026-11-15 05:35:01', 6600.00, 'Pending', 'Pending', '2026-11-15 05:35:01', 'Bank Transfer', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 6, 3, 1, 500.00),
(2, 6, 5, 1, 3000.00),
(3, 7, 5, 1, 3000.00),
(4, 7, 4, 1, 300.00),
(5, 8, 5, 1, 3000.00),
(6, 9, 5, 1, 3000.00),
(7, 9, 3, 2, 500.00),
(8, 10, 3, 2, 500.00),
(9, 11, 3, 11, 500.00),
(10, 12, 4, 1, 300.00),
(11, 13, 4, 1, 300.00),
(12, 14, 4, 2, 300.00),
(13, 15, 1, 1, 2500.00),
(14, 16, 4, 1, 300.00),
(15, 17, 3, 6, 500.00),
(16, 18, 5, 4, 3000.00),
(17, 19, 4, 1, 300.00),
(18, 20, 5, 2, 3000.00),
(19, 21, 4, 2, 300.00),
(20, 21, 3, 1, 500.00),
(21, 21, 5, 1, 3000.00),
(22, 21, 1, 1, 2500.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `image`) VALUES
(1, 1, 'Chocolate Birthday Cake', 'Delicious chocolate cake with cream', 2500.00, 'Chocolate Birthday Cake.jpg'),
(3, 2, 'Vanilla Cupcake', 'Soft vanilla cupcake', 500.00, 'Vanilla Cupcake.jpg'),
(4, 3, 'Chocolate Cookies', 'Fresh baked cookies', 300.00, 'Chocolate Cookies.jpg'),
(5, 1, 'Red Velvet Cake', 'Delicious red velvet cake with cream cheese frosting', 3000.00, 'Red Velvet Cake.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`, `loyalty_points`) VALUES
(1, 'Test Customer', 'test@gmail.com', '$2y$10$BvZJU6VfD7IeTERCidGB0OE/JtMLkruP4/njY/T1daK5mfb5VjKpi', '0712345678', 'Kandy', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `loyalty`
--
ALTER TABLE `loyalty`
  ADD PRIMARY KEY (`loyalty_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`loyalty_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD PRIMARY KEY (`redemption_id`);

--
-- Indexes for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`reward_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loyalty`
--
ALTER TABLE `loyalty`
  MODIFY `loyalty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `loyalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  MODIFY `redemption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loyalty`
--
ALTER TABLE `loyalty`
  ADD CONSTRAINT `loyalty_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
