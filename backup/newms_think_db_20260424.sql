-- MySQL dump 10.13  Distrib 8.0.12, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: think
-- ------------------------------------------------------
-- Server version	8.0.12

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8mb4 ;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'normal',
  `brand_id` int(10) unsigned DEFAULT NULL,
  `constructor_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$/FBzYPmo262RyBbQomUT1ezNt9XZ1Nr3s6YkU4RhUpIfflkg9xYh2','super',NULL,NULL,'2026-04-21 17:37:22'),(2,'brand_demo','$2y$10$RCgLNvoW.cwpO6LKUIft0.kPW5oyZ1DeuJd5ZsQq.kqrI.9buW8W2','brand',1,NULL,'2026-04-22 19:46:34'),(3,'constructor_2','$2y$10$8x.ldWUkiHt56yGnuuxlqOwxYDFBIzLX2IsU9mzlreSmWXe0ikNSW','constructor',NULL,2,'2026-04-22 23:39:41');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand`
--

DROP TABLE IF EXISTS `brand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `brand` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT '',
  `description` text,
  `follower_count` int(11) NOT NULL DEFAULT '0',
  `total_authorizations` int(11) NOT NULL DEFAULT '0',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand`
--

LOCK TABLES `brand` WRITE;
/*!40000 ALTER TABLE `brand` DISABLE KEYS */;
INSERT INTO `brand` VALUES (1,'Brand A','','鍝佺墝鏂瑰悗鍙版紨绀鸿处鍙锋墍灞炲搧鐗岋紝褰撳墠鍙敤浜庝笂浼犲拰缁存姢鏈搧鐗屽墽鏈€?,1,0,'approved','2026-04-21 17:37:22'),(2,'Brand B','',NULL,0,0,'approved','2026-04-21 17:37:22'),(3,'Brand C','',NULL,0,0,'approved','2026-04-21 17:37:22');
/*!40000 ALTER TABLE `brand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,'鎭愭€?,2,'2026-04-21 17:37:22'),(2,'鎮枒',3,'2026-04-21 17:37:22'),(4,'鎯呮劅',9,'2026-04-21 17:37:22'),(5,'娆箰',12,'2026-04-21 17:37:22'),(6,'绉戝够',10,'2026-04-21 17:37:22'),(7,'鍎跨瀵嗗',1,'2026-04-23 03:19:25'),(8,'瀹炴櫙鎺ㄧ悊',4,'2026-04-23 03:19:25'),(9,'娌夋蹈婕旂粠',5,'2026-04-23 03:19:25'),(10,'瑙ｈ皽閫冭劚',6,'2026-04-23 03:19:25'),(11,'瑙掕壊鎵紨',7,'2026-04-23 03:19:25'),(12,'鏈哄叧瀵嗗',8,'2026-04-23 03:19:25'),(13,'鍙ら',11,'2026-04-23 03:19:25');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `construction_case`
--

DROP TABLE IF EXISTS `construction_case`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `construction_case` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `brand_name` varchar(191) NOT NULL DEFAULT '',
  `project_name` varchar(191) NOT NULL DEFAULT '',
  `phase` varchar(100) NOT NULL DEFAULT '',
  `cover` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `notes` longtext,
  `images` longtext,
  `videos` longtext,
  `reject_reason` text,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `construction_case`
--

LOCK TABLES `construction_case` WRITE;
/*!40000 ALTER TABLE `construction_case` DISABLE KEYS */;
INSERT INTO `construction_case` VALUES (1,2,'Brand A','??????','????','http://127.0.0.1:8090/uploads/banner-local-1.png','??????????','[\"???\",\"???\"]','[\"http:\\/\\/127.0.0.1:8090\\/uploads\\/banner-local-1.png\"]','[\"http:\\/\\/127.0.0.1:8090\\/uploads\\/demo.mp4\"]','','approved','2026-04-22 13:17:15'),(2,2,'Brand A','??????','????','http://127.0.0.1:8090/uploads/banner-local-1.png','??????????','[\"???\",\"???\"]','[\"http:\\/\\/127.0.0.1:8090\\/uploads\\/banner-local-1.png\"]','[\"http:\\/\\/127.0.0.1:8090\\/uploads\\/demo.mp4\"]','','approved','2026-04-22 13:17:31');
/*!40000 ALTER TABLE `construction_case` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `construction_permission`
--

DROP TABLE IF EXISTS `construction_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `construction_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_type` varchar(30) NOT NULL DEFAULT 'member',
  `brand_name` varchar(191) NOT NULL DEFAULT '',
  `company_name` varchar(191) NOT NULL DEFAULT '',
  `contact_name` varchar(100) NOT NULL DEFAULT '',
  `contact_phone` varchar(50) NOT NULL DEFAULT '',
  `reason` text,
  `description` text,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `review_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `construction_permission`
--

LOCK TABLES `construction_permission` WRITE;
/*!40000 ALTER TABLE `construction_permission` DISABLE KEYS */;
INSERT INTO `construction_permission` VALUES (1,2,'constructor','Brand A','Case Team','Tester','13800000000','??????',NULL,'pending',NULL,'2026-04-22 13:17:15'),(2,2,'constructor','Brand A','Case Team','Tester','13800000000','??????',NULL,'approved',NULL,'2026-04-22 13:17:30');
/*!40000 ALTER TABLE `construction_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_ad`
--

DROP TABLE IF EXISTS `home_ad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `home_ad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_ad`
--

LOCK TABLES `home_ad` WRITE;
/*!40000 ALTER TABLE `home_ad` DISABLE KEYS */;
INSERT INTO `home_ad` VALUES (1,'/uploads/69e8f54f0babd.jpg','market',1,'2026-04-22 19:25:12');
/*!40000 ALTER TABLE `home_ad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_banner`
--

DROP TABLE IF EXISTS `home_banner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `home_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_banner`
--

LOCK TABLES `home_banner` WRITE;
/*!40000 ALTER TABLE `home_banner` DISABLE KEYS */;
INSERT INTO `home_banner` VALUES (1,'/uploads/69e8f15b2ab97.jpg','script/1',1,'2026-04-22 19:25:12'),(2,'/uploads/69e8f16433509.jpg','brand/1',2,'2026-04-22 19:25:12'),(3,'/uploads/69e8f169009f5.jpg','2',0,'2026-04-23 00:03:55');
/*!40000 ALTER TABLE `home_banner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `market_listing`
--

DROP TABLE IF EXISTS `market_listing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `market_listing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `type` enum('buy','sell') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_featured` (`is_featured`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `market_listing`
--

LOCK TABLES `market_listing` WRITE;
/*!40000 ALTER TABLE `market_listing` DISABLE KEYS */;
INSERT INTO `market_listing` VALUES (1,'Sell Horror Script','sell',500.00,'approved',0,'2026-04-21 17:37:22'),(2,'Buy Mystery Script','buy',300.00,'pending',0,'2026-04-21 17:37:22'),(3,'Sell Detective Script','sell',800.00,'approved',0,'2026-04-21 17:37:22'),(4,'Buy Comedy Script','buy',200.00,'pending',0,'2026-04-21 17:37:22'),(5,'smoke','sell',1.00,'pending',0,'2026-04-22 21:04:13');
/*!40000 ALTER TABLE `market_listing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `script`
--

DROP TABLE IF EXISTS `script`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `script` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `brand_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `min_players` int(11) NOT NULL DEFAULT '2',
  `max_players` int(11) NOT NULL DEFAULT '8',
  `duration` int(11) NOT NULL DEFAULT '120',
  `cover_image` varchar(255) DEFAULT '',
  `description` text,
  `script_type` varchar(100) DEFAULT '',
  `horror_level` tinyint(3) unsigned DEFAULT '0',
  `difficulty` varchar(50) DEFAULT '',
  `room_size` varchar(50) DEFAULT '',
  `feature_tags` text,
  `area_size` int(10) unsigned DEFAULT '0',
  `room_count` varchar(20) DEFAULT '',
  `rotation_count` varchar(20) DEFAULT '',
  `npc_count` varchar(20) DEFAULT '',
  `corridor_count` varchar(20) DEFAULT '',
  `suitable_players` text,
  `auth_status` varchar(30) DEFAULT '',
  `auth_services` text,
  `authorized_cities` text,
  `auth_cities` text,
  `gallery_images` longtext,
  `video_url` varchar(255) DEFAULT '',
  `detail_content` longtext,
  `authorizer` varchar(100) DEFAULT '',
  `price_tier1` decimal(10,2) DEFAULT '0.00',
  `status` enum('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft',
  `view_count` int(11) NOT NULL DEFAULT '0',
  `like_count` int(11) NOT NULL DEFAULT '0',
  `collect_count` int(11) NOT NULL DEFAULT '0',
  `purchase_count` int(11) NOT NULL DEFAULT '0',
  `is_home_featured` tinyint(1) NOT NULL DEFAULT '0',
  `home_featured_sort` int(11) NOT NULL DEFAULT '0',
  `is_script_featured` tinyint(1) NOT NULL DEFAULT '0',
  `script_featured_sort` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_brand_id` (`brand_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `script`
--

LOCK TABLES `script` WRITE;
/*!40000 ALTER TABLE `script` DISABLE KEYS */;
INSERT INTO `script` VALUES (1,'闆ㄥ灞犲か',1,8,NULL,2,4,120,'/uploads/69e8f1f80a973.jpg','123','瀹炴櫙鎺ㄧ悊',2,'涓瓑闅惧害','涓瀷瀵嗗','[\"鍏ㄦ伅鎶曞奖\",\"姊版垬\",\"闊╁紡\",\"鐜勫够\",\"榄旀硶\",\"鏃犳崲瑁匼",\"瀵规姉\",\"鏈烘\",\"绉戝够\",\"鍙ら\",\"鏃ュ紡\",\"绌胯秺\",\"姝︿緺\",\"瑙掕壊鎵紨\",\"娆у紡\",\"娓\",\"鏈夊墽鎯匼"]',21,'2','3','2','2','[\"鎯呬荆绾︿細\",\"瀛︾敓缁勯槦\",\"鍥㈠缓鑱氫細\",\"寰亹浣撻獙\",\"绀剧墰鐜╁\",\"纭牳瑙ｈ皽\",\"鏂版墜鐜╁\"]','鍙巿鏉?,'[\"鍥剧墖璧勬枡\",\"瀹ｄ紶瑙嗛\",\"涓婚娴佺▼\",\"涓婚璇︽儏\",\"寮哄急鐢靛浘绾竆",\"娴锋姤\",\"闊抽璧勬枡\",\"骞抽潰鍥剧焊\",\"鏈哄叧娓呭崟\",\"搴楀憳鍩硅\"]','[\"涓婃捣\",\"娣卞湷\",\"鏉窞\"]','[\"涓婃捣\",\"鍖椾含\",\"骞垮窞\",\"娣卞湷\",\"鏉窞\",\"鎴愰兘\",\"閲嶅簡\",\"姝︽眽\",\"瑗垮畨\",\"鍗椾含\",\"鑻忓窞\",\"闀挎矙\",\"鑸熷北甯俓"]','[\"http:\\/\\/127.0.0.1:8090\\/uploads\\/69e8f1f80a973.jpg\",\"http:\\/\\/127.0.0.1:8090\\/uploads\\/69e8f229a22fb.jpg\",\"http:\\/\\/127.0.0.1:8090\\/uploads\\/69e8f22dce8dc.jpg\",\"\\/uploads\\/69e8f1f80a973.jpg\"]','/uploads/69e9a6e5187e5.mp4','123123123123123','11111',999.00,'approved',56,22,33,1111,1,0,0,0,'2026-04-21 17:37:22'),(2,'娴嬭瘯鍓ф湰1',1,1,NULL,3,6,150,'/uploads/69e8f1a647784.jpg','娴嬭瘯鍓ф湰1锛岃祫鏂欐鍦ㄥ畬鍠勪腑锛屽綋鍓嶅彲鏌ョ湅鍩虹淇℃伅銆佸浘鐗囧強鎺堟潈淇℃伅銆?,'鎭愭€?,0,'','','[]',0,'','','','','[]','寰呰ˉ鍏?,'[]','[]','[]','[\"\\/uploads\\/69e8f1a647784.jpg\",\"\\/uploads\\/69e8f1a647784.jpg\"]','','娴嬭瘯鍓ф湰1 璇︾粏浠嬬粛寰呰ˉ鍏咃紝鍚庣画灏嗚ˉ鍏呭畬鏁寸殑鍓ф儏璇存槑銆佺┖闂翠寒鐐逛笌鐜╂硶缁嗚妭銆?,'寰呰ˉ鍏?,0.00,'approved',3,0,0,0,1,0,0,0,'2026-04-21 17:37:22'),(3,'Detective Script 1',2,2,NULL,4,8,180,'/uploads/69ea5180a473c.jpg','Detective Script 1锛岃祫鏂欐鍦ㄥ畬鍠勪腑锛屽綋鍓嶅彲鏌ョ湅鍩虹淇℃伅銆佸浘鐗囧強鎺堟潈淇℃伅銆?,'鎮枒',0,'','','[]',0,'','','','','[]','寰呰ˉ鍏?,'[]','[]','[]','[\"\\/uploads\\/69ea5180a473c.jpg\"]','','Detective Script 1 璇︾粏浠嬬粛寰呰ˉ鍏咃紝鍚庣画灏嗚ˉ鍏呭畬鏁寸殑鍓ф儏璇存槑銆佺┖闂翠寒鐐逛笌鐜╂硶缁嗚妭銆?,'寰呰ˉ鍏?,0.00,'approved',2,0,20,44,1,0,0,0,'2026-04-21 17:37:22'),(4,'Drama Script 1',2,4,NULL,2,6,120,'/uploads/69ea5177840ca.jpg','','鎯呮劅',0,'','','[]',0,'','','','','[]','','[]','[]','[]','[\"\\/uploads\\/69ea5177840ca.jpg\"]','','','',0.00,'draft',0,0,124,555,1,0,0,0,'2026-04-21 17:37:22'),(5,'Comedy Script 1',3,5,NULL,4,10,90,'/uploads/69ea516d74612.jpg','Comedy Script 1锛岃祫鏂欐鍦ㄥ畬鍠勪腑锛屽綋鍓嶅彲鏌ョ湅鍩虹淇℃伅銆佸浘鐗囧強鎺堟潈淇℃伅銆?,'娆箰',0,'','','[]',0,'','','','','[]','寰呰ˉ鍏?,'[]','[]','[]','[\"\\/uploads\\/69ea516d74612.jpg\"]','','Comedy Script 1 璇︾粏浠嬬粛寰呰ˉ鍏咃紝鍚庣画灏嗚ˉ鍏呭畬鏁寸殑鍓ф儏璇存槑銆佺┖闂翠寒鐐逛笌鐜╂硶缁嗚妭銆?,'寰呰ˉ鍏?,0.00,'approved',0,0,0,0,1,0,0,0,'2026-04-21 17:37:22'),(6,'ceshi1',1,7,NULL,4,8,120,'/uploads/69e91fd529970.jpg','ceshi1锛岃祫鏂欐鍦ㄥ畬鍠勪腑锛屽綋鍓嶅彲鏌ョ湅鍩虹淇℃伅銆佸浘鐗囧強鎺堟潈淇℃伅銆?,'鍎跨瀵嗗',3,'','','[\"姊版垬\",\"瀵规姉\"]',120,'6','2','3','2','[\"鍥㈠缓鑱氫細\",\"绀剧墰鐜╁\"]','鍙巿鏉?,'[\"鍥剧墖璧勬枡\",\"涓婚璇︽儏\"]','[]','[]','[\"\\/uploads\\/69e91fd529970.jpg\",\"\\/uploads\\/69e91fd529970.jpg\"]','https://example.com/demo.mp4','ceshi1 璇︾粏浠嬬粛寰呰ˉ鍏咃紝鍚庣画灏嗚ˉ鍏呭畬鏁寸殑鍓ф儏璇存槑銆佺┖闂翠寒鐐逛笌鐜╂硶缁嗚妭銆?,'寰呰ˉ鍏?,6999.00,'approved',13,0,22,33,1,0,0,0,'2026-04-22 20:23:44');
/*!40000 ALTER TABLE `script` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `script_purchase_intent`
--

DROP TABLE IF EXISTS `script_purchase_intent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `script_purchase_intent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `script_id` int(10) unsigned NOT NULL,
  `brand_id` int(10) unsigned NOT NULL DEFAULT '0',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_script_id` (`script_id`),
  KEY `idx_brand_id` (`brand_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `script_purchase_intent`
--

LOCK TABLES `script_purchase_intent` WRITE;
/*!40000 ALTER TABLE `script_purchase_intent` DISABLE KEYS */;
/*!40000 ALTER TABLE `script_purchase_intent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(64) NOT NULL,
  `nickname` varchar(64) DEFAULT '',
  `avatar` varchar(255) DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`),
  KEY `idx_openid` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'smoke-user','Smoke User','','2026-04-22 21:04:11'),(2,'case-user','Case User','','2026-04-22 21:17:14');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-24  2:51:52
