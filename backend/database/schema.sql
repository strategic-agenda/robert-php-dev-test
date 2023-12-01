-- MySQL dump 10.13  Distrib 8.0.32, for Linux (x86_64)
--
-- Host: localhost    Database: robert
-- ------------------------------------------------------
-- Server version	8.0.35-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `translation_units`
--

DROP TABLE IF EXISTS `translation_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translation_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_text` text NOT NULL,
  `unit_type` enum('word','sentence','paragraph') NOT NULL DEFAULT 'word',
  `lang_code` varchar(10) NOT NULL DEFAULT 'ru',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translation_units`
--

LOCK TABLES `translation_units` WRITE;
/*!40000 ALTER TABLE `translation_units` DISABLE KEYS */;
INSERT INTO `translation_units` VALUES (1,'Chair','word','en','2023-12-01 11:33:20','2023-12-01 11:33:31'),(2,'Стол','word','ru','2023-12-01 11:33:48','2023-12-01 11:33:48'),(3,'On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même. L\'avantage du Lorem Ipsum sur un texte générique comme \'Du texte. Du texte. Du texte.\' est qu\'il possède une distribution de lettres plus ou moins normale, et en tout cas comparable avec celle du français standard. De nombreuses suites logicielles de mise en page ou éditeurs de sites Web ont fait du Lorem Ipsum leur faux texte par défaut, et une recherche pour \'Lorem Ipsum\' vous conduira vers de nombreux sites qui n\'en sont encore qu\'à leur phase de construction. Plusieurs versions sont apparues avec le temps, parfois par accident, souvent intentionnellement (histoire d\'y rajouter de petits clins d\'oeil, voire des phrases embarassantes).','paragraph','fr','2023-12-01 11:34:29','2023-12-01 11:34:29'),(4,'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.','sentence','fr','2023-12-01 11:35:16','2023-12-01 11:35:16'),(5,'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).','paragraph','en','2023-12-01 11:36:10','2023-12-01 11:36:10'),(6,'The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested.','sentence','en','2023-12-01 11:36:36','2023-12-01 11:36:36'),(7,'Unit','word','en','2023-12-01 11:36:46','2023-12-01 11:36:50');
/*!40000 ALTER TABLE `translation_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_history`
--

DROP TABLE IF EXISTS `unit_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `old_value` text NOT NULL,
  `new_value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `message` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_history`
--

LOCK TABLES `unit_history` WRITE;
/*!40000 ALTER TABLE `unit_history` DISABLE KEYS */;
INSERT INTO `unit_history` VALUES (5,1,'Table','Chair','2023-12-01 11:33:31','Translation Unit #31 property Text was changed from Table to Chair'),(6,7,'Unit.','Unit','2023-12-01 11:36:50','Translation Unit #37 property Text was changed from Unit. to Unit');
/*!40000 ALTER TABLE `unit_history` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-01 13:39:23
