-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 08:18 AM
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
-- Database: `db_chateau`
--

-- --------------------------------------------------------

--
-- Table structure for table `menuitems`
--

CREATE TABLE `menuitems` (
  `menu_item_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menuitems`
--

INSERT INTO `menuitems` (`menu_item_id`, `restaurant_id`, `name`, `description`, `price`, `category`, `image_url`, `is_available`) VALUES
(1, 1, 'Beef Wellington', 'Premium beef wrapped in puff pastry with mushroom duxelles', 450000.00, 'Degustation', NULL, 1),
(2, 1, 'Duck Confit', 'Slow-cooked duck leg with garlic and herbs', 320000.00, 'Degustation', NULL, 1),
(3, 1, 'Cheese Platter', 'Selection of French cheeses with crackers and fruits', 180000.00, 'Prasmanan', NULL, 1),
(4, 2, 'Nasi Gudeg Yogya', 'Traditional Yogyakarta gudeg with rice and side dishes', 45000.00, 'Prasmanan', NULL, 1),
(5, 2, 'Ayam Bakar Klaten', 'Grilled chicken with traditional Javanese spices', 55000.00, 'Prasmanan', NULL, 1),
(6, 2, 'Es Dawet Ayu', 'Traditional Javanese dessert with coconut milk', 15000.00, 'Prasmanan', NULL, 1),
(7, 3, 'Sashimi Set', 'Fresh assorted sashimi with wasabi and soy sauce', 180000.00, 'Degustation', NULL, 1),
(8, 3, 'Tempura Moriawase', 'Mixed tempura with vegetables and prawns', 85000.00, 'Prasmanan', NULL, 1),
(9, 3, 'Chirashi Don', 'Assorted sashimi over seasoned sushi rice', 120000.00, 'Prasmanan', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `cardholder_name` varchar(100) DEFAULT NULL,
  `card_number_last4` varchar(4) DEFAULT NULL,
  `expiry_date` varchar(5) DEFAULT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'Confirmed',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `reservation_id`, `payment_method`, `cardholder_name`, `card_number_last4`, `expiry_date`, `transaction_id`, `payment_status`, `payment_date`, `amount`) VALUES
(1, 1, 'Credit Card', 'John Doe', '1234', '12/25', 'CHT-0EB3E8B9', 'Confirmed', '2025-05-26 11:27:56', 450000.00),
(2, 2, 'PayPal', 'Jane Smith', NULL, NULL, 'CHT-8AA0E1EC', 'Confirmed', '2025-05-26 11:27:56', 180000.00),
(3, 3, 'Credit Card', 'Mike Brown', '5678', '03/26', 'CHT-C836C3F7', 'Confirmed', '2025-05-26 11:27:56', 540000.00),
(4, 4, 'Credit Card', 'Sarah Jones', '9012', '08/27', 'CHT-D7C10BA4', 'Confirmed', '2025-05-26 11:27:56', 320000.00);

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `generate_transaction_id` BEFORE INSERT ON `payments` FOR EACH ROW BEGIN
    IF NEW.transaction_id IS NULL OR NEW.transaction_id = '' THEN
        SET NEW.transaction_id = CONCAT(
            'CHT-',
            UPPER(SUBSTRING(MD5(RAND()), 1, 8))
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `promotionsevents`
--

CREATE TABLE `promotionsevents` (
  `promotion_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `discount` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotionsevents`
--

INSERT INTO `promotionsevents` (`promotion_id`, `restaurant_id`, `title`, `discount`, `description`, `start_date`, `end_date`, `type`, `image_url`, `created_at`) VALUES
(1, 1, 'Christmas Special Menu', '15%', 'Special Christmas dinner with 15% discount for early bookings', '2024-12-20', '2024-12-31', 'Penawaran Khusus', NULL, '2025-05-26 11:27:56'),
(2, 2, 'Traditional Food Festival', '20%', 'Celebrate Indonesian heritage with 20% off traditional dishes', '2024-12-15', '2024-12-30', 'Event Musiman', NULL, '2025-05-26 11:27:56'),
(3, 3, 'New Year Sushi Promotion', '10%', 'Welcome 2025 with fresh sushi and special discounts', '2024-12-28', '2025-01-05', 'Penawaran Khusus', NULL, '2025-05-26 11:27:56'),
(4, 1, 'Valentine Romance Package', '25%', 'Romantic dinner package for couples with complimentary dessert', '2025-02-10', '2025-02-16', 'Event Musiman', NULL, '2025-05-26 11:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `reservation_number` varchar(10) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Confirmed',
  `seating_preference` varchar(100) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `restaurant_id`, `reservation_number`, `reservation_date`, `reservation_time`, `number_of_guests`, `status`, `seating_preference`, `special_requests`, `created_at`) VALUES
(1, 1, 1, 'AZWI97', '2024-12-25', '19:00:00', 2, 'Confirmed', 'Window table', 'Anniversary dinner, please prepare romantic setup', '2025-05-26 11:27:56'),
(2, 2, 2, 'YUDI22', '2024-12-26', '18:30:00', 4, 'Confirmed', 'Private room', 'Business dinner, need quiet environment', '2025-05-26 11:27:56'),
(3, 3, 3, 'UMCZ70', '2024-12-27', '20:00:00', 6, 'Confirmed', 'Main dining area', 'Birthday celebration for 6 people', '2025-05-26 11:27:56'),
(4, 4, 1, 'KAVB80', '2024-12-28', '19:30:00', 2, 'Confirmed', 'Terrace', 'Vegetarian menu preferred', '2025-05-26 11:27:56');

--
-- Triggers `reservations`
--
DELIMITER $$
CREATE TRIGGER `generate_reservation_number` BEFORE INSERT ON `reservations` FOR EACH ROW BEGIN
    DECLARE random_code VARCHAR(6);
    DECLARE done INT DEFAULT FALSE;
    DECLARE existing_count INT DEFAULT 1;
    
    WHILE existing_count > 0 DO
        SET random_code = CONCAT(
            CHAR(65 + FLOOR(RAND() * 26)),
            CHAR(65 + FLOOR(RAND() * 26)),
            CHAR(65 + FLOOR(RAND() * 26)),
            CHAR(65 + FLOOR(RAND() * 26)),
            FLOOR(10 + RAND() * 90)
        );
        
        SELECT COUNT(*) INTO existing_count 
        FROM Reservations 
        WHERE reservation_number = random_code;
    END WHILE;
    
    SET NEW.reservation_number = random_code;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `restaurantphotos`
--

CREATE TABLE `restaurantphotos` (
  `photo_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurantphotos`
--

INSERT INTO `restaurantphotos` (`photo_id`, `restaurant_id`, `image_url`, `caption`, `uploaded_at`, `is_primary`) VALUES
(1, 1, '/images/chateau_interior.jpg', 'Elegant dining room with chandelier', '2025-05-26 11:27:56', 1),
(2, 1, '/images/chateau_dish1.jpg', 'Signature Beef Wellington', '2025-05-26 11:27:56', 0),
(3, 2, '/images/warung_exterior.jpg', 'Traditional Indonesian restaurant facade', '2025-05-26 11:27:56', 1),
(4, 2, '/images/warung_gudeg.jpg', 'Famous Nasi Gudeg Yogya', '2025-05-26 11:27:56', 0),
(5, 3, '/images/sakura_interior.jpg', 'Modern Japanese dining atmosphere', '2025-05-26 11:27:56', 1),
(6, 3, '/images/sakura_sushi.jpg', 'Fresh sashimi platter', '2025-05-26 11:27:56', 0);

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `operating_hours_lunch` varchar(100) DEFAULT NULL,
  `operating_hours_dinner` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `overall_rating` decimal(2,1) DEFAULT 0.0,
  `food_rating` decimal(2,1) DEFAULT 0.0,
  `service_rating` decimal(2,1) DEFAULT 0.0,
  `ambience_rating` decimal(2,1) DEFAULT 0.0,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`restaurant_id`, `name`, `address`, `phone_number`, `operating_hours_lunch`, `operating_hours_dinner`, `description`, `overall_rating`, `food_rating`, `service_rating`, `ambience_rating`, `country`, `city`, `created_at`) VALUES
(1, 'Chateau Fine Dining', 'Jl. Thamrin No. 1, Jakarta Pusat', '+62-21-555-0001', '11:00-15:00', '18:00-23:00', 'Elegant French cuisine with premium ingredients and sophisticated atmosphere', 4.8, 4.5, 5.0, 5.0, 'Indonesia', 'Jakarta', '2025-05-26 11:27:56'),
(2, 'Warung Nusantara', 'Jl. Malioboro No. 56, Yogyakarta', '+62-274-555-0002', '10:00-16:00', '17:00-22:00', 'Authentic Indonesian dishes with modern presentation and traditional recipes', 4.0, 4.0, 4.0, 4.0, 'Indonesia', 'Yogyakarta', '2025-05-26 11:27:56'),
(3, 'Sakura Sushi', 'Jl. Sudirman No. 25, Bandung', '+62-22-555-0003', '12:00-15:00', '18:00-22:00', 'Fresh sushi and Japanese cuisine with authentic flavors from Japan', 4.3, 5.0, 4.0, 4.0, 'Indonesia', 'Bandung', '2025-05-26 11:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `food_rating` int(11) DEFAULT NULL CHECK (`food_rating` >= 1 and `food_rating` <= 5),
  `service_rating` int(11) DEFAULT NULL CHECK (`service_rating` >= 1 and `service_rating` <= 5),
  `ambience_rating` int(11) DEFAULT NULL CHECK (`ambience_rating` >= 1 and `ambience_rating` <= 5),
  `comment` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `restaurant_id`, `reservation_id`, `food_rating`, `service_rating`, `ambience_rating`, `comment`, `review_date`) VALUES
(1, 1, 1, 1, 5, 5, 5, 'Exceptional dining experience! The beef wellington was perfectly cooked and the service was impeccable.', '2025-05-26 11:27:56'),
(2, 2, 2, 2, 4, 4, 4, 'Great authentic Indonesian food. The gudeg was delicious and the atmosphere was cozy.', '2025-05-26 11:27:56'),
(3, 3, 3, 3, 5, 4, 4, 'Fresh sushi and great presentation. Will definitely come back for more.', '2025-05-26 11:27:56'),
(4, 4, 1, 4, 4, 5, 5, 'Beautiful restaurant with excellent service. The vegetarian options were creative and tasty.', '2025-05-26 11:27:56');

--
-- Triggers `reviews`
--
DELIMITER $$
CREATE TRIGGER `update_restaurant_ratings` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    UPDATE Restaurants 
    SET 
        overall_rating = (
            SELECT ROUND(AVG((food_rating + service_rating + ambience_rating) / 3), 1)
            FROM Reviews 
            WHERE restaurant_id = NEW.restaurant_id
        ),
        food_rating = (
            SELECT ROUND(AVG(food_rating), 1)
            FROM Reviews 
            WHERE restaurant_id = NEW.restaurant_id
        ),
        service_rating = (
            SELECT ROUND(AVG(service_rating), 1)
            FROM Reviews 
            WHERE restaurant_id = NEW.restaurant_id
        ),
        ambience_rating = (
            SELECT ROUND(AVG(ambience_rating), 1)
            FROM Reviews 
            WHERE restaurant_id = NEW.restaurant_id
        )
    WHERE restaurant_id = NEW.restaurant_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `full_name`, `phone_number`, `registration_date`) VALUES
(1, 'johndoe', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'john.doe@email.com', 'John Doe', '+62-812-3456-7890', '2025-05-26 11:27:56'),
(2, 'janesmith', '$2y$10$zyxwvutsrqponmlkjihgfedcba654321', 'jane.smith@email.com', 'Jane Smith', '+62-823-4567-8901', '2025-05-26 11:27:56'),
(3, 'mikebrown', '$2y$10$1234567890abcdefghijklmnopqrstuv', 'mike.brown@email.com', 'Mike Brown', '+62-834-5678-9012', '2025-05-26 11:27:56'),
(4, 'sarahjones', '$2y$10$vwxyz12345abcdefghijklmnopqrstuv', 'sarah.jones@email.com', 'Sarah Jones', '+62-845-6789-0123', '2025-05-26 11:27:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD PRIMARY KEY (`menu_item_id`),
  ADD KEY `idx_menuitems_restaurant` (`restaurant_id`),
  ADD KEY `idx_menuitems_category` (`category`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `reservation_id` (`reservation_id`),
  ADD KEY `idx_payments_reservation` (`reservation_id`);

--
-- Indexes for table `promotionsevents`
--
ALTER TABLE `promotionsevents`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `idx_promotions_restaurant` (`restaurant_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD UNIQUE KEY `reservation_number` (`reservation_number`),
  ADD KEY `idx_reservations_user` (`user_id`),
  ADD KEY `idx_reservations_restaurant` (`restaurant_id`),
  ADD KEY `idx_reservations_date` (`reservation_date`),
  ADD KEY `idx_reservations_number` (`reservation_number`);

--
-- Indexes for table `restaurantphotos`
--
ALTER TABLE `restaurantphotos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `idx_photos_restaurant` (`restaurant_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD KEY `idx_restaurants_city` (`city`),
  ADD KEY `idx_restaurants_rating` (`overall_rating`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `idx_reviews_restaurant` (`restaurant_id`),
  ADD KEY `idx_reviews_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menuitems`
--
ALTER TABLE `menuitems`
  MODIFY `menu_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promotionsevents`
--
ALTER TABLE `promotionsevents`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurantphotos`
--
ALTER TABLE `restaurantphotos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD CONSTRAINT `menuitems_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE;

--
-- Constraints for table `promotionsevents`
--
ALTER TABLE `promotionsevents`
  ADD CONSTRAINT `promotionsevents_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurantphotos`
--
ALTER TABLE `restaurantphotos`
  ADD CONSTRAINT `restaurantphotos_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
