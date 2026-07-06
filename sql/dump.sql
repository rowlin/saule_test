-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: saule_betting
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `balance_logs`
--

DROP TABLE IF EXISTS `balance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `currency` enum('EUR','USD','RUB') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `balance_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balance_logs`
--

LOCK TABLES `balance_logs` WRITE;
/*!40000 ALTER TABLE `balance_logs` DISABLE KEYS */;
INSERT INTO `balance_logs` VALUES (160,1,NULL,'bet_placed','EUR',-50.00,500.00,450.00,'Bet #50 on Duplicate Test Event','2026-07-04 23:02:20'),(161,1,NULL,'bet_placed','EUR',-100.00,500.00,400.00,'Bet #51 on Test Event','2026-07-04 23:02:20'),(162,1,NULL,'bet_won','EUR',250.00,400.00,650.00,'Bet #51 settled as won (Test Event)','2026-07-04 23:02:20'),(163,1,NULL,'bet_placed','EUR',-50.00,500.00,450.00,'Bet #52 on Lost Test Event','2026-07-04 23:02:20'),(164,1,NULL,'bet_lost','EUR',0.00,450.00,450.00,'Bet #52 settled as lost (Lost Test Event)','2026-07-04 23:02:20'),(165,1,NULL,'test_action','EUR',100.00,500.00,600.00,'Test note','2026-07-04 23:02:20'),(166,2,NULL,'balance_update','EUR',100.00,500.00,600.00,NULL,'2026-07-04 23:02:20'),(167,2,2,'balance_set','EUR',799.99,200.00,999.99,'Admin set balance (EUR converted to EUR)','2026-07-04 23:02:20'),(168,2,2,'balance_set','USD',231.48,0.00,231.48,'Admin set balance (USD converted to EUR)','2026-07-04 23:02:20'),(169,2,2,'balance_set','EUR',899.00,100.00,999.00,'Admin set balance (EUR converted to EUR)','2026-07-04 23:02:20');
/*!40000 ALTER TABLE `balance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `balances`
--

DROP TABLE IF EXISTS `balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `currency` enum('EUR','USD','RUB') NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_currency` (`user_id`,`currency`),
  CONSTRAINT `balances_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balances`
--

LOCK TABLES `balances` WRITE;
/*!40000 ALTER TABLE `balances` DISABLE KEYS */;
INSERT INTO `balances` VALUES (2,2,'EUR',0.00),(3,3,'EUR',750.00),(4,4,'EUR',92.59),(6,4,'RUB',100.00),(7,2,'USD',250.00),(8,4,'USD',100.00),(9,2,'RUB',0.00),(17,1,'EUR',500.00);
/*!40000 ALTER TABLE `balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bets`
--

DROP TABLE IF EXISTS `bets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `outcome` enum('team1_win','draw','team2_win') NOT NULL,
  `odds` decimal(10,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` enum('EUR','USD','RUB') NOT NULL DEFAULT 'EUR',
  `status` enum('pending','won','lost') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `settled_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bets`
--

LOCK TABLES `bets` WRITE;
/*!40000 ALTER TABLE `bets` DISABLE KEYS */;
INSERT INTO `bets` VALUES (49,1,'Barcelona - Real Madrid','team1_win',2.50,100.00,'EUR','pending','2026-07-04 23:02:19',NULL),(50,1,'Duplicate Test Event','team1_win',2.00,50.00,'EUR','pending','2026-07-04 23:02:20',NULL),(51,1,'Test Event','team1_win',2.50,100.00,'EUR','won','2026-07-04 23:02:20','2026-07-04 23:02:20'),(52,1,'Lost Test Event','draw',3.20,50.00,'EUR','lost','2026-07-04 23:02:20','2026-07-04 23:02:20');
/*!40000 ALTER TABLE `bets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency_rates`
--

DROP TABLE IF EXISTS `currency_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_currency` enum('EUR','USD','RUB') NOT NULL DEFAULT 'EUR',
  `to_currency` enum('EUR','USD','RUB') NOT NULL,
  `rate` decimal(15,6) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pair` (`from_currency`,`to_currency`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency_rates`
--

LOCK TABLES `currency_rates` WRITE;
/*!40000 ALTER TABLE `currency_rates` DISABLE KEYS */;
INSERT INTO `currency_rates` VALUES (1,'EUR','USD',1.080000,'2026-07-04 23:02:20'),(2,'EUR','RUB',98.500000,'2026-07-02 14:46:21'),(3,'USD','EUR',0.925926,'2026-07-04 23:02:20'),(4,'USD','RUB',91.203704,'2026-07-02 14:46:21'),(5,'RUB','EUR',0.010152,'2026-07-02 14:46:21'),(6,'RUB','USD',0.010964,'2026-07-02 14:46:21');
/*!40000 ALTER TABLE `currency_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_contacts`
--

DROP TABLE IF EXISTS `user_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('phone','email') NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_contacts`
--

LOCK TABLES `user_contacts` WRITE;
/*!40000 ALTER TABLE `user_contacts` DISABLE KEYS */;
INSERT INTO `user_contacts` VALUES (3,1,'phone','+0987654321'),(4,2,'email','admin@example.com'),(5,2,'phone','+1112223333'),(8,4,'email','bob@example.com'),(9,4,'phone','+499876543210'),(26,1,'phone','+71111111111');
/*!40000 ALTER TABLE `user_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `birth_date` date NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `default_currency` enum('EUR','USD','RUB') NOT NULL DEFAULT 'EUR',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'john_doe','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','John Doe','male','1990-05-15','123 Main St, City','active',0,'EUR','2026-07-02 14:46:21'),(2,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Admin User','male','1985-01-01','456 Admin Ave','active',1,'EUR','2026-07-02 14:46:21'),(3,'jane_smith','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Jane Smith','female','1995-08-20','789 Oak Ln, Town','active',0,'EUR','2026-07-02 14:46:21'),(4,'bob_wilson','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Bob Wilson','male','1988-12-03','321 Pine Rd, Village','active',0,'USD','2026-07-02 14:46:21');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-05 13:47:27
