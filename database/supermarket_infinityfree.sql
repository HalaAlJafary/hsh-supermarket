-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 29 أبريل 2026 الساعة 17:33
-- إصدار الخادم: 8.0.17
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `supermarket`
--

-- --------------------------------------------------------

--
-- بنية الجدول `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Admin','Customer') NOT NULL DEFAULT 'Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `customers`
--

INSERT INTO `customers` (`CustomerID`, `FirstName`, `LastName`, `Email`, `Phone`, `City`, `Password`, `Role`) VALUES
(2, 'Mona', 'Haddad', 'mona.haddad2@example.com', '0790669151', 'Karak', '', 'Customer'),
(3, 'Huda', 'Ali', 'huda.ali3@example.com', '0791510395', 'Madaba', '', 'Customer'),
(4, 'Rana', 'Nassar', 'rana.nassar4@example.com', '0788655962', 'Mafraq', '', 'Customer'),
(5, 'Ahmad', 'Khoury', 'ahmad.khoury5@example.com', '0799252670', 'Irbid', '', 'Customer'),
(6, 'Hassan', 'Masri', 'hassan.masri6@example.com', '0773871964', 'Balqa', '', 'Customer'),
(7, 'Omar', 'Qasim', 'omar.qasim7@example.com', '0778032059', 'Ajloun', '', 'Customer'),
(8, 'Khaled', 'Ali', 'khaled.ali8@example.com', '0788916047', 'Amman', '', 'Customer'),
(9, 'Sara', 'Sami', 'sara.sami9@example.com', '0796268318', 'Aqaba', '', 'Customer'),
(10, 'Omar', 'Masri', 'omar.masri10@example.com', '0784622567', 'Jerash', '', 'Customer'),
(11, 'Mona', 'Sami', 'mona.sami11@example.com', '0784750463', 'Ajloun', '', 'Customer'),
(12, 'Sami', 'Ali', 'sami.ali12@example.com', '0772534044', 'Balqa', '', 'Customer'),
(13, 'Farah', 'Omar', 'farah.omar13@example.com', '0777215938', 'Madaba', '', 'Customer'),
(14, 'Mona', 'Sami', 'mona.sami14@example.com', '0787392656', 'Mafraq', '', 'Customer'),
(15, 'Hassan', 'Nassar', 'hassan.nassar15@example.com', '0790304340', 'Amman', '', 'Customer'),
(16, 'Huda', 'Khoury', 'huda.khoury16@example.com', '0786223521', 'Zarqa', '', 'Customer'),
(17, 'Ali', 'Masri', 'ali.masri17@example.com', '0789837545', 'Mafraq', '', 'Customer'),
(18, 'Rana', 'Haddad', 'rana.haddad18@example.com', '0794625316', 'Madaba', '', 'Customer'),
(19, 'Noor', 'Haddad', 'noor.haddad19@example.com', '0793140558', 'Balqa', '', 'Customer'),
(20, 'Sara', 'Sami', 'sara.sami20@example.com', '0797104825', 'Mafraq', '', 'Customer'),
(21, 'Rana', 'Jaber', 'rana.jaber21@example.com', '0773277348', 'Ajloun', '', 'Customer'),
(22, 'Mona', 'Nassar', 'mona.nassar22@example.com', '0795495953', 'Madaba', '', 'Customer'),
(23, 'Hassan', 'Nassar', 'hassan.nassar23@example.com', '0775438224', 'Amman', '', 'Customer'),
(24, 'Sami', 'Haddad', 'sami.haddad24@example.com', '0794788550', 'Ajloun', '', 'Customer'),
(25, 'Sami', 'Masri', 'sami.masri25@example.com', '0799183036', 'Amman', '', 'Customer'),
(26, 'Huda', 'Nassar', 'huda.nassar26@example.com', '0772050774', 'Jerash', '', 'Customer'),
(27, 'Farah', 'Nassar', 'farah.nassar27@example.com', '0775599958', 'Zarqa', '', 'Customer'),
(28, 'Sami', 'Nassar', 'sami.nassar28@example.com', '0785320509', 'Balqa', '', 'Customer'),
(29, 'Zaid', 'Ali', 'zaid.ali29@example.com', '0788584693', 'Karak', '', 'Customer'),
(30, 'Noor', 'Sami', 'noor.sami30@example.com', '0778631609', 'Karak', '', 'Customer'),
(31, 'Ahmad', 'Omar', 'ahmad.omar31@example.com', '0793809006', 'Irbid', '', 'Customer'),
(32, 'Huda', 'Omar', 'huda.omar32@example.com', '0795643629', 'Jerash', '', 'Customer'),
(33, 'Mona', 'Kamel', 'mona.kamel33@example.com', '0775708837', 'Ajloun', '', 'Customer'),
(34, 'Farah', 'Ali', 'farah.ali34@example.com', '0775189546', 'Irbid', '', 'Customer'),
(35, 'Huda', 'Haddad', 'huda.haddad35@example.com', '0794682895', 'Jerash', '', 'Customer'),
(36, 'Mona', 'Ali', 'mona.ali36@example.com', '0774522891', 'Ajloun', '', 'Customer'),
(37, 'Khaled', 'Qasim', 'khaled.qasim37@example.com', '0775893399', 'Madaba', '', 'Customer'),
(38, 'Ahmad', 'Qasim', 'ahmad.qasim38@example.com', '0783866075', 'Jerash', '', 'Customer'),
(39, 'Noor', 'Qasim', 'noor.qasim39@example.com', '0796569626', 'Madaba', '', 'Customer'),
(40, 'Ali', 'Masri', 'ali.masri40@example.com', '0798421306', 'Amman', '', 'Customer'),
(41, 'Ali', 'Omar', 'ali.omar41@example.com', '0789433578', 'Amman', '', 'Customer'),
(42, 'Laila', 'Ali', 'laila.ali42@example.com', '0790922707', 'Zarqa', '', 'Customer'),
(43, 'Huda', 'Kamel', 'huda.kamel43@example.com', '0777543479', 'Zarqa', '', 'Customer'),
(44, 'Laila', 'Omar', 'laila.omar44@example.com', '0780500625', 'Amman', '', 'Customer'),
(45, 'Hassan', 'Nassar', 'hassan.nassar45@example.com', '0777215862', 'Zarqa', '', 'Customer'),
(46, 'Sami', 'Nassar', 'sami.nassar46@example.com', '0773503610', 'Balqa', '', 'Customer'),
(47, 'Ahmad', 'Kamel', 'ahmad.kamel47@example.com', '0772836064', 'Mafraq', '', 'Customer'),
(48, 'Hassan', 'Sami', 'hassan.sami48@example.com', '0790181625', 'Zarqa', '', 'Customer'),
(49, 'Zaid', 'Nassar', 'zaid.nassar49@example.com', '0793397996', 'Balqa', '', 'Customer'),
(50, 'Huda', 'Omar', 'huda.omar50@example.com', '0793151974', 'Madaba', '', 'Customer'),
(51, 'Khaled', 'Kamel', 'khaled.kamel51@example.com', '0791555351', 'Mafraq', '', 'Customer'),
(52, 'Tariq', 'Ali', 'tariq.ali52@example.com', '0789948766', 'Irbid', '', 'Customer'),
(53, 'Hassan', 'Kamel', 'hassan.kamel53@example.com', '0785433214', 'Jerash', '', 'Customer'),
(54, 'Mona', 'Omar', 'mona.omar54@example.com', '0796586122', 'Balqa', '', 'Customer'),
(55, 'Sami', 'Qasim', 'sami.qasim55@example.com', '0787123989', 'Ajloun', '', 'Customer'),
(56, 'Ali', 'Khoury', 'ali.khoury56@example.com', '0771005233', 'Karak', '', 'Customer'),
(57, 'Noor', 'Haddad', 'noor.haddad57@example.com', '0781704998', 'Irbid', '', 'Customer'),
(58, 'Rana', 'Qasim', 'rana.qasim58@example.com', '0786912285', 'Karak', '', 'Customer'),
(59, 'Sami', 'Khoury', 'sami.khoury59@example.com', '0797358911', 'Madaba', '', 'Customer'),
(60, 'Ahmad', 'Masri', 'ahmad.masri60@example.com', '0784678993', 'Zarqa', '', 'Customer'),
(61, 'Sami', 'Sami', 'sami.sami61@example.com', '0772370451', 'Karak', '', 'Customer'),
(62, 'Omar', 'Omar', 'omar.omar62@example.com', '0777983981', 'Balqa', '', 'Customer'),
(63, 'Omar', 'Masri', 'omar.masri63@example.com', '0799146381', 'Ajloun', '', 'Customer'),
(64, 'Mona', 'Omar', 'mona.omar64@example.com', '0787096877', 'Irbid', '', 'Customer'),
(65, 'Noor', 'Nassar', 'noor.nassar65@example.com', '0773631781', 'Ajloun', '', 'Customer'),
(66, 'Rana', 'Qasim', 'rana.qasim66@example.com', '0790202537', 'Aqaba', '', 'Customer'),
(67, 'Sara', 'Nassar', 'sara.nassar67@example.com', '0798460656', 'Amman', '', 'Customer'),
(68, 'Omar', 'Khoury', 'omar.khoury68@example.com', '0777694516', 'Ajloun', '', 'Customer'),
(69, 'Hassan', 'Khoury', 'hassan.khoury69@example.com', '0777772177', 'Balqa', '', 'Customer'),
(70, 'Mona', 'Khoury', 'mona.khoury70@example.com', '0787362217', 'Madaba', '', 'Customer'),
(71, 'Sami', 'Ali', 'sami.ali71@example.com', '0797552086', 'Mafraq', '', 'Customer'),
(72, 'Ali', 'Nassar', 'ali.nassar72@example.com', '0774186023', 'Irbid', '', 'Customer'),
(73, 'Sami', 'Khoury', 'sami.khoury73@example.com', '0795403320', 'Karak', '', 'Customer'),
(74, 'Ahmad', 'Omar', 'ahmad.omar74@example.com', '0785753215', 'Jerash', '', 'Customer'),
(75, 'Mona', 'Ali', 'mona.ali75@example.com', '0781141101', 'Karak', '', 'Customer'),
(76, 'Huda', 'Qasim', 'huda.qasim76@example.com', '0795894207', 'Balqa', '', 'Customer'),
(77, 'Rana', 'Kamel', 'rana.kamel77@example.com', '0798317902', 'Amman', '', 'Customer'),
(78, 'Laila', 'Kamel', 'laila.kamel78@example.com', '0791344712', 'Karak', '', 'Customer'),
(79, 'Ali', 'Masri', 'ali.masri79@example.com', '0775358546', 'Irbid', '', 'Customer'),
(80, 'Sami', 'Masri', 'sami.masri80@example.com', '0776550909', 'Ajloun', '', 'Customer'),
(81, 'Farah', 'Khoury', 'farah.khoury81@example.com', '0773206808', 'Irbid', '', 'Customer'),
(82, 'Omar', 'Kamel', 'omar.kamel82@example.com', '0771729770', 'Aqaba', '', 'Customer'),
(83, 'Farah', 'Kamel', 'farah.kamel83@example.com', '0770125832', 'Zarqa', '', 'Customer'),
(84, 'Tariq', 'Nassar', 'tariq.nassar84@example.com', '0796519995', 'Zarqa', '', 'Customer'),
(85, 'Mona', 'Sami', 'mona.sami85@example.com', '0772370508', 'Zarqa', '', 'Customer'),
(86, 'Omar', 'Sami', 'omar.sami86@example.com', '0773872209', 'Irbid', '', 'Customer'),
(87, 'Sara', 'Masri', 'sara.masri87@example.com', '0774375372', 'Jerash', '', 'Customer'),
(88, 'Rana', 'Qasim', 'rana.qasim88@example.com', '0796078736', 'Irbid', '', 'Customer'),
(89, 'Tariq', 'Jaber', 'tariq.jaber89@example.com', '0779767939', 'Irbid', '', 'Customer'),
(90, 'Sami', 'Jaber', 'sami.jaber90@example.com', '0795041165', 'Amman', '', 'Customer'),
(91, 'Ahmad', 'Omar', 'ahmad.omar91@example.com', '0794204563', 'Balqa', '', 'Customer'),
(92, 'Noor', 'Qasim', 'noor.qasim92@example.com', '0784976217', 'Ajloun', '', 'Customer'),
(93, 'Zaid', 'Nassar', 'zaid.nassar93@example.com', '0799347230', 'Ajloun', '', 'Customer'),
(94, 'Laila', 'Ali', 'laila.ali94@example.com', '0799484027', 'Zarqa', '', 'Customer'),
(95, 'Noor', 'Ali', 'noor.ali95@example.com', '0799884001', 'Jerash', '', 'Customer'),
(96, 'Tariq', 'Jaber', 'tariq.jaber96@example.com', '0795274413', 'Ajloun', '', 'Customer'),
(97, 'Khaled', 'Nassar', 'khaled.nassar97@example.com', '0771301823', 'Jerash', '', 'Customer'),
(98, 'Tariq', 'Khoury', 'tariq.khoury98@example.com', '0795018381', 'Balqa', '', 'Customer'),
(99, 'Noor', 'Masri', 'noor.masri99@example.com', '0797989625', 'Zarqa', '', 'Customer'),
(100, 'Zaid', 'Sami', 'zaid.sami100@example.com', '0770115604', 'Aqaba', '', 'Customer'),
(101, 'dema', 'moh', 'a@g.com', '0777777777', 'amman', '1234', 'Customer'),
(102, 'kk', 'kk', 'kk@g.com', '00006543218', 'amman', '12wq', 'Customer'),
(103, 'حلا', 'محمد', 'hala@g.com', '0999999999', 'amman', '1qaz', 'Customer'),
(104, 'Momen', 'j', 'momenjafary@gmail.com', '0799002073', 'amman', 'Momen', 'Customer'),
(105, 'malak', 'malak', 'malak@g.com', '0799002073', 'amman', 'malak', 'Customer'),
(106, 'dema', 'dema', 'dema@g.com', '0000000000', 'amman', 'dema', 'Customer'),
(107, 's', 's', 's@g.com', '0000000000', 'amman', 's', 'Customer'),
(108, 'h', 'h', 'h@g.com', '0799002073', 'amman', 'h', 'Customer'),
(109, 'khaled', 'k', 'k@g.com', '0799002073', 'amman', 'k', 'Customer'),
(110, 'hadeel', 'sarah', 'sarah@g.com', '0799002073', 'amman', 'sarah', 'Customer'),
(111, 'hala', 'aljafary', 'halaaljafary@g.com', '0799002073', 'amman', 'hala', 'Admin'),
(112, 'hadeel', 'saleh', 'hadeel@g.com', '0799002073', 'amman', 'hadeel', 'Customer');

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `OrderID` varchar(20) NOT NULL,
  `FamilyID` int(11) DEFAULT NULL,
  `OrderDate` date DEFAULT NULL,
  `SalesChannel` varchar(50) DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `OrderDetails` text,
  `CustomerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`OrderID`, `FamilyID`, `OrderDate`, `SalesChannel`, `TotalAmount`, `OrderDetails`, `CustomerID`) VALUES
('ORD-1075', NULL, '2026-04-29', 'Online', '0.80', 'Popcorn 100g (x1)', 109),
('ORD-1526', NULL, '2026-04-29', 'Online', '4.50', 'hala (x3)', 109),
('ORD-1806', NULL, '2026-04-29', 'Online', '13.50', 'hala (x9)', 110),
('ORD-2179', NULL, '2026-04-29', 'Online', '0.90', 'Cola 1.5L (x1)', 107),
('ORD-3927', NULL, '2026-04-29', 'Online', '20.00', 'hadeel (x2)', 111),
('ORD-4452', NULL, '2026-04-29', 'Online', '4.50', 'Potato Chips 150g (x1), Orange Juice 1L (x1), Milk Chocolate 100g (x1)', 112),
('ORD-5558', NULL, '2026-04-29', 'Online', '3.50', 'Sunflower Oil 1.5L (x1)', 112),
('ORD-6456', NULL, '2026-04-29', 'Online', '3.00', 'hala (x2)', 107);

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `ProductID` varchar(10) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductEmoji` varchar(50) DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  `StockLevel` int(11) DEFAULT NULL,
  `WarehouseID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `ProductEmoji`, `Category`, `Price`, `StockLevel`, `WarehouseID`) VALUES
('22222', 'Apple', '🍎', 'Produce', '1.50', 49, 'W005'),
('P001', 'Milk 1L', '🥛', 'Dairy', '1.20', 72, 'W005'),
('P002', 'Cheddar Cheese 200g', '🧀', 'Dairy', '3.50', 159, 'W003'),
('P003', 'Yogurt 500g', '🥛', 'Dairy', '1.00', 58, 'W003'),
('P004', 'Butter 200g', '🧈', 'Dairy', '2.20', 18, 'W001'),
('P005', 'Eggs 30-pack', '🥚', 'Dairy', '4.50', 252, 'W005'),
('P006', 'Labneh 500g', '🥛', 'Dairy', '2.80', 63, 'W002'),
('P007', 'Sliced White Bread', '🍞', 'Bakery', '0.60', 491, 'W005'),
('P008', 'Whole Wheat Bread', '🥖', 'Bakery', '0.80', 287, 'W003'),
('P009', 'Croissant 4-pack', '🥐', 'Bakery', '2.00', 489, 'W001'),
('P010', 'Baguette', '🥖', 'Bakery', '0.90', 116, 'W002'),
('P011', 'Chocolate Cookies', '🍪', 'Bakery', '1.50', 128, 'W001'),
('P012', 'Instant Coffee 200g', '☕', 'Beverages', '5.50', 178, 'W002'),
('P013', 'Black Tea 100 bags', '🫖', 'Beverages', '3.20', 139, 'W001'),
('P014', 'Green Tea 50 bags', '🍵', 'Beverages', '2.50', 155, 'W001'),
('P015', 'Orange Juice 1L', '🍹', 'Beverages', '1.80', 128, 'W002'),
('P016', 'Cola 1.5L', '🍺', 'Beverages', '0.90', 46, 'W002'),
('P017', 'Bottled Water 1.5L', '💧', 'Beverages', '0.40', 400, 'W004'),
('P018', 'Extra Virgin Olive Oil 1L', '🫒', 'Pantry', '8.00', 331, 'W003'),
('P019', 'Sunflower Oil 1.5L', '🌻', 'Pantry', '3.50', 413, 'W001'),
('P020', 'White Sugar 1kg', '🧂', 'Pantry', '0.80', 448, 'W004'),
('P021', 'Table Salt 500g', '🧂', 'Pantry', '0.40', 347, 'W005'),
('P022', 'Basmati Rice 5kg', '🍚', 'Pantry', '6.50', 24, 'W003'),
('P023', 'Spaghetti 500g', '🍝', 'Pantry', '1.20', 484, 'W001'),
('P024', 'Tomato Paste 400g', '🍝', 'Pantry', '0.90', 197, 'W004'),
('P025', 'Canned Tuna 160g', '🐟', 'Pantry', '1.50', 158, 'W001'),
('P026', 'Flour 2kg', '🌾', 'Pantry', '1.20', 248, 'W005'),
('P027', 'Apples 1kg', '🍎 ', 'Produce', '1.50', 196, 'W002'),
('P028', 'Bananas 1kg', '🍌', 'Produce', '1.20', 252, 'W001'),
('P029', 'Oranges 1kg', '🍊', 'Produce', '1.00', 444, 'W003'),
('P030', 'Tomatoes 1kg', '🍅', 'Produce', '0.80', 407, 'W005'),
('P031', 'Potatoes 2kg', '🥔', 'Produce', '1.50', 454, 'W005'),
('P032', 'Onions 1kg', '🧅', 'Produce', '0.90', 173, 'W002'),
('P033', 'Cucumber 1kg', '🥒', 'Produce', '0.70', 31, 'W005'),
('P034', 'Lettuce 1 piece', '🥬', 'Produce', '0.50', 243, 'W005'),
('P035', 'Potato Chips 150g', '🍟', 'Snacks', '1.20', 193, 'W004'),
('P036', 'Mixed Nuts 200g', '🥜', 'Snacks', '4.00', 494, 'W004'),
('P037', 'Milk Chocolate 100g', '🍫', 'Snacks', '1.50', 272, 'W004'),
('P038', 'Popcorn 100g', '🍿', 'Snacks', '0.80', 124, 'W004'),
('P039', 'Whole Chicken 1kg', '🐔', 'Meat', '3.50', 250, 'W001'),
('P040', 'Minced Beef 500g', '🥩', 'Meat', '4.50', 34, 'W001'),
('P041', 'Chicken Breast 500g', '🍗', 'Meat', '3.80', 40, 'W001'),
('P042', 'Fresh Salmon 250g', '🐟', 'Meat', '7.50', 47, 'W005'),
('P043', 'Dishwashing Liquid 1L', '🧼', 'Cleaning', '1.80', 467, 'W002'),
('P044', 'Laundry Detergent 3kg', '🫧', 'Cleaning', '6.00', 249, 'W005'),
('P045', 'Glass Cleaner 500ml', '🫧', 'Cleaning', '1.50', 186, 'W005'),
('P046', 'Bleach 1L', '🧼', 'Cleaning', '1.20', 350, 'W001'),
('P047', 'Toilet Paper 12-roll', '🧻', 'Cleaning', '4.50', 280, 'W005'),
('P048', 'Shampoo 400ml', '🧼', 'Personal Care', '3.50', 348, 'W001'),
('P049', 'Body Wash 500ml', '🧽', 'Personal Care', '3.00', 91, 'W003'),
('P050', 'Toothpaste 75ml', '🪥', 'Personal Care', '2.00', 88, 'W005');

-- --------------------------------------------------------

--
-- بنية الجدول `warehouses`
--

CREATE TABLE `warehouses` (
  `WarehouseID` varchar(10) NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `AvailableSpace` int(11) DEFAULT NULL,
  `ManagerName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `warehouses`
--

INSERT INTO `warehouses` (`WarehouseID`, `Location`, `Capacity`, `AvailableSpace`, `ManagerName`) VALUES
('W001', 'Amman_Main', 5374, 113, 'Zaid'),
('W002', 'Zarqa_Branch', 8697, 108, 'Omar'),
('W003', 'Irbid_Branch', 7349, 100, 'Farah'),
('W004', 'Aqaba_Hub', 2263, 106, 'Sara'),
('W005', 'Amman_South', 8062, 118, 'Tariq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `WarehouseID` (`WarehouseID`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`WarehouseID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE CASCADE;

--
-- القيود للجدول `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`WarehouseID`) REFERENCES `warehouses` (`WarehouseID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
