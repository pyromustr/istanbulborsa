-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 08:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `istanbulborsa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_positions`
--

CREATE TABLE `admin_positions` (
  `id` int(11) NOT NULL,
  `asset_symbol` varchar(10) NOT NULL,
  `position_type` enum('Buy','Sell') NOT NULL,
  `percentage_of_balance` decimal(5,2) NOT NULL,
  `open_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `open_price` decimal(18,2) NOT NULL,
  `close_date` timestamp NULL DEFAULT NULL,
  `close_price` decimal(18,2) DEFAULT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_positions`
--

INSERT INTO `admin_positions` (`id`, `asset_symbol`, `position_type`, `percentage_of_balance`, `open_date`, `open_price`, `close_date`, `close_price`, `status`) VALUES
(1, 'AKBNK', 'Buy', 10.00, '2025-06-12 21:38:58', 20.00, '2025-06-12 20:38:58', 76.00, 'Closed'),
(2, 'AKBNK', 'Buy', 10.00, '2025-06-13 04:20:52', 25.00, '2025-06-13 04:20:00', 60.00, 'Closed');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `asset_symbol` varchar(10) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `current_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `price_24h_ago` decimal(18,2) NOT NULL DEFAULT 0.00,
  `market_cap` decimal(24,2) NOT NULL DEFAULT 0.00,
  `volume_24h` decimal(24,2) NOT NULL DEFAULT 0.00,
  `circulating_supply` decimal(24,8) NOT NULL DEFAULT 0.00000000,
  `all_time_high` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `asset_symbol`, `icon_class`, `current_price`, `price_24h_ago`, `market_cap`, `volume_24h`, `circulating_supply`, `all_time_high`, `created_at`, `updated_at`) VALUES
(6, 'Akbank', 'AKBNK', 'fas fa-building', 68.10, 68.20, 184184000000.00, 121101939.00, 5200000000.00000000, 42.56, '2025-05-19 10:45:02', '2025-07-01 21:45:21.268242'),
(7, 'Arçelik', 'ARCLK', 'fas fa-home', 128.40, 122.50, 55405000000.00, 3452990.00, 674500000.00000000, 95.30, '2025-05-19 10:45:02', '2025-07-01 21:45:21.752510'),
(8, 'BİM', 'BIMAS', 'fas fa-shopping-cart', 496.00, 494.75, 151932000000.00, 6293677.00, 607000000.00000000, 285.40, '2025-05-19 10:45:02', '2025-07-01 21:45:22.268165'),
(9, 'Emlak Konut', 'EKGYO', 'fas fa-home', 17.85, 18.00, 29812500000.00, 193368000.00, 3800000000.00000000, 12.45, '2025-05-19 10:45:02', '2025-07-01 21:45:22.768143'),
(10, 'Ereğli Demir', 'EREGL', 'fas fa-industry', 26.70, 26.66, 149380000000.00, 260445570.00, 3500000000.00000000, 52.35, '2025-05-19 10:45:02', '2025-07-01 21:45:23.252532'),
(11, 'Garanti BBVA', 'GARAN', 'fas fa-university', 136.90, 135.00, 180180000000.00, 34197090.00, 4200000000.00000000, 48.72, '2025-05-19 10:45:02', '2025-07-01 21:45:23.752537'),
(12, 'İş Bankası', 'ISCTR', 'fas fa-university', 13.70, 13.35, 115920000000.00, 760794970.00, 4500000000.00000000, 32.45, '2025-05-19 10:45:02', '2025-07-01 21:45:24.268149'),
(13, 'Koç Holding', 'KCHOL', 'fas fa-building', 157.10, 154.00, 305670000000.00, 43949627.00, 2536000000.00000000, 145.20, '2025-05-19 10:45:02', '2025-07-01 21:45:24.768149'),
(14, 'Sabancı Holding', 'SAHOL', 'fas fa-building', 55.15, 54.80, 112506000000.00, 680000000.00, 2041000000.00000000, 68.35, '2025-05-19 10:45:02', ''),
(15, 'Türk Hava Yolları', 'THYAO', 'fas fa-plane', 290.50, 283.50, 343282000000.00, 37385296.00, 1378800000.00000000, 266.40, '2025-05-19 10:45:02', '2025-07-01 21:45:25.752513'),
(16, 'Türk Telekom', 'TTKOM', 'fas fa-phone', 61.20, 58.85, 142312500000.00, 17733033.00, 4037000000.00000000, 42.80, '2025-05-19 10:45:02', '2025-07-01 21:45:26.252590'),
(17, 'Vakıfbank', 'VAKBN', 'fas fa-university', 26.66, 26.46, 119490000000.00, 46606543.00, 4200000000.00000000, 35.60, '2025-05-19 10:45:02', '2025-07-01 21:45:26.752550'),
(18, 'Yapı Kredi', 'YKBNK', 'fas fa-university', 22.80, 22.60, 95760000000.00, 650000000.00, 4200000000.00000000, 28.90, '2025-05-19 10:45:02', ''),
(19, 'Ziraat Bankası', 'ZRGYO', 'fas fa-university', 23.82, 24.32, 66150000000.00, 3467975.00, 4200000000.00000000, 20.40, '2025-05-19 10:45:02', '2025-07-01 21:45:27.752479'),
(20, 'Aselsan', 'ASELS', 'fas fa-microchip', 148.40, 150.80, 113000000000.00, 26946538.00, 2500000000.00000000, 55.30, '2025-05-19 10:45:02', '2025-07-01 21:45:28.268133'),
(21, 'Tofaş', 'TOASO', 'fas fa-car', 203.90, 196.20, 72160000000.00, 8961287.00, 400000000.00000000, 220.50, '2025-05-19 10:45:02', '2025-07-01 21:45:28.752560'),
(22, 'Ford Otosan', 'FROTO', 'fas fa-car', 90.15, 89.25, 180320000000.00, 16707792.00, 400000000.00000000, 550.20, '2025-05-19 10:45:02', '2025-07-01 21:45:29.252540'),
(23, 'Petkim', 'PETKM', 'fas fa-flask', 17.64, 17.21, 36500000000.00, 69184896.00, 2000000000.00000000, 22.80, '2025-05-19 10:45:02', '2025-07-01 21:45:29.752516'),
(24, 'Sisecam', 'SISE', 'fas fa-wine-glass', 36.00, 36.04, 71200000000.00, 39371371.00, 2000000000.00000000, 43.90, '2025-05-19 10:45:02', '2025-07-01 21:45:30.252546'),
(25, 'Türkiye Petrol', 'TUPRS', 'fas fa-gas-pump', 141.70, 140.00, 300500000000.00, 23128083.00, 2000000000.00000000, 185.40, '2025-05-19 10:45:02', '2025-07-01 21:45:30.752539'),
(26, 'Anadolu Efes', 'AEFES', 'fas fa-beer', 15.36, 14.97, 85600000000.00, 75289060.00, 2000000000.00000000, 52.60, '2025-05-19 10:45:02', '2025-07-01 21:45:31.253127'),
(27, 'Migros', 'MGROS', 'fas fa-shopping-basket', 499.50, 494.75, 170800000000.00, 1445755.00, 2000000000.00000000, 105.20, '2025-05-19 10:45:02', '2025-07-01 21:45:31.846302'),
(28, 'Pegasus', 'PGSUS', 'fas fa-plane', 257.75, 257.00, 130600000000.00, 12304736.00, 2000000000.00000000, 80.50, '2025-05-19 10:45:02', '2025-07-01 21:45:32.268164'),
(29, 'Sabanci', 'SAHOL', 'fas fa-building', 90.45, 89.65, 112506000000.00, 27766583.00, 2041000000.00000000, 68.35, '2025-05-19 10:45:02', '2025-07-01 21:45:25.252540'),
(30, 'Türkcell', 'TCELL', 'fas fa-mobile-alt', 98.50, 96.15, 91200000000.00, 22931237.00, 2000000000.00000000, 56.80, '2025-05-19 10:45:02', '2025-07-01 21:45:32.752503'),
(31, 'Ulker', 'ULKER', 'fas fa-cookie', 107.20, 105.60, 70800000000.00, 7176984.00, 2000000000.00000000, 43.60, '2025-05-19 10:45:02', '2025-07-01 21:45:33.252531'),
(32, 'Vestel', 'VESTL', 'fas fa-tv', 35.90, 34.32, 57500000000.00, 7713499.00, 2000000000.00000000, 35.40, '2025-05-19 10:45:02', '2025-07-01 21:45:33.752543'),
(33, 'Yapı Kredi', 'YKBNK', 'fas fa-university', 32.00, 31.70, 95760000000.00, 284641090.00, 4200000000.00000000, 28.90, '2025-05-19 10:45:02', '2025-07-01 21:45:27.252541'),
(34, 'Zorlu Enerji', 'ZOREN', 'fas fa-bolt', 3.09, 3.04, 30800000000.00, 58040861.00, 2000000000.00000000, 19.80, '2025-05-19 10:45:02', '2025-07-01 21:45:34.252525'),
(35, 'EUR/USD', 'EURUSD', 'fas fa-euro-sign', 1.18, 1.18, 0.00, 165701.00, 0.00000000, 1.60, '2025-05-19 10:46:41', '2025-07-01 21:45:34.752542'),
(36, 'GBP/USD', 'GBPUSD', 'fas fa-pound-sign', 1.37, 1.37, 0.00, 200810.00, 0.00000000, 2.12, '2025-05-19 10:46:41', '2025-07-01 21:45:35.252544'),
(37, 'USD/JPY', 'USDJPY', 'fas fa-yen-sign', 143.77, 144.02, 0.00, 283119.00, 0.00000000, 151.94, '2025-05-19 10:46:41', '2025-07-01 21:45:35.752561'),
(38, 'USD/CHF', 'USDCHF', 'fas fa-money-bill-wave', 0.79, 0.79, 0.00, 96265.00, 0.00000000, 1.80, '2025-05-19 10:46:41', '2025-07-01 21:45:36.252542'),
(39, 'AUD/USD', 'AUDUSD', 'fas fa-dollar-sign', 0.66, 0.66, 0.00, 70262.00, 0.00000000, 1.11, '2025-05-19 10:46:41', '2025-07-01 21:45:36.752525'),
(40, 'USD/CAD', 'USDCAD', 'fas fa-dollar-sign', 1.37, 1.36, 0.00, 119540.00, 0.00000000, 1.62, '2025-05-19 10:46:41', '2025-07-01 21:45:37.252526'),
(41, 'NZD/USD', 'NZDUSD', 'fas fa-dollar-sign', 0.61, 0.61, 0.00, 71929.00, 0.00000000, 0.88, '2025-05-19 10:46:41', '2025-07-01 21:45:37.752544'),
(42, 'EUR/GBP', 'EURGBP', 'fas fa-euro-sign', 0.86, 0.86, 0.00, 56906.00, 0.00000000, 0.98, '2025-05-19 10:46:41', '2025-07-01 21:45:38.252559'),
(43, 'EUR/JPY', 'EURJPY', 'fas fa-euro-sign', 169.31, 169.78, 0.00, 217443.00, 0.00000000, 169.00, '2025-05-19 10:46:41', '2025-07-01 21:45:38.752539'),
(44, 'GBP/JPY', 'GBPJPY', 'fas fa-pound-sign', 197.47, 197.81, 0.00, 266163.00, 0.00000000, 251.09, '2025-05-19 10:46:41', '2025-07-01 21:45:39.268120'),
(45, 'Gold', 'XAUUSD', 'fas fa-coins', 3337.75, 3303.15, 0.00, 392676.00, 0.00000000, 2074.60, '2025-05-19 10:48:09', '2025-07-01 21:45:39.752595'),
(46, 'Gümüş', 'XAGUSD', 'fas fa-coins', 36.13, 36.11, 0.00, 75422.00, 0.00000000, 49.45, '2025-05-19 10:48:09', '2025-07-01 21:45:40.252536'),
(47, 'Bayrak Ticaret', 'BAYRK', 'fas fa-flag', 22.26, 22.92, 15800000000.00, 6121923.00, 1000000000.00000000, 18.90, '2025-05-19 10:48:10', '2025-07-01 21:45:40.768120'),
(48, 'Bitcoin', 'BTCUSD', 'fab fa-bitcoin', 105946.10, 107167.32, 0.00, 192.14, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:41.252547'),
(49, 'Ethereum', 'ETHUSD', 'fab fa-ethereum', 2423.78, 2486.71, 0.00, 7963.71, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:41.752522'),
(50, 'Tether', 'USDTRY', 'fas fa-dollar-sign', 39.83, 39.81, 0.00, 31569.00, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:42.252562'),
(51, 'Solana', 'SOLUSD', 'fas fa-sun', 146.96, 154.92, 0.00, 68210.82, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:42.752541'),
(52, 'Cardano', 'ADAUSD', 'fas fa-heart', 0.54, 0.57, 0.00, 2629622.10, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:43.252526'),
(53, 'Ripple', 'XRPUSD', 'fas fa-water', 2.18, 2.24, 0.00, 2020554.50, 0.00000000, 0.00, '2025-06-12 19:32:49', '2025-07-01 21:45:43.752541');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asset_symbol` varchar(10) NOT NULL,
  `position_type` enum('Buy','Sell') NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `open_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `open_price` decimal(18,2) NOT NULL,
  `close_date` timestamp NULL DEFAULT NULL,
  `close_price` decimal(18,2) DEFAULT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `admin_position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `user_id`, `asset_symbol`, `position_type`, `amount`, `open_date`, `open_price`, `close_date`, `close_price`, `status`, `admin_position_id`) VALUES
(1, 1, 'AKBNK', 'Buy', 10.00, '2025-06-13 04:20:52', 25.00, '2025-06-13 04:20:00', 60.00, 'Closed', 2);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `type` enum('Buy','Sell','Transfer') NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `status` enum('Pending','Completed','Failed') NOT NULL DEFAULT 'Pending',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'Hakan', 'SARI', 'pyromusbtc@gmail.com', '05516402897', '123456', '2025-05-19 01:53:59'),
(2, 'emre', 'ikli', 'propfundvip@gmail.com', '05317395236', '1qw2er3ty', '2025-06-26 21:51:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_assets`
--

CREATE TABLE `user_assets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `asset_symbol` varchar(10) NOT NULL,
  `quantity` decimal(18,8) NOT NULL DEFAULT 0.00000000,
  `avg_buy_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `current_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `price_24h_ago` decimal(18,2) NOT NULL DEFAULT 0.00,
  `icon_class` varchar(50) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_wallets`
--

CREATE TABLE `user_wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `crypto_symbol` varchar(10) NOT NULL,
  `crypto_name` varchar(50) NOT NULL,
  `balance` decimal(18,8) NOT NULL DEFAULT 0.00000000,
  `usd_equivalent` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `crypto_symbol` varchar(10) DEFAULT NULL,
  `crypto_name` varchar(50) DEFAULT NULL,
  `transaction_type` enum('Deposit','Withdrawal') NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `usd_amount` decimal(18,2) NOT NULL,
  `crypto_address` varchar(255) DEFAULT NULL,
  `payment_method` enum('Bank Transfer','Crypto') NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Completed','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `crypto_symbol`, `crypto_name`, `transaction_type`, `amount`, `usd_amount`, `crypto_address`, `payment_method`, `bank_name`, `account_name`, `iban`, `reference_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'USDT', 'Tether', 'Deposit', 100.00000000, 100.00, '0x0000000000000', 'Crypto', NULL, NULL, NULL, NULL, 'Completed', '2025-06-12 20:34:37', '2025-06-12 20:35:13'),
(2, 1, 'USDT', 'Tether', 'Deposit', 100.00000000, 100.00, '0x0000000', 'Crypto', NULL, NULL, NULL, NULL, 'Pending', '2025-06-12 22:37:49', NULL),
(3, 1, 'USDT', 'Tether', 'Deposit', 100.00000000, 100.00, '0x0000', 'Crypto', NULL, NULL, NULL, NULL, 'Pending', '2025-06-12 22:38:11', NULL),
(4, 1, 'USDT', 'Tether', 'Deposit', 14.00000000, 14.00, NULL, 'Crypto', NULL, NULL, NULL, NULL, 'Completed', '2025-06-13 04:20:52', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_positions`
--
ALTER TABLE `admin_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_assets`
--
ALTER TABLE `user_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `user_wallets`
--
ALTER TABLE `user_wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_crypto` (`user_id`,`crypto_symbol`),
  ADD KEY `crypto_symbol` (`crypto_symbol`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `crypto_symbol` (`crypto_symbol`),
  ADD KEY `status` (`status`),
  ADD KEY `transaction_type` (`transaction_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_positions`
--
ALTER TABLE `admin_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_assets`
--
ALTER TABLE `user_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wallets`
--
ALTER TABLE `user_wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`);

--
-- Constraints for table `user_assets`
--
ALTER TABLE `user_assets`
  ADD CONSTRAINT `user_assets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_assets_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
