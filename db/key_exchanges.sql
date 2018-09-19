-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2018 at 02:56 PM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mp_unlockvgo`
--

-- --------------------------------------------------------

--
-- Table structure for table `key_exchanges`
--

CREATE TABLE `key_exchanges` (
  `id` int(11) NOT NULL,
  `send_expresstrade` mediumtext,
  `receive_expresstrade` mediumtext,
  `send_steam` mediumtext,
  `receive_steam` mediumtext,
  `issuer_id` varchar(17) NOT NULL,
  `remaining_balance` decimal(13,2) NOT NULL,
  `send_steam_items` mediumtext,
  `receive_steam_items` mediumtext,
  `send_expresstrade_offer_id` varchar(20) DEFAULT NULL,
  `receive_expresstrade_offer_id` varchar(20) DEFAULT NULL,
  `send_steam_offer_id` varchar(20) DEFAULT NULL,
  `receive_steam_offer_id` varchar(20) DEFAULT NULL,
  `send_steam_informed` int(1) NOT NULL,
  `next_step_after_send_steam_informed` int(1) NOT NULL,
  `send_expresstrade_accepted` int(1) NOT NULL,
  `send_steam_accepted` int(1) NOT NULL,
  `steam_trade_url` varchar(2500) NOT NULL,
  `cancel` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `key_exchanges`
--
ALTER TABLE `key_exchanges`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `key_exchanges`
--
ALTER TABLE `key_exchanges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
