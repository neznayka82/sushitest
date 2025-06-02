-- MySQL dump 10.13  Distrib 8.0.31, for Win64 (x86_64)
--
-- Host: localhost    Database: sushi-test
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cat2fathers`
--

DROP TABLE IF EXISTS `cat2fathers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat2fathers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` bigint(20) unsigned NOT NULL,
  `father_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat2father_father_id_IDX` (`father_id`) USING BTREE,
  KEY `cat2fathers_cats_FK` (`cat_id`),
  CONSTRAINT `cat2fathers_cats_FK` FOREIGN KEY (`cat_id`) REFERENCES `cats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cat2fathers_cats_FK_1` FOREIGN KEY (`father_id`) REFERENCES `cats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat2fathers`
--

LOCK TABLES `cat2fathers` WRITE;
/*!40000 ALTER TABLE `cat2fathers` DISABLE KEYS */;
INSERT INTO `cat2fathers` VALUES (35,15,3),(36,15,4);
/*!40000 ALTER TABLE `cat2fathers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cats`
--

DROP TABLE IF EXISTS `cats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'кличка',
  `age` smallint(5) unsigned DEFAULT NULL COMMENT 'возраст',
  `mother_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ид матери',
  `gender` tinyint(3) unsigned DEFAULT NULL COMMENT 'пол',
  PRIMARY KEY (`id`),
  KEY `cats_gender_IDX` (`gender`) USING BTREE,
  KEY `cats_cats_FK` (`mother_id`),
  FULLTEXT KEY `cats_name_IDX` (`name`),
  CONSTRAINT `cats_cats_FK` FOREIGN KEY (`mother_id`) REFERENCES `cats` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cats`
--

LOCK TABLES `cats` WRITE;
/*!40000 ALTER TABLE `cats` DISABLE KEYS */;
INSERT INTO `cats` VALUES (3,'Лапочка',1,NULL,0),(4,'Лапочка 2',1,NULL,0),(15,'Первая',3,15,1),(16,'Первая',2,NULL,1);
/*!40000 ALTER TABLE `cats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sushi-test'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-02 11:07:58
