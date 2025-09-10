/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.0.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: raspadinha777
-- ------------------------------------------------------
-- Server version	12.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `admin_config`
--

DROP TABLE IF EXISTS `admin_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_config`
--

LOCK TABLES `admin_config` WRITE;
/*!40000 ALTER TABLE `admin_config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `admin_config` VALUES
(1,'win_probability','0.0','Probabilidade de vitória no jogo (0.0 a 1.0)','2025-07-21 05:55:49','2025-07-21 06:04:36'),
(2,'game_enabled','1','Status do jogo (1 = ativo, 0 = inativo)','2025-07-21 05:55:49','2025-07-21 05:55:49'),
(3,'min_bet','1.00','Valor mínimo de aposta','2025-07-21 06:04:36','2025-07-21 06:04:36'),
(4,'max_bet','100.00','Valor máximo de aposta','2025-07-21 06:04:36','2025-07-21 06:04:36');
/*!40000 ALTER TABLE `admin_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `admin_simulated_reports`
--

DROP TABLE IF EXISTS `admin_simulated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_simulated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `simulated_clicks` int(11) DEFAULT 0,
  `simulated_conversions` int(11) DEFAULT 0,
  `report_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_date` (`report_date`),
  KEY `affiliate_id` (`affiliate_id`),
  CONSTRAINT `admin_simulated_reports_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_simulated_reports`
--

LOCK TABLES `admin_simulated_reports` WRITE;
/*!40000 ALTER TABLE `admin_simulated_reports` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `admin_simulated_reports` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `affiliate_clicks`
--

DROP TABLE IF EXISTS `affiliate_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `affiliate_id` (`affiliate_id`),
  CONSTRAINT `affiliate_clicks_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_clicks`
--

LOCK TABLES `affiliate_clicks` WRITE;
/*!40000 ALTER TABLE `affiliate_clicks` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `affiliate_clicks` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `affiliate_conversions`
--

DROP TABLE IF EXISTS `affiliate_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_conversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `converted_user_id` int(11) NOT NULL,
  `conversion_type` enum('signup','deposit','sale') NOT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `converted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_aff_user_type` (`affiliate_id`,`converted_user_id`,`conversion_type`),
  UNIQUE KEY `uniq_aff_conv` (`affiliate_id`,`converted_user_id`,`conversion_type`),
  KEY `affiliate_id` (`affiliate_id`),
  KEY `affiliate_conversions_ibfk_2` (`converted_user_id`),
  CONSTRAINT `affiliate_conversions_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`),
  CONSTRAINT `affiliate_conversions_ibfk_2` FOREIGN KEY (`converted_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_conversions`
--

LOCK TABLES `affiliate_conversions` WRITE;
/*!40000 ALTER TABLE `affiliate_conversions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `affiliate_conversions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `affiliates`
--

DROP TABLE IF EXISTS `affiliates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `affiliate_code` varchar(255) NOT NULL,
  `cpa_commission_rate` decimal(5,2) DEFAULT 0.00,
  `revshare_commission_rate` decimal(5,2) DEFAULT 0.00,
  `cpa_commission_rate_admin` decimal(5,2) DEFAULT 0.00,
  `revshare_commission_rate_admin` decimal(5,2) DEFAULT 0.00,
  `allow_sub_affiliate_earnings` tinyint(1) DEFAULT 1,
  `fixed_commission_per_signup` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `affiliate_code` (`affiliate_code`),
  CONSTRAINT `affiliates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliates`
--

LOCK TABLES `affiliates` WRITE;
/*!40000 ALTER TABLE `affiliates` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `affiliates` VALUES
(27,1,'admin1',10.00,50.00,0.00,50.00,1,0.00,1,'2025-08-21 22:50:37','2025-08-21 22:52:17');
/*!40000 ALTER TABLE `affiliates` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `file_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT 0,
  `height` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `position` varchar(50) DEFAULT 'header',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `banners` VALUES
(2,'Banner Header Principal','Banner principal do cabeþalho','/assets/banner_header.jpg',150000,'image/jpeg',1200,300,1,'header',1,'2025-07-30 23:21:36','2025-07-30 23:21:36'),
(3,'Banner Sidebar PromoþÒo','Banner lateral promocional','/assets/banner_sidebar.jpg',80000,'image/jpeg',300,600,1,'sidebar',1,'2025-07-30 23:21:36','2025-07-30 23:21:36'),
(4,'Banner Footer Info','Banner informativo do rodapÚ','/assets/banner_footer.jpg',120000,'image/jpeg',1200,200,0,'footer',1,'2025-07-30 23:21:36','2025-07-30 23:21:36'),
(10,'a','','../uploads/banners/banner_68b2e43e9c634.php',231155,'application/octet-stream',0,0,1,'0',1,'2025-08-30 11:45:02','2025-08-30 11:45:02'),
(11,'a','','../uploads/banners/banner_68b2e4f55cb23.php',231140,'application/octet-stream',0,0,1,'0',1,'2025-08-30 11:48:05','2025-08-30 11:48:05');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bets`
--

DROP TABLE IF EXISTS `bets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `symbols` varchar(255) DEFAULT NULL,
  `win` tinyint(1) DEFAULT NULL,
  `prize` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bets`
--

LOCK TABLES `bets` WRITE;
/*!40000 ALTER TABLE `bets` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bets` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `referred_user_id` int(11) NOT NULL,
  `type` enum('CPA','RevShare') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `level` int(11) NOT NULL,
  `status` enum('pending','approved','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `payment_id` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `credited` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_commission_payment` (`payment_id`),
  KEY `affiliate_id` (`affiliate_id`),
  KEY `referred_user_id` (`referred_user_id`),
  CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`),
  CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commissions`
--

LOCK TABLES `commissions` WRITE;
/*!40000 ALTER TABLE `commissions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `commissions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `config` (
  `name` varchar(50) NOT NULL,
  `value` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `config` VALUES
('rtp','5');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(50) DEFAULT NULL,
  `valor` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracoes`
--

LOCK TABLES `configuracoes` WRITE;
/*!40000 ALTER TABLE `configuracoes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `configuracoes` VALUES
(1,'rtp','5'),
(2,'bspay_client_id','mattheusedwardo@agenciakg.com.br'),
(3,'bspay_client_secret','aeL7SABBk3Iy54DT7BQvOT6zap'),
(4,'bspay_webhook_url','https://raspada777.com/webhook_bspay_novo.php'),
(20,'chance_vitoria','1'),
(26,'bspay_base_url','https://api.xgateglobal.com'),
(223,'bspay_token_path','/auth/token'),
(224,'bspay_deposit_path','/deposit'),
(225,'bspay_tx_status_path','/deposit/status'),
(226,'bspay_deposit_company_currencies_path','/deposit/company/currencies'),
(227,'bspay_withdraw_path','/withdraw'),
(228,'bspay_withdraw_company_currencies_path','/withdraw/company/currencies'),
(229,'bspay_pix_keys_by_customer_path','/pix/customer/{customerId}/key'),
(230,'bspay_balance_path','/balance');
/*!40000 ALTER TABLE `configuracoes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `custom_prizes`
--

DROP TABLE IF EXISTS `custom_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_prizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_name` varchar(100) NOT NULL COMMENT 'Nome do prêmio (ex: 3 repetições)',
  `prize_value` decimal(10,2) NOT NULL COMMENT 'Valor do prêmio em reais',
  `occurrence_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Quantas vezes deve sair',
  `current_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Quantas vezes já saiu',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o prêmio está ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_custom_prizes_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_prizes`
--

LOCK TABLES `custom_prizes` WRITE;
/*!40000 ALTER TABLE `custom_prizes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `custom_prizes` VALUES
(1,'3 repetições de R$2',6.00,2,0,1,'2025-07-19 11:56:18','2025-07-19 11:56:18'),
(2,'3 repetições de R$5',15.00,1,0,1,'2025-07-19 11:56:18','2025-07-19 11:56:18'),
(3,'3 repetições de R$10',30.00,1,0,1,'2025-07-19 11:56:18','2025-07-19 11:56:18');
/*!40000 ALTER TABLE `custom_prizes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `depositos`
--

DROP TABLE IF EXISTS `depositos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `depositos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `metodo` varchar(50) DEFAULT 'pix',
  `codigo_transacao` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_criacao` (`data_criacao`),
  KEY `idx_depositos_status_data` (`status`,`data_criacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depositos`
--

LOCK TABLES `depositos` WRITE;
/*!40000 ALTER TABLE `depositos` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `depositos` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `external_id` varchar(255) DEFAULT NULL,
  `affiliate_id` int(11) DEFAULT NULL,
  `affiliate_user_id` int(11) DEFAULT NULL,
  `referrer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_external_id` (`external_id`),
  UNIQUE KEY `uq_payment_id` (`payment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `deposits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposits`
--

LOCK TABLES `deposits` WRITE;
/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `game_config`
--

DROP TABLE IF EXISTS `game_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_percentage` decimal(5,2) NOT NULL DEFAULT 50.00 COMMENT 'Porcentagem de pagamento (0-100%)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o sistema está ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_config`
--

LOCK TABLES `game_config` WRITE;
/*!40000 ALTER TABLE `game_config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `game_config` VALUES
(1,50.00,1,'2025-07-19 11:56:18','2025-07-19 11:56:18');
/*!40000 ALTER TABLE `game_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `game_history`
--

DROP TABLE IF EXISTS `game_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bet_amount` decimal(10,2) NOT NULL,
  `prize_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `symbols` text NOT NULL COMMENT 'Símbolos sorteados (JSON)',
  `won` tinyint(1) NOT NULL DEFAULT 0,
  `game_type` varchar(50) NOT NULL DEFAULT 'normal' COMMENT 'normal, influencer, custom_prize',
  `config_used` text DEFAULT NULL COMMENT 'Configuração usada no momento do jogo (JSON)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `idx_game_history_user_date` (`user_id`,`created_at`),
  CONSTRAINT `game_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_history`
--

LOCK TABLES `game_history` WRITE;
/*!40000 ALTER TABLE `game_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `game_history` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_settings`
--

LOCK TABLES `global_settings` WRITE;
/*!40000 ALTER TABLE `global_settings` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `global_settings` VALUES
(1,'default_revshare_rate','5.0','Taxa padrão de RevShare (%)','2025-07-13 20:28:17'),
(2,'min_payout_amount','10.00','Valor mínimo para saque (R$)','2025-07-13 20:28:17'),
(3,'max_payout_amount','5000.00','Valor máximo para saque (R$)','2025-07-13 20:28:17'),
(4,'affiliate_system_enabled','1','Sistema de afiliados ativo (1=Sim, 0=Não)','2025-07-13 20:28:17'),
(5,'auto_approve_payouts','0','Aprovar saques automaticamente (1=Sim, 0=Não)','2025-07-13 20:28:17'),
(6,'commission_delay_hours','0','Delay para liberar comissões (horas)','2025-07-21 06:39:10'),
(7,'max_affiliate_levels','4','Número máximo de níveis de afiliados','2025-07-13 20:28:17'),
(8,'level_2_percentage','20','Porcentagem do nível 2 (% da comissão do nível 1)','2025-07-13 20:28:17'),
(9,'level_3_percentage','10','Porcentagem do nível 3 (% da comissão do nível 1)','2025-07-13 20:28:17'),
(10,'level_4_percentage','5','Porcentagem do nível 4 (% da comissão do nível 1)','2025-07-13 20:28:17'),
(154,'min_deposit_amount','5.00','Valor mínimo para depósito global (R$)','2025-07-14 05:15:49'),
(155,'initial_bonus_amount','0','Valor do bônus inicial para novos usuários (R$)','2025-07-15 18:51:29'),
(156,'initial_bonus_enabled','1','Bônus inicial ativo (1=Sim, 0=Não)','2025-07-14 05:15:49');
/*!40000 ALTER TABLE `global_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `influencer_mode`
--

DROP TABLE IF EXISTS `influencer_mode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `influencer_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID do usuário influenciador',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o modo está ativo para este usuário',
  `win_percentage` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT 'Porcentagem de vitória (0-100%)',
  `prize_value` decimal(10,2) NOT NULL DEFAULT 10.00 COMMENT 'Valor do prêmio quando ganhar',
  `max_wins` int(11) DEFAULT NULL COMMENT 'Máximo de vitórias (NULL = ilimitado)',
  `current_wins` int(11) NOT NULL DEFAULT 0 COMMENT 'Vitórias atuais',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_influencer_mode_active` (`is_active`),
  CONSTRAINT `influencer_mode_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `influencer_mode`
--

LOCK TABLES `influencer_mode` WRITE;
/*!40000 ALTER TABLE `influencer_mode` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `influencer_mode` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jogadas`
--

DROP TABLE IF EXISTS `jogadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `simbolos` varchar(255) DEFAULT NULL,
  `ganhou` tinyint(1) DEFAULT NULL,
  `premio` decimal(10,2) DEFAULT NULL,
  `aposta` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogadas`
--

LOCK TABLES `jogadas` WRITE;
/*!40000 ALTER TABLE `jogadas` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `jogadas` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jogadas_raspadinha`
--

DROP TABLE IF EXISTS `jogadas_raspadinha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jogadas_raspadinha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `is_influencer` tinyint(1) NOT NULL DEFAULT 0,
  `tipo` varchar(100) DEFAULT NULL,
  `resultado` enum('ganhou','perdeu') NOT NULL,
  `premio` decimal(10,2) DEFAULT 0.00,
  `data_jogada` timestamp NULL DEFAULT current_timestamp(),
  `valor_aposta` decimal(10,2) NOT NULL DEFAULT 1.00,
  `status` enum('pending','finalized') NOT NULL DEFAULT 'pending',
  `token` varchar(64) DEFAULT NULL,
  `positions_json` text DEFAULT NULL,
  `winning_note` varchar(16) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `finalized_at` timestamp NULL DEFAULT NULL,
  `client_request_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_resultado` (`resultado`),
  KEY `idx_data_jogada` (`data_jogada`),
  KEY `idx_jogadas_resultado_data` (`resultado`,`data_jogada`),
  KEY `ix_jogadas_status_id` (`status`,`id`),
  KEY `ix_jogadas_usuario_status` (`usuario_id`,`status`),
  KEY `idx_jogadas_status` (`status`),
  KEY `idx_client_request_id` (`client_request_id`),
  KEY `idx_jogadas_influencer_status` (`is_influencer`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1640 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jogadas_raspadinha`
--

LOCK TABLES `jogadas_raspadinha` WRITE;
/*!40000 ALTER TABLE `jogadas_raspadinha` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `jogadas_raspadinha` VALUES
(201,1,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-22 14:46:26',1.00,'finalized','ebc26172627831a7f81717b0829b769a','[0,1,2]','10.00','2025-08-22 14:46:26','2025-08-22 14:46:27',NULL),
(202,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:28',1.00,'finalized','a7b0ef884d1c66f3c2f307b6d54cc4d2','[]',NULL,'2025-08-22 14:46:28','2025-08-22 14:46:28',NULL),
(203,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:29',1.00,'finalized','547934964f2b96d2641e615a557418be','[]',NULL,'2025-08-22 14:46:29','2025-08-22 14:46:29',NULL),
(204,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:30',1.00,'finalized','ecaf5a218aa69f8693a55deede817c21','[]',NULL,'2025-08-22 14:46:30','2025-08-22 14:46:30',NULL),
(205,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:30',1.00,'finalized','0ac266b78bd31f0351ae224e983480ef','[]',NULL,'2025-08-22 14:46:30','2025-08-22 14:46:30',NULL),
(206,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:31',1.00,'finalized','b088f23e869b4e219feba5b937678f26','[]',NULL,'2025-08-22 14:46:31','2025-08-22 14:46:31',NULL),
(207,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:32',1.00,'finalized','42d482a6a1de43655cf1c85d475c3778','[]',NULL,'2025-08-22 14:46:32','2025-08-22 14:46:32',NULL),
(208,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:46:33',1.00,'finalized','a6653d8ba994b9f7f31c2fcb2c4f029a','[]',NULL,'2025-08-22 14:46:33','2025-08-22 14:46:33',NULL),
(209,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:48:37',1.00,'finalized','cc1e924e31b93de278c20d27115e1033','[]',NULL,'2025-08-22 14:48:37','2025-08-22 14:48:37',NULL),
(210,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:48:38',1.00,'finalized','c374c646963ec727ca8ddf2db318d3c3','[]',NULL,'2025-08-22 14:48:38','2025-08-22 14:48:38',NULL),
(211,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:48:39',1.00,'finalized','e0bbd342c8b877d2380ce032296ed1cb','[]',NULL,'2025-08-22 14:48:39','2025-08-22 14:48:39',NULL),
(212,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:48:40',1.00,'finalized','dd0b320bdea91d47af438fc5908ba36b','[]',NULL,'2025-08-22 14:48:40','2025-08-22 14:48:40',NULL),
(213,1,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 14:48:41',1.00,'finalized','8e0a3b2bf98483aaf5e9190cf74f757d','[2,6,7]','5.00','2025-08-22 14:48:41','2025-08-22 14:48:41',NULL),
(214,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 14:48:43',1.00,'finalized','a69923ba76f9e732aec6237144692cf6','[]',NULL,'2025-08-22 14:48:43','2025-08-22 14:48:43',NULL),
(215,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 14:48:49',5.00,'finalized','acdcfce8ac53ae36fa5df5f913c57318','[0,3,4]','5.00','2025-08-22 14:48:49','2025-08-22 14:48:50',NULL),
(216,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:51',5.00,'finalized','c5d409c55bbf0b807f19e643045d8a12','[]',NULL,'2025-08-22 14:48:51','2025-08-22 14:48:52',NULL),
(217,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:53',5.00,'finalized','d0936b6acd5078647d59cf2d1bd6522f','[]',NULL,'2025-08-22 14:48:53','2025-08-22 14:48:53',NULL),
(218,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:53',5.00,'finalized','cd47a98c50abbe969076089d90af2b30','[]',NULL,'2025-08-22 14:48:53','2025-08-22 14:48:53',NULL),
(219,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:54',5.00,'finalized','65e1711bf2f5ff2fb983d868510a35f6','[]',NULL,'2025-08-22 14:48:54','2025-08-22 14:48:54',NULL),
(220,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:55',5.00,'finalized','492dd7b1dcdd7caf2c170efa9e082e0b','[]',NULL,'2025-08-22 14:48:55','2025-08-22 14:48:55',NULL),
(221,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 14:48:56',5.00,'finalized','c5aa5e9967d6ebb4c6c2af8e7d8789d0','[]',NULL,'2025-08-22 14:48:56','2025-08-22 14:48:56',NULL),
(222,1,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-22 14:48:56',5.00,'finalized','974b99aa1d02aa040c2903fd5363973e','[0,1,6]','20.00','2025-08-22 14:48:56','2025-08-22 14:48:57',NULL),
(223,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:22:55',2.00,'finalized','54dc908c707419fdd9a2aac7d872148b','[]',NULL,'2025-08-22 15:22:55','2025-08-22 15:22:55',NULL),
(224,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:29:34',5.00,'finalized','c2af58e584aa50b4fabaae3bb4b9b8cb','[]',NULL,'2025-08-22 15:29:34','2025-08-22 15:29:34',NULL),
(225,97,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 15:29:35',5.00,'finalized','28abe7756f3b6d1bb6bfc43c0859985e','[0,1,6]','10.00','2025-08-22 15:29:35','2025-08-22 15:29:36',NULL),
(226,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:42:28',2.00,'finalized','4e255ecac9d5c6403038a2366774a0c9','[]',NULL,'2025-08-22 15:42:28','2025-08-22 15:42:28',NULL),
(227,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:42:29',2.00,'finalized','f1620bd5eedf4242b8a693e28f750519','[]',NULL,'2025-08-22 15:42:29','2025-08-22 15:42:29',NULL),
(228,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:42:30',2.00,'finalized','ccc2dd12586bf6f81cd4771ecd7723dd','[]',NULL,'2025-08-22 15:42:30','2025-08-22 15:42:31',NULL),
(229,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:44:04',5.00,'finalized','1196e30032c383c5ba2218d4e297588e','[]',NULL,'2025-08-22 15:44:04','2025-08-22 15:44:04',NULL),
(230,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:44:05',5.00,'finalized','05ebb44d0d11f4369cc39ae1dc19bb71','[]',NULL,'2025-08-22 15:44:05','2025-08-22 15:44:05',NULL),
(231,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:44:05',5.00,'finalized','e11768919cd8833e81aaffb5828b2144','[]',NULL,'2025-08-22 15:44:05','2025-08-22 15:44:05',NULL),
(232,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:44:06',5.00,'finalized','1ecefe0e98ec48f37db1bb3a730edc2c','[]',NULL,'2025-08-22 15:44:06','2025-08-22 15:44:06',NULL),
(233,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:44:07',5.00,'finalized','56a1bca4d4e20858411bdf7b1faed59d','[]',NULL,'2025-08-22 15:44:07','2025-08-22 15:44:07',NULL),
(234,97,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 15:44:07',5.00,'finalized','3afc6b33537866f0169b72b90502d64f','[1,2,3]','10.00','2025-08-22 15:44:07','2025-08-22 15:44:08',NULL),
(235,97,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:46:25',5.00,'finalized','a88786334844e394e6eb1cbaa69bb363','[]',NULL,'2025-08-22 15:46:25','2025-08-22 15:46:26',NULL),
(236,97,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:46:27',5.00,'finalized','f367d645f135c500ab307e38191fba21','[]',NULL,'2025-08-22 15:46:27','2025-08-22 15:46:27',NULL),
(237,97,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:46:27',5.00,'finalized','fd38d9de55d26383da538408f127f1b9','[]',NULL,'2025-08-22 15:46:27','2025-08-22 15:46:27',NULL),
(238,97,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 15:46:28',5.00,'finalized','981862eff901c0b77c3d7eb4b10b6453','[]',NULL,'2025-08-22 15:46:28','2025-08-22 15:46:28',NULL),
(239,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:04',2.00,'finalized','c74216517052da9863d81aa971a54756','[]',NULL,'2025-08-22 15:48:04','2025-08-22 15:48:05',NULL),
(240,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:05',2.00,'finalized','1bc92ce86e09503579e29f5500decd35','[]',NULL,'2025-08-22 15:48:05','2025-08-22 15:48:05',NULL),
(241,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:06',2.00,'finalized','930405ccd4b16f81c59220c278d8386f','[]',NULL,'2025-08-22 15:48:06','2025-08-22 15:48:06',NULL),
(242,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:06',2.00,'finalized','e34a1ec8aa1804da8fbfd2a8d7836264','[]',NULL,'2025-08-22 15:48:06','2025-08-22 15:48:07',NULL),
(243,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:07',2.00,'finalized','d92a9fcdc19fb732b1e9d3509ce2de68','[]',NULL,'2025-08-22 15:48:07','2025-08-22 15:48:07',NULL),
(244,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:07',2.00,'finalized','c02d72536db0497570941e1e89bd4ec6','[]',NULL,'2025-08-22 15:48:07','2025-08-22 15:48:08',NULL),
(245,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:08',2.00,'finalized','7cb9a86ec77de648a8922fd8f511bdc7','[]',NULL,'2025-08-22 15:48:08','2025-08-22 15:48:08',NULL),
(246,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:09',2.00,'finalized','738d44b40c9dadd3e94b9f960a7aaae6','[]',NULL,'2025-08-22 15:48:09','2025-08-22 15:48:09',NULL),
(247,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:09',2.00,'finalized','7e1b43a59d9805fa1386f113c123cdd3','[]',NULL,'2025-08-22 15:48:09','2025-08-22 15:48:09',NULL),
(248,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:10',2.00,'finalized','1ce25e43bc1edc7022f6add541ac3488','[]',NULL,'2025-08-22 15:48:10','2025-08-22 15:48:10',NULL),
(249,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:10',2.00,'finalized','d45cbe7caa7e20e86cc2362e98afa068','[]',NULL,'2025-08-22 15:48:10','2025-08-22 15:48:11',NULL),
(250,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:11',2.00,'finalized','b30ecd20831cfa2cbf9450e1cf5187a1','[]',NULL,'2025-08-22 15:48:11','2025-08-22 15:48:11',NULL),
(251,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:12',2.00,'finalized','11d7056286e52e11e69da3e71a971b4e','[]',NULL,'2025-08-22 15:48:12','2025-08-22 15:48:12',NULL),
(252,97,1,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 15:48:12',2.00,'finalized','ac16e2109c29fcbed571524bc9fb39d9','[0,6,7]','2.00','2025-08-22 15:48:12','2025-08-22 15:48:13',NULL),
(253,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:48:53',2.00,'finalized','ea20c14be78a995a418e48db2b9f0ed8','[]',NULL,'2025-08-22 15:48:53','2025-08-22 15:48:53',NULL),
(254,97,1,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 15:49:09',2.00,'finalized','749d3a78d1c88ad22605a4f7c7cf1564','[3,4,7]','2.00','2025-08-22 15:49:09','2025-08-22 15:49:10',NULL),
(255,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:11',2.00,'finalized','b6977ce4146a262f438a0cef11163d5b','[]',NULL,'2025-08-22 15:49:11','2025-08-22 15:49:11',NULL),
(256,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:13',2.00,'finalized','701c6b604787b9d6c8db34e0b62738f7','[]',NULL,'2025-08-22 15:49:13','2025-08-22 15:49:13',NULL),
(257,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:13',2.00,'finalized','60e5d375d2f55d8d725b4c29e3daf3ed','[]',NULL,'2025-08-22 15:49:13','2025-08-22 15:49:13',NULL),
(258,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:14',2.00,'finalized','15148d7ae1d58232bd9b7e7a00c4c48d','[]',NULL,'2025-08-22 15:49:14','2025-08-22 15:49:14',NULL),
(259,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 15:49:31',2.00,'finalized','cc890369d737c2da47a5066c53646559','[3,7,8]','1.00','2025-08-22 15:49:31','2025-08-22 15:49:32',NULL),
(260,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:33',2.00,'finalized','bc0d8752dfd4b6e0357bc27212cc3622','[]',NULL,'2025-08-22 15:49:33','2025-08-22 15:49:33',NULL),
(261,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:49:34',2.00,'finalized','6296c7205ee9f5945b650be0aa4321a4','[]',NULL,'2025-08-22 15:49:34','2025-08-22 15:49:34',NULL),
(262,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:51:30',2.00,'finalized','f9c5ec8dda08136b444d130ad429a66b','[]',NULL,'2025-08-22 15:51:30','2025-08-22 15:51:30',NULL),
(263,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:55:15',2.00,'finalized','1ea9a11d1c8e4478c1b7163bb604c897','[]',NULL,'2025-08-22 15:55:15','2025-08-22 15:55:15',NULL),
(264,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 15:55:16',2.00,'finalized','9ac53363981e1452fc5ff6415d63064c','[]',NULL,'2025-08-22 15:55:16','2025-08-22 15:55:16',NULL),
(265,97,1,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 15:55:28',5.00,'finalized','8351d25ef969c1ef4e58111dad07d43a','[4,6,7]','50.00','2025-08-22 15:55:28','2025-08-22 15:55:29',NULL),
(266,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:55:30',5.00,'finalized','fd372cea687a96000e8fea38f63e52fa','[0,3,5]','200.00','2025-08-22 15:55:30','2025-08-22 15:55:31',NULL),
(267,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:55:32',5.00,'finalized','ba514c824f913f9bac7cf5e970eb024e','[1,2,8]','200.00','2025-08-22 15:55:32','2025-08-22 15:55:32',NULL),
(268,97,1,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 15:55:33',5.00,'finalized','c357947dc73b670ae42a7ca693b45937','[2,3,6]','5.00','2025-08-22 15:55:33','2025-08-22 15:55:34',NULL),
(269,97,1,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 15:55:56',5.00,'finalized','b83cdb76cfd85d3e8a3829f7ea10b395','[2,3,6]','50.00','2025-08-22 15:55:56','2025-08-22 15:55:57',NULL),
(270,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:55:58',5.00,'finalized','15fc11dbd7fba38d54d1771ab7578150','[1,2,8]','200.00','2025-08-22 15:55:58','2025-08-22 15:55:58',NULL),
(271,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:55:59',5.00,'finalized','eeec1ce45a29c868d3913dd5c6a6928b','[1,4,7]','200.00','2025-08-22 15:55:59','2025-08-22 15:56:00',NULL),
(272,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:56:00',5.00,'finalized','72d5e47c62cb45c38724ad370f9bc559','[1,6,8]','200.00','2025-08-22 15:56:00','2025-08-22 15:56:01',NULL),
(273,97,1,'Trono da Prada 👑','ganhou',200.00,'2025-08-22 15:57:20',5.00,'finalized','2a94b330ce69bd41a9762f717f8d0e8b','[4,6,8]','200.00','2025-08-22 15:57:20','2025-08-22 15:57:21',NULL),
(274,97,1,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 15:57:23',5.00,'finalized','78eb2ca3d19886dbda22112b09ff46ac','[0,6,7]','50.00','2025-08-22 15:57:23','2025-08-22 15:57:30',NULL),
(275,97,1,'Trono da Prada 👑','ganhou',100.00,'2025-08-22 15:57:32',5.00,'finalized','b571b621d675432dd1a5a4ac1e7eb47c','[0,1,2]','100.00','2025-08-22 15:57:32','2025-08-22 15:57:33',NULL),
(276,97,1,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 15:57:40',5.00,'finalized','8c13c9a34f337f6aa320a344650d3846','[3,7,8]','50.00','2025-08-22 15:57:40','2025-08-22 15:57:41',NULL),
(277,97,1,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 15:57:42',5.00,'finalized','25f5afa735f724565d66e8e1dfee3fea','[0,5,6]','50.00','2025-08-22 15:57:42','2025-08-22 15:57:42',NULL),
(278,97,1,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 15:57:43',5.00,'finalized','92db494589d583ea1fc6ed8ed1ecc438','[2,6,7]','10.00','2025-08-22 15:57:43','2025-08-22 15:57:44',NULL),
(279,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 15:57:59',10.00,'finalized','de6457d2b97187644042bcc94c0d6b4d','[]',NULL,'2025-08-22 15:57:59','2025-08-22 15:57:59',NULL),
(280,1,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-22 15:58:00',10.00,'finalized','0f304d21641ddc12bf7024ac4bf04d37','[0,1,3]','20.00','2025-08-22 15:58:00','2025-08-22 15:58:01',NULL),
(281,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 15:58:02',10.00,'finalized','414c010fc7f48d72f6bda2222d978b11','[]',NULL,'2025-08-22 15:58:02','2025-08-22 15:58:02',NULL),
(282,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 15:58:03',10.00,'finalized','8b8e2b8ddfaa6a3b7c78846c74f137c6','[]',NULL,'2025-08-22 15:58:03','2025-08-22 15:58:03',NULL),
(283,97,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-22 16:02:04',50.00,'finalized','f0135af794adc94ca4fe534cd08284fa','[1,3,7]','200.00','2025-08-22 16:02:04','2025-08-22 16:02:05',NULL),
(284,97,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-22 16:02:06',50.00,'finalized','ad5e9a800f0d6ea44f60a7476bd48287','[0,3,4]','200.00','2025-08-22 16:02:06','2025-08-22 16:02:06',NULL),
(285,97,1,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:02:07',50.00,'finalized','bb806ca1ed4d97f0082c58b8401ca091','[0,2,5]','50.00','2025-08-22 16:02:07','2025-08-22 16:02:08',NULL),
(286,97,1,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 16:02:09',50.00,'finalized','e437b77b2b72211e77afbdd417a91d00','[3,4,8]','100.00','2025-08-22 16:02:09','2025-08-22 16:02:09',NULL),
(287,97,1,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 16:02:10',50.00,'finalized','1d70835d4a4cf6bd89c63a13841b1060','[2,5,8]','100.00','2025-08-22 16:02:10','2025-08-22 16:02:11',NULL),
(288,97,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-22 16:02:12',50.00,'finalized','46bed0b0d6fef117a39895303315ee48','[0,4,6]','200.00','2025-08-22 16:02:12','2025-08-22 16:02:12',NULL),
(289,97,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-22 16:02:13',50.00,'finalized','bd18bfaf36a2573b544f7b7ca68b1fbe','[0,4,5]','200.00','2025-08-22 16:02:13','2025-08-22 16:02:14',NULL),
(290,99,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:02:58',2.00,'finalized','a6ad9b1f3fb5adc4856b629a0ce1e48a','[]',NULL,'2025-08-22 16:02:58','2025-08-22 16:02:58',NULL),
(291,99,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:03:02',2.00,'finalized','3eb48e249c7a38c9d4e6299ef672de60','[]',NULL,'2025-08-22 16:03:02','2025-08-22 16:03:02',NULL),
(292,99,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:03:05',2.00,'finalized','9589bc5d20dc77efef501c113676d05e','[]',NULL,'2025-08-22 16:03:05','2025-08-22 16:03:06',NULL),
(293,99,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:03:08',2.00,'finalized','586364c2c82a95402633e4c9c1fce76b','[]',NULL,'2025-08-22 16:03:08','2025-08-22 16:03:08',NULL),
(294,99,0,'Baú da sorte 🎁','ganhou',10.00,'2025-08-22 16:03:14',2.00,'finalized','5e9e3811f53313ccf78742b7970cd8f7','[3,4,6]','10.00','2025-08-22 16:03:14','2025-08-22 16:03:15',NULL),
(295,99,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:03:19',2.00,'finalized','c2dea37a47415ed0fd1daaff73fb3bd0','[]',NULL,'2025-08-22 16:03:19','2025-08-22 16:03:20',NULL),
(296,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:05:57',1.00,'finalized','b1980a1202d2e28f178026e405278632','[]',NULL,'2025-08-22 16:05:57','2025-08-22 16:05:57',NULL),
(297,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:02',1.00,'finalized','f885f38ff00de5fa13358f76d2986b12','[]',NULL,'2025-08-22 16:06:02','2025-08-22 16:06:02',NULL),
(298,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:03',1.00,'finalized','0393e1636d2592d4b3d7ae8375bf63ab','[]',NULL,'2025-08-22 16:06:03','2025-08-22 16:06:03',NULL),
(299,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:04',1.00,'finalized','89779d9e106cc41b1cce1815278c0827','[]',NULL,'2025-08-22 16:06:04','2025-08-22 16:06:05',NULL),
(300,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:06',1.00,'finalized','56ec4c2ced4bde42770fd37a49a54e6c','[]',NULL,'2025-08-22 16:06:06','2025-08-22 16:06:07',NULL),
(301,106,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 16:06:08',1.00,'finalized','9335da3926313bee15daed6daf08bf4a','[2,6,7]','5.00','2025-08-22 16:06:08','2025-08-22 16:06:09',NULL),
(302,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:10',1.00,'finalized','1b771436740045a29e64b4a0fa3498c1','[]',NULL,'2025-08-22 16:06:10','2025-08-22 16:06:11',NULL),
(303,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:12',1.00,'finalized','169f944b2240af71bd195fdb182f1ec0','[]',NULL,'2025-08-22 16:06:12','2025-08-22 16:06:12',NULL),
(304,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:13',1.00,'finalized','c879a487dec2d98560cd6998923847da','[]',NULL,'2025-08-22 16:06:13','2025-08-22 16:06:14',NULL),
(305,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:15',1.00,'finalized','3b98b604448cd50c344a3a5e714141da','[]',NULL,'2025-08-22 16:06:15','2025-08-22 16:06:15',NULL),
(306,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:16',1.00,'finalized','3fc012974e906ac45281b4652d172de1','[]',NULL,'2025-08-22 16:06:16','2025-08-22 16:06:17',NULL),
(307,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:18',1.00,'finalized','2918ce6828e3c730c6904b83398b8734','[]',NULL,'2025-08-22 16:06:18','2025-08-22 16:06:18',NULL),
(308,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:19',1.00,'finalized','13a2c91da0231cde3bd41478d95540c3','[]',NULL,'2025-08-22 16:06:19','2025-08-22 16:06:19',NULL),
(309,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:21',1.00,'finalized','2049a13fcf9f879d8470720e1136a894','[]',NULL,'2025-08-22 16:06:21','2025-08-22 16:06:21',NULL),
(310,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:22',1.00,'finalized','273431e6cd491664b398429f3c76bcd2','[]',NULL,'2025-08-22 16:06:22','2025-08-22 16:06:22',NULL),
(311,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:24',1.00,'finalized','fcbcf82c927b9423d6f674087726cc7a','[]',NULL,'2025-08-22 16:06:24','2025-08-22 16:06:25',NULL),
(312,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-22 16:06:25',50.00,'finalized','c26e1f7bc1548003e311f64c040d23dd','[1,2,3]','1000.00','2025-08-22 16:06:25','2025-08-22 16:06:26',NULL),
(313,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:25',1.00,'finalized','7d320e5b37a573a9551bf2429995b8ac','[]',NULL,'2025-08-22 16:06:25','2025-08-22 16:06:26',NULL),
(314,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-22 16:06:27',50.00,'finalized','ba1f7c9f1ca4505d2950487bf0a883b7','[0,2,3]','1000.00','2025-08-22 16:06:27','2025-08-22 16:06:27',NULL),
(315,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:27',1.00,'finalized','8f753eb6238862320649e42744648051','[]',NULL,'2025-08-22 16:06:27','2025-08-22 16:06:27',NULL),
(316,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-22 16:06:28',50.00,'finalized','6e86a8580f127fa0174aac50f66af9c7','[2,5,7]','1000.00','2025-08-22 16:06:28','2025-08-22 16:06:29',NULL),
(317,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:31',1.00,'finalized','ea658dbf85a076012a7d793dfdaf81bd','[]',NULL,'2025-08-22 16:06:31','2025-08-22 16:06:31',NULL),
(318,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:33',1.00,'finalized','59a374447f87564db34927a5b77aa19c','[]',NULL,'2025-08-22 16:06:33','2025-08-22 16:06:33',NULL),
(319,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:34',1.00,'finalized','9f497507f970f5aa8bfff57bbfadf919','[]',NULL,'2025-08-22 16:06:34','2025-08-22 16:06:34',NULL),
(320,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:37',1.00,'finalized','f66a6517488cc9c4262632eaa7e4d92b','[]',NULL,'2025-08-22 16:06:37','2025-08-22 16:06:37',NULL),
(321,106,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-22 16:06:38',1.00,'finalized','a16b7e649499033ef30f23e9d0f8864b','[0,3,6]','10.00','2025-08-22 16:06:38','2025-08-22 16:06:39',NULL),
(322,106,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 16:06:44',1.00,'finalized','260b8f6f8a09dfaaf616a16983b12eec','[0,6,8]','2.00','2025-08-22 16:06:44','2025-08-22 16:06:44',NULL),
(323,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:46',1.00,'finalized','a12d6df03581c67a0b3d30dad0d01af0','[]',NULL,'2025-08-22 16:06:46','2025-08-22 16:06:46',NULL),
(324,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:48',1.00,'finalized','2acc3d71e06bff549e585db3e7e3a0ff','[]',NULL,'2025-08-22 16:06:48','2025-08-22 16:06:48',NULL),
(325,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:49',1.00,'finalized','61388c1bf17b9f451f8335de544a808e','[]',NULL,'2025-08-22 16:06:49','2025-08-22 16:06:50',NULL),
(326,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:51',1.00,'finalized','bcd8511505969d9b7a07502c85438d71','[]',NULL,'2025-08-22 16:06:51','2025-08-22 16:06:51',NULL),
(327,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:52',1.00,'finalized','61cc6752033525e967e94fccc61de11e','[]',NULL,'2025-08-22 16:06:52','2025-08-22 16:06:52',NULL),
(328,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:53',1.00,'finalized','b6485fa53ab8699a5c7013919600f403','[]',NULL,'2025-08-22 16:06:53','2025-08-22 16:06:53',NULL),
(329,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:55',1.00,'finalized','8e3b58106df6421fee20eaa88ba87ed5','[]',NULL,'2025-08-22 16:06:55','2025-08-22 16:06:55',NULL),
(330,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:06:56',1.00,'finalized','7543f654e8a84519e77800db8142ce13','[]',NULL,'2025-08-22 16:06:56','2025-08-22 16:06:56',NULL),
(331,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:24',1.00,'finalized','e1a80540ba89d13ec715eb5eff24446b','[]',NULL,'2025-08-22 16:07:24','2025-08-22 16:07:24',NULL),
(332,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:25',1.00,'finalized','cfbcc8c2f866da78780863699c7fcd1d','[]',NULL,'2025-08-22 16:07:25','2025-08-22 16:07:26',NULL),
(333,106,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 16:07:27',1.00,'finalized','58b5f4e936a5b2de5d7f098b3724ba00','[3,4,6]','5.00','2025-08-22 16:07:27','2025-08-22 16:07:27',NULL),
(334,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:32',1.00,'finalized','f654579d525e60f765e854124563db15','[]',NULL,'2025-08-22 16:07:32','2025-08-22 16:07:32',NULL),
(335,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:33',1.00,'finalized','362718dfed675c96f85d08560db1348c','[]',NULL,'2025-08-22 16:07:33','2025-08-22 16:07:34',NULL),
(336,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:35',1.00,'finalized','996ba2019eeb8208afac4eb52264abf3','[]',NULL,'2025-08-22 16:07:35','2025-08-22 16:07:35',NULL),
(337,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:36',1.00,'finalized','7db74b0d04c52634807c96150f8adabc','[]',NULL,'2025-08-22 16:07:36','2025-08-22 16:07:36',NULL),
(338,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:38',1.00,'finalized','e713b5278021b62b73074130f2730f81','[]',NULL,'2025-08-22 16:07:38','2025-08-22 16:07:38',NULL),
(339,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:40',1.00,'finalized','ae5db9fa99f917a5e104b544ba6a489a','[]',NULL,'2025-08-22 16:07:40','2025-08-22 16:07:41',NULL),
(340,106,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:07:42',1.00,'finalized','4a6258e15d30647d91b08e3f1e9f0644','[]',NULL,'2025-08-22 16:07:42','2025-08-22 16:07:43',NULL),
(341,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:11:56',10.00,'finalized','1bfbdedb5720e79e86857c9d86d02ed4','[0,4,7]','5000.00','2025-08-22 16:11:56','2025-08-22 16:11:57',NULL),
(342,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:11:58',10.00,'finalized','735958f74e6b8bb13ab8f7481968d4c3','[0,2,6]','5000.00','2025-08-22 16:11:58','2025-08-22 16:11:59',NULL),
(343,97,1,'Tesouro lendário 🏆','ganhou',100.00,'2025-08-22 16:12:00',10.00,'finalized','d393578a33aafc0324eaae95d9c8ac6b','[0,1,6]','100.00','2025-08-22 16:12:00','2025-08-22 16:12:00',NULL),
(344,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:12:01',10.00,'finalized','ae6e41e21b46eade5ebd4b461656fa67','[0,6,7]','5000.00','2025-08-22 16:12:01','2025-08-22 16:12:02',NULL),
(345,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:12:03',10.00,'finalized','d58851dae61538c42fb971e477030e66','[0,5,7]','5000.00','2025-08-22 16:12:03','2025-08-22 16:12:03',NULL),
(346,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:12:04',10.00,'finalized','7961cdd8c68f9c5b402b1428d51f9451','[2,5,6]','5000.00','2025-08-22 16:12:04','2025-08-22 16:12:05',NULL),
(347,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:12:06',10.00,'finalized','7df019f704688c200a8fe74e6412f09c','[3,5,7]','5000.00','2025-08-22 16:12:06','2025-08-22 16:12:06',NULL),
(348,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-22 16:12:07',10.00,'finalized','647da4a02065547f452f3681d4f30125','[0,1,4]','5000.00','2025-08-22 16:12:07','2025-08-22 16:12:08',NULL),
(349,97,1,'Luxo Máximo 👑','ganhou',5000.00,'2025-08-22 16:12:16',50.00,'finalized','8c0c86aa824ae3a70c34eb3e836042c6','[1,2,3]','5000.00','2025-08-22 16:12:16','2025-08-22 16:12:17',NULL),
(350,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:12:31',50.00,'finalized','ced7aac7e3cfd93a8b0c88ab7283b6b9','[1,4,5]','50.00','2025-08-22 16:12:31','2025-08-22 16:12:32',NULL),
(351,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:12:33',50.00,'finalized','f0502e12d546176188030735ff2b476d','[]',NULL,'2025-08-22 16:12:33','2025-08-22 16:12:33',NULL),
(352,1,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 16:12:34',50.00,'finalized','28d0a8bd5b4f2c09683c6c501ad6efe1','[1,3,8]','100.00','2025-08-22 16:12:34','2025-08-22 16:12:35',NULL),
(353,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:12:36',50.00,'finalized','9de6b4f02c15ef7ad2e8198035a2f67f','[]',NULL,'2025-08-22 16:12:36','2025-08-22 16:12:36',NULL),
(354,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:12:37',50.00,'finalized','5ba5b6a5903466935dc1e06e3469b7a0','[0,3,6]','50.00','2025-08-22 16:12:37','2025-08-22 16:12:38',NULL),
(355,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:12:39',50.00,'finalized','159f793b5b7398d5becc33d15159adbe','[]',NULL,'2025-08-22 16:12:39','2025-08-22 16:12:39',NULL),
(356,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:12:40',50.00,'finalized','97ad855e2400d62d97d78b5b59cd8303','[0,1,2]','50.00','2025-08-22 16:12:40','2025-08-22 16:12:41',NULL),
(357,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:20',5.00,'finalized','66ebe184aab0d27c2faa9e35b2c28bc5','[]',NULL,'2025-08-22 16:19:20','2025-08-22 16:19:20',NULL),
(358,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:21',5.00,'finalized','6c4c98404665bd327472abd31d1194c7','[]',NULL,'2025-08-22 16:19:21','2025-08-22 16:19:21',NULL),
(359,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:22',5.00,'finalized','2f71fe93cdab44195c9a9ccb6f96c242','[]',NULL,'2025-08-22 16:19:22','2025-08-22 16:19:22',NULL),
(360,1,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-22 16:19:22',5.00,'finalized','1babda99edb43fc6abedae5ab5804f8d','[1,6,8]','20.00','2025-08-22 16:19:22','2025-08-22 16:19:23',NULL),
(361,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:24',5.00,'finalized','f1bb46064bf585f88ebc9b77457ad359','[]',NULL,'2025-08-22 16:19:24','2025-08-22 16:19:24',NULL),
(362,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:25',5.00,'finalized','190fb86fb2c93d9ef2748137a972aa40','[]',NULL,'2025-08-22 16:19:25','2025-08-22 16:19:25',NULL),
(363,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:25',5.00,'finalized','ed3db979c07fd30e7fc9575ad74bf5f6','[]',NULL,'2025-08-22 16:19:25','2025-08-22 16:19:26',NULL),
(364,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:26',5.00,'finalized','2b5ac9b6c3314acbbfdf251014ba6433','[]',NULL,'2025-08-22 16:19:26','2025-08-22 16:19:26',NULL),
(365,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:27',5.00,'finalized','9be540650a380c6057e8392e6f64f9e6','[]',NULL,'2025-08-22 16:19:27','2025-08-22 16:19:27',NULL),
(366,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:27',5.00,'finalized','5cc765eca6d6c577ae40bd8bda169ef5','[]',NULL,'2025-08-22 16:19:27','2025-08-22 16:19:27',NULL),
(367,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:28',5.00,'finalized','1bc1088c61be35b999b4a3918c68e0bd','[]',NULL,'2025-08-22 16:19:28','2025-08-22 16:19:28',NULL),
(368,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:19:29',5.00,'finalized','a64161e96b333c885a62ca279064b02d','[]',NULL,'2025-08-22 16:19:29','2025-08-22 16:19:29',NULL),
(369,1,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-22 16:19:29',5.00,'finalized','dfb0c672de075148007af30e301ef311','[2,3,4]','20.00','2025-08-22 16:19:29','2025-08-22 16:19:30',NULL),
(370,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:19:36',1.00,'finalized','d17847fe0bc2b77e79e11dca5d889a7b','[]',NULL,'2025-08-22 16:19:36','2025-08-22 16:19:36',NULL),
(371,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:19:37',1.00,'finalized','343c169ab2349022e7edbf52ee160afe','[]',NULL,'2025-08-22 16:19:37','2025-08-22 16:19:37',NULL),
(372,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:19:38',1.00,'finalized','10a02be298139b3a4ae333bcb6028608','[]',NULL,'2025-08-22 16:19:38','2025-08-22 16:19:38',NULL),
(373,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:19:39',1.00,'finalized','afdc133ae71299c48ab84f6460b26a7d','[]',NULL,'2025-08-22 16:19:39','2025-08-22 16:19:39',NULL),
(374,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:19:55',1.00,'finalized','632ce22e5abf5f562eca327832e6b7a6','[]',NULL,'2025-08-22 16:19:55','2025-08-22 16:19:55',NULL),
(375,1,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-22 16:19:59',1.00,'finalized','4ac2a5d5730f80404c785434b0f03c09','[5,6,7]','10.00','2025-08-22 16:19:59','2025-08-22 16:20:00',NULL),
(376,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:01',1.00,'finalized','70afc311934289309ef35ad5c093c6a5','[]',NULL,'2025-08-22 16:20:01','2025-08-22 16:20:01',NULL),
(377,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:02',1.00,'finalized','573c8c710e62556e8c6977c17d95f15e','[]',NULL,'2025-08-22 16:20:02','2025-08-22 16:20:02',NULL),
(378,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:03',1.00,'finalized','f5e99d2e0c6af6e8def07d4168675eeb','[]',NULL,'2025-08-22 16:20:03','2025-08-22 16:20:03',NULL),
(379,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:04',1.00,'finalized','1c41d0e1518cebbf59f3a8464494a75e','[]',NULL,'2025-08-22 16:20:04','2025-08-22 16:20:04',NULL),
(380,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:05',1.00,'finalized','7cbcba226a72c62d9ae2264f1b98e9a5','[]',NULL,'2025-08-22 16:20:05','2025-08-22 16:20:05',NULL),
(381,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:06',1.00,'finalized','daf61ecfaa8ce35651acba32707d5af9','[]',NULL,'2025-08-22 16:20:06','2025-08-22 16:20:06',NULL),
(382,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:06',1.00,'finalized','3323458bdb1948c1baec07641281f67d','[]',NULL,'2025-08-22 16:20:06','2025-08-22 16:20:06',NULL),
(383,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:07',1.00,'finalized','017e8cd2b98ab4aec6614a5e1daf09df','[]',NULL,'2025-08-22 16:20:07','2025-08-22 16:20:07',NULL),
(384,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:20:08',1.00,'finalized','ea030e5682d8f7eafc1cee5c903203cb','[]',NULL,'2025-08-22 16:20:08','2025-08-22 16:20:08',NULL),
(385,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:27:01',1.00,'finalized','85f346b06b29c23fff4c861d8b3cf9a5','[]',NULL,'2025-08-22 16:27:01','2025-08-22 16:27:01',NULL),
(386,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:27:02',1.00,'finalized','95c3db3c751310e48a968c5889964aa8','[]',NULL,'2025-08-22 16:27:02','2025-08-22 16:27:02',NULL),
(387,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 16:27:02',1.00,'finalized','1503413fb54ac844a8b1d30f89183193','[]',NULL,'2025-08-22 16:27:02','2025-08-22 16:27:03',NULL),
(388,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:22',1.00,'finalized','d17b3504a0cba55e62b271ac2e170fa4','[3,5,7]','0.50','2025-08-22 16:27:22','2025-08-22 16:27:22',NULL),
(389,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:23',1.00,'finalized','5e2e07d9275255076cdf9c5e6fe544eb','[3,4,5]','0.50','2025-08-22 16:27:23','2025-08-22 16:27:24',NULL),
(390,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:25',1.00,'finalized','918fccd75dba4d6bda399dba94ab7699','[0,5,7]','0.50','2025-08-22 16:27:25','2025-08-22 16:27:25',NULL),
(391,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:26',1.00,'finalized','121804e89b0647241d61e6c42688e3e8','[6,7,8]','0.50','2025-08-22 16:27:26','2025-08-22 16:27:27',NULL),
(392,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:28',1.00,'finalized','4a4525602e47d3888094b0939bc0df83','[3,4,5]','0.50','2025-08-22 16:27:28','2025-08-22 16:27:28',NULL),
(393,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 16:27:29',1.00,'finalized','29a4642ae5a86903732f2a2ead8b2cdb','[3,4,7]','1.00','2025-08-22 16:27:29','2025-08-22 16:27:30',NULL),
(394,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:31',1.00,'finalized','15a3cfcb6937eef11c895e0a9f0f25d0','[1,4,7]','0.50','2025-08-22 16:27:31','2025-08-22 16:27:31',NULL),
(395,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 16:27:32',1.00,'finalized','2334c822ee36c0eceaf322acbd2063a0','[6,7,8]','1.00','2025-08-22 16:27:32','2025-08-22 16:27:33',NULL),
(396,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 16:27:33',1.00,'finalized','c9dce46e8cc7a1c2cf8d23a0fc1f5411','[2,3,7]','1.00','2025-08-22 16:27:33','2025-08-22 16:27:34',NULL),
(397,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:35',1.00,'finalized','b40d2e31fc9cecd434c6c01235744b11','[6,7,8]','0.50','2025-08-22 16:27:35','2025-08-22 16:27:35',NULL),
(398,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:36',1.00,'finalized','3847a7cd9d00a8d09a1d689ea635b398','[0,1,5]','0.50','2025-08-22 16:27:36','2025-08-22 16:27:37',NULL),
(399,1,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 16:27:38',1.00,'finalized','b17bbe1403479d13a88a3d2c36188cb0','[4,5,8]','5.00','2025-08-22 16:27:38','2025-08-22 16:27:39',NULL),
(400,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 16:27:40',1.00,'finalized','1ec8144723777a55b51dd74885bb917a','[2,3,4]','0.50','2025-08-22 16:27:40','2025-08-22 16:27:40',NULL),
(401,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 16:27:41',1.00,'finalized','12c89d357369fcc627b0b691a6880a20','[0,1,7]','1.00','2025-08-22 16:27:41','2025-08-22 16:27:42',NULL),
(402,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 16:27:42',1.00,'finalized','5c39dcd2ad56859546a4f10d5bd74dd1','[2,3,5]','2.00','2025-08-22 16:27:42','2025-08-22 16:27:43',NULL),
(403,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 16:27:44',1.00,'finalized','9263fc809a181354d7f6029f117977c6','[2,3,5]','1.00','2025-08-22 16:27:44','2025-08-22 16:27:44',NULL),
(404,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:27:48',2.00,'finalized','4912bb3e5e0ee8d196b022030181ff14','[1,2,4]','2.00','2025-08-22 16:27:48','2025-08-22 16:27:49',NULL),
(405,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:27:50',2.00,'finalized','d8a8c453b596441f2865a283b7da2d5d','[1,5,7]','0.50','2025-08-22 16:27:50','2025-08-22 16:27:51',NULL),
(406,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:27:52',2.00,'finalized','f42d97beed0c537064591ddc23f410aa','[0,1,5]','2.00','2025-08-22 16:27:52','2025-08-22 16:27:52',NULL),
(407,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:27:54',2.00,'finalized','af0efdd837919a613c7874cd6df286f8','[4,5,8]','0.50','2025-08-22 16:27:54','2025-08-22 16:27:55',NULL),
(408,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:27:56',2.00,'finalized','6a299c4056e3b328906c1ca07439ed9c','[2,4,5]','0.50','2025-08-22 16:27:56','2025-08-22 16:27:57',NULL),
(409,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 16:29:31',2.00,'finalized','eeb02c89ad07ea77a54b23c39d4de738','[0,4,7]','1.00','2025-08-22 16:29:31','2025-08-22 16:29:31',NULL),
(410,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 16:31:25',2.00,'finalized','93ecbfffc33976383f6df1a1f666983e','[0,2,4]','1.00','2025-08-22 16:31:25','2025-08-22 16:31:26',NULL),
(411,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:31:27',2.00,'finalized','a25a18cd832776086276fbe775a89a52','[0,4,5]','0.50','2025-08-22 16:31:27','2025-08-22 16:31:27',NULL),
(412,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:32:26',2.00,'finalized','8d36e581f20e88f36ab6a8c243917dc6','[1,2,6]','5.00','2025-08-22 16:32:26','2025-08-22 16:32:27',NULL),
(413,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 16:32:27',2.00,'finalized','b894eacdc511b0eccccaa4c77f2e8a09','[2,4,5]','1.00','2025-08-22 16:32:27','2025-08-22 16:32:28',NULL),
(414,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:32:29',2.00,'finalized','97c207a4a48cbb7ee78261e106615598','[2,6,7]','5.00','2025-08-22 16:32:29','2025-08-22 16:32:29',NULL),
(415,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:32:30',2.00,'finalized','6e852959d9541eb586d7d6ec808e6ee0','[]',NULL,'2025-08-22 16:32:30','2025-08-22 16:32:30',NULL),
(416,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:32:31',2.00,'finalized','d4b7a4ba19876734b899e9c035f9b3ee','[2,3,6]','0.50','2025-08-22 16:32:31','2025-08-22 16:32:32',NULL),
(417,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:32:33',2.00,'finalized','37faf895c35548d9416652ec37a1e6de','[]',NULL,'2025-08-22 16:32:33','2025-08-22 16:32:33',NULL),
(418,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 16:32:34',2.00,'finalized','1e83c5bbe3e8b5dc5355e35747572ff0','[1,2,3]','0.50','2025-08-22 16:32:34','2025-08-22 16:32:35',NULL),
(419,1,0,'Baú da sorte 🎁','ganhou',10.00,'2025-08-22 16:32:36',2.00,'finalized','89881fb1990970efb4a6b88c2723c326','[3,5,6]','10.00','2025-08-22 16:32:36','2025-08-22 16:32:36',NULL),
(420,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:32:45',10.00,'finalized','edc61b258f9fb79d9b41c3538749087c','[]',NULL,'2025-08-22 16:32:45','2025-08-22 16:32:45',NULL),
(421,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 16:32:46',10.00,'finalized','97a38cd90cf7e3c7c986cc672302edc6','[3,6,8]','5.00','2025-08-22 16:32:46','2025-08-22 16:32:47',NULL),
(422,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 16:32:52',10.00,'finalized','42c541a0b29ab08c30c03b94b2c90648','[2,3,8]','5.00','2025-08-22 16:32:52','2025-08-22 16:32:53',NULL),
(423,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:32:53',10.00,'finalized','986673aa6c3c5cff31683c23740d87d6','[]',NULL,'2025-08-22 16:32:53','2025-08-22 16:32:54',NULL),
(424,1,0,'Tesouro lendário 🏆','ganhou',100.00,'2025-08-22 16:32:55',10.00,'finalized','39c658f0ea4b0e593d7dfd8c5c707ccf','[4,5,8]','100.00','2025-08-22 16:32:55','2025-08-22 16:32:58',NULL),
(425,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:01',10.00,'finalized','1818f1b0974817bcd787af443d1d21c3','[]',NULL,'2025-08-22 16:33:01','2025-08-22 16:33:01',NULL),
(426,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:02',10.00,'finalized','5a1bb486ce4de135620e69ea2522a20d','[]',NULL,'2025-08-22 16:33:02','2025-08-22 16:33:02',NULL),
(427,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:03',10.00,'finalized','5c1c3f81cf51202fb2be2711282d4287','[]',NULL,'2025-08-22 16:33:03','2025-08-22 16:33:03',NULL),
(428,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:03',10.00,'finalized','48fabea525d9cd9a55d2e3596e6872a9','[]',NULL,'2025-08-22 16:33:03','2025-08-22 16:33:04',NULL),
(429,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:04',10.00,'finalized','c3cb325356fe7a87c47422e71ddaa0be','[]',NULL,'2025-08-22 16:33:04','2025-08-22 16:33:04',NULL),
(430,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:05',10.00,'finalized','cafa3598298c01718e83d74f14853695','[]',NULL,'2025-08-22 16:33:05','2025-08-22 16:33:05',NULL),
(431,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:06',10.00,'finalized','cc7978e14e6a48250429afa0cc4eb18e','[]',NULL,'2025-08-22 16:33:06','2025-08-22 16:33:06',NULL),
(432,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:07',10.00,'finalized','dff04648065b74cb7812df216f485f1c','[]',NULL,'2025-08-22 16:33:07','2025-08-22 16:33:07',NULL),
(433,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 16:33:07',10.00,'finalized','540f353c95a5dffef3ec168ddd97b896','[]',NULL,'2025-08-22 16:33:07','2025-08-22 16:33:08',NULL),
(434,1,0,'Tesouro lendário 🏆','ganhou',50.00,'2025-08-22 16:33:08',10.00,'finalized','3af53b54486694d21823a324f924a99a','[2,6,7]','50.00','2025-08-22 16:33:08','2025-08-22 16:33:09',NULL),
(435,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:06',5.00,'finalized','390aa938adea7b0eda776d4cd5016233','[]',NULL,'2025-08-22 16:42:06','2025-08-22 16:42:07',NULL),
(436,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:08',5.00,'finalized','01ee8699ff94478d73a0d806bbff8f60','[]',NULL,'2025-08-22 16:42:08','2025-08-22 16:42:08',NULL),
(437,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:08',5.00,'finalized','0aa2bdf1a11db83cfc3f6fce2ea0b46e','[]',NULL,'2025-08-22 16:42:08','2025-08-22 16:42:09',NULL),
(438,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:09',5.00,'finalized','31f4474e19cd2e52badd8795d207c74f','[]',NULL,'2025-08-22 16:42:09','2025-08-22 16:42:09',NULL),
(439,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:10',5.00,'finalized','c850e87fd05b8251b52efe0d78f87f4a','[]',NULL,'2025-08-22 16:42:10','2025-08-22 16:42:10',NULL),
(440,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:11',5.00,'finalized','9dbf64be21e7a566626f1708b56b504e','[]',NULL,'2025-08-22 16:42:11','2025-08-22 16:42:11',NULL),
(441,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:11',5.00,'finalized','da3e2804490b3a1fabd1f9b3ba1f08f1','[]',NULL,'2025-08-22 16:42:11','2025-08-22 16:42:11',NULL),
(442,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:12',5.00,'finalized','5a2bb5334165206f0688e0e09e261728','[]',NULL,'2025-08-22 16:42:12','2025-08-22 16:42:12',NULL),
(443,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:12',5.00,'finalized','62c2b5c10a81e012b3437f1c57c5f8b0','[]',NULL,'2025-08-22 16:42:12','2025-08-22 16:42:13',NULL),
(444,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:13',5.00,'finalized','33a80a9b452b779d541c921f4fe7e9fd','[]',NULL,'2025-08-22 16:42:13','2025-08-22 16:42:13',NULL),
(445,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:14',5.00,'finalized','a83672ed530987152aa58efdfee3802f','[]',NULL,'2025-08-22 16:42:14','2025-08-22 16:42:14',NULL),
(446,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:14',5.00,'finalized','64d208ff06b69ea8c6cb8b0e00dd286a','[]',NULL,'2025-08-22 16:42:14','2025-08-22 16:42:15',NULL),
(447,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:17',5.00,'finalized','9a78a8c0cfa67757e6514c8abb47040e','[]',NULL,'2025-08-22 16:42:17','2025-08-22 16:42:17',NULL),
(448,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:18',5.00,'finalized','4bb7095bdc055e9a0256ee287bc73559','[]',NULL,'2025-08-22 16:42:18','2025-08-22 16:42:18',NULL),
(449,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:19',5.00,'finalized','f4818501802d1003310fbc1c14a02227','[]',NULL,'2025-08-22 16:42:19','2025-08-22 16:42:19',NULL),
(450,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:19',5.00,'finalized','dd92604e4fdfecd3b2f941afc2b5ce28','[]',NULL,'2025-08-22 16:42:19','2025-08-22 16:42:20',NULL),
(451,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:20',5.00,'finalized','75240c23ff752b104594bc540e12a5cb','[]',NULL,'2025-08-22 16:42:20','2025-08-22 16:42:20',NULL),
(452,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 16:42:21',5.00,'finalized','647d1dc8357bbad0d451d7ad129c0d1a','[]',NULL,'2025-08-22 16:42:21','2025-08-22 16:42:21',NULL),
(453,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:32',2.00,'finalized','f853c180111e8f879d5d3b5fb93541b6','[]',NULL,'2025-08-22 16:42:32','2025-08-22 16:42:32',NULL),
(454,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:42:33',2.00,'finalized','5fd294aadbe74869e149d6d6c83c41e0','[4,6,8]','5.00','2025-08-22 16:42:33','2025-08-22 16:42:34',NULL),
(455,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:35',2.00,'finalized','d44577f7bdebde42457486794adeffcc','[]',NULL,'2025-08-22 16:42:35','2025-08-22 16:42:35',NULL),
(456,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:36',2.00,'finalized','d51a7f2224eb274a26ef25f3d9044d57','[]',NULL,'2025-08-22 16:42:36','2025-08-22 16:42:36',NULL),
(457,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:36',2.00,'finalized','3f4f95cbf4c929d64cc4867667a25dbf','[]',NULL,'2025-08-22 16:42:36','2025-08-22 16:42:37',NULL),
(458,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:42:37',2.00,'finalized','80b60d52890d37a0cc730742c68041bd','[0,5,6]','5.00','2025-08-22 16:42:37','2025-08-22 16:42:38',NULL),
(459,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:39',2.00,'finalized','6ef88c5b273b683dc5d2e2a1540951ab','[]',NULL,'2025-08-22 16:42:39','2025-08-22 16:42:39',NULL),
(460,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:40',2.00,'finalized','c7d93f3fd59c3e5de751fb02d0ecb2ea','[]',NULL,'2025-08-22 16:42:40','2025-08-22 16:42:40',NULL),
(461,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:40',2.00,'finalized','d1e469990dae7f3fa43ff8a99e72feb1','[]',NULL,'2025-08-22 16:42:40','2025-08-22 16:42:41',NULL),
(462,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:41',2.00,'finalized','c4336e8c6d54eed9114066bce2c4b93e','[]',NULL,'2025-08-22 16:42:41','2025-08-22 16:42:41',NULL),
(463,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:42:42',2.00,'finalized','28569f9d6982ba781815d175c1b23da7','[0,5,8]','2.00','2025-08-22 16:42:42','2025-08-22 16:42:43',NULL),
(464,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:43',2.00,'finalized','1f21412db31a38a6d4933a3739c907ea','[]',NULL,'2025-08-22 16:42:43','2025-08-22 16:42:44',NULL),
(465,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:42:44',2.00,'finalized','3ae7599500b3680bff3d6b1f5554ef37','[0,1,3]','2.00','2025-08-22 16:42:44','2025-08-22 16:42:45',NULL),
(466,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:42:46',2.00,'finalized','498428f2099d8a290af7e9edfc66f8a8','[0,2,5]','5.00','2025-08-22 16:42:46','2025-08-22 16:42:46',NULL),
(467,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:47',2.00,'finalized','f557c1d7a4a2269e201624b943826afd','[]',NULL,'2025-08-22 16:42:47','2025-08-22 16:42:48',NULL),
(468,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:49',2.00,'finalized','6beedc5590d9759dec5d8dcf8e409d91','[]',NULL,'2025-08-22 16:42:49','2025-08-22 16:42:49',NULL),
(469,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:42:50',2.00,'finalized','8e04bfa6076dadda8a883ca93e3921f5','[]',NULL,'2025-08-22 16:42:50','2025-08-22 16:42:50',NULL),
(470,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:42:50',2.00,'finalized','a76f9613e77abad79d0bc0c89e62278a','[1,2,8]','2.00','2025-08-22 16:42:50','2025-08-22 16:42:51',NULL),
(471,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:45:45',2.00,'finalized','676272e610180a1edae2bad9a4e670d9','[]',NULL,'2025-08-22 16:45:45','2025-08-22 16:45:46',NULL),
(472,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:46:00',2.00,'finalized','91649fcf82024f91e84631b2d412635f','[0,4,7]','2.00','2025-08-22 16:46:00','2025-08-22 16:46:01',NULL),
(473,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 16:46:02',2.00,'finalized','c0faf782f440db045f326d48985abf68','[0,4,8]','2.00','2025-08-22 16:46:02','2025-08-22 16:46:02',NULL),
(474,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:03',2.00,'finalized','f9f2628028d51c21cd994dc41dd7ce02','[]',NULL,'2025-08-22 16:46:03','2025-08-22 16:46:03',NULL),
(475,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:04',2.00,'finalized','f1874c057b13b193b3a11d3177b0541d','[]',NULL,'2025-08-22 16:46:04','2025-08-22 16:46:04',NULL),
(476,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:05',2.00,'finalized','3ced24e262495d356113c80363fd6ded','[]',NULL,'2025-08-22 16:46:05','2025-08-22 16:46:05',NULL),
(477,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:06',2.00,'finalized','ede80d4812ce3983f63d7fd3091572e1','[]',NULL,'2025-08-22 16:46:06','2025-08-22 16:46:06',NULL),
(478,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 16:46:07',2.00,'finalized','1c425a073d519ea4b8b2f978f5702a48','[0,2,8]','1.00','2025-08-22 16:46:07','2025-08-22 16:46:08',NULL),
(479,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:46:08',2.00,'finalized','e9127c10b68677e24f44fd5882c09725','[0,3,4]','5.00','2025-08-22 16:46:08','2025-08-22 16:46:09',NULL),
(480,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 16:46:11',2.00,'finalized','104d8781715376234267179a5ae090a2','[2,6,7]','5.00','2025-08-22 16:46:11','2025-08-22 16:46:11',NULL),
(481,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:12',2.00,'finalized','4b607985f62acef7ba424387e13769a3','[]',NULL,'2025-08-22 16:46:12','2025-08-22 16:46:12',NULL),
(482,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:14',2.00,'finalized','7c1eb95b5bcc3daf1f60afc75aeb2212','[]',NULL,'2025-08-22 16:46:14','2025-08-22 16:46:14',NULL),
(483,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 16:46:15',2.00,'finalized','211608e2d0bf996a60cf20cd63709f61','[]',NULL,'2025-08-22 16:46:15','2025-08-22 16:46:15',NULL),
(484,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 16:46:16',2.00,'finalized','5223fd828fb5acd03cecc4f4ec1fb4f4','[1,2,6]','1.00','2025-08-22 16:46:16','2025-08-22 16:46:16',NULL),
(485,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:46:24',50.00,'finalized','d2ef01e89454efaf02ab29cf005736d4','[]',NULL,'2025-08-22 16:46:24','2025-08-22 16:46:24',NULL),
(486,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:46:25',50.00,'finalized','c804ca837ec4c64ff6f41661ed3209eb','[0,3,8]','50.00','2025-08-22 16:46:25','2025-08-22 16:46:25',NULL),
(487,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:46:26',50.00,'finalized','e31f53a5c870f1325a70aa7bc1b4f64c','[]',NULL,'2025-08-22 16:46:26','2025-08-22 16:46:26',NULL),
(488,1,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 16:46:27',50.00,'finalized','1ac64790d29a522d061930e6d99fbf3d','[0,5,7]','100.00','2025-08-22 16:46:27','2025-08-22 16:46:28',NULL),
(489,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:46:29',50.00,'finalized','a1ad1c7f3ca70d16232604826673d03e','[]',NULL,'2025-08-22 16:46:29','2025-08-22 16:46:29',NULL),
(490,1,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 16:46:30',50.00,'finalized','f79f25e0fe1d8e7138274b63c69a988a','[0,2,4]','20.00','2025-08-22 16:46:30','2025-08-22 16:46:30',NULL),
(491,1,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-22 16:47:05',50.00,'finalized','71d7905a9847572e40bf42acb0ddecd0','[1,6,8]','10.00','2025-08-22 16:47:05','2025-08-22 16:47:05',NULL),
(492,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 16:47:06',50.00,'finalized','4bfc31b31a145fae238d52ad459f9e18','[]',NULL,'2025-08-22 16:47:06','2025-08-22 16:47:10',NULL),
(493,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 16:47:12',50.00,'finalized','34170673dd1159044399b6755a96bdf5','[4,6,7]','50.00','2025-08-22 16:47:12','2025-08-22 16:47:13',NULL),
(494,1,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 16:47:43',50.00,'finalized','819639ca25f3444f81be297ea809ba20','[2,7,8]','20.00','2025-08-22 16:47:43','2025-08-22 16:47:44',NULL),
(495,107,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 17:20:36',2.00,'finalized','e948076556fe0685a1bc79cb7bbe35d1','[0,4,8]','1.00','2025-08-22 17:20:36','2025-08-22 17:20:37',NULL),
(496,107,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 17:20:40',2.00,'finalized','2c5994470d651209a5aacf4d787b12c3','[]',NULL,'2025-08-22 17:20:40','2025-08-22 17:20:41',NULL),
(497,107,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 17:20:44',2.00,'finalized','60d382a87ae29f741ccd699faf5eb822','[0,6,8]','1.00','2025-08-22 17:20:44','2025-08-22 17:20:45',NULL),
(498,107,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 17:20:48',2.00,'finalized','d2bdeb29ab9b274489030c259240db22','[2,3,5]','2.00','2025-08-22 17:20:48','2025-08-22 17:20:49',NULL),
(499,107,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 17:20:52',2.00,'finalized','fc9458170cc8e6fca8d125f8d38f3fd8','[0,2,3]','2.00','2025-08-22 17:20:52','2025-08-22 17:20:53',NULL),
(500,107,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 17:20:59',2.00,'finalized','9205442fb004d5354c806c80fbcc9b4c','[0,2,7]','2.00','2025-08-22 17:20:59','2025-08-22 17:21:00',NULL),
(501,107,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 17:21:09',2.00,'finalized','a14bdbe9b3a92c5ab5edabda526c85b2','[]',NULL,'2025-08-22 17:21:09','2025-08-22 17:21:10',NULL),
(502,107,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 17:21:14',2.00,'finalized','9703684910baad14f29f09a1601111b5','[4,5,6]','1.00','2025-08-22 17:21:14','2025-08-22 17:21:15',NULL),
(503,107,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 17:21:19',2.00,'finalized','f6a74483fd97b69c99c36a2a06c828a0','[3,4,5]','2.00','2025-08-22 17:21:19','2025-08-22 17:21:20',NULL),
(504,107,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 17:21:24',2.00,'finalized','b5e1c708d6ae54c37136b464c7d614eb','[]',NULL,'2025-08-22 17:21:24','2025-08-22 17:21:24',NULL),
(505,107,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:21:52',1.00,'finalized','11fca06664de69a6577b833316e21b04','[]',NULL,'2025-08-22 17:21:52','2025-08-22 17:21:53',NULL),
(506,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:27:35',50.00,'finalized','0d30c42154a3256ecfdf7322e3475d36','[0,3,5]','5.00','2025-08-22 17:27:35','2025-08-22 17:27:35',NULL),
(507,107,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-22 17:27:42',50.00,'finalized','9953ce113a56d6bfd5a56774affacb05','[4,5,7]','10.00','2025-08-22 17:27:42','2025-08-22 17:27:43',NULL),
(508,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:27:47',50.00,'finalized','48c803f60762b15e66b684ab493b1bfc','[]',NULL,'2025-08-22 17:27:47','2025-08-22 17:27:48',NULL),
(509,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:27:50',50.00,'finalized','c9b9ef4ff0088e7e7a1a2a09c5cb0b42','[2,3,8]','50.00','2025-08-22 17:27:50','2025-08-22 17:27:51',NULL),
(510,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:27:55',50.00,'finalized','a78cd140a1b2ca6b0bf18ddc055fe863','[0,2,6]','100.00','2025-08-22 17:27:55','2025-08-22 17:27:56',NULL),
(511,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:28:02',50.00,'finalized','65ba5beacdf52110a9187d170b855b33','[0,3,5]','50.00','2025-08-22 17:28:02','2025-08-22 17:28:02',NULL),
(512,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:28:06',50.00,'finalized','1b33fbad665228032878fa4523ecab46','[]',NULL,'2025-08-22 17:28:06','2025-08-22 17:28:06',NULL),
(513,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:28:09',50.00,'finalized','1e07232f095b451c04c949c1d4dcb0ea','[]',NULL,'2025-08-22 17:28:09','2025-08-22 17:28:09',NULL),
(514,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:28:12',50.00,'finalized','15862dd346e8d30941c675fb6e959b02','[5,7,8]','20.00','2025-08-22 17:28:12','2025-08-22 17:28:13',NULL),
(515,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:28:16',50.00,'finalized','4ee3e3466d113695a56c06a642e65d52','[]',NULL,'2025-08-22 17:28:16','2025-08-22 17:28:16',NULL),
(516,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:28:20',50.00,'finalized','40cdcb9f9688aba2c9a93dffa3ed96a7','[2,5,6]','50.00','2025-08-22 17:28:20','2025-08-22 17:28:21',NULL),
(517,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:28:25',50.00,'finalized','3394828dff27c9f3299ebfe558802d55','[2,7,8]','50.00','2025-08-22 17:28:25','2025-08-22 17:28:26',NULL),
(518,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:28:37',50.00,'finalized','f72bdde7d0d37e7be25c716c7fd65fc5','[3,5,8]','50.00','2025-08-22 17:28:37','2025-08-22 17:28:38',NULL),
(519,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:28:41',50.00,'finalized','3e658b122d3a14504f91e4888a5b56da','[]',NULL,'2025-08-22 17:28:41','2025-08-22 17:28:41',NULL),
(520,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:28:44',50.00,'finalized','649d2a7a9e4b537f6581b8e5392745dc','[2,3,6]','50.00','2025-08-22 17:28:44','2025-08-22 17:28:45',NULL),
(521,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:28:48',50.00,'finalized','228f60e44360fec44e603272fc443283','[0,3,4]','20.00','2025-08-22 17:28:48','2025-08-22 17:28:49',NULL),
(522,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:28:52',50.00,'finalized','1a636ff47307a1f226ec493b885c259e','[1,6,8]','5.00','2025-08-22 17:28:52','2025-08-22 17:28:53',NULL),
(523,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:29:02',50.00,'finalized','9747a01537df9dd3bc4f777a7f68c70e','[1,7,8]','5.00','2025-08-22 17:29:02','2025-08-22 17:29:03',NULL),
(524,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:29:06',50.00,'finalized','ee9a2505a4d4c4ae48267f44907e3757','[]',NULL,'2025-08-22 17:29:06','2025-08-22 17:29:06',NULL),
(525,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:29:10',50.00,'finalized','6c72c714109bb7e5e18ef3a033293a62','[0,3,8]','100.00','2025-08-22 17:29:10','2025-08-22 17:29:10',NULL),
(526,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:29:13',50.00,'finalized','72dc1ab6267a6f2d1cab4e1d15ad3605','[]',NULL,'2025-08-22 17:29:13','2025-08-22 17:29:14',NULL),
(527,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:29:16',50.00,'finalized','b306ac9f2f235297d6f18a9516ec3be4','[]',NULL,'2025-08-22 17:29:16','2025-08-22 17:29:16',NULL),
(528,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:29:19',50.00,'finalized','c086166f3cfc2e20516aed0a1b7087e2','[0,1,3]','50.00','2025-08-22 17:29:19','2025-08-22 17:29:20',NULL),
(529,107,0,'Luxo Máximo 👑','ganhou',1.00,'2025-08-22 17:29:23',50.00,'finalized','da7a3e395635e3f69c5c81bbd5e9a688','[0,4,6]','1.00','2025-08-22 17:29:23','2025-08-22 17:29:24',NULL),
(530,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:29:27',50.00,'finalized','aea04e9eac9d029c4b6b7c2a5d63b5fa','[2,4,7]','100.00','2025-08-22 17:29:27','2025-08-22 17:29:27',NULL),
(531,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:29:31',50.00,'finalized','3cd08deb198a852d6fa178ef36143076','[2,5,7]','20.00','2025-08-22 17:29:31','2025-08-22 17:29:32',NULL),
(532,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:29:41',50.00,'finalized','6e8666ce39f12b6bf2607645c75ab215','[0,1,3]','50.00','2025-08-22 17:29:41','2025-08-22 17:29:42',NULL),
(533,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:29:45',50.00,'finalized','d63bb506b0e0b376f16ba9ed0832708f','[3,7,8]','100.00','2025-08-22 17:29:45','2025-08-22 17:29:46',NULL),
(534,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:30:00',50.00,'finalized','0fe12de96e2a4fcb8a59eb10a256e6d2','[2,3,8]','100.00','2025-08-22 17:30:00','2025-08-22 17:30:01',NULL),
(535,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:04',50.00,'finalized','cfed3ba69288d6570d8632202cbf8a41','[]',NULL,'2025-08-22 17:30:04','2025-08-22 17:30:04',NULL),
(536,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:07',50.00,'finalized','8292ed0a65fe80fbee1892e0535f5de8','[]',NULL,'2025-08-22 17:30:07','2025-08-22 17:30:07',NULL),
(537,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:30:09',50.00,'finalized','850704cce7ec7adb483fe5609b69b055','[0,5,6]','50.00','2025-08-22 17:30:09','2025-08-22 17:30:10',NULL),
(538,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:30:13',50.00,'finalized','aec67ef8784192f2037217044f46c095','[1,2,6]','50.00','2025-08-22 17:30:13','2025-08-22 17:30:14',NULL),
(539,107,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-22 17:30:16',50.00,'finalized','3a8c1102ea6f250f317453f217545d6e','[1,2,5]','10.00','2025-08-22 17:30:16','2025-08-22 17:30:17',NULL),
(540,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:30:20',50.00,'finalized','e47d3520b27cbdf60bead90f0943e6e5','[0,6,7]','20.00','2025-08-22 17:30:20','2025-08-22 17:30:20',NULL),
(541,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:23',50.00,'finalized','6933aabf4ea08f2df19cab01ae2a2299','[]',NULL,'2025-08-22 17:30:23','2025-08-22 17:30:23',NULL),
(542,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:30:27',50.00,'finalized','9c9d3cb1cf8e6d26494ac83c27ea1bf3','[4,5,8]','20.00','2025-08-22 17:30:27','2025-08-22 17:30:28',NULL),
(543,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:31',50.00,'finalized','2590ad74bf8c1d43faf19007a29fbac3','[]',NULL,'2025-08-22 17:30:31','2025-08-22 17:30:32',NULL),
(544,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:34',50.00,'finalized','790806e3b56a5fb2d050130f436c4642','[]',NULL,'2025-08-22 17:30:34','2025-08-22 17:30:35',NULL),
(545,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:30:39',50.00,'finalized','59e7741fdcbc4ffaf874ff990c0c7c10','[]',NULL,'2025-08-22 17:30:39','2025-08-22 17:30:39',NULL),
(546,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:30:48',50.00,'finalized','56331a634f675c04f24bd8dbde91b926','[1,3,5]','5.00','2025-08-22 17:30:48','2025-08-22 17:30:49',NULL),
(547,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:30:55',50.00,'finalized','c193bfdb4ca11c0ccb327ff213408a79','[2,7,8]','50.00','2025-08-22 17:30:55','2025-08-22 17:30:55',NULL),
(548,107,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-22 17:30:59',50.00,'finalized','63e20e5ca529b9dbafb86ed2f9cd66e4','[3,7,8]','10.00','2025-08-22 17:30:59','2025-08-22 17:30:59',NULL),
(549,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:12',50.00,'finalized','410d3dd0c3cc3f80cdc8b294246d0e7b','[]',NULL,'2025-08-22 17:31:12','2025-08-22 17:31:13',NULL),
(550,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:31:16',50.00,'finalized','1ded7983c9f254af6947325c26c031a7','[0,4,7]','20.00','2025-08-22 17:31:16','2025-08-22 17:31:17',NULL),
(551,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:28',50.00,'finalized','9f9d8b6aaaadbe9182e84bc4b4e91079','[]',NULL,'2025-08-22 17:31:28','2025-08-22 17:31:28',NULL),
(552,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:31:32',50.00,'finalized','12392919250b51c9fa20806df9e861bb','[0,3,8]','5.00','2025-08-22 17:31:32','2025-08-22 17:31:32',NULL),
(553,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:31:36',50.00,'finalized','e16751c1fd737773527fe73bf61bdad9','[0,1,3]','20.00','2025-08-22 17:31:36','2025-08-22 17:31:37',NULL),
(554,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:40',50.00,'finalized','d511d579f25cfbf68abf823ac29b9094','[]',NULL,'2025-08-22 17:31:40','2025-08-22 17:31:40',NULL),
(555,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:43',50.00,'finalized','f927cd92b9c782a3b83bf34c24fb48b5','[]',NULL,'2025-08-22 17:31:43','2025-08-22 17:31:43',NULL),
(556,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:31:46',50.00,'finalized','c771ed12e8e91e66594b30a189d1edfd','[0,2,3]','5.00','2025-08-22 17:31:46','2025-08-22 17:31:47',NULL),
(557,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:50',50.00,'finalized','b68bfc7323261ac3887fcfea3431d13f','[]',NULL,'2025-08-22 17:31:50','2025-08-22 17:31:50',NULL),
(558,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:53',50.00,'finalized','74ccda1a95289107b18a5a5555a4986c','[]',NULL,'2025-08-22 17:31:53','2025-08-22 17:31:53',NULL),
(559,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:31:58',50.00,'finalized','b7b16d088532ff545e05bc31c685ec81','[]',NULL,'2025-08-22 17:31:58','2025-08-22 17:31:58',NULL),
(560,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:32:02',50.00,'finalized','433c85fb7c3b3bb90b9cf02041756c87','[]',NULL,'2025-08-22 17:32:02','2025-08-22 17:32:02',NULL),
(561,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:32:05',50.00,'finalized','8c8490f81a9d5c7d870584ee11b4c514','[]',NULL,'2025-08-22 17:32:05','2025-08-22 17:32:05',NULL),
(562,107,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 17:32:07',50.00,'finalized','ce36fa4ce053a5da890091622694c203','[0,1,7]','20.00','2025-08-22 17:32:07','2025-08-22 17:32:08',NULL),
(563,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:32:15',50.00,'finalized','46078253ad0d4803ea5cec5b737a29df','[]',NULL,'2025-08-22 17:32:15','2025-08-22 17:32:15',NULL),
(564,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:32:18',50.00,'finalized','23fb2a9b7395ed8c4b08ded9fca503a7','[0,3,4]','50.00','2025-08-22 17:32:18','2025-08-22 17:32:19',NULL),
(565,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:32:22',50.00,'finalized','dd96392cd5ae1ab15a19d00bc95c83f4','[1,3,8]','50.00','2025-08-22 17:32:22','2025-08-22 17:32:23',NULL),
(566,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:32:26',50.00,'finalized','0645113fbf0da49dd77b900874badd69','[]',NULL,'2025-08-22 17:32:26','2025-08-22 17:32:26',NULL),
(567,107,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 17:32:28',50.00,'finalized','b613ec7f8042fc350256f900f28a664b','[1,6,7]','2.00','2025-08-22 17:32:28','2025-08-22 17:32:29',NULL),
(568,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:32:32',50.00,'finalized','2e133314de7f5c57c8e857fb25684831','[]',NULL,'2025-08-22 17:32:32','2025-08-22 17:32:33',NULL),
(569,107,0,'Luxo Máximo 👑','ganhou',200.00,'2025-08-22 17:32:36',50.00,'finalized','38d7f37898b92bb135c01cb55b730110','[0,1,5]','200.00','2025-08-22 17:32:36','2025-08-22 17:32:37',NULL),
(570,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:32:41',50.00,'finalized','5e48563cb84fb16dfdc271d042de7583','[0,7,8]','5.00','2025-08-22 17:32:41','2025-08-22 17:32:42',NULL),
(571,107,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 17:32:45',50.00,'finalized','be594c2e16c829cae7ec340f0c3704a6','[2,6,7]','2.00','2025-08-22 17:32:45','2025-08-22 17:32:46',NULL),
(572,107,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-22 17:32:48',50.00,'finalized','9ad08deae9e92768073a04177ef4b8ab','[2,5,7]','10.00','2025-08-22 17:32:48','2025-08-22 17:32:49',NULL),
(573,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:32:52',50.00,'finalized','17632e702582230a623d570b1597d0c1','[0,7,8]','50.00','2025-08-22 17:32:52','2025-08-22 17:32:53',NULL),
(574,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:32:56',50.00,'finalized','c4e0a2269b2493e379e3a0c7f0d88d1a','[1,5,8]','50.00','2025-08-22 17:32:56','2025-08-22 17:32:57',NULL),
(575,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:33:02',50.00,'finalized','42bd51acb3594a06f7b22b9afc85b66b','[2,4,6]','5.00','2025-08-22 17:33:02','2025-08-22 17:33:03',NULL),
(576,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:33:14',50.00,'finalized','3e7926afbd1760d1f57e8e07b6fecc10','[1,3,8]','50.00','2025-08-22 17:33:14','2025-08-22 17:33:14',NULL),
(577,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:33:17',50.00,'finalized','8314ba7e337c84ca38974e7aee7207dd','[0,5,6]','100.00','2025-08-22 17:33:17','2025-08-22 17:33:18',NULL),
(578,107,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 17:33:22',50.00,'finalized','b133010f18eb5fd70519bd5414b1bac6','[2,6,8]','100.00','2025-08-22 17:33:22','2025-08-22 17:33:22',NULL),
(579,107,0,'Luxo Máximo 👑','ganhou',1.00,'2025-08-22 17:33:25',50.00,'finalized','01388b9821ef346c17ef99863ca377e2','[2,3,4]','1.00','2025-08-22 17:33:25','2025-08-22 17:33:26',NULL),
(580,107,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 17:33:29',50.00,'finalized','0c91640bad9e891ed2cf19381635a272','[4,5,6]','2.00','2025-08-22 17:33:29','2025-08-22 17:33:30',NULL),
(581,107,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 17:33:33',50.00,'finalized','ed77555cc133dec085ccc62d90daa243','[2,4,7]','2.00','2025-08-22 17:33:33','2025-08-22 17:33:33',NULL),
(582,107,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-22 17:33:39',50.00,'finalized','b6543f493e96a20d6146896eba1730db','[4,6,8]','5.00','2025-08-22 17:33:39','2025-08-22 17:33:40',NULL),
(583,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:33:42',50.00,'finalized','026c1211869bad2882653788e28b82ca','[3,5,8]','50.00','2025-08-22 17:33:42','2025-08-22 17:33:43',NULL),
(584,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:33:48',50.00,'finalized','b6f03749091995851745fe3147738d56','[3,5,7]','50.00','2025-08-22 17:33:48','2025-08-22 17:33:49',NULL),
(585,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:35:55',50.00,'finalized','13fb0925de9ea048e8ac250ddac72e50','[]',NULL,'2025-08-22 17:35:55','2025-08-22 17:35:56',NULL),
(586,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:35:59',50.00,'finalized','9a296d47e1fe4885d83db759736e6446','[]',NULL,'2025-08-22 17:35:59','2025-08-22 17:35:59',NULL),
(587,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:36:01',50.00,'finalized','ef423cd59079e7bb36284ec15d66f56b','[3,5,6]','50.00','2025-08-22 17:36:01','2025-08-22 17:36:02',NULL),
(588,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:36:05',50.00,'finalized','d50fa810174cef2523b3b302315b4c1f','[1,4,6]','50.00','2025-08-22 17:36:05','2025-08-22 17:36:06',NULL),
(589,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:38:00',50.00,'finalized','2c20c4b87c67308f104bfdd764f74e18','[]',NULL,'2025-08-22 17:38:00','2025-08-22 17:38:01',NULL),
(590,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:38:03',50.00,'finalized','5147b9f68f5cb75953ba2b4e22b1344d','[]',NULL,'2025-08-22 17:38:03','2025-08-22 17:38:03',NULL),
(591,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:38:05',50.00,'finalized','04197998bdfc0d97998c75c57283cf66','[]',NULL,'2025-08-22 17:38:05','2025-08-22 17:38:05',NULL),
(592,107,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 17:38:07',50.00,'finalized','bd3f4eb0db6a290661ffefa9eb8a63c0','[6,7,8]','50.00','2025-08-22 17:38:07','2025-08-22 17:38:08',NULL),
(593,107,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 17:38:11',50.00,'finalized','5ddcf9704bfc17c351fea12731f058d1','[]',NULL,'2025-08-22 17:38:11','2025-08-22 17:38:11',NULL),
(594,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:38:39',30.00,'finalized','93996e08dd27a0ae5b9b7e323afc279e','[]',NULL,'2025-08-22 17:38:39','2025-08-22 17:38:39',NULL),
(595,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:38:42',30.00,'finalized','cd45579af553287c3254a89b95bd76a7','[]',NULL,'2025-08-22 17:38:42','2025-08-22 17:38:42',NULL),
(596,107,0,'Império dourado 🏰','ganhou',2.00,'2025-08-22 17:38:46',30.00,'finalized','c984d7e69f122976824f3482fbadd9d8','[0,5,8]','2.00','2025-08-22 17:38:46','2025-08-22 17:38:47',NULL),
(597,107,0,'Império dourado 🏰','ganhou',200.00,'2025-08-22 17:38:49',30.00,'finalized','46b38b9504f7ca21c40eb1173dcc412f','[0,1,5]','200.00','2025-08-22 17:38:49','2025-08-22 17:38:50',NULL),
(598,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:00',30.00,'finalized','02a1a9e3ab75bc6158a261ec7960e632','[]',NULL,'2025-08-22 17:39:00','2025-08-22 17:39:00',NULL),
(599,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:06',30.00,'finalized','6664d1e4872ee239c8d8b77c521d0e07','[]',NULL,'2025-08-22 17:39:06','2025-08-22 17:39:07',NULL),
(600,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:10',30.00,'finalized','c413013bc1d794fe47999ec79c71cf3c','[]',NULL,'2025-08-22 17:39:10','2025-08-22 17:39:10',NULL),
(601,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:14',30.00,'finalized','dd7d147028ca80dc376a069a40939c55','[]',NULL,'2025-08-22 17:39:14','2025-08-22 17:39:15',NULL),
(602,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:18',30.00,'finalized','5e88d090a3943dfa0985245f53f10fa1','[]',NULL,'2025-08-22 17:39:18','2025-08-22 17:39:18',NULL),
(603,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:20',30.00,'finalized','54d555abbbec2b71273a2d21011d838f','[]',NULL,'2025-08-22 17:39:20','2025-08-22 17:39:20',NULL),
(604,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:25',30.00,'finalized','3dbe7290785778d44727bd1bd4af581f','[]',NULL,'2025-08-22 17:39:25','2025-08-22 17:39:25',NULL),
(605,107,0,'Império dourado 🏰','ganhou',2.00,'2025-08-22 17:39:29',30.00,'finalized','aeb74c219fd2d48147020e644b2029cf','[1,6,7]','2.00','2025-08-22 17:39:29','2025-08-22 17:39:29',NULL),
(606,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:32',30.00,'finalized','74afe0fc979cd8aca42e455c9921493b','[]',NULL,'2025-08-22 17:39:32','2025-08-22 17:39:33',NULL),
(607,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:35',30.00,'finalized','d0cd9cfdc9f925e060e368db482d173f','[]',NULL,'2025-08-22 17:39:35','2025-08-22 17:39:36',NULL),
(608,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:39:38',30.00,'finalized','c9d64daf0d8eeab50761885292838aab','[3,6,8]','20.00','2025-08-22 17:39:38','2025-08-22 17:39:39',NULL),
(609,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:39:42',30.00,'finalized','04f8d0719445d684211326572c4cdd3f','[]',NULL,'2025-08-22 17:39:42','2025-08-22 17:39:42',NULL),
(610,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:39:45',30.00,'finalized','615d1accbf91e8c37bbd5dfd63852449','[0,1,7]','20.00','2025-08-22 17:39:45','2025-08-22 17:39:45',NULL),
(611,107,0,'Império dourado 🏰','ganhou',100.00,'2025-08-22 17:39:48',30.00,'finalized','153f9194fdf6180d8674c84577b13574','[2,6,8]','100.00','2025-08-22 17:39:48','2025-08-22 17:39:49',NULL),
(612,107,0,'Império dourado 🏰','ganhou',50.00,'2025-08-22 17:39:52',30.00,'finalized','c9335c74df0ac923ab9febcbdcd613e9','[1,2,3]','50.00','2025-08-22 17:39:52','2025-08-22 17:39:53',NULL),
(613,107,0,'Império dourado 🏰','ganhou',50.00,'2025-08-22 17:39:55',30.00,'finalized','9acaea225e8fccc4a181b26aac9320c0','[1,7,8]','50.00','2025-08-22 17:39:55','2025-08-22 17:39:56',NULL),
(614,107,0,'Império dourado 🏰','ganhou',10.00,'2025-08-22 17:39:59',30.00,'finalized','66edea3c88766f9bbc843fc2e75044ad','[1,2,3]','10.00','2025-08-22 17:39:59','2025-08-22 17:39:59',NULL),
(615,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:01',30.00,'finalized','2f013433c0ecf00e82a3491de6d6fb91','[]',NULL,'2025-08-22 17:40:01','2025-08-22 17:40:02',NULL),
(616,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:04',30.00,'finalized','c238c25f4b987eb2a61560697c60804d','[]',NULL,'2025-08-22 17:40:04','2025-08-22 17:40:04',NULL),
(617,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:06',30.00,'finalized','fa47b27a723c4d75775e11d710e317b6','[0,5,6]','20.00','2025-08-22 17:40:06','2025-08-22 17:40:06',NULL),
(618,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:08',30.00,'finalized','c59ea235191c86419f66438ee211c910','[]',NULL,'2025-08-22 17:40:08','2025-08-22 17:40:08',NULL),
(619,107,0,'Império dourado 🏰','ganhou',50.00,'2025-08-22 17:40:09',30.00,'finalized','0bf5ec7ba692a764ea4983733193d3b2','[2,4,6]','50.00','2025-08-22 17:40:09','2025-08-22 17:40:10',NULL),
(620,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:12',30.00,'finalized','346f82de87a1ca781fab6a3be1c48d02','[5,6,8]','20.00','2025-08-22 17:40:12','2025-08-22 17:40:13',NULL),
(621,107,0,'Império dourado 🏰','ganhou',2.00,'2025-08-22 17:40:14',30.00,'finalized','ed5e2f653d1d75a9da8517c82c3f8b19','[3,6,8]','2.00','2025-08-22 17:40:14','2025-08-22 17:40:15',NULL),
(622,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:16',30.00,'finalized','e1a4e3f0e820e30c4e07aff71ea6c0a5','[1,2,8]','20.00','2025-08-22 17:40:16','2025-08-22 17:40:17',NULL),
(623,107,0,'Império dourado 🏰','ganhou',2.00,'2025-08-22 17:40:18',30.00,'finalized','2790075ecd0bb2460233ed1bdf56f013','[0,2,7]','2.00','2025-08-22 17:40:18','2025-08-22 17:40:19',NULL),
(624,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:20',30.00,'finalized','ac1762fed51f6cf157b6045e80db66ff','[0,1,7]','20.00','2025-08-22 17:40:20','2025-08-22 17:40:21',NULL),
(625,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:22',30.00,'finalized','0ff661de7289b0e6b55ab3405fe77f03','[1,2,4]','20.00','2025-08-22 17:40:22','2025-08-22 17:40:23',NULL),
(626,107,0,'Império dourado 🏰','ganhou',5.00,'2025-08-22 17:40:24',30.00,'finalized','27c02874d2ad7df779b0e553ba6f8219','[2,3,5]','5.00','2025-08-22 17:40:24','2025-08-22 17:40:25',NULL),
(627,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:26',30.00,'finalized','5127c7933a80a5d41fa651bc8f8d6e44','[]',NULL,'2025-08-22 17:40:26','2025-08-22 17:40:26',NULL),
(628,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:27',30.00,'finalized','74f17c1fb682d95da71453d21a860a3b','[]',NULL,'2025-08-22 17:40:27','2025-08-22 17:40:27',NULL),
(629,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:28',30.00,'finalized','23cdfd33be64b9d02e25ea80b4519d6d','[]',NULL,'2025-08-22 17:40:28','2025-08-22 17:40:28',NULL),
(630,107,0,'Império dourado 🏰','ganhou',5.00,'2025-08-22 17:40:29',30.00,'finalized','f16ab7f04dc1ab1b469ab208a545d253','[0,2,4]','5.00','2025-08-22 17:40:29','2025-08-22 17:40:30',NULL),
(631,107,0,'Império dourado 🏰','ganhou',2.00,'2025-08-22 17:40:31',30.00,'finalized','cfa2dc1c422f09d875eaaadce8bbc48b','[0,3,4]','2.00','2025-08-22 17:40:31','2025-08-22 17:40:32',NULL),
(632,107,0,'Império dourado 🏰','ganhou',200.00,'2025-08-22 17:40:34',30.00,'finalized','26ed6949bc0a4bbf276aa46d36f1855e','[5,7,8]','200.00','2025-08-22 17:40:34','2025-08-22 17:40:34',NULL),
(633,107,0,'Império dourado 🏰','ganhou',5.00,'2025-08-22 17:40:35',30.00,'finalized','8fec1a25d449b87919a08ec89d691b31','[2,3,6]','5.00','2025-08-22 17:40:35','2025-08-22 17:40:36',NULL),
(634,107,0,'Império dourado 🏰','perdeu',0.00,'2025-08-22 17:40:38',30.00,'finalized','748bf05d0e444b752c55fead1cde6565','[]',NULL,'2025-08-22 17:40:38','2025-08-22 17:40:38',NULL),
(635,107,0,'Império dourado 🏰','ganhou',1.00,'2025-08-22 17:40:38',30.00,'finalized','bf2be38045413e7732848180233d91d4','[0,1,8]','1.00','2025-08-22 17:40:38','2025-08-22 17:40:39',NULL),
(636,107,0,'Império dourado 🏰','ganhou',10.00,'2025-08-22 17:40:40',30.00,'finalized','68f3deb35fc3751d61f54b5ff875e126','[0,3,4]','10.00','2025-08-22 17:40:40','2025-08-22 17:40:41',NULL),
(637,107,0,'Império dourado 🏰','ganhou',20.00,'2025-08-22 17:40:44',30.00,'finalized','79d108c1a8b2c3aa967c7e5eae37dcb0','[0,1,5]','20.00','2025-08-22 17:40:44','2025-08-22 17:40:44',NULL),
(638,107,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 17:41:10',5.00,'finalized','cf0af446d4a6734c2218126863bbbb0c','[1,3,4]','1.00','2025-08-22 17:41:10','2025-08-22 17:41:11',NULL),
(639,107,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 17:41:14',5.00,'finalized','0fae6f304bbcdd04fa7993345bd68147','[4,6,7]','1.00','2025-08-22 17:41:14','2025-08-22 17:41:15',NULL),
(640,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 17:41:19',5.00,'finalized','df8f0c306966657dfdfee7f9a88c9d20','[]',NULL,'2025-08-22 17:41:19','2025-08-22 17:41:20',NULL),
(641,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:41:21',5.00,'finalized','4729393ee90aeafc3c819dfee61d1c23','[0,5,8]','5.00','2025-08-22 17:41:21','2025-08-22 17:41:22',NULL),
(642,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 17:41:23',5.00,'finalized','074fbbbd486562dcf897a694554b5f07','[0,4,8]','2.00','2025-08-22 17:41:23','2025-08-22 17:41:24',NULL),
(643,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 17:41:26',5.00,'finalized','b3d26df405a029aefd0f90ff2c1ea21d','[0,3,4]','2.00','2025-08-22 17:41:26','2025-08-22 17:41:26',NULL),
(644,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:41:28',5.00,'finalized','58b17db740269ed4f6c35f25ef30c7c5','[1,4,5]','5.00','2025-08-22 17:41:28','2025-08-22 17:41:28',NULL),
(645,107,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 17:41:30',5.00,'finalized','a8c650d27c1464f7c3caefb228eceedb','[0,5,6]','1.00','2025-08-22 17:41:30','2025-08-22 17:41:31',NULL),
(646,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 17:41:32',5.00,'finalized','abfff929bd08aed9afa688dc0e698fa5','[3,6,8]','2.00','2025-08-22 17:41:32','2025-08-22 17:41:33',NULL),
(647,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 17:41:34',5.00,'finalized','c3b8934c7c057fb7c79b66d7806ee918','[0,2,3]','10.00','2025-08-22 17:41:34','2025-08-22 17:41:35',NULL),
(648,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:41:37',5.00,'finalized','2104637ea4beaec9ef4a369d2a861d94','[1,7,8]','5.00','2025-08-22 17:41:37','2025-08-22 17:41:37',NULL),
(649,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 17:53:17',5.00,'finalized','38a263485c0118bed83468866d29041a','[]',NULL,'2025-08-22 17:53:17','2025-08-22 17:53:17',NULL),
(650,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:53:18',5.00,'finalized','c2b1ef5ba5b371de4f1c0da43552f273','[4,6,7]','5.00','2025-08-22 17:53:18','2025-08-22 17:53:19',NULL),
(651,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 17:53:21',5.00,'finalized','50c6dda66f0e390dc092b3be0cbd62e8','[]',NULL,'2025-08-22 17:53:21','2025-08-22 17:53:21',NULL),
(652,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 17:53:22',5.00,'finalized','52beb009d3bbe83c4f01061255fe7644','[]',NULL,'2025-08-22 17:53:22','2025-08-22 17:53:22',NULL),
(653,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 17:53:23',5.00,'finalized','31053052c82d590f956b92e6891c9aa1','[]',NULL,'2025-08-22 17:53:23','2025-08-22 17:53:23',NULL),
(654,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:53:23',5.00,'finalized','693ecc6e513dd085067b29992460b0b0','[3,5,8]','5.00','2025-08-22 17:53:23','2025-08-22 17:53:24',NULL),
(655,1,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 17:53:26',5.00,'finalized','ec517a2c5bc5e0bb37732dea97fa3c89','[4,7,8]','2.00','2025-08-22 17:53:26','2025-08-22 17:53:26',NULL),
(656,1,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 17:53:28',5.00,'finalized','b317f01ad2931bc0b62ce03817acb6d0','[0,4,6]','10.00','2025-08-22 17:53:28','2025-08-22 17:53:29',NULL),
(657,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:53:30',5.00,'finalized','b0c8e019e085bbe6225515e66b84e95e','[1,4,8]','5.00','2025-08-22 17:53:30','2025-08-22 17:53:31',NULL),
(658,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:53:32',5.00,'finalized','cf55bbf7df6e53552e5a9f976cb1c964','[0,1,5]','5.00','2025-08-22 17:53:32','2025-08-22 17:53:33',NULL),
(659,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 17:53:34',5.00,'finalized','0bc6a02f7f4182f8a9fc3930e35ebd9f','[3,4,7]','5.00','2025-08-22 17:53:34','2025-08-22 17:53:35',NULL),
(660,1,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 17:53:37',5.00,'finalized','e86d3c9e7e993bd03d6b683834485469','[1,4,5]','2.00','2025-08-22 17:53:37','2025-08-22 17:53:37',NULL),
(661,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 17:55:01',1.00,'finalized','9b34c28f23b25e4caf5a60ec9435e6f5','[5,7,8]','0.50','2025-08-22 17:55:01','2025-08-22 17:55:02',NULL),
(662,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:03',1.00,'finalized','4b3e4d884a0d068327c072e7561c9732','[]',NULL,'2025-08-22 17:55:03','2025-08-22 17:55:03',NULL),
(663,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 17:55:05',1.00,'finalized','196c89a174e3520b5e33ea9c925236ed','[1,6,8]','2.00','2025-08-22 17:55:05','2025-08-22 17:55:05',NULL),
(664,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:07',1.00,'finalized','870bef5498b2735f4acf95453693d3a9','[]',NULL,'2025-08-22 17:55:07','2025-08-22 17:55:08',NULL),
(665,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 17:55:09',1.00,'finalized','aae51725f9cc2ccf97bae965297258ea','[4,5,6]','0.50','2025-08-22 17:55:09','2025-08-22 17:55:09',NULL),
(666,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 17:55:11',1.00,'finalized','95bac3395fe148ba546bc7dce60bde2e','[3,4,6]','2.00','2025-08-22 17:55:11','2025-08-22 17:55:11',NULL),
(667,1,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 17:55:14',1.00,'finalized','784cf1c0e80f96f7e1f5be1edf6b5ed3','[1,3,5]','5.00','2025-08-22 17:55:14','2025-08-22 17:55:14',NULL),
(668,1,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 17:55:16',1.00,'finalized','aa8e06c459e08d4c4bd1a4d6643335fc','[0,3,5]','5.00','2025-08-22 17:55:16','2025-08-22 17:55:16',NULL),
(669,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:19',1.00,'finalized','02afb77fbf06c617985804b45fb21536','[]',NULL,'2025-08-22 17:55:19','2025-08-22 17:55:19',NULL),
(670,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 17:55:21',1.00,'finalized','6793942a018909ab313f8a7228f8f70e','[5,6,8]','0.50','2025-08-22 17:55:21','2025-08-22 17:55:21',NULL),
(671,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 17:55:23',1.00,'finalized','762bb3884c4584a52fb7ee3d7c69cd8b','[2,7,8]','1.00','2025-08-22 17:55:23','2025-08-22 17:55:24',NULL),
(672,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:25',1.00,'finalized','b66cd19cfa97839690233acb3459dae2','[]',NULL,'2025-08-22 17:55:25','2025-08-22 17:55:25',NULL),
(673,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 17:55:27',1.00,'finalized','a0d089fca1ba8c3d21d66be680738f6c','[2,4,6]','2.00','2025-08-22 17:55:27','2025-08-22 17:55:28',NULL),
(674,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 17:55:29',1.00,'finalized','ad4575315d121c3ad9ffcf23175114f7','[0,3,4]','1.00','2025-08-22 17:55:29','2025-08-22 17:55:30',NULL),
(675,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:31',1.00,'finalized','605d594cc39da141312fbcd71433470c','[]',NULL,'2025-08-22 17:55:31','2025-08-22 17:55:31',NULL),
(676,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 17:55:33',1.00,'finalized','00772e0cfdb11c1baef7d4fa32e9eb50','[0,5,8]','1.00','2025-08-22 17:55:33','2025-08-22 17:55:34',NULL),
(677,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 17:55:35',1.00,'finalized','5e5218d0947adb4bce8068460e6b8f0d','[2,6,7]','0.50','2025-08-22 17:55:35','2025-08-22 17:55:36',NULL),
(678,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 17:55:38',1.00,'finalized','ba2ad17b0cb1494a11f2efddd6a6a0f5','[]',NULL,'2025-08-22 17:55:38','2025-08-22 17:55:38',NULL),
(679,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 17:55:40',1.00,'finalized','5ca853187c7ee6cd1e19c9983918b6bf','[0,3,6]','0.50','2025-08-22 17:55:40','2025-08-22 17:55:41',NULL),
(680,1,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-22 17:55:42',1.00,'finalized','e2f1feca66b5d47f6ffaaff85556550b','[0,4,6]','5.00','2025-08-22 17:55:42','2025-08-22 17:55:43',NULL),
(681,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:04:35',5.00,'finalized','3dbb22275a751d72b2d2d4e37125bf15','[2,3,4]','5.00','2025-08-22 18:04:35','2025-08-22 18:04:36',NULL),
(682,107,0,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 18:04:39',5.00,'finalized','f41ca0b2f3c3de1b80fa2d6c356402db','[2,3,6]','50.00','2025-08-22 18:04:39','2025-08-22 18:04:40',NULL),
(683,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:04:43',5.00,'finalized','203d6d78919d7226b8482727246d5b71','[0,2,5]','2.00','2025-08-22 18:04:43','2025-08-22 18:04:43',NULL),
(684,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:04:46',5.00,'finalized','66e4920e1277cac7c5a7de7344647b47','[]',NULL,'2025-08-22 18:04:46','2025-08-22 18:04:47',NULL),
(685,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:04:49',5.00,'finalized','e652cdc68b04c26811835c864e2d3ddf','[]',NULL,'2025-08-22 18:04:49','2025-08-22 18:04:49',NULL),
(686,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 18:04:52',5.00,'finalized','de7c74057e5e4060454b2aa22fe93ae7','[3,6,7]','10.00','2025-08-22 18:04:52','2025-08-22 18:04:53',NULL),
(687,107,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-22 18:04:56',5.00,'finalized','4a3663c8cfae307f8c879e539f4613c9','[1,4,5]','20.00','2025-08-22 18:04:56','2025-08-22 18:04:57',NULL),
(688,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:05:01',5.00,'finalized','3135892e7e17661cf3955265e309b613','[]',NULL,'2025-08-22 18:05:01','2025-08-22 18:05:01',NULL),
(689,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:05:06',5.00,'finalized','6473679844ac1a47445397b0831c290b','[2,6,8]','2.00','2025-08-22 18:05:06','2025-08-22 18:05:07',NULL),
(690,107,0,'Trono da Prada 👑','ganhou',50.00,'2025-08-22 18:06:00',5.00,'finalized','a381ef2cfa2b28261990660b9f1a41ed','[1,7,8]','50.00','2025-08-22 18:06:00','2025-08-22 18:06:01',NULL),
(691,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 18:06:04',5.00,'finalized','2a42548f501ba38aaea3e4d729325dc0','[0,6,7]','10.00','2025-08-22 18:06:04','2025-08-22 18:06:05',NULL),
(692,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 18:06:08',5.00,'finalized','6443cdbc8266f54c4ac1063ec5a28474','[0,4,5]','10.00','2025-08-22 18:06:08','2025-08-22 18:06:09',NULL),
(693,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:06:13',5.00,'finalized','812787367dd66e33e09e2a60aec7c320','[2,3,8]','2.00','2025-08-22 18:06:13','2025-08-22 18:06:14',NULL),
(694,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:06:17',5.00,'finalized','607ae974881670e100d1a2f32ca5b0cb','[4,5,6]','5.00','2025-08-22 18:06:17','2025-08-22 18:06:18',NULL),
(695,107,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 18:06:26',5.00,'finalized','dbdc751b52e2ba29acd1258807ef6379','[1,2,3]','1.00','2025-08-22 18:06:26','2025-08-22 18:06:27',NULL),
(696,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:06:29',5.00,'finalized','83a1c6a6990e24e5dbaf8b080eb0fd8c','[]',NULL,'2025-08-22 18:06:29','2025-08-22 18:06:29',NULL),
(697,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 18:06:31',5.00,'finalized','0b06bcae66d308ba1584b12381f8c6da','[3,6,8]','10.00','2025-08-22 18:06:31','2025-08-22 18:06:32',NULL),
(698,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:06:34',5.00,'finalized','57053225bc0563468fdc8bf4bdad0d11','[5,6,8]','2.00','2025-08-22 18:06:34','2025-08-22 18:06:35',NULL),
(699,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:06:37',5.00,'finalized','77ad1c5933deb7e01bb64e383532c09d','[]',NULL,'2025-08-22 18:06:37','2025-08-22 18:06:37',NULL),
(700,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:06:39',5.00,'finalized','eb10050bb436b09d0b938a6dddabe3ac','[2,3,5]','5.00','2025-08-22 18:06:39','2025-08-22 18:06:40',NULL),
(701,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:06:42',5.00,'finalized','7830044784b503db6ce9480b8495c331','[3,4,7]','5.00','2025-08-22 18:06:42','2025-08-22 18:06:43',NULL),
(702,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:06:45',5.00,'finalized','2de22edd4bf3e3f4a3b05c3300654e92','[1,6,7]','5.00','2025-08-22 18:06:45','2025-08-22 18:06:46',NULL),
(703,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:06:49',5.00,'finalized','5a6f6f10959c8541ebcf41a7daa02327','[6,7,8]','5.00','2025-08-22 18:06:49','2025-08-22 18:06:50',NULL),
(704,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:07:33',5.00,'finalized','bfe6c3f5d6044eacfa37959d5bc90d7e','[]',NULL,'2025-08-22 18:07:33','2025-08-22 18:07:34',NULL),
(705,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:07:36',5.00,'finalized','5512cffdda5893f8d7712ceea8cb6afe','[0,6,8]','2.00','2025-08-22 18:07:36','2025-08-22 18:07:37',NULL),
(706,107,0,'Trono da Prada 👑','ganhou',0.50,'2025-08-22 18:07:39',5.00,'finalized','847dd4cec7a650756483acad479012ac','[2,5,7]','0.50','2025-08-22 18:07:39','2025-08-22 18:07:40',NULL),
(707,107,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 18:07:42',5.00,'finalized','df7403a4bed00fcc801d23b598926c1c','[2,6,8]','2.00','2025-08-22 18:07:42','2025-08-22 18:07:43',NULL),
(708,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:07:45',5.00,'finalized','eb3e9117b7cc00ca790bfc27f5d0699a','[]',NULL,'2025-08-22 18:07:45','2025-08-22 18:07:45',NULL),
(709,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 18:07:47',5.00,'finalized','bf96b2cd01bb4b0662919004107b20da','[0,3,6]','5.00','2025-08-22 18:07:47','2025-08-22 18:07:48',NULL),
(710,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 18:07:50',5.00,'finalized','199a5655f94aa8a965f64fed9d36b048','[]',NULL,'2025-08-22 18:07:50','2025-08-22 18:07:51',NULL),
(711,1,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-22 20:13:27',50.00,'finalized','9f5765c8bdb8211484e797d7be568656','[3,6,8]','20.00','2025-08-22 20:13:27','2025-08-22 20:13:30',NULL),
(712,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-22 20:13:32',50.00,'finalized','13669a6dbf20f0a17de7c041bf2975ce','[3,4,7]','50.00','2025-08-22 20:13:32','2025-08-22 20:13:34',NULL),
(713,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 21:16:24',5.00,'finalized','a11bfe19d6286d4093425d958e21ffff','[]',NULL,'2025-08-22 21:16:24','2025-08-22 21:16:25',NULL),
(714,107,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 21:16:29',5.00,'finalized','9efd35a2b3abc8ee5fc11d917b309631','[5,7,8]','10.00','2025-08-22 21:16:29','2025-08-22 21:16:30',NULL),
(715,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 21:16:33',5.00,'finalized','5484d8b5b17d53bcb9f1be974da194ef','[]',NULL,'2025-08-22 21:16:33','2025-08-22 21:16:34',NULL),
(716,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 21:16:37',5.00,'finalized','52c009e2fa89f037db4100494e09e66c','[]',NULL,'2025-08-22 21:16:37','2025-08-22 21:16:37',NULL),
(717,107,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 21:16:40',5.00,'finalized','738921054acfd2499d280a75974dd3dd','[1,4,5]','1.00','2025-08-22 21:16:40','2025-08-22 21:16:41',NULL),
(718,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 21:16:43',5.00,'finalized','0fd742f6963886bf74e6465e44988a5e','[2,3,4]','5.00','2025-08-22 21:16:43','2025-08-22 21:16:44',NULL),
(719,107,0,'Trono da Prada 👑','ganhou',0.50,'2025-08-22 21:16:47',5.00,'finalized','3189074638a9a125a8ce7dabe057f6a5','[2,3,4]','0.50','2025-08-22 21:16:47','2025-08-22 21:16:48',NULL),
(720,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 21:16:51',5.00,'finalized','4b924ad4ada7a25c459bef5e4e965e65','[1,3,6]','5.00','2025-08-22 21:16:51','2025-08-22 21:16:52',NULL),
(721,107,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-22 21:16:55',5.00,'finalized','7e3e1d27e339670ffcdc2a12b02abb64','[1,4,7]','20.00','2025-08-22 21:16:55','2025-08-22 21:16:56',NULL),
(722,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 22:56:06',2.00,'finalized','50f341e579d18c7815202d4a1eeede19','[]',NULL,'2025-08-22 22:56:06','2025-08-22 23:16:01',NULL),
(723,1,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 23:05:56',5.00,'finalized','a600a623390c4cf4f85e81a8f8b2086f','[6,7,8]','1.00','2025-08-22 23:05:56','2025-08-22 23:10:07',NULL),
(724,1,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 23:10:29',5.00,'finalized','80f5b2239536f898c83e4f7c0f06b9c5','[2,5,8]','10.00','2025-08-22 23:10:29','2025-08-22 23:11:10',NULL),
(725,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 23:11:12',5.00,'finalized','d90fcbc5103b143ddfef3502676441dd','[]',NULL,'2025-08-22 23:11:12','2025-08-22 23:11:13',NULL),
(726,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 23:11:14',5.00,'finalized','0102e0792b040d84d3ae6dd8a4187a7b','[]',NULL,'2025-08-22 23:11:14','2025-08-22 23:11:14',NULL),
(727,1,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-22 23:11:50',5.00,'finalized','fa6c4c781509fbed72dc11cd6d66a192','[1,2,5]','2.00','2025-08-22 23:11:50','2025-08-22 23:11:51',NULL),
(728,1,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-22 23:12:56',5.00,'finalized','8ca6204eed34d8ffc72cd257f1f67238','[0,2,4]','1.00','2025-08-22 23:12:56','2025-08-22 23:12:57',NULL),
(729,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-22 23:14:06',5.00,'finalized','8cafa763844f6ad7fb907a8fd939dba5','[]',NULL,'2025-08-22 23:14:06','2025-08-22 23:14:06',NULL),
(730,1,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-22 23:14:07',5.00,'finalized','67b24aaa9d605401c5a2806f94016f55','[6,7,8]','10.00','2025-08-22 23:14:07','2025-08-22 23:14:08',NULL),
(731,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-22 23:14:13',5.00,'finalized','e90b24631531a8f9e8a6506fda22a5bc','[5,6,8]','5.00','2025-08-22 23:14:13','2025-08-22 23:14:14',NULL),
(732,1,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-22 23:15:08',10.00,'finalized','8627414bb0beca652d92311f8524996a','[5,6,7]','2.00','2025-08-22 23:15:08','2025-08-22 23:15:08',NULL),
(733,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:15:14',10.00,'finalized','44ed3e02b4dbd38d1418686e25cc7e9b','[]',NULL,'2025-08-22 23:15:14','2025-08-22 23:15:14',NULL),
(734,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 23:15:16',10.00,'finalized','aa0ebb5080200bd9542c6f397c0ea342','[3,5,6]','5.00','2025-08-22 23:15:16','2025-08-22 23:15:16',NULL),
(735,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 23:15:18',10.00,'finalized','3f82380e4acde37a7570a6dc2151f060','[3,4,8]','5.00','2025-08-22 23:15:18','2025-08-22 23:15:19',NULL),
(736,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:15:20',10.00,'finalized','5ebafce92dd4c4e504efa6f65e9628c3','[]',NULL,'2025-08-22 23:15:20','2025-08-22 23:15:20',NULL),
(737,1,0,'Tesouro lendário 🏆','ganhou',100.00,'2025-08-22 23:15:31',10.00,'finalized','10b935e9118c059545782f099a3098d7','[1,5,6]','100.00','2025-08-22 23:15:31','2025-08-22 23:15:32',NULL),
(738,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:15:34',10.00,'finalized','b05dc7788b1eaee9050002af5675c90d','[]',NULL,'2025-08-22 23:15:34','2025-08-22 23:15:34',NULL),
(739,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 23:15:36',10.00,'finalized','5191f0ca6f4333e64d5c5f49722d8218','[1,2,5]','5.00','2025-08-22 23:15:36','2025-08-22 23:15:37',NULL),
(740,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:15:40',10.00,'finalized','88e9f43071c042ab1eca3e49640138d7','[]',NULL,'2025-08-22 23:15:40','2025-08-22 23:15:41',NULL),
(741,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 23:15:42',10.00,'finalized','2f7064db9cd2eb69508c59863b50f6a9','[3,4,8]','5.00','2025-08-22 23:15:42','2025-08-22 23:15:43',NULL),
(742,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:16:10',2.00,'finalized','a74508a7246cadddb54c40545b805928','[]',NULL,'2025-08-22 23:16:10','2025-08-22 23:16:16',NULL),
(743,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 23:16:18',2.00,'finalized','8f0a89462a2bae3f4d6405e82a9ea8a1','[4,6,8]','0.50','2025-08-22 23:16:18','2025-08-22 23:16:22',NULL),
(744,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 23:16:26',2.00,'finalized','f520a1fa02de3ed06709d76382d3c9ff','[2,4,6]','1.00','2025-08-22 23:16:26','2025-08-22 23:16:31',NULL),
(745,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:16:35',2.00,'finalized','a652f9ead7b1e54fde5e65dc8c48c14b','[]',NULL,'2025-08-22 23:16:35','2025-08-22 23:16:39',NULL),
(746,1,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-22 23:16:42',2.00,'finalized','6c14947304ad77719781423bb7c1afa5','[3,4,6]','0.50','2025-08-22 23:16:42','2025-08-22 23:16:48',NULL),
(747,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:16:54',2.00,'finalized','812b2b982446db48720486058370745e','[]',NULL,'2025-08-22 23:16:54','2025-08-22 23:16:58',NULL),
(748,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:17:00',2.00,'finalized','402fc6ca936863ec9398ce2ec265ed13','[1,3,5]','2.00','2025-08-22 23:17:00','2025-08-22 23:17:04',NULL),
(749,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:17:06',2.00,'finalized','ce9746cc7ffdd61b2e179e5b10343230','[]',NULL,'2025-08-22 23:17:06','2025-08-22 23:17:10',NULL),
(750,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:17:13',2.00,'finalized','684c75bb6e99f959cda34af8bebdc5a1','[4,6,7]','2.00','2025-08-22 23:17:13','2025-08-22 23:17:16',NULL),
(751,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 23:17:23',1.00,'finalized','3c39739911133a5caf0cc846920f86c1','[]',NULL,'2025-08-22 23:17:23','2025-08-22 23:17:24',NULL),
(752,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 23:17:26',1.00,'finalized','4d72cfaefef715539985df964d63c27d','[0,5,8]','0.50','2025-08-22 23:17:26','2025-08-22 23:17:26',NULL),
(753,1,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-22 23:17:28',1.00,'finalized','9689b2152ec3fbc8c042ac403c9c5c67','[4,5,8]','0.50','2025-08-22 23:17:28','2025-08-22 23:17:29',NULL),
(754,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 23:17:30',1.00,'finalized','5944a28757fb2e140502b5c24ab41e20','[0,4,7]','1.00','2025-08-22 23:17:30','2025-08-22 23:17:31',NULL),
(755,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 23:17:32',1.00,'finalized','271f1169fcd48da5f121f96c438f2ca6','[1,4,5]','2.00','2025-08-22 23:17:32','2025-08-22 23:17:33',NULL),
(756,1,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-22 23:34:36',10.00,'finalized','70b3bab0ff5c9dd8e9ffb43779b707cc','[0,6,7]','10.00','2025-08-22 23:34:36','2025-08-22 23:34:40',NULL),
(757,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 23:34:53',1.00,'finalized','7317814da4da35d9dead51991ff1d3df','[2,6,7]','2.00','2025-08-22 23:34:53','2025-08-22 23:34:58',NULL),
(758,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 23:35:45',1.00,'finalized','fa01dc4ba57e00197281ff3024385a56','[3,6,7]','2.00','2025-08-22 23:35:45','2025-08-22 23:36:02',NULL),
(759,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:37:58',2.00,'finalized','8637d0768430c15f07e04d7a1545aa09','[]',NULL,'2025-08-22 23:37:58','2025-08-22 23:37:59',NULL),
(760,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:38:00',2.00,'finalized','f84e5b028dd500b70d4c9b30d076e4b5','[]',NULL,'2025-08-22 23:38:00','2025-08-22 23:38:00',NULL),
(761,1,0,'Baú da sorte 🎁','ganhou',10.00,'2025-08-22 23:38:03',2.00,'finalized','21f0172e3ba9c657d3c12092e16bc092','[0,3,6]','10.00','2025-08-22 23:38:03','2025-08-22 23:38:04',NULL),
(762,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:38:11',2.00,'finalized','3d5c6610d49d95fbd03c32b2c955eb85','[4,5,6]','2.00','2025-08-22 23:38:11','2025-08-22 23:38:14',NULL),
(763,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 23:39:12',2.00,'finalized','434b6f44604bfac80586d7e9ac5f6acd','[1,3,6]','5.00','2025-08-22 23:39:12','2025-08-22 23:39:12',NULL),
(764,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:39:22',2.00,'finalized','72708d1e18bd3ffd20d2fd02b83ad735','[2,3,5]','2.00','2025-08-22 23:39:22','2025-08-22 23:39:22',NULL),
(765,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:39:32',2.00,'finalized','9aa1cb3c24fae2448329d635a3514abc','[2,4,8]','2.00','2025-08-22 23:39:32','2025-08-22 23:39:33',NULL),
(766,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:39:37',2.00,'finalized','8146ec88a9a8f94f3c44334fd2bc1f1d','[]',NULL,'2025-08-22 23:39:37','2025-08-22 23:39:37',NULL),
(767,1,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-22 23:39:40',2.00,'finalized','f84ea3e42000abc251abd69b79505916','[2,5,6]','5.00','2025-08-22 23:39:40','2025-08-22 23:39:43',NULL),
(768,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:39:44',2.00,'finalized','e5de0c9b3ea6e8af9221413fcfd47cc3','[0,4,8]','2.00','2025-08-22 23:39:44','2025-08-22 23:39:45',NULL),
(769,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:39:46',2.00,'finalized','7596f2ffad2a4234a5f7d8813347ed6a','[]',NULL,'2025-08-22 23:39:46','2025-08-22 23:39:51',NULL),
(770,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:39:53',2.00,'finalized','38cacce01d4d9941a2fcdb11ef0faf28','[]',NULL,'2025-08-22 23:39:53','2025-08-22 23:39:58',NULL),
(771,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:40:00',2.00,'finalized','da0884bec139ec896e1560ad99d5367c','[0,1,8]','2.00','2025-08-22 23:40:00','2025-08-22 23:40:01',NULL),
(772,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:40:02',2.00,'finalized','2678d1b6bcb8e218ac4b3671aa3b1436','[0,5,6]','2.00','2025-08-22 23:40:02','2025-08-22 23:40:07',NULL),
(773,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:40:09',2.00,'finalized','6d067e2d5a96017775ec8b2112280abf','[]',NULL,'2025-08-22 23:40:09','2025-08-22 23:40:13',NULL),
(774,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:40:24',2.00,'finalized','6db1ccacb31f251e5d70ce15fe857759','[3,4,5]','2.00','2025-08-22 23:40:24','2025-08-22 23:40:25',NULL),
(775,1,0,'Baú da sorte 🎁','ganhou',10.00,'2025-08-22 23:40:26',2.00,'finalized','ebcea20295de2eacd626c74d4ae9d603','[3,4,5]','10.00','2025-08-22 23:40:26','2025-08-22 23:40:27',NULL),
(776,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:40:30',2.00,'finalized','594bf2203c25a48bb0aaa119a6f001c4','[]',NULL,'2025-08-22 23:40:30','2025-08-22 23:40:30',NULL),
(777,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:41:16',2.00,'finalized','f29b2f66d7f0dc30d8ed9db861988b37','[]',NULL,'2025-08-22 23:41:16','2025-08-22 23:41:20',NULL),
(778,1,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-22 23:41:16',10.00,'finalized','445685019378c2633c3624bf58097c64','[0,3,4]','20.00','2025-08-22 23:41:16','2025-08-22 23:41:17',NULL),
(779,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:41:22',10.00,'finalized','de355e828cbd1cb13fcdd4e03c3e871b','[]',NULL,'2025-08-22 23:41:22','2025-08-22 23:41:22',NULL),
(780,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 23:41:22',2.00,'finalized','75b891f935f83889594edb1aee9aeb55','[2,4,6]','1.00','2025-08-22 23:41:22','2025-08-22 23:41:23',NULL),
(781,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:41:24',2.00,'finalized','34c687452859fd277ba030f006b6fef6','[]',NULL,'2025-08-22 23:41:24','2025-08-22 23:41:24',NULL),
(782,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-22 23:41:24',10.00,'finalized','4e91a27d110127b28fe502b555076931','[2,3,6]','5.00','2025-08-22 23:41:24','2025-08-22 23:41:25',NULL),
(783,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:41:25',2.00,'finalized','f4eeed5049f2c2d65b67fe701c8eec81','[]',NULL,'2025-08-22 23:41:25','2025-08-22 23:41:25',NULL),
(784,1,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-22 23:41:26',2.00,'finalized','cc411909655bc61bfb84b3612ac6583e','[]',NULL,'2025-08-22 23:41:26','2025-08-22 23:41:26',NULL),
(785,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-22 23:41:27',10.00,'finalized','fe64d56bf416a4dfb48b330882a7ac62','[]',NULL,'2025-08-22 23:41:27','2025-08-22 23:41:27',NULL),
(786,1,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-22 23:41:33',10.00,'finalized','c49105e817eea4a348a9ba97a2e4eecf','[5,6,7]','10.00','2025-08-22 23:41:33','2025-08-22 23:41:34',NULL),
(787,1,0,'Luxo Máximo 👑','ganhou',100.00,'2025-08-22 23:50:48',50.00,'finalized','30dedea97f786e7b721a1298bd796f07','[0,1,4]','100.00','2025-08-22 23:50:48','2025-08-22 23:50:49',NULL),
(788,1,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 23:50:52',50.00,'finalized','a184f1d4127667dd1f22f8f557642c73','[0,5,8]','2.00','2025-08-22 23:50:52','2025-08-22 23:50:53',NULL),
(789,1,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-22 23:50:55',50.00,'finalized','8e585ec59449be26f8d8621ecec0e688','[0,1,8]','2.00','2025-08-22 23:50:55','2025-08-22 23:50:56',NULL),
(790,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-22 23:50:58',50.00,'finalized','569d3aa219a76567fc9068bd675954ad','[]',NULL,'2025-08-22 23:50:58','2025-08-22 23:50:58',NULL),
(791,1,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-22 23:51:59',1.00,'finalized','f5611bb245b911e15e5f14b91451da7d','[0,3,7]','2.00','2025-08-22 23:51:59','2025-08-22 23:57:27',NULL),
(792,1,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-22 23:57:13',2.00,'finalized','16922809a6672a7976d7e32356bfd496','[2,7,8]','2.00','2025-08-22 23:57:13','2025-08-22 23:57:14',NULL),
(793,1,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-22 23:57:15',2.00,'finalized','be95379dcc46cfde8a09de1d4271df4b','[3,6,7]','1.00','2025-08-22 23:57:15','2025-08-22 23:57:15',NULL),
(794,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 23:57:28',1.00,'finalized','5e438fb204f88bf32d76a70e5bf5dadb','[0,6,7]','1.00','2025-08-22 23:57:28','2025-08-22 23:57:29',NULL),
(795,1,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-22 23:57:30',1.00,'finalized','fc2568c75593154cef26335c0142a9b1','[]',NULL,'2025-08-22 23:57:30','2025-08-22 23:57:31',NULL),
(796,1,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-22 23:57:32',1.00,'finalized','e5caed14a6f5117d876896e10e2b90cc','[1,4,6]','1.00','2025-08-22 23:57:32','2025-08-22 23:57:33',NULL),
(797,1,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-23 01:34:35',5.00,'finalized','2c776309d535f5b75c82bc5044be0ae9','[0,1,4]','5.00','2025-08-23 01:34:35','2025-08-23 01:34:36',NULL),
(798,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-23 01:34:37',5.00,'finalized','d0a681bb1ac16fbbd62bcdd930ce0c95','[]',NULL,'2025-08-23 01:34:37','2025-08-23 01:34:37',NULL),
(799,1,0,'Trono da Prada 👑','ganhou',0.50,'2025-08-23 01:34:39',5.00,'finalized','4331a98d680c9dd8169e198b98437535','[2,3,5]','0.50','2025-08-23 01:34:39','2025-08-23 01:34:49',NULL),
(800,1,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-23 01:34:59',5.00,'finalized','f984ffbb979158f043edec8213e9cf3e','[2,5,6]','2.00','2025-08-23 01:34:59','2025-08-23 01:35:00',NULL),
(801,1,0,'Trono da Prada 👑','ganhou',0.50,'2025-08-23 01:35:01',5.00,'finalized','41cf9d383dd43c7e44a43d7d775c2815','[2,5,6]','0.50','2025-08-23 01:35:01','2025-08-23 01:35:02',NULL),
(802,1,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-23 01:35:10',50.00,'finalized','4f505184d0fa805f6fd1f48733767b66','[0,2,6]','5.00','2025-08-23 01:35:10','2025-08-23 01:35:10',NULL),
(803,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-23 01:35:11',50.00,'finalized','aa8f36e95525d25cd7389967024a48ad','[]',NULL,'2025-08-23 01:35:11','2025-08-23 01:35:11',NULL),
(804,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-23 01:35:13',50.00,'finalized','8ce78c7e84ce01bb2ec4061305a7282b','[]',NULL,'2025-08-23 01:35:13','2025-08-23 01:35:13',NULL),
(805,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-23 01:35:16',50.00,'finalized','b5219192e00d0ea28e775cfb86c4fff9','[0,3,4]','50.00','2025-08-23 01:35:16','2025-08-23 01:35:16',NULL),
(806,1,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-23 01:35:18',50.00,'finalized','49929a1e4a704d57bd4cd7f807180fa3','[5,7,8]','5.00','2025-08-23 01:35:18','2025-08-23 01:35:19',NULL),
(807,1,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-23 01:35:20',50.00,'finalized','935cb76bd74e136bb85a37bbcdce1643','[1,4,8]','50.00','2025-08-23 01:35:20','2025-08-23 01:35:21',NULL),
(808,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-23 01:35:21',50.00,'finalized','4baffc1f50c103818de23c5b438b9529','[]',NULL,'2025-08-23 01:35:21','2025-08-23 01:35:22',NULL),
(809,1,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-23 01:35:23',50.00,'finalized','c7a57ce4cad705fda1ecea1cd4a1a353','[]',NULL,'2025-08-23 01:35:23','2025-08-23 01:35:23',NULL),
(810,1,0,'Luxo Máximo 👑','ganhou',2.00,'2025-08-23 01:35:24',50.00,'finalized','914caffe061e1711bacfe80ce9d51025','[1,5,7]','2.00','2025-08-23 01:35:24','2025-08-23 01:35:24',NULL),
(811,111,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-23 17:13:43',1.00,'finalized','cba7074979876ae6820496acadb06f0c','[0,3,5]','10.00','2025-08-23 17:13:43','2025-08-23 17:13:44',NULL),
(812,111,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-23 17:14:12',1.00,'finalized','1efdf501bb88d195b8b8bc5c8b6cbe1d','[0,3,8]','0.50','2025-08-23 17:14:12','2025-08-23 17:14:13',NULL),
(813,111,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-23 17:14:17',1.00,'finalized','1995cf2ac8977d471f9c1d11af1a3970','[0,1,8]','5.00','2025-08-23 17:14:17','2025-08-23 17:14:17',NULL),
(814,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:14:43',1.00,'finalized','e50a2ec4f9b8bca269cbaa91d83b5d9b','[2,3,4]','2.00','2025-08-23 17:14:43','2025-08-23 17:14:44',NULL),
(815,111,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-23 17:14:48',1.00,'finalized','b2283d4c81338d32d3c19429322a3d4e','[1,2,3]','0.50','2025-08-23 17:14:48','2025-08-23 17:14:48',NULL),
(816,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:14:53',1.00,'finalized','d5de32382dd8b1566ed5e4f36f8c3c47','[0,2,5]','2.00','2025-08-23 17:14:53','2025-08-23 17:14:53',NULL),
(817,111,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-23 17:15:00',1.00,'finalized','f0d5f9674870da18e88b4884a8aad8c5','[1,4,8]','1.00','2025-08-23 17:15:00','2025-08-23 17:15:00',NULL),
(818,111,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-23 17:15:03',1.00,'finalized','3d08daf227ebb377cadd6e82094d7bce','[2,5,6]','1.00','2025-08-23 17:15:03','2025-08-23 17:15:04',NULL),
(819,111,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-23 17:15:06',1.00,'finalized','9a7c67d9552bee68a74dbf91e35b6404','[1,6,8]','10.00','2025-08-23 17:15:06','2025-08-23 17:15:07',NULL),
(820,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:15:24',1.00,'finalized','10f13eebf277e36c618c0ee5d1fe6a63','[]',NULL,'2025-08-23 17:15:24','2025-08-23 17:15:25',NULL),
(821,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:15:29',1.00,'finalized','607e53ae5ad0465aa998229895b961e5','[]',NULL,'2025-08-23 17:15:29','2025-08-23 17:15:29',NULL),
(822,111,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-23 17:15:32',1.00,'finalized','2f12d8c59425563c3d1d31e53a10a6c0','[0,1,3]','0.50','2025-08-23 17:15:32','2025-08-23 17:15:32',NULL),
(823,111,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-23 17:15:36',1.00,'finalized','55f1791f76a904e29283ea447a1ceca8','[1,7,8]','5.00','2025-08-23 17:15:36','2025-08-23 17:15:37',NULL),
(824,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:15:40',1.00,'finalized','341040fcc3586124dab5dbbe2179b1b9','[]',NULL,'2025-08-23 17:15:40','2025-08-23 17:15:40',NULL),
(825,111,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-23 17:15:44',1.00,'finalized','b78850e2059218a6c302f4e093da285a','[5,6,7]','1.00','2025-08-23 17:15:44','2025-08-23 17:15:45',NULL),
(826,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:15:47',1.00,'finalized','e3624d2354a14afb1433a03282376987','[3,5,7]','2.00','2025-08-23 17:15:47','2025-08-23 17:15:48',NULL),
(827,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:15:50',1.00,'finalized','d5e4a7a0acb121aeb6b030f7fe7b3ca6','[]',NULL,'2025-08-23 17:15:50','2025-08-23 17:15:50',NULL),
(828,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:15:53',1.00,'finalized','55dcc8ac3970293f46a5eba491a1c245','[0,1,6]','2.00','2025-08-23 17:15:53','2025-08-23 17:15:54',NULL),
(829,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:09',1.00,'finalized','eb4706da6bcc7ed16ac94853ca05e214','[]',NULL,'2025-08-23 17:16:09','2025-08-23 17:16:09',NULL),
(830,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:13',1.00,'finalized','bc291fe573de87864f8a0105a7eef946','[]',NULL,'2025-08-23 17:16:13','2025-08-23 17:16:14',NULL),
(831,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:16:16',1.00,'finalized','22794b5326c452e486d0f262f3b45387','[2,4,8]','2.00','2025-08-23 17:16:16','2025-08-23 17:16:17',NULL),
(832,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:20',1.00,'finalized','a9ca0401a72665a611885c5934e0f450','[]',NULL,'2025-08-23 17:16:20','2025-08-23 17:16:20',NULL),
(833,111,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-23 17:16:23',1.00,'finalized','ec86c573f68e9fc6f0a47614b1d502c8','[1,4,8]','1.00','2025-08-23 17:16:23','2025-08-23 17:16:24',NULL),
(834,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:26',1.00,'finalized','8e3ef654f043dac60a76824f6291fe9b','[]',NULL,'2025-08-23 17:16:26','2025-08-23 17:16:26',NULL),
(835,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:16:31',1.00,'finalized','e6d9871313575ddd5289e2724c568db7','[2,3,6]','2.00','2025-08-23 17:16:31','2025-08-23 17:16:32',NULL),
(836,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:35',1.00,'finalized','8e2ffcffa03e2d45f081e4e3dfbf4169','[]',NULL,'2025-08-23 17:16:35','2025-08-23 17:16:35',NULL),
(837,111,0,'Cofrinho mágico 🪙','ganhou',10.00,'2025-08-23 17:16:38',1.00,'finalized','b712dcb39c975dafbe8bbbc4133d9f34','[0,3,8]','10.00','2025-08-23 17:16:38','2025-08-23 17:16:39',NULL),
(838,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:53',1.00,'finalized','21819e3a600c56d30fa953b212f0131e','[]',NULL,'2025-08-23 17:16:53','2025-08-23 17:16:54',NULL),
(839,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:16:58',1.00,'finalized','67d93cfca0ceca80d2bdb65ae9d97b6b','[]',NULL,'2025-08-23 17:16:58','2025-08-23 17:16:58',NULL),
(840,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:02',1.00,'finalized','14df9bd21ba050c1c6cf439233af52f3','[]',NULL,'2025-08-23 17:17:02','2025-08-23 17:17:02',NULL),
(841,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:06',1.00,'finalized','cb92da6040aec02cad24f128189ae332','[]',NULL,'2025-08-23 17:17:06','2025-08-23 17:17:07',NULL),
(842,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:11',1.00,'finalized','f32b547bdb9651f576bef4d772f587c3','[]',NULL,'2025-08-23 17:17:11','2025-08-23 17:17:11',NULL),
(843,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:22',1.00,'finalized','280ade589e1510c0fb625ef39c924a9e','[]',NULL,'2025-08-23 17:17:22','2025-08-23 17:17:23',NULL),
(844,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:27',1.00,'finalized','4ba59f6f90a48f439a71be119cbb1521','[]',NULL,'2025-08-23 17:17:27','2025-08-23 17:17:27',NULL),
(845,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:31',1.00,'finalized','09dda5f07c328353eadad7e853a7ccd7','[]',NULL,'2025-08-23 17:17:31','2025-08-23 17:17:31',NULL),
(846,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:34',1.00,'finalized','6c7f122b2d44728fea2310e5f77f3483','[]',NULL,'2025-08-23 17:17:34','2025-08-23 17:17:34',NULL),
(847,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:38',1.00,'finalized','4c0385fadcb6efa4efb26021bdb44bc7','[]',NULL,'2025-08-23 17:17:38','2025-08-23 17:17:38',NULL),
(848,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:17:58',1.00,'finalized','112944c630fa300fdd5058d524a9756f','[]',NULL,'2025-08-23 17:17:58','2025-08-23 17:17:59',NULL),
(849,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:02',1.00,'finalized','454cc1927f814e59aa6b567c94d8888e','[]',NULL,'2025-08-23 17:18:02','2025-08-23 17:18:02',NULL),
(850,111,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-23 17:18:09',1.00,'finalized','0b1a55ba57c29f4f50e99f1f4968afc7','[3,4,7]','5.00','2025-08-23 17:18:09','2025-08-23 17:18:10',NULL),
(851,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:15',1.00,'finalized','c155ddbd05df536a591fa5be2f70d1bf','[]',NULL,'2025-08-23 17:18:15','2025-08-23 17:18:15',NULL),
(852,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:19',1.00,'finalized','0a71fccc06e4f778da44ea9414e5ae3a','[]',NULL,'2025-08-23 17:18:19','2025-08-23 17:18:19',NULL),
(853,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:46',1.00,'finalized','2b77c8097297fa41bd4103d842585fa4','[]',NULL,'2025-08-23 17:18:46','2025-08-23 17:18:46',NULL),
(854,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:50',1.00,'finalized','c64c5e67e432aea98c2456241ec7f261','[]',NULL,'2025-08-23 17:18:50','2025-08-23 17:18:50',NULL),
(855,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:53',1.00,'finalized','02f28b9cbd208704557faf1553d0a4b8','[]',NULL,'2025-08-23 17:18:53','2025-08-23 17:18:53',NULL),
(856,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:18:57',1.00,'finalized','0d4ada47d9f857b7db5fd73bf528b320','[]',NULL,'2025-08-23 17:18:57','2025-08-23 17:18:57',NULL),
(857,111,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-23 17:19:00',1.00,'finalized','cbdbdaad35135ee47538cb6a429a70ad','[2,4,7]','5.00','2025-08-23 17:19:00','2025-08-23 17:19:01',NULL),
(858,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:19:15',1.00,'finalized','62099d7dba24977dc4b291e50cb9442a','[]',NULL,'2025-08-23 17:19:15','2025-08-23 17:19:16',NULL),
(859,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:19:19',1.00,'finalized','e62a6ee3d605806faf05c25c2ba92e37','[]',NULL,'2025-08-23 17:19:19','2025-08-23 17:19:19',NULL),
(860,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:19:24',1.00,'finalized','04dedcd0df1fad3f12d1f5c3f747e3de','[]',NULL,'2025-08-23 17:19:24','2025-08-23 17:19:24',NULL),
(861,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:19:27',1.00,'finalized','83a2bd185642224cc9457994a98f0758','[]',NULL,'2025-08-23 17:19:27','2025-08-23 17:19:27',NULL),
(862,111,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-23 17:19:31',1.00,'finalized','7445958447f185c66919d1f3623d148a','[2,5,6]','2.00','2025-08-23 17:19:31','2025-08-23 17:19:31',NULL),
(863,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:19:49',1.00,'finalized','0f781f464475e8e88fcae2f981d6e6fc','[]',NULL,'2025-08-23 17:19:49','2025-08-23 17:19:49',NULL),
(864,111,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-23 17:20:01',1.00,'finalized','0ba0d7d5affbee219be16ffd09f82b69','[]',NULL,'2025-08-23 17:20:01','2025-08-23 17:20:02',NULL),
(865,112,1,'Trono da Prada 👑','ganhou',1.00,'2025-08-25 18:13:44',5.00,'finalized','7f83ecfba22826eae8303ade9a95590c','[3,7,8]','1.00','2025-08-25 18:13:44','2025-08-25 18:13:45',NULL),
(866,112,1,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 18:13:48',5.00,'finalized','32eb6dc69e7e221047fc7d0e602aa849','[4,6,7]','5.00','2025-08-25 18:13:48','2025-08-25 18:13:49',NULL),
(867,112,1,'Trono da Prada 👑','ganhou',0.50,'2025-08-25 18:13:52',5.00,'finalized','fd2631ed820e5db67b1e95d9add56cc9','[4,5,7]','0.50','2025-08-25 18:13:52','2025-08-25 18:13:54',NULL),
(868,112,1,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 18:13:57',5.00,'finalized','7dfbb0715dfca5c00b6021b8fbf0fc61','[1,2,7]','5.00','2025-08-25 18:13:57','2025-08-25 18:13:58',NULL),
(869,112,1,'Trono da Prada 👑','ganhou',20.00,'2025-08-25 18:14:00',5.00,'finalized','bcf87f5f3315cb7648b9ef2c525bd81b','[5,7,8]','20.00','2025-08-25 18:14:00','2025-08-25 18:14:01',NULL),
(870,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:15:45',30.00,'finalized','0eb1419fdc3bcca309abb5796cfa0dac','[0,2,4]','5.00','2025-08-25 18:15:45','2025-08-25 18:15:46',NULL),
(871,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:15:48',30.00,'finalized','9e19db6e6ceaf5626e9e1eeda21d5d26','[0,1,7]','20.00','2025-08-25 18:15:48','2025-08-25 18:15:49',NULL),
(872,112,1,'Império dourado 🏰','ganhou',50.00,'2025-08-25 18:15:51',30.00,'finalized','6af97825a0b5f31b58f0a73fc2851a26','[0,1,4]','50.00','2025-08-25 18:15:51','2025-08-25 18:15:52',NULL),
(873,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:15:54',30.00,'finalized','e4810f44044ea9cf00dcb39d12b3df7d','[0,3,7]','20.00','2025-08-25 18:15:54','2025-08-25 18:15:55',NULL),
(874,112,1,'Império dourado 🏰','ganhou',10.00,'2025-08-25 18:15:59',30.00,'finalized','e208b8db17a8288c56cd2d942d39e723','[0,1,2]','10.00','2025-08-25 18:15:59','2025-08-25 18:16:00',NULL),
(875,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:06',30.00,'finalized','bcc1afbdfa266d4698c2f379f51409cd','[5,7,8]','5.00','2025-08-25 18:16:06','2025-08-25 18:16:07',NULL),
(876,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:09',30.00,'finalized','77ce2fcea73f49aa425d314f41561b3f','[2,4,8]','5.00','2025-08-25 18:16:09','2025-08-25 18:16:10',NULL),
(877,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:12',30.00,'finalized','2c426cb13e607cfc27d9fa7981315d36','[1,3,7]','5.00','2025-08-25 18:16:12','2025-08-25 18:16:13',NULL),
(878,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:16:17',30.00,'finalized','60fe01f0241a591f6e7c1c27a0a94b9a','[0,1,3]','20.00','2025-08-25 18:16:17','2025-08-25 18:16:18',NULL),
(879,112,1,'Império dourado 🏰','ganhou',10.00,'2025-08-25 18:16:24',30.00,'finalized','4e890b1d4c0d69dbe7be646bad56a826','[0,1,4]','10.00','2025-08-25 18:16:24','2025-08-25 18:16:25',NULL),
(880,112,1,'Império dourado 🏰','ganhou',2.00,'2025-08-25 18:16:27',30.00,'finalized','3ade931e17b19f4ebe7bc83ed48f4925','[0,3,4]','2.00','2025-08-25 18:16:27','2025-08-25 18:16:28',NULL),
(881,112,1,'Império dourado 🏰','ganhou',2.00,'2025-08-25 18:16:29',30.00,'finalized','fc611038b5394c535e2c878d9cac3399','[0,3,7]','2.00','2025-08-25 18:16:29','2025-08-25 18:16:30',NULL),
(882,112,1,'Império dourado 🏰','ganhou',100.00,'2025-08-25 18:16:33',30.00,'finalized','f7faec6c67ce66ca5dfe00f3eb6f50c4','[4,5,6]','100.00','2025-08-25 18:16:33','2025-08-25 18:16:34',NULL),
(883,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:36',30.00,'finalized','42acfc6e82ece0a133872fbd106bcc28','[0,6,8]','5.00','2025-08-25 18:16:36','2025-08-25 18:16:37',NULL),
(884,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:39',30.00,'finalized','ac7b50577001e87380bca24c223b2edc','[2,6,8]','5.00','2025-08-25 18:16:39','2025-08-25 18:16:40',NULL),
(885,112,1,'Império dourado 🏰','ganhou',5.00,'2025-08-25 18:16:42',30.00,'finalized','d38f2fbaffb1098d3f7838ff76e9b477','[1,3,5]','5.00','2025-08-25 18:16:42','2025-08-25 18:16:42',NULL),
(886,112,1,'Império dourado 🏰','ganhou',50.00,'2025-08-25 18:16:44',30.00,'finalized','11ffa736ca162f2d6fdc498322741f04','[0,4,5]','50.00','2025-08-25 18:16:44','2025-08-25 18:16:45',NULL),
(887,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:16:48',30.00,'finalized','b4aeaade03976055f7ca83951f90ec5f','[1,3,5]','20.00','2025-08-25 18:16:48','2025-08-25 18:16:49',NULL),
(888,112,1,'Império dourado 🏰','ganhou',50.00,'2025-08-25 18:16:51',30.00,'finalized','af1f98e532c3622f36b236ad3ce036a8','[6,7,8]','50.00','2025-08-25 18:16:51','2025-08-25 18:16:52',NULL),
(889,112,1,'Luxo Máximo 👑','ganhou',5.00,'2025-08-25 18:17:05',50.00,'finalized','c01f4e280d0262017229e9e73e6582d0','[1,4,7]','5.00','2025-08-25 18:17:05','2025-08-25 18:17:06',NULL),
(890,112,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-25 18:17:08',50.00,'finalized','40d87b20b50ff05c79ccec4b97c10f81','[2,3,8]','200.00','2025-08-25 18:17:08','2025-08-25 18:17:09',NULL),
(891,112,1,'Luxo Máximo 👑','ganhou',5.00,'2025-08-25 18:17:14',50.00,'finalized','3eacc2a426be12c3a37b60d23ca84b16','[1,5,7]','5.00','2025-08-25 18:17:14','2025-08-25 18:17:15',NULL),
(892,112,1,'Luxo Máximo 👑','ganhou',2.00,'2025-08-25 18:17:17',50.00,'finalized','68ad1227058e9ec1e3b67d6844b41a5a','[0,7,8]','2.00','2025-08-25 18:17:17','2025-08-25 18:17:18',NULL),
(893,97,1,'Trono da Prada 👑','ganhou',2.00,'2025-08-25 18:19:02',5.00,'finalized','0eaaa0436a085d7ee8f1bdc1e45a15fb','[5,6,8]','2.00','2025-08-25 18:19:02','2025-08-25 18:19:03',NULL),
(894,97,1,'Trono da Prada 👑','ganhou',1.00,'2025-08-25 18:19:04',5.00,'finalized','5d15dcadceb259cdd87d9c8d91693115','[1,2,5]','1.00','2025-08-25 18:19:04','2025-08-25 18:19:04',NULL),
(895,97,1,'Luxo Máximo 👑','ganhou',2.00,'2025-08-25 18:19:11',50.00,'finalized','c00c8dab231c2761490f2a5cf36f66b4','[0,2,5]','2.00','2025-08-25 18:19:11','2025-08-25 18:19:12',NULL),
(896,97,1,'Luxo Máximo 👑','ganhou',20.00,'2025-08-25 18:19:13',50.00,'finalized','5b4714423e435c6535682841a7ccf5fd','[0,4,5]','20.00','2025-08-25 18:19:13','2025-08-25 18:19:13',NULL),
(897,97,1,'Luxo Máximo 👑','ganhou',50.00,'2025-08-25 18:19:18',50.00,'finalized','6e8433aa4b0c34f97acae26784fbc155','[0,1,8]','50.00','2025-08-25 18:19:18','2025-08-25 18:19:19',NULL),
(898,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:31:44',30.00,'finalized','486145900b14578e8c43d8249ada450b','[4,6,7]','20.00','2025-08-25 18:31:44','2025-08-25 18:31:45',NULL),
(899,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:31:47',30.00,'finalized','f4e1fcdcb370468eaa3b316d8a562c22','[0,5,7]','20.00','2025-08-25 18:31:47','2025-08-25 18:31:48',NULL),
(900,112,1,'Império dourado 🏰','ganhou',20.00,'2025-08-25 18:31:50',30.00,'finalized','620b238f0f16503ce66badcf5f9f8a4c','[0,1,7]','20.00','2025-08-25 18:31:50','2025-08-25 18:31:51',NULL),
(901,112,1,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-25 18:32:08',10.00,'finalized','3035a527bb35cd310a8010568ff2b074','[0,5,8]','5.00','2025-08-25 18:32:08','2025-08-25 18:32:09',NULL),
(902,112,1,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-25 18:32:11',10.00,'finalized','40dd7aa00dcfb26b3f01642579949660','[1,4,6]','10.00','2025-08-25 18:32:11','2025-08-25 18:32:12',NULL),
(903,97,1,'Trono da Prada 👑','ganhou',1.00,'2025-08-25 18:34:56',5.00,'finalized','13f0caf5b8f6fd18f31ea622ed9b569a','[0,4,5]','1.00','2025-08-25 18:34:56','2025-08-25 18:34:59',NULL),
(904,97,1,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-25 18:35:19',10.00,'finalized','d69398b7f3a1ee0a99a72718983777dc','[0,2,8]','2.00','2025-08-25 18:35:19','2025-08-25 18:35:19','c73e5658-855c-4355-945f-92a2ee2e3c83'),
(905,97,1,'Luxo Máximo 👑','ganhou',1.00,'2025-08-25 18:35:30',50.00,'finalized','3a363673777828bc9400b0b61cb26002','[1,5,8]','1.00','2025-08-25 18:35:30','2025-08-25 18:35:31','e9934615-b33c-4a48-a1d7-0fcd845ae0af'),
(906,97,1,'Luxo Máximo 👑','ganhou',20.00,'2025-08-25 18:35:49',50.00,'finalized','4f6f3ae91b32fcce140819c050156b66','[1,4,8]','20.00','2025-08-25 18:35:49','2025-08-25 18:35:50','3d43a15b-2a87-4217-ba74-76b17b6ba8fb'),
(907,97,1,'Luxo Máximo 👑','ganhou',200.00,'2025-08-25 18:35:53',50.00,'finalized','3745f1c5fa497b1d0fa5d0fac0e392ac','[0,2,4]','200.00','2025-08-25 18:35:53','2025-08-25 18:35:53','654167cb-f7ae-43fa-b4b5-952aed7f20b5'),
(908,97,1,'Luxo Máximo 👑','ganhou',20.00,'2025-08-25 18:36:07',50.00,'finalized','e11c2f2f5308ebaa147533aac3ad3880','[0,1,5]','20.00','2025-08-25 18:36:07','2025-08-25 18:36:07',NULL),
(909,97,1,'Luxo Máximo 👑','ganhou',20.00,'2025-08-25 18:36:08',50.00,'finalized','0efa54ab01d1c5eba63e1a72c60ae5d1','[0,6,8]','20.00','2025-08-25 18:36:08','2025-08-25 18:36:26',NULL),
(910,97,1,'Luxo Máximo 👑','ganhou',2.00,'2025-08-25 18:36:26',50.00,'finalized','ebe15f97703fef571c65d6edafeac448','[3,5,8]','2.00','2025-08-25 18:36:26','2025-08-25 18:36:27','67145c63-cc83-4e02-9ab6-e621d901ab24'),
(911,97,1,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 18:36:52',2.00,'finalized','f9bb62e1aeda815bd6396e8145d78d02','[2,3,5]','1.00','2025-08-25 18:36:52','2025-08-25 18:36:53',NULL),
(912,97,1,'Baú da sorte 🎁','ganhou',0.50,'2025-08-25 18:36:54',2.00,'finalized','1dfc8b47a4c41da91da25bfc4afcdce4','[6,7,8]','0.50','2025-08-25 18:36:54','2025-08-25 18:36:54',NULL),
(913,97,1,'Baú da sorte 🎁','ganhou',5.00,'2025-08-25 18:36:55',2.00,'finalized','6ba50f0b456310a550fffe399ca2f88b','[1,2,7]','5.00','2025-08-25 18:36:55','2025-08-25 18:36:56',NULL),
(914,97,1,'Trono da Prada 👑','ganhou',1000.00,'2025-08-25 18:42:42',5.00,'finalized','a281519c552dd635d3ec6193e2e3ac81','[2,4,7]','1000.00','2025-08-25 18:42:42','2025-08-25 18:42:42','1d3c6274-9004-4949-afb8-75b22cff4b30'),
(915,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:42:56',10.00,'finalized','df3975d316b00cce0e4c52ed142ebe5a','[2,7,8]','1000.00','2025-08-25 18:42:56','2025-08-25 18:42:56','2166c1b6-8adc-4f0c-8bcb-479744fe4152'),
(916,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:43:10',10.00,'finalized','57be8aa78ccc357a4f2bfce68fbfa8cc','[4,6,8]','1000.00','2025-08-25 18:43:10','2025-08-25 18:43:11','a1298dcb-e5f4-4c99-9b58-dd9a373f1a4d'),
(917,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:44:12',10.00,'finalized','23935b6db2dc66a84ab3e7ffe06c50be','[0,2,7]','1000.00','2025-08-25 18:44:12','2025-08-25 18:44:12','bf4d024b-567e-4049-ad93-a49ad5659808'),
(918,97,1,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-25 18:44:29',10.00,'finalized','a0e8db22ec738d25381e523cb7089fe8','[5,6,8]','20.00','2025-08-25 18:44:29','2025-08-25 18:44:30','50f94e43-6393-403f-8c04-b4231290f922'),
(919,97,1,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-25 18:44:46',10.00,'finalized','17fe96cdccfb815bc92be4074a4e478d','[0,2,7]','2.00','2025-08-25 18:44:46','2025-08-25 18:44:47',NULL),
(920,97,1,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-25 18:44:48',10.00,'finalized','c80c3abcddf8e646f2011b22be2065b9','[3,6,7]','5.00','2025-08-25 18:44:48','2025-08-25 18:44:49',NULL),
(921,97,1,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-25 18:44:50',10.00,'finalized','2944696e71172f3fc0f9a495331c406e','[5,6,8]','2.00','2025-08-25 18:44:50','2025-08-25 18:44:50',NULL),
(922,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:44:56',10.00,'finalized','07d427fd6a0803182a334b59c856765b','[1,3,7]','1000.00','2025-08-25 18:44:56','2025-08-25 18:44:57',NULL),
(923,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:44:58',10.00,'finalized','f8351b68e90ad941b53b6664c158aa4e','[1,6,7]','1000.00','2025-08-25 18:44:58','2025-08-25 18:44:58',NULL),
(924,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:44:59',10.00,'finalized','878b3f7c0f46f9437ff9e4af9a9d58b5','[1,2,3]','1000.00','2025-08-25 18:44:59','2025-08-25 18:45:00',NULL),
(925,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:45:01',10.00,'finalized','88d14969479154a4d55a67e4ba525b5d','[0,3,8]','1000.00','2025-08-25 18:45:01','2025-08-25 18:45:01',NULL),
(926,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:45:02',10.00,'finalized','8cd9598f1af2b1e77af9ad086b403e04','[5,7,8]','1000.00','2025-08-25 18:45:02','2025-08-25 18:45:03',NULL),
(927,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:45:03',10.00,'finalized','32e880ff822f60b04cbd4b13cb37e7c9','[1,5,7]','1000.00','2025-08-25 18:45:03','2025-08-25 18:45:04',NULL),
(928,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:45:05',10.00,'finalized','6ed2c5f754db9d7191543d07b3d8857e','[0,5,6]','1000.00','2025-08-25 18:45:05','2025-08-25 18:45:05',NULL),
(929,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:45:06',10.00,'finalized','a3800a36ec28087870987afacbb0b469','[0,7,8]','1000.00','2025-08-25 18:45:06','2025-08-25 18:45:07',NULL),
(930,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:46:18',10.00,'finalized','b99898008e219d0193eaf2eb747dcf13','[0,3,6]','1000.00','2025-08-25 18:46:18','2025-08-25 18:46:19',NULL),
(931,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:48:06',10.00,'finalized','e9e2b2c03d315c788d663ce0db96207a','[2,3,8]','1000.00','2025-08-25 18:48:06','2025-08-25 18:48:06',NULL),
(932,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:48:07',10.00,'finalized','8efaf325fb4f2529a8dfe349fbe0523f','[2,5,7]','1000.00','2025-08-25 18:48:07','2025-08-25 18:48:08',NULL),
(933,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:48:09',10.00,'finalized','63092a6e2443428784ea44bb7ca3da98','[1,4,6]','1000.00','2025-08-25 18:48:09','2025-08-25 18:48:10',NULL),
(934,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-25 18:48:11',10.00,'finalized','9acd6022f669aceb9d5d8c71100ca43f','[0,3,6]','5000.00','2025-08-25 18:48:11','2025-08-25 18:48:12',NULL),
(935,97,1,'Tesouro lendário 🏆','ganhou',5000.00,'2025-08-25 18:48:13',10.00,'finalized','35439ba0803e78baf491aabde7c9c3ca','[5,6,7]','5000.00','2025-08-25 18:48:13','2025-08-25 18:48:13',NULL),
(936,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:48:14',10.00,'finalized','cf0f806d1b01214a125af81aa3c1fa17','[1,3,8]','1000.00','2025-08-25 18:48:14','2025-08-25 18:48:15',NULL),
(937,97,1,'Tesouro lendário 🏆','ganhou',30000.00,'2025-08-25 18:48:16',10.00,'finalized','d85d9e783fdf4b4a695c26c2c2c47508','[4,7,8]','30000.00','2025-08-25 18:48:16','2025-08-25 18:48:17',NULL),
(938,97,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 18:49:04',5.00,'finalized','5bb794a6e3605817cbb3a981dea0c398','[0,2,7]','5.00','2025-08-25 18:49:04','2025-08-25 18:49:05',NULL),
(939,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 18:49:06',5.00,'finalized','216c5168c321c694b14230ee846a8de2','[]',NULL,'2025-08-25 18:49:06','2025-08-25 18:49:06',NULL),
(940,97,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-25 18:49:07',5.00,'finalized','dd07cdd48d89b676325bf9b36c3f4a17','[2,5,7]','1.00','2025-08-25 18:49:07','2025-08-25 18:49:08',NULL),
(941,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 18:49:09',5.00,'finalized','10cd4d9bed2091922799a11284b42fd8','[]',NULL,'2025-08-25 18:49:09','2025-08-25 18:49:09',NULL),
(942,97,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-25 18:49:10',5.00,'finalized','012e99a07e0859deabfc2bc80f6b28a7','[1,4,5]','10.00','2025-08-25 18:49:10','2025-08-25 18:49:11',NULL),
(943,97,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-25 18:49:12',5.00,'finalized','514f255f30ff90b7d16a7f52414a19cd','[2,3,6]','10.00','2025-08-25 18:49:12','2025-08-25 18:49:12',NULL),
(944,97,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 18:49:13',5.00,'finalized','a914da4af396987e0cb3921dc899eef4','[0,2,6]','5.00','2025-08-25 18:49:13','2025-08-25 18:49:14',NULL),
(945,97,0,'Luxo Máximo 👑','ganhou',20.00,'2025-08-25 18:49:19',50.00,'finalized','014e31f0ed3e6a9d88e7868b1743028f','[2,4,8]','20.00','2025-08-25 18:49:19','2025-08-25 18:49:20',NULL),
(946,97,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-25 18:49:20',50.00,'finalized','f0b79a52c9051280809119baa50fc4d5','[0,1,4]','50.00','2025-08-25 18:49:20','2025-08-25 18:49:21',NULL),
(947,97,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-25 18:49:22',50.00,'finalized','13fef8492c42f3ad5430ebb8ef4a7160','[]',NULL,'2025-08-25 18:49:22','2025-08-25 18:49:22',NULL),
(948,97,1,'Luxo Máximo 👑','ganhou',5000.00,'2025-08-25 18:49:55',50.00,'finalized','6b195c8e55a87aa1b78d9a8ea4b295aa','[0,1,8]','5000.00','2025-08-25 18:49:55','2025-08-25 18:49:55',NULL),
(949,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-25 18:49:56',50.00,'finalized','d054d57f9e64a4ba5208282f7cba9de3','[0,5,7]','1000.00','2025-08-25 18:49:56','2025-08-25 18:49:57',NULL),
(950,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-25 18:49:57',50.00,'finalized','6903496d2b2f257baf5a5736d7ea28b5','[0,6,8]','1000.00','2025-08-25 18:49:57','2025-08-25 18:49:58',NULL),
(951,97,1,'Luxo Máximo 👑','ganhou',5000.00,'2025-08-25 18:49:59',50.00,'finalized','8e075145f07933f05790d96362a7b7a4','[1,2,7]','5000.00','2025-08-25 18:49:59','2025-08-25 18:50:00',NULL),
(952,97,1,'Luxo Máximo 👑','ganhou',1000.00,'2025-08-25 18:50:01',50.00,'finalized','507d3fcf60213eec1efb0babcb395476','[4,7,8]','1000.00','2025-08-25 18:50:01','2025-08-25 18:50:01',NULL),
(953,97,1,'Luxo Máximo 👑','ganhou',30000.00,'2025-08-25 18:50:40',50.00,'finalized','7ec96eefb0a8d972bbf729ac967ce087','[5,6,7]','30000.00','2025-08-25 18:50:40','2025-08-25 18:50:41',NULL),
(954,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:51:47',10.00,'finalized','14986ebd2ed45611c02e6fe3fdb69878','[2,5,8]','1000.00','2025-08-25 18:51:47','2025-08-25 18:51:48',NULL),
(955,97,1,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-25 18:51:48',10.00,'finalized','77ee97125865f53d0a21036919dc1760','[]',NULL,'2025-08-25 18:51:48','2025-08-25 18:51:48',NULL),
(956,97,1,'Tesouro lendário 🏆','ganhou',1000.00,'2025-08-25 18:51:50',10.00,'finalized','d67572efad5e51b9e6b7c423e09142e3','[3,6,8]','1000.00','2025-08-25 18:51:50','2025-08-25 18:51:50',NULL),
(957,97,1,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-25 18:51:51',10.00,'finalized','40860d303b477f11f062981923946185','[]',NULL,'2025-08-25 18:51:51','2025-08-25 18:51:51',NULL),
(958,97,1,'Baú da sorte 🎁','ganhou',5000.00,'2025-08-25 18:52:01',2.00,'finalized','952a0d1387823c7bf8a1d1d8e671cba8','[4,6,7]','5000.00','2025-08-25 18:52:01','2025-08-25 18:52:02',NULL),
(959,97,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-25 18:52:03',2.00,'finalized','c5a6fbbaf548b5bb00406113ed08c7ec','[1,7,8]','1000.00','2025-08-25 18:52:03','2025-08-25 18:52:04',NULL),
(960,97,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-25 18:52:04',2.00,'finalized','0a2705f78b195e40e7436c280d4a7429','[4,6,7]','1000.00','2025-08-25 18:52:04','2025-08-25 18:52:05',NULL),
(961,97,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-25 18:52:06',2.00,'finalized','1f5a0bd384987f0b6149c2f7088600ec','[1,3,5]','1000.00','2025-08-25 18:52:06','2025-08-25 18:52:06',NULL),
(962,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 18:52:07',2.00,'finalized','ced9c668fa5771c455301f014f3a08d5','[]',NULL,'2025-08-25 18:52:07','2025-08-25 18:52:07',NULL),
(963,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 18:52:09',2.00,'finalized','726a4a53caec5a843145d0b30abf6a65','[]',NULL,'2025-08-25 18:52:09','2025-08-25 18:52:09',NULL),
(964,97,1,'Baú da sorte 🎁','ganhou',5000.00,'2025-08-25 18:52:10',2.00,'finalized','a01a91e52605ed521d576ae231285b21','[3,4,5]','5000.00','2025-08-25 18:52:10','2025-08-25 18:52:11',NULL),
(965,97,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-25 18:52:12',2.00,'finalized','fe520bcc1774ddd2b411dc5d61b56843','[2,4,8]','1000.00','2025-08-25 18:52:12','2025-08-25 18:52:13',NULL),
(966,97,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-25 18:52:13',2.00,'finalized','1c9daffc9bfff37bdb8b3a01a38ecac9','[3,7,8]','1000.00','2025-08-25 18:52:13','2025-08-25 18:52:14',NULL),
(967,97,1,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 18:52:15',2.00,'finalized','df20ee58a071987e8e1b8094c8e6a6ee','[]',NULL,'2025-08-25 18:52:15','2025-08-25 18:52:15',NULL),
(968,112,1,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-25 19:30:15',10.00,'finalized','306d5bf2c4c23861cb11043b1677e462','[]',NULL,'2025-08-25 19:30:15','2025-08-25 19:30:16',NULL),
(969,112,1,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-25 19:30:20',10.00,'finalized','5d2d7163a4db4cd2c02de4ba30a20566','[]',NULL,'2025-08-25 19:30:20','2025-08-25 19:30:20',NULL),
(970,112,1,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-25 19:30:23',10.00,'finalized','8b66b27e1070b579da15a255a049ca8e','[]',NULL,'2025-08-25 19:30:23','2025-08-25 19:30:23',NULL),
(971,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:30:42',30.00,'finalized','318e1b9b8885e870a70fc0df2e5fc9a6','[]',NULL,'2025-08-25 19:30:42','2025-08-25 19:30:55',NULL),
(972,97,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-25 19:30:43',50.00,'finalized','1f6d18151bf7730fec6d5b0b88ca2013','[]',NULL,'2025-08-25 19:30:43','2025-08-25 19:30:43',NULL),
(973,97,0,'Luxo Máximo 👑','ganhou',10.00,'2025-08-25 19:30:44',50.00,'finalized','6bbe1bc95b990849a5219a5b063c5356','[3,5,6]','10.00','2025-08-25 19:30:44','2025-08-25 19:30:45',NULL),
(974,97,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-25 19:30:50',2.00,'finalized','98c17b1a06edceb8769253f6060ee357','[2,6,8]','0.50','2025-08-25 19:30:50','2025-08-25 19:30:50',NULL),
(975,97,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-25 19:30:51',2.00,'finalized','2c9cd5459e6a28cb66a74e1d14693e3b','[2,3,7]','0.50','2025-08-25 19:30:51','2025-08-25 19:30:52',NULL),
(976,97,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 19:30:53',2.00,'finalized','0ecddb663bc24b3b4d8864c494f26ef8','[0,5,7]','1.00','2025-08-25 19:30:53','2025-08-25 19:30:53',NULL),
(977,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:54',2.00,'finalized','ffbb69bb277c31eaf66e722f51dcbed2','[]',NULL,'2025-08-25 19:30:54','2025-08-25 19:30:54',NULL),
(978,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:55',2.00,'finalized','063de1c029bdd6d8170157b34d1d842f','[]',NULL,'2025-08-25 19:30:55','2025-08-25 19:30:55',NULL),
(979,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:56',2.00,'finalized','e0feb29c057eefd438139778ac7206bc','[]',NULL,'2025-08-25 19:30:56','2025-08-25 19:30:56',NULL),
(980,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:57',2.00,'finalized','685d79ba0fef3e7766f742c1d9150848','[]',NULL,'2025-08-25 19:30:57','2025-08-25 19:30:57',NULL),
(981,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:30:57',30.00,'finalized','2326ec49955113135cce4a9e312088ff','[]',NULL,'2025-08-25 19:30:57','2025-08-25 19:30:58',NULL),
(982,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:58',2.00,'finalized','3593f678f02f710d2f95dbf64dd20b46','[]',NULL,'2025-08-25 19:30:58','2025-08-25 19:30:58',NULL),
(983,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:30:58',2.00,'finalized','aa41b66abe075aa4da853867140a9def','[]',NULL,'2025-08-25 19:30:58','2025-08-25 19:30:58',NULL),
(984,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:00',30.00,'finalized','883d5bf3596159b1269924cfa51fbcc2','[]',NULL,'2025-08-25 19:31:00','2025-08-25 19:31:00',NULL),
(985,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:02',30.00,'finalized','bd2e23b60f3adc6f46f80b11ad28848a','[]',NULL,'2025-08-25 19:31:02','2025-08-25 19:31:02',NULL),
(986,97,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 19:31:02',2.00,'finalized','df025f45f80e80313395feb0148575be','[1,3,7]','1.00','2025-08-25 19:31:02','2025-08-25 19:31:03',NULL),
(987,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:31:04',2.00,'finalized','9ec093bb303683be745b6592e5b5259b','[]',NULL,'2025-08-25 19:31:04','2025-08-25 19:31:04',NULL),
(988,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:05',30.00,'finalized','19ac9a33456de8b3d1f84006e69784b9','[]',NULL,'2025-08-25 19:31:05','2025-08-25 19:31:16',NULL),
(989,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:31:05',2.00,'finalized','e0c004be409fcd4eef9fa41c2809a910','[]',NULL,'2025-08-25 19:31:05','2025-08-25 19:31:05',NULL),
(990,97,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 19:31:06',2.00,'finalized','472e16c0512ab1f39303eb94d46b6138','[0,1,8]','1.00','2025-08-25 19:31:06','2025-08-25 19:31:07',NULL),
(991,97,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-25 19:31:08',2.00,'finalized','ce0c35a411d1fe0688c7fbc3f30a7590','[1,3,5]','2.00','2025-08-25 19:31:08','2025-08-25 19:31:09',NULL),
(992,97,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 19:31:10',2.00,'finalized','b34ab205f45232ab8ba5422fceda263e','[0,4,6]','1.00','2025-08-25 19:31:10','2025-08-25 19:31:11',NULL),
(993,97,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-25 19:31:12',2.00,'finalized','62ee51d33c1f1006e5e5c6c431f15163','[2,4,6]','0.50','2025-08-25 19:31:12','2025-08-25 19:31:12',NULL),
(994,97,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-25 19:31:13',2.00,'finalized','3c687bfad31a6af51bbd1d02a5a7b581','[4,5,8]','0.50','2025-08-25 19:31:13','2025-08-25 19:31:14',NULL),
(995,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:21',30.00,'finalized','cff49a4eb74744e3f865df32b1f1ed0f','[]',NULL,'2025-08-25 19:31:21','2025-08-25 19:31:23',NULL),
(996,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:25',30.00,'finalized','fef94ee5c673b7e131ebdc11cea58474','[]',NULL,'2025-08-25 19:31:25','2025-08-25 19:31:26',NULL),
(997,112,1,'Império dourado 🏰','perdeu',0.00,'2025-08-25 19:31:27',30.00,'finalized','f3759533a219cc2b8c4a9faacc0a0411','[]',NULL,'2025-08-25 19:31:27','2025-08-25 19:31:28',NULL),
(998,112,1,'Império dourado 🏰','ganhou',15000.00,'2025-08-25 19:31:29',30.00,'finalized','773e55c12ecdce8fa95d5c6db63f27cb','[0,4,6]','15000.00','2025-08-25 19:31:29','2025-08-25 19:31:30',NULL),
(999,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:56:25',1.00,'finalized','7846f9b774b543c6d6f3e22778f7d2c1','[1,4,7]','1.00','2025-08-25 19:56:25','2025-08-25 19:56:29',NULL),
(1000,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:56:34',1.00,'finalized','5a67de587fa32d7f40101415dd689dbc','[2,4,7]','1.00','2025-08-25 19:56:34','2025-08-25 19:56:38',NULL),
(1001,115,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 19:56:43',1.00,'finalized','b028d0ae98d85d3623e65fff4197126b','[1,4,7]','2.00','2025-08-25 19:56:43','2025-08-25 19:56:47',NULL),
(1002,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:56:51',1.00,'finalized','730e26a6b0979a8055eb77150ea6290f','[]',NULL,'2025-08-25 19:56:51','2025-08-25 19:56:57',NULL),
(1003,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:57:01',1.00,'finalized','8d37002a1376d2c9a1823e06994c9145','[]',NULL,'2025-08-25 19:57:01','2025-08-25 19:57:07',NULL),
(1004,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:57:11',1.00,'finalized','6bbf9c7d671d0e365585843e55789782','[]',NULL,'2025-08-25 19:57:11','2025-08-25 19:57:18',NULL),
(1005,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:57:21',1.00,'finalized','6333e5c0b23a5af420b510ce53f1948a','[0,1,7]','1.00','2025-08-25 19:57:21','2025-08-25 19:57:25',NULL),
(1006,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:57:29',1.00,'finalized','3db06926d308df1860809744384b935c','[0,2,6]','1.00','2025-08-25 19:57:29','2025-08-25 19:57:33',NULL),
(1007,115,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 19:57:36',1.00,'finalized','cb7def869920fec7d305733698c49271','[3,5,6]','2.00','2025-08-25 19:57:36','2025-08-25 19:57:41',NULL),
(1008,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:57:44',1.00,'finalized','99ce0dcc9f788ad25befc9d05d2131b9','[]',NULL,'2025-08-25 19:57:44','2025-08-25 19:57:53',NULL),
(1009,115,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 19:57:55',1.00,'finalized','2c0b1caea258c34206f163bae3143bf7','[1,7,8]','2.00','2025-08-25 19:57:55','2025-08-25 19:58:01',NULL),
(1010,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:58:04',1.00,'finalized','7065d8466492e65fcb5a18acb6e87089','[]',NULL,'2025-08-25 19:58:04','2025-08-25 19:58:16',NULL),
(1011,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:58:21',1.00,'finalized','ff6c1916113ea839d25e069767a205d0','[1,2,5]','1.00','2025-08-25 19:58:21','2025-08-25 19:58:27',NULL),
(1012,115,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 19:58:40',2.00,'finalized','f6e257db1b84eaffd74847dcf3b20fb2','[]',NULL,'2025-08-25 19:58:40','2025-08-25 19:58:47',NULL),
(1013,115,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-25 19:58:51',2.00,'finalized','2ddd0c1d66e693bdd91d3c153f90687c','[5,6,7]','2.00','2025-08-25 19:58:51','2025-08-25 19:58:56',NULL),
(1014,115,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-25 19:58:59',2.00,'finalized','7deafc1fdbc6d15170e25a1cf7a04a2c','[6,7,8]','2.00','2025-08-25 19:58:59','2025-08-25 19:59:00',NULL),
(1015,115,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-25 19:59:02',2.00,'finalized','fa99b595fb8b26c09fc93043945813cd','[1,5,7]','1.00','2025-08-25 19:59:02','2025-08-25 19:59:03',NULL),
(1016,115,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 19:59:15',1.00,'finalized','7ee32da8d9358db0311b83084cd2d6f5','[2,4,8]','2.00','2025-08-25 19:59:15','2025-08-25 19:59:16',NULL),
(1017,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:59:18',1.00,'finalized','7d0c9cc506c9c7f76f827084da1b248b','[]',NULL,'2025-08-25 19:59:18','2025-08-25 19:59:18',NULL),
(1018,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:59:21',1.00,'finalized','40641b9cac25ef6c9ca97ea303f8b84f','[]',NULL,'2025-08-25 19:59:21','2025-08-25 19:59:22',NULL),
(1019,115,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 19:59:25',1.00,'finalized','3eee99f7c66c6599a97a2baec5f6f87d','[0,1,6]','1.00','2025-08-25 19:59:25','2025-08-25 19:59:25',NULL),
(1020,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:59:27',1.00,'finalized','8b728028d4475c664f235358543ca6c8','[]',NULL,'2025-08-25 19:59:27','2025-08-25 19:59:28',NULL),
(1021,115,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-25 19:59:30',1.00,'finalized','84d6bce4f99dc5846f2ed9d3b6069eae','[0,2,8]','0.50','2025-08-25 19:59:30','2025-08-25 19:59:31',NULL),
(1022,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:59:33',1.00,'finalized','1150d4d512cd92ef0cfee85f6f03d02a','[]',NULL,'2025-08-25 19:59:33','2025-08-25 19:59:34',NULL),
(1023,115,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 19:59:37',1.00,'finalized','cfad4c53f3a1647cb856cf5724011407','[]',NULL,'2025-08-25 19:59:37','2025-08-25 19:59:38',NULL),
(1024,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:16:02',1.00,'finalized','84e2181f07b359efa8341bd0f4fd1e90','[0,2,6]','1.00','2025-08-25 20:16:02','2025-08-25 20:16:03',NULL),
(1025,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:16:08',1.00,'finalized','688539701313759a1c0d3e4b05b5ba74','[1,6,7]','1.00','2025-08-25 20:16:08','2025-08-25 20:16:08',NULL),
(1026,117,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 20:16:13',1.00,'finalized','739f8c8a4d9de4c026c71ebb0ca30665','[0,5,7]','2.00','2025-08-25 20:16:13','2025-08-25 20:16:14',NULL),
(1027,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:16:21',1.00,'finalized','455d7f5879effac6118f5fdc9c625678','[1,3,6]','1.00','2025-08-25 20:16:21','2025-08-25 20:16:22',NULL),
(1028,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:25',1.00,'finalized','17babda65a31724498cb0c7c60c87359','[]',NULL,'2025-08-25 20:16:25','2025-08-25 20:16:26',NULL),
(1029,117,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-25 20:16:30',1.00,'finalized','e5fc46f804e77913c1c13a7d0c88a2d3','[1,2,3]','5.00','2025-08-25 20:16:30','2025-08-25 20:16:31',NULL),
(1030,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:36',1.00,'finalized','87ab719b9a80da69ca51129775854063','[]',NULL,'2025-08-25 20:16:36','2025-08-25 20:16:37',NULL),
(1031,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:41',1.00,'finalized','87c59d4944e1333c1d63282e949422d4','[]',NULL,'2025-08-25 20:16:41','2025-08-25 20:16:41',NULL),
(1032,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:45',1.00,'finalized','a4327b3045395d6eb0be3605090f080c','[]',NULL,'2025-08-25 20:16:45','2025-08-25 20:16:45',NULL),
(1033,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:16:48',1.00,'finalized','326b90607e94a42476c5ad617e57204b','[1,2,7]','1.00','2025-08-25 20:16:48','2025-08-25 20:16:49',NULL),
(1034,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:53',1.00,'finalized','4c662c0c9aa1deeb32162c561b979822','[]',NULL,'2025-08-25 20:16:53','2025-08-25 20:16:53',NULL),
(1035,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:16:57',1.00,'finalized','fd4eea23dd1370d8561a71a7e407c2be','[]',NULL,'2025-08-25 20:16:57','2025-08-25 20:16:57',NULL),
(1036,117,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-25 20:17:00',1.00,'finalized','9cb1034de7db16c8cafc7f399b2c3583','[4,5,6]','0.50','2025-08-25 20:17:00','2025-08-25 20:17:01',NULL),
(1037,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:17:04',1.00,'finalized','ea118743841f9fe8f552d56790842fa5','[4,5,7]','1.00','2025-08-25 20:17:04','2025-08-25 20:17:05',NULL),
(1038,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:17:08',1.00,'finalized','688b8529ac3ea63f49676cb992a4a12e','[4,7,8]','1.00','2025-08-25 20:17:08','2025-08-25 20:17:09',NULL),
(1039,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:11',1.00,'finalized','0d638ffa27bbc4f6cacc83dc9b525621','[]',NULL,'2025-08-25 20:17:11','2025-08-25 20:17:12',NULL),
(1040,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:17:15',1.00,'finalized','d59af845059ca8a74beb3ad3452d220f','[1,2,8]','1.00','2025-08-25 20:17:15','2025-08-25 20:17:16',NULL),
(1041,117,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 20:17:19',1.00,'finalized','170cafb84d0470582e148d61d49dddd7','[0,4,8]','2.00','2025-08-25 20:17:19','2025-08-25 20:17:20',NULL),
(1042,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:22',1.00,'finalized','d66367f0b6a2912bd8695ad49d1400d7','[]',NULL,'2025-08-25 20:17:22','2025-08-25 20:17:22',NULL),
(1043,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:25',1.00,'finalized','15279d2adce760496eec09f973efb60d','[]',NULL,'2025-08-25 20:17:25','2025-08-25 20:17:25',NULL),
(1044,117,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-25 20:17:28',1.00,'finalized','1c2f6ca38474232d230f1fdc009941d1','[1,3,5]','2.00','2025-08-25 20:17:28','2025-08-25 20:17:29',NULL),
(1045,117,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-25 20:17:31',1.00,'finalized','d1c00f20bf3c31ace62d736f34be5613','[2,3,7]','0.50','2025-08-25 20:17:31','2025-08-25 20:17:32',NULL),
(1046,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:34',1.00,'finalized','3aa9294fa4c4d2c741405c3e04093c5f','[]',NULL,'2025-08-25 20:17:34','2025-08-25 20:17:35',NULL),
(1047,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:17:37',1.00,'finalized','970f62fa70b96ce5dd9ab010457b89cd','[2,4,6]','1.00','2025-08-25 20:17:37','2025-08-25 20:17:38',NULL),
(1048,117,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-25 20:17:40',1.00,'finalized','521f910fed2d603733b7173f1d83b6d8','[4,6,8]','1.00','2025-08-25 20:17:40','2025-08-25 20:17:41',NULL),
(1049,117,0,'Cofrinho mágico 🪙','ganhou',50.00,'2025-08-25 20:17:43',1.00,'finalized','833ce3430a4e3d7e02bfe55fed0979f1','[0,6,8]','50.00','2025-08-25 20:17:43','2025-08-25 20:17:44',NULL),
(1050,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:46',1.00,'finalized','c3166dfd1ca4ee46e33f7c73fb509067','[]',NULL,'2025-08-25 20:17:46','2025-08-25 20:17:46',NULL),
(1051,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:17:49',1.00,'finalized','2c0cfb1a5349d19f416339db58d8b9de','[]',NULL,'2025-08-25 20:17:49','2025-08-25 20:17:49',NULL),
(1052,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:16',1.00,'finalized','28e13fcd8d2a896bad541674343a8104','[]',NULL,'2025-08-25 20:19:16','2025-08-25 20:19:17',NULL),
(1053,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:29',1.00,'finalized','0e15e431cfa306b3928011c5a95be3cc','[]',NULL,'2025-08-25 20:19:29','2025-08-25 20:19:29',NULL),
(1054,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:33',1.00,'finalized','4ec8714669545b34305aa5ed584e5c43','[]',NULL,'2025-08-25 20:19:33','2025-08-25 20:19:33',NULL),
(1055,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:36',1.00,'finalized','c3d938b5aaafa747d7c79a395b883ca8','[]',NULL,'2025-08-25 20:19:36','2025-08-25 20:19:37',NULL),
(1056,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:40',1.00,'finalized','015e0cc0f703bb79fd024635483aadda','[]',NULL,'2025-08-25 20:19:40','2025-08-25 20:19:41',NULL),
(1057,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:44',1.00,'finalized','d848994a962b642e859bf36b784160bf','[]',NULL,'2025-08-25 20:19:44','2025-08-25 20:19:45',NULL),
(1058,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:49',1.00,'finalized','e6a56848ebbfe0dcc34f5c9f780c9c38','[]',NULL,'2025-08-25 20:19:49','2025-08-25 20:19:50',NULL),
(1059,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:54',1.00,'finalized','e987092e26df62bf4a491739094a43f1','[]',NULL,'2025-08-25 20:19:54','2025-08-25 20:19:54',NULL),
(1060,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:19:58',1.00,'finalized','e876720fff5896c339f56a9590df3997','[]',NULL,'2025-08-25 20:19:58','2025-08-25 20:19:59',NULL),
(1061,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:20:02',1.00,'finalized','2400cd81ed5e0612439ef80bf86aa283','[]',NULL,'2025-08-25 20:20:02','2025-08-25 20:20:03',NULL),
(1062,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:20:06',1.00,'finalized','069ec0cdb0aefa00d0b06e84e439f979','[]',NULL,'2025-08-25 20:20:06','2025-08-25 20:20:07',NULL),
(1063,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:20:10',1.00,'finalized','ec25feea7af1bb597d823aada19a7e9a','[]',NULL,'2025-08-25 20:20:10','2025-08-25 20:20:10',NULL),
(1064,117,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:20:15',1.00,'finalized','2cfd15579c1c7ffcb47a8facd4f78c83','[]',NULL,'2025-08-25 20:20:15','2025-08-25 20:20:15',NULL),
(1065,107,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 20:32:54',5.00,'finalized','ecdd11408eb942b433951213f0c88171','[3,4,8]','5.00','2025-08-25 20:32:54','2025-08-25 20:32:55',NULL),
(1066,107,0,'Trono da Prada 👑','ganhou',20.00,'2025-08-25 20:32:58',5.00,'finalized','ec4c17bb80b5d09d2b5006bb48da9371','[0,2,3]','20.00','2025-08-25 20:32:58','2025-08-25 20:32:59',NULL),
(1067,107,0,'Trono da Prada 👑','ganhou',50.00,'2025-08-25 20:33:02',5.00,'finalized','04111f959aa99103ccf20dbdff0d7deb','[2,5,7]','50.00','2025-08-25 20:33:02','2025-08-25 20:33:02',NULL),
(1068,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:33:05',5.00,'finalized','bf2b659d719c81224a0285336981b6b7','[]',NULL,'2025-08-25 20:33:05','2025-08-25 20:33:05',NULL),
(1069,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:33:08',5.00,'finalized','be9d5bb52c7bc77d24681c2cc1956073','[]',NULL,'2025-08-25 20:33:08','2025-08-25 20:33:08',NULL),
(1070,107,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:33:10',5.00,'finalized','23bd61747a89ab73e729434cc78e31ca','[]',NULL,'2025-08-25 20:33:10','2025-08-25 20:33:10',NULL),
(1071,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:34:21',1.00,'finalized','14c6913153f702f704649875cd92e239','[]',NULL,'2025-08-25 20:34:21','2025-08-25 20:35:21',NULL),
(1072,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:35:33',1.00,'finalized','7abaff45a6d628da0d5332deb9433260','[]',NULL,'2025-08-25 20:35:33','2025-08-25 20:35:33',NULL),
(1073,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:35:41',1.00,'finalized','b2f99cbd7f87ff08a2bd860eff200fb0','[]',NULL,'2025-08-25 20:35:41','2025-08-25 20:35:42',NULL),
(1074,119,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 20:35:56',2.00,'finalized','739f09eab5ed2fc1a0eb8865efd53793','[]',NULL,'2025-08-25 20:35:56','2025-08-25 20:35:57',NULL),
(1075,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:36:12',1.00,'finalized','7763c9c91fae4358c7d31279df472568','[]',NULL,'2025-08-25 20:36:12','2025-08-25 20:36:22',NULL),
(1076,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:36:27',1.00,'finalized','002b9e520bd2cd669632be074e5563c0','[]',NULL,'2025-08-25 20:36:27','2025-08-25 20:36:27',NULL),
(1077,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:36:31',1.00,'finalized','18dde4aace3bcdec7b7f3a26641a8280','[]',NULL,'2025-08-25 20:36:31','2025-08-25 20:36:32',NULL),
(1078,119,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 20:36:38',1.00,'finalized','6ecb9f7d6863825fb935da80321cf50a','[]',NULL,'2025-08-25 20:36:38','2025-08-25 20:36:38',NULL),
(1079,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:39:55',5.00,'finalized','ffb7fef682a1c414984e89e8e7a551c2','[]',NULL,'2025-08-25 20:39:55','2025-08-25 20:39:55',NULL),
(1080,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:39:57',5.00,'finalized','4af29dc90004eace735688cff18160fc','[]',NULL,'2025-08-25 20:39:57','2025-08-25 20:39:57',NULL),
(1081,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:39:59',5.00,'finalized','c491946fc2c545691324544b2dbe9452','[]',NULL,'2025-08-25 20:39:59','2025-08-25 20:39:59',NULL),
(1082,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:01',5.00,'finalized','fe1bcc54180d135bea2770d8de5a3da2','[]',NULL,'2025-08-25 20:40:01','2025-08-25 20:40:01',NULL),
(1083,112,1,'Trono da Prada 👑','ganhou',1000.00,'2025-08-25 20:40:02',5.00,'finalized','ac97326896fd4e427b80d36f384f82ac','[1,6,7]','1000.00','2025-08-25 20:40:02','2025-08-25 20:40:03',NULL),
(1084,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:08',5.00,'finalized','1c62d696c92d0548ba62790af509706d','[]',NULL,'2025-08-25 20:40:08','2025-08-25 20:40:08',NULL),
(1085,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:09',5.00,'finalized','f206cfd2e514866a549cbca8a3357bb5','[]',NULL,'2025-08-25 20:40:09','2025-08-25 20:40:09',NULL),
(1086,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:10',5.00,'finalized','42d43765e0b75a569e0a5983f498f475','[]',NULL,'2025-08-25 20:40:10','2025-08-25 20:40:11',NULL),
(1087,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:12',5.00,'finalized','2309f637358d9dc5ffd1e5e00ce164cb','[]',NULL,'2025-08-25 20:40:12','2025-08-25 20:40:12',NULL),
(1088,112,1,'Trono da Prada 👑','perdeu',0.00,'2025-08-25 20:40:13',5.00,'finalized','7fd86d4de10bc86912a07c1c3c0c26af','[]',NULL,'2025-08-25 20:40:13','2025-08-25 20:40:13',NULL),
(1089,112,1,'Trono da Prada 👑','ganhou',5000.00,'2025-08-25 20:40:14',5.00,'finalized','07d7b2d5d4b23e5bb7cfbbbe2742c45f','[2,3,4]','5000.00','2025-08-25 20:40:14','2025-08-25 20:40:15',NULL),
(1090,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:43:58',1.00,'finalized','a16e29a58a190498722c7b15e92913b1','[]',NULL,'2025-08-25 21:43:58','2025-08-25 21:44:08',NULL),
(1091,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:44:17',1.00,'finalized','2e07efb52b9e5334375087478998f14b','[]',NULL,'2025-08-25 21:44:17','2025-08-25 21:44:32',NULL),
(1092,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:44:41',1.00,'finalized','9cc8f25780c592ec106a275ed7bc974a','[]',NULL,'2025-08-25 21:44:41','2025-08-25 21:44:49',NULL),
(1093,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:44:57',1.00,'finalized','e3873c74388849d69174840cead85fe2','[]',NULL,'2025-08-25 21:44:57','2025-08-25 21:45:05',NULL),
(1094,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:45:17',1.00,'finalized','052e7e5abf0526d4bfdb7205b839819e','[]',NULL,'2025-08-25 21:45:17','2025-08-25 21:45:25',NULL),
(1095,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:45:30',1.00,'finalized','8de3675be7bfed36578794b3680a5b2c','[]',NULL,'2025-08-25 21:45:30','2025-08-25 21:45:35',NULL),
(1096,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:45:44',1.00,'finalized','ccf53f253a548c3c7a6756f457af60da','[]',NULL,'2025-08-25 21:45:44','2025-08-25 21:45:53',NULL),
(1097,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:46:04',1.00,'finalized','8e96625f7814def0a26ab8a1f14b657f','[]',NULL,'2025-08-25 21:46:04','2025-08-25 21:46:09',NULL),
(1098,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:46:18',1.00,'finalized','ae3fc97b9d809f963b19173734291692','[]',NULL,'2025-08-25 21:46:18','2025-08-25 21:46:28',NULL),
(1099,118,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:47:26',1.00,'finalized','08c48f5427eaff7e1faa9c377a27f172','[]',NULL,'2025-08-25 21:47:26','2025-08-25 21:47:41',NULL),
(1100,129,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-25 21:54:29',2.00,'finalized','a3de8ee261f0394dd02cf9648e7cfc9c','[]',NULL,'2025-08-25 21:54:29','2025-08-25 21:54:37',NULL),
(1101,129,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:54:54',1.00,'finalized','a86c724966d1104b59a153aff34175e6','[]',NULL,'2025-08-25 21:54:54','2025-08-25 21:55:03',NULL),
(1102,129,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:55:09',1.00,'finalized','9466c3aca568b6011bebd2792b86449c','[]',NULL,'2025-08-25 21:55:09','2025-08-25 21:55:17',NULL),
(1103,129,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 21:55:23',1.00,'finalized','66254274574ba5bb204413742737ee9a','[]',NULL,'2025-08-25 21:55:23','2025-08-25 21:55:32',NULL),
(1104,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:24:20',1.00,'finalized','482a04a900f4b055e81c13240198f294','[]',NULL,'2025-08-25 22:24:20','2025-08-25 22:24:43',NULL),
(1105,132,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-25 22:25:13',5.00,'finalized','acf84a14cede3e5cd93594fd3f375932','[5,6,7]','5.00','2025-08-25 22:25:13','2025-08-25 22:25:19',NULL),
(1106,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:25:42',1.00,'finalized','caaf84bec9b32c117ea5b8778aabe404','[]',NULL,'2025-08-25 22:25:42','2025-08-25 22:25:48',NULL),
(1107,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:25:51',1.00,'finalized','7845febeb8a5da9b5664463e260cbd2a','[]',NULL,'2025-08-25 22:25:51','2025-08-25 22:25:55',NULL),
(1108,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:25:57',1.00,'finalized','375b176a37fee3b8bf0b661362d57a80','[]',NULL,'2025-08-25 22:25:57','2025-08-25 22:26:03',NULL),
(1109,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:05',1.00,'finalized','bb1cfb469bd6d992406e0f84fc0f4087','[]',NULL,'2025-08-25 22:26:05','2025-08-25 22:26:08',NULL),
(1110,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:12',1.00,'finalized','94a70f3a6669793a57650a1055c37103','[]',NULL,'2025-08-25 22:26:12','2025-08-25 22:26:17',NULL),
(1111,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:20',1.00,'finalized','8c0db2f2e623e5ca7718844867bc767f','[]',NULL,'2025-08-25 22:26:20','2025-08-25 22:26:24',NULL),
(1112,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:26',1.00,'finalized','c339b291b37e0132669cc083f65d36c8','[]',NULL,'2025-08-25 22:26:26','2025-08-25 22:26:30',NULL),
(1113,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:32',1.00,'finalized','929f26a8e1f734f7f77ffc3c7bafee9e','[]',NULL,'2025-08-25 22:26:32','2025-08-25 22:26:40',NULL),
(1114,132,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-25 22:26:42',1.00,'finalized','426f4d8a35195960dbbcb76e1dbbfa58','[]',NULL,'2025-08-25 22:26:42','2025-08-25 22:26:47',NULL),
(1115,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:43',1.00,'finalized','055e17acbe1666d2011636d8896d6a2b','[]',NULL,'2025-08-26 02:47:43','2025-08-26 02:47:44',NULL),
(1116,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:47',1.00,'finalized','a590fd316ebe80fa9500165bd314e1d8','[]',NULL,'2025-08-26 02:47:47','2025-08-26 02:47:47',NULL),
(1117,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:49',1.00,'finalized','91a6d1b8b268e7d03201b0c5344630c0','[]',NULL,'2025-08-26 02:47:49','2025-08-26 02:47:50',NULL),
(1118,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:51',1.00,'finalized','24e5c5dcc615d4d42bb827bada9a5cac','[]',NULL,'2025-08-26 02:47:51','2025-08-26 02:47:51',NULL),
(1119,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:53',1.00,'finalized','eb4085f34d4acbcd1dcdb8decd1b933b','[]',NULL,'2025-08-26 02:47:53','2025-08-26 02:47:53',NULL),
(1120,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:54',1.00,'finalized','af08612e0968fd72cd4bc82df44bd5b8','[]',NULL,'2025-08-26 02:47:54','2025-08-26 02:47:55',NULL),
(1121,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:57',1.00,'finalized','282c5302559a89c7886786a259530053','[]',NULL,'2025-08-26 02:47:57','2025-08-26 02:47:57',NULL),
(1122,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:47:59',1.00,'finalized','75ddcd956102b33c6f791c6692e6a649','[]',NULL,'2025-08-26 02:47:59','2025-08-26 02:47:59',NULL),
(1123,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:01',1.00,'finalized','975c134096670e38fa690e320b913fba','[]',NULL,'2025-08-26 02:48:01','2025-08-26 02:48:02',NULL),
(1124,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:03',1.00,'finalized','f9a29c5608443c0949af27b32b916407','[]',NULL,'2025-08-26 02:48:03','2025-08-26 02:48:03',NULL),
(1125,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:05',1.00,'finalized','63ee96c138ac3219c0d62f1c73a397e4','[]',NULL,'2025-08-26 02:48:05','2025-08-26 02:48:05',NULL),
(1126,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:08',1.00,'finalized','6ebeded535c6109a4a28f338aa8f7620','[]',NULL,'2025-08-26 02:48:08','2025-08-26 02:48:08',NULL),
(1127,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:10',1.00,'finalized','01250c62cd2ae73fcaaf6148ceb7a23a','[]',NULL,'2025-08-26 02:48:10','2025-08-26 02:48:10',NULL),
(1128,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:12',1.00,'finalized','f5d79e7de6235354fc10f9c90810544f','[]',NULL,'2025-08-26 02:48:12','2025-08-26 02:48:12',NULL),
(1129,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:17',1.00,'finalized','86364e8daf5de055b4ab2607e1e77a2f','[]',NULL,'2025-08-26 02:48:17','2025-08-26 02:48:17',NULL),
(1130,140,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-26 02:48:20',1.00,'finalized','7803965497aabf24661a2f9a4f9ea159','[0,3,5]','0.50','2025-08-26 02:48:20','2025-08-26 02:48:21',NULL),
(1131,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:48:27',1.00,'finalized','1e55292f9253a563b618a8bfb7499682','[]',NULL,'2025-08-26 02:48:27','2025-08-26 02:48:27',NULL),
(1132,140,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-26 02:48:29',1.00,'finalized','2519c97723f520efce903bc6a480f17a','[1,2,4]','1.00','2025-08-26 02:48:29','2025-08-26 02:48:30',NULL),
(1133,140,0,'Cofrinho mágico 🪙','ganhou',100.00,'2025-08-26 02:48:33',1.00,'finalized','62a9d883f67c1a3cf6d6abf01108c722','[2,6,8]','100.00','2025-08-26 02:48:33','2025-08-26 02:48:34',NULL),
(1134,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:49:28',1.00,'finalized','14601a208d3cd97e83ee9d25a4007b3d','[]',NULL,'2025-08-26 02:49:28','2025-08-26 02:49:28',NULL),
(1135,140,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:49:30',1.00,'finalized','5dfa9a1258e0db0943054e94a216acaa','[]',NULL,'2025-08-26 02:49:30','2025-08-26 02:49:30',NULL),
(1136,140,0,'Baú da sorte 🎁','ganhou',1.00,'2025-08-26 02:49:38',2.00,'finalized','df4575a1bd5603ea062a67d1ba9f1ea1','[0,2,5]','1.00','2025-08-26 02:49:38','2025-08-26 02:49:39',NULL),
(1137,140,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:49:41',2.00,'finalized','e6309a55a05e020e372ed3a958a05f94','[]',NULL,'2025-08-26 02:49:41','2025-08-26 02:49:41',NULL),
(1138,140,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-26 02:49:42',2.00,'finalized','51633892b73ec0f8722b8af2f475f745','[0,5,6]','0.50','2025-08-26 02:49:42','2025-08-26 02:49:43',NULL),
(1139,140,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:49:44',2.00,'finalized','f85310cfd9c272a1d8f98c8350283846','[]',NULL,'2025-08-26 02:49:44','2025-08-26 02:49:44',NULL),
(1140,140,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-26 02:49:46',2.00,'finalized','8f68e5872840365d89e7786138ac59f3','[0,3,5]','2.00','2025-08-26 02:49:46','2025-08-26 02:49:47',NULL),
(1141,140,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:49:48',2.00,'finalized','600b77d49ec400329535060104749066','[]',NULL,'2025-08-26 02:49:48','2025-08-26 02:49:48',NULL),
(1142,140,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-26 02:49:50',2.00,'finalized','2eba5425d4e74dcc825d1f7cac909f0b','[0,3,5]','5.00','2025-08-26 02:49:50','2025-08-26 02:49:50',NULL),
(1143,140,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:49:52',2.00,'finalized','71b6a701bfdac15f10fda77a0791c4d1','[]',NULL,'2025-08-26 02:49:52','2025-08-26 02:49:52',NULL),
(1144,140,0,'Baú da sorte 🎁','ganhou',5.00,'2025-08-26 02:49:54',2.00,'finalized','12400daecbaa9035e1df9369bdae4913','[0,2,8]','5.00','2025-08-26 02:49:54','2025-08-26 02:49:55',NULL),
(1145,140,0,'Baú da sorte 🎁','ganhou',2.00,'2025-08-26 02:49:59',2.00,'finalized','0bc41ed2c876cc4b0f014cae0fe6eab1','[1,6,8]','2.00','2025-08-26 02:49:59','2025-08-26 02:50:00',NULL),
(1146,140,0,'Baú da sorte 🎁','ganhou',0.50,'2025-08-26 02:50:01',2.00,'finalized','10c7dc9c060e6f1e2002891eaa908719','[1,5,8]','0.50','2025-08-26 02:50:01','2025-08-26 02:50:02',NULL),
(1147,140,0,'Baú da sorte 🎁','ganhou',200.00,'2025-08-26 02:50:03',2.00,'finalized','252330a458c1efb2f7fd7e102b263fc9','[1,3,4]','200.00','2025-08-26 02:50:03','2025-08-26 02:50:04',NULL),
(1148,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:50:17',10.00,'finalized','9bc6c61e47985dbe35be90559f5018ed','[]',NULL,'2025-08-26 02:50:17','2025-08-26 02:50:22',NULL),
(1149,140,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:50:18',2.00,'finalized','d1efc1f19985f61136152ed18d8235ca','[]',NULL,'2025-08-26 02:50:18','2025-08-26 02:50:18',NULL),
(1150,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:50:24',10.00,'finalized','66affc995d18c199bbec40bdad0be8d9','[5,6,7]','2.00','2025-08-26 02:50:24','2025-08-26 02:50:27',NULL),
(1151,140,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-26 02:50:27',5.00,'finalized','52c245e914a5efe304c5c9455b9ca530','[0,2,7]','1.00','2025-08-26 02:50:27','2025-08-26 02:50:28',NULL),
(1152,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:50:29',10.00,'finalized','76fdfdfaba30c087ede8843f74cf3b36','[]',NULL,'2025-08-26 02:50:29','2025-08-26 02:50:29',NULL),
(1153,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:29',5.00,'finalized','8a67e35face97318ad3bb7134b906b65','[3,4,6]','5.00','2025-08-26 02:50:29','2025-08-26 02:50:30',NULL),
(1154,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:50:31',10.00,'finalized','8423ebf740c653f0efe55256a8cea807','[]',NULL,'2025-08-26 02:50:31','2025-08-26 02:50:31',NULL),
(1155,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:31',5.00,'finalized','a7a0129bbd841cbfb2bc48e8909e709c','[1,2,8]','5.00','2025-08-26 02:50:31','2025-08-26 02:50:32',NULL),
(1156,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:50:32',10.00,'finalized','8d2f1bd5b12a13c62a22e87e434293d8','[1,2,4]','20.00','2025-08-26 02:50:32','2025-08-26 02:50:33',NULL),
(1157,140,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-26 02:50:34',5.00,'finalized','f70127cd5b8b631c37c6fb272bab345f','[0,3,7]','1.00','2025-08-26 02:50:34','2025-08-26 02:50:34',NULL),
(1158,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:50:34',10.00,'finalized','7218c7309619bbd10c436dddcae30c1f','[0,3,8]','20.00','2025-08-26 02:50:34','2025-08-26 02:50:35',NULL),
(1159,140,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:50:35',5.00,'finalized','78a4b201903481c84e0dd20c0a34e7fe','[]',NULL,'2025-08-26 02:50:35','2025-08-26 02:50:36',NULL),
(1160,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-26 02:50:37',10.00,'finalized','0d3e72b45bf8e9975827049fa156b1fb','[4,6,7]','10.00','2025-08-26 02:50:37','2025-08-26 02:50:37',NULL),
(1161,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:37',5.00,'finalized','266e17aee93fd018caa24c2233de2206','[2,3,8]','5.00','2025-08-26 02:50:37','2025-08-26 02:50:37',NULL),
(1162,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:50:39',10.00,'finalized','e2a352d163c293bb3b5b8e732a97e9a5','[]',NULL,'2025-08-26 02:50:39','2025-08-26 02:50:39',NULL),
(1163,140,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:50:39',5.00,'finalized','1d1300c2677694dbcfb3703538704301','[]',NULL,'2025-08-26 02:50:39','2025-08-26 02:50:40',NULL),
(1164,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:41',5.00,'finalized','112b70d6abb6e10582c2d324b65a709d','[2,3,4]','5.00','2025-08-26 02:50:41','2025-08-26 02:50:42',NULL),
(1165,100,0,'Tesouro lendário 🏆','ganhou',50.00,'2025-08-26 02:50:41',10.00,'finalized','772bd4413378690669ca9909a067db2a','[0,2,7]','50.00','2025-08-26 02:50:41','2025-08-26 02:50:41',NULL),
(1166,140,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-26 02:50:43',5.00,'finalized','6909ecc6ef4c5433d0c3729b84157d98','[0,3,8]','10.00','2025-08-26 02:50:43','2025-08-26 02:50:44',NULL),
(1167,100,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:50:43',10.00,'finalized','be91d64143856a87f0aa6bece5682e56','[0,6,8]','1.00','2025-08-26 02:50:43','2025-08-26 02:50:44',NULL),
(1168,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:50:46',10.00,'finalized','23b76f057704d547149f85a3d6c69d0b','[]',NULL,'2025-08-26 02:50:46','2025-08-26 02:50:46',NULL),
(1169,140,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:50:46',5.00,'finalized','da5c58386492d4fcdadbcb1940949918','[]',NULL,'2025-08-26 02:50:46','2025-08-26 02:50:46',NULL),
(1170,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-26 02:50:47',10.00,'finalized','7b4842ae87c9b153dc1f7167ed2efb35','[2,4,7]','10.00','2025-08-26 02:50:47','2025-08-26 02:50:48',NULL),
(1171,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:48',5.00,'finalized','532f7bedecd4b9d9b34a0c15e2c82e02','[1,2,5]','5.00','2025-08-26 02:50:48','2025-08-26 02:50:49',NULL),
(1172,140,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:50:50',5.00,'finalized','91db6cc63ccfeef742c790a1594fa586','[1,4,8]','5.00','2025-08-26 02:50:50','2025-08-26 02:50:51',NULL),
(1173,100,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:50:50',10.00,'finalized','30cbb0958342629a7e1e46e8b66cd313','[3,4,7]','1.00','2025-08-26 02:50:50','2025-08-26 02:50:50',NULL),
(1174,140,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:50:52',5.00,'finalized','04c80b6c4de5d7e41e7dab55a3a5fd0d','[]',NULL,'2025-08-26 02:50:52','2025-08-26 02:50:52',NULL),
(1175,100,0,'Tesouro lendário 🏆','ganhou',50.00,'2025-08-26 02:50:52',10.00,'finalized','4ca29827f915cd4d5f8ba365f7380fcd','[2,6,8]','50.00','2025-08-26 02:50:52','2025-08-26 02:50:53',NULL),
(1176,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:50:54',10.00,'finalized','e47a51d0e07d1f505e6c99f1e0f5a1c0','[2,4,5]','2.00','2025-08-26 02:50:54','2025-08-26 02:50:55',NULL),
(1177,140,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-26 02:50:55',5.00,'finalized','99f0004e736128ec4029eb880b69b03e','[0,1,4]','2.00','2025-08-26 02:50:55','2025-08-26 02:50:55',NULL),
(1178,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:50:56',10.00,'finalized','e0088ddd2a9036c4fb0a1ad2984f31f9','[0,6,8]','2.00','2025-08-26 02:50:56','2025-08-26 02:50:57',NULL),
(1179,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:50:59',10.00,'finalized','c6b5ae5a0b373418ba0c7466118c4faa','[2,3,8]','5.00','2025-08-26 02:50:59','2025-08-26 02:50:59',NULL),
(1180,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:51:01',10.00,'finalized','fa99cdb85a2381e9559ffb6c792d4615','[1,2,7]','5.00','2025-08-26 02:51:01','2025-08-26 02:51:01',NULL),
(1181,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:51:03',10.00,'finalized','48e068c197606abb53bed2e4910d2f58','[1,2,5]','5.00','2025-08-26 02:51:03','2025-08-26 02:51:03',NULL),
(1182,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:51:05',10.00,'finalized','a5c166c20c857c468515a97289346bc6','[2,3,4]','2.00','2025-08-26 02:51:05','2025-08-26 02:51:05',NULL),
(1183,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:51:07',10.00,'finalized','47ee15ca6a08c2d98d1c84562c92afc3','[0,2,4]','20.00','2025-08-26 02:51:07','2025-08-26 02:51:08',NULL),
(1184,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:10',10.00,'finalized','2dab0e6fdc6dd02dc44c84822dad6e30','[]',NULL,'2025-08-26 02:51:10','2025-08-26 02:51:10',NULL),
(1185,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:12',10.00,'finalized','af961e0659bdd41eec603235f7e1e679','[]',NULL,'2025-08-26 02:51:12','2025-08-26 02:51:12',NULL),
(1186,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:13',10.00,'finalized','87fff309789de0c1ffe182f6c082fc3a','[]',NULL,'2025-08-26 02:51:13','2025-08-26 02:51:13',NULL),
(1187,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-26 02:51:14',10.00,'finalized','83918188e29e2f6d97404a6b05888861','[1,6,7]','10.00','2025-08-26 02:51:14','2025-08-26 02:51:14',NULL),
(1188,140,0,'Tesouro lendário 🏆','ganhou',50.00,'2025-08-26 02:51:15',10.00,'finalized','d947b84f41fe124bf702c9918e8795f5','[2,5,7]','50.00','2025-08-26 02:51:15','2025-08-26 02:51:16',NULL),
(1189,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:16',10.00,'finalized','38fe9e3a4d3db2061753f81d9b51a658','[]',NULL,'2025-08-26 02:51:16','2025-08-26 02:51:16',NULL),
(1190,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:51:18',10.00,'finalized','36138569b2f5857a9196612d06ab8d12','[0,2,3]','20.00','2025-08-26 02:51:18','2025-08-26 02:51:19',NULL),
(1191,100,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:51:21',10.00,'finalized','64f1866a7d505305140c32aa40d5ef09','[0,7,8]','1.00','2025-08-26 02:51:21','2025-08-26 02:51:22',NULL),
(1192,140,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:51:23',10.00,'finalized','f2ee05112f8c25a46b965f6a024c8c1b','[1,2,7]','2.00','2025-08-26 02:51:23','2025-08-26 02:51:24',NULL),
(1193,140,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-26 02:51:26',10.00,'finalized','39b234a3cf9f33938c7fd9c5bab6e34d','[0,2,4]','2.00','2025-08-26 02:51:26','2025-08-26 02:51:27',NULL),
(1194,140,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-26 02:51:28',10.00,'finalized','bff77ce16080b57c2e9a74ab146a16bb','[3,5,7]','10.00','2025-08-26 02:51:28','2025-08-26 02:51:29',NULL),
(1195,140,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:51:30',10.00,'finalized','ffe19b3ad95e5e25b32d7f15e8be0426','[2,5,7]','1.00','2025-08-26 02:51:30','2025-08-26 02:51:31',NULL),
(1196,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:33',10.00,'finalized','1a2e37a39f7551660b813c3f72b96e59','[]',NULL,'2025-08-26 02:51:33','2025-08-26 02:51:33',NULL),
(1197,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:34',10.00,'finalized','c3f95b83d2a6e830a4b8962251e5194d','[]',NULL,'2025-08-26 02:51:34','2025-08-26 02:51:35',NULL),
(1198,140,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:51:35',10.00,'finalized','91e11471f538e2993774521c5b1417f5','[0,1,3]','5.00','2025-08-26 02:51:35','2025-08-26 02:51:36',NULL),
(1199,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:36',10.00,'finalized','58186a561b2eedbee71f47d3a5d74f49','[]',NULL,'2025-08-26 02:51:36','2025-08-26 02:51:36',NULL),
(1200,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:39',10.00,'finalized','bbade204aa105e0894dfc85ed7c6b04e','[]',NULL,'2025-08-26 02:51:39','2025-08-26 02:51:39',NULL),
(1201,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:42',10.00,'finalized','5ed356ce68775c7e7424ecf5a8754b37','[]',NULL,'2025-08-26 02:51:42','2025-08-26 02:51:42',NULL),
(1202,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:51:44',10.00,'finalized','4dc8ed65fd052fc2ee9f4fe4e897f3bc','[0,5,7]','20.00','2025-08-26 02:51:44','2025-08-26 02:51:45',NULL),
(1203,100,0,'Tesouro lendário 🏆','ganhou',50.00,'2025-08-26 02:51:47',10.00,'finalized','059b5dc4d66e66be80975b7a51384120','[0,3,7]','50.00','2025-08-26 02:51:47','2025-08-26 02:51:47',NULL),
(1204,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:51:49',10.00,'finalized','207059daf748e70bee2070d6115b67f8','[]',NULL,'2025-08-26 02:51:49','2025-08-26 02:51:49',NULL),
(1205,100,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:51:51',10.00,'finalized','199e8d75e800ab0fbdf09f1b5059b8ed','[1,3,6]','20.00','2025-08-26 02:51:51','2025-08-26 02:51:52',NULL),
(1206,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:15',10.00,'finalized','828df6d62b18f73c04403099e13a9468','[]',NULL,'2025-08-26 02:52:15','2025-08-26 02:52:16',NULL),
(1207,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:17',10.00,'finalized','ff524c7a2483893ba0cfb5d5690fb192','[]',NULL,'2025-08-26 02:52:17','2025-08-26 02:52:17',NULL),
(1208,140,0,'Tesouro lendário 🏆','ganhou',100.00,'2025-08-26 02:52:18',10.00,'finalized','824ee65d564a29a50917f4cbb080777c','[1,3,5]','100.00','2025-08-26 02:52:18','2025-08-26 02:52:19',NULL),
(1209,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:52:34',2.00,'finalized','dd61966ef7849dcf8c3314d75f5e128c','[]',NULL,'2025-08-26 02:52:34','2025-08-26 02:52:34',NULL),
(1210,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:52:35',2.00,'finalized','1869492b0abbf2722cbf95b87b97c449','[]',NULL,'2025-08-26 02:52:35','2025-08-26 02:52:35',NULL),
(1211,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:52:36',2.00,'finalized','9adc6fb7dca12874de2124dc7d942eed','[]',NULL,'2025-08-26 02:52:36','2025-08-26 02:52:36',NULL),
(1212,97,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 02:52:40',5.00,'finalized','53fc89a4b2b617692dc63020edd769fc','[3,4,7]','5.00','2025-08-26 02:52:40','2025-08-26 02:52:40',NULL),
(1213,97,0,'Trono da Prada 👑','ganhou',10.00,'2025-08-26 02:52:41',5.00,'finalized','56d2dbf1ae63fbe91a7f954b6c3e7ea5','[1,3,7]','10.00','2025-08-26 02:52:41','2025-08-26 02:52:42',NULL),
(1214,97,0,'Trono da Prada 👑','ganhou',1.00,'2025-08-26 02:52:43',5.00,'finalized','60aa7b73071948da675b83aea8857374','[6,7,8]','1.00','2025-08-26 02:52:43','2025-08-26 02:52:43',NULL),
(1215,97,0,'Trono da Prada 👑','ganhou',50.00,'2025-08-26 02:52:44',5.00,'finalized','8d6891ca196be8de77458d4163be9f89','[1,3,4]','50.00','2025-08-26 02:52:44','2025-08-26 02:52:45',NULL),
(1216,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:52',10.00,'finalized','2d51df60a27c7d5314fec2817893b72a','[]',NULL,'2025-08-26 02:52:52','2025-08-26 02:52:53',NULL),
(1217,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:54',10.00,'finalized','f3c148d3c5d008765af0bcf0e1717ac4','[]',NULL,'2025-08-26 02:52:54','2025-08-26 02:52:54',NULL),
(1218,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:55',10.00,'finalized','0c5bb9557b294f56211003dec120d4a5','[]',NULL,'2025-08-26 02:52:55','2025-08-26 02:52:56',NULL),
(1219,140,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:52:57',10.00,'finalized','1fdacc9c6fe2aa24f11bdbcede56df4c','[2,3,4]','20.00','2025-08-26 02:52:57','2025-08-26 02:52:58',NULL),
(1220,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:52:59',10.00,'finalized','df23aa89ac2388b87e517d7f0950d26a','[]',NULL,'2025-08-26 02:52:59','2025-08-26 02:52:59',NULL),
(1221,140,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-26 02:53:00',10.00,'finalized','09e060029dce33232a1f7666c6c4409a','[0,2,8]','10.00','2025-08-26 02:53:00','2025-08-26 02:53:01',NULL),
(1222,140,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:53:03',10.00,'finalized','8c1aa9c996b89e2bd5a08b79af6a1080','[0,7,8]','1.00','2025-08-26 02:53:03','2025-08-26 02:53:03',NULL),
(1223,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:53:05',10.00,'finalized','81c0d98aa19e004331afc08af485bf06','[]',NULL,'2025-08-26 02:53:05','2025-08-26 02:53:05',NULL),
(1224,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:53:07',10.00,'finalized','ce7050ccfd52fede484e51a026a5e55a','[]',NULL,'2025-08-26 02:53:07','2025-08-26 02:53:07',NULL),
(1225,140,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:53:08',10.00,'finalized','a72ea7bccb6fbbf07b91d71c3e69104a','[4,6,8]','5.00','2025-08-26 02:53:08','2025-08-26 02:53:09',NULL),
(1226,140,0,'Tesouro lendário 🏆','ganhou',20.00,'2025-08-26 02:53:10',10.00,'finalized','db72fd041addc1b5809046708bd48747','[0,5,6]','20.00','2025-08-26 02:53:10','2025-08-26 02:53:11',NULL),
(1227,140,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:53:12',10.00,'finalized','cf415459b1f449b082c27e63f8a06549','[]',NULL,'2025-08-26 02:53:12','2025-08-26 02:53:13',NULL),
(1228,140,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:53:14',10.00,'finalized','32da725cdb2ff28ee087f11209c572a1','[0,1,2]','5.00','2025-08-26 02:53:14','2025-08-26 02:53:15',NULL),
(1229,140,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:53:16',10.00,'finalized','6ff7cedc79b0730481440ab68f5f7b92','[0,3,8]','5.00','2025-08-26 02:53:16','2025-08-26 02:53:17',NULL),
(1230,140,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:53:18',10.00,'pending','bf93e85ba98fb126e646d0f646f4e388','[0,5,6]','5.00','2025-08-26 02:53:18',NULL,NULL),
(1231,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:55:31',10.00,'finalized','1550350a96ed3e0218658e132fadf581','[]',NULL,'2025-08-26 02:55:31','2025-08-26 02:55:31',NULL),
(1232,100,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 02:55:34',10.00,'finalized','b9c465114e610b84111cc4d58307915b','[3,7,8]','1.00','2025-08-26 02:55:34','2025-08-26 02:55:34',NULL),
(1233,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 02:55:36',10.00,'finalized','0da62d96584a29cf81b6159f71f51886','[0,1,5]','5.00','2025-08-26 02:55:36','2025-08-26 02:55:37',NULL),
(1234,100,0,'Luxo Máximo 👑','ganhou',50.00,'2025-08-26 02:55:52',50.00,'finalized','ebbffd02944af58b6dd914af5de45735','[3,4,7]','50.00','2025-08-26 02:55:52','2025-08-26 02:55:53',NULL),
(1235,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:55:54',50.00,'finalized','c202f421809ca899e26ed84a5c7d3028','[]',NULL,'2025-08-26 02:55:54','2025-08-26 02:55:55',NULL),
(1236,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:55:56',5.00,'finalized','b63652ca804fffe602313a54b939499f','[]',NULL,'2025-08-26 02:55:56','2025-08-26 02:55:56',NULL),
(1237,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:55:57',5.00,'finalized','25c5cfdb10bd452efab8a794a1370dc5','[]',NULL,'2025-08-26 02:55:57','2025-08-26 02:55:57',NULL),
(1238,100,0,'Luxo Máximo 👑','ganhou',5.00,'2025-08-26 02:55:57',50.00,'finalized','d48ef7153b2fe2e1d804fd5656d3bb59','[1,2,6]','5.00','2025-08-26 02:55:57','2025-08-26 02:55:58',NULL),
(1239,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:55:57',5.00,'finalized','ba1565cd2e83806d9d758b4a569a4f55','[]',NULL,'2025-08-26 02:55:57','2025-08-26 02:55:58',NULL),
(1240,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:55:58',5.00,'finalized','0d758fe0a3fd483524a97202b201158b','[]',NULL,'2025-08-26 02:55:58','2025-08-26 02:55:58',NULL),
(1241,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:55:59',5.00,'finalized','a2b981ae694708fa1338bcf20d4e2a25','[]',NULL,'2025-08-26 02:55:59','2025-08-26 02:55:59',NULL),
(1242,100,0,'Luxo Máximo 👑','ganhou',200.00,'2025-08-26 02:56:01',50.00,'finalized','90584d2210a45f0dc4072fb8e63e8cf8','[5,7,8]','200.00','2025-08-26 02:56:01','2025-08-26 02:56:02',NULL),
(1243,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:56:04',5.00,'finalized','886c1245f35e8f267724477ba8446723','[]',NULL,'2025-08-26 02:56:04','2025-08-26 02:56:04',NULL),
(1244,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:56:04',50.00,'finalized','bb521f5cb6b6979e0c9f94d0a4fcc32a','[]',NULL,'2025-08-26 02:56:04','2025-08-26 02:56:04',NULL),
(1245,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:56:04',5.00,'finalized','0f19fd1d77c8b7abdd5c23f7780a515e','[]',NULL,'2025-08-26 02:56:04','2025-08-26 02:56:05',NULL),
(1246,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:56:05',5.00,'finalized','e956801163084364359d1e88be9507a2','[]',NULL,'2025-08-26 02:56:05','2025-08-26 02:56:05',NULL),
(1247,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 02:56:06',5.00,'finalized','b9a07fa2f16e35cad9bcc5ab7404ea3b','[]',NULL,'2025-08-26 02:56:06','2025-08-26 02:56:06',NULL),
(1248,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:56:07',50.00,'finalized','0f4f5f3c421823807782e5612b54dba3','[]',NULL,'2025-08-26 02:56:07','2025-08-26 02:56:07',NULL),
(1249,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:56:08',50.00,'finalized','117fc26c67080f1729cfb903260619d6','[]',NULL,'2025-08-26 02:56:08','2025-08-26 02:56:09',NULL),
(1250,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:09',10.00,'finalized','0f8f6dc03ceeff808e0e27a162200521','[]',NULL,'2025-08-26 02:56:09','2025-08-26 02:56:09',NULL),
(1251,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:10',10.00,'finalized','afd6cb077f29a08e1f455e9d499f7a62','[]',NULL,'2025-08-26 02:56:10','2025-08-26 02:56:10',NULL),
(1252,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:56:10',50.00,'finalized','08471b84d8865aa34e8d5b188cf891f5','[]',NULL,'2025-08-26 02:56:10','2025-08-26 02:56:10',NULL),
(1253,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:11',10.00,'finalized','640d39cb0449dc1481c1088b2c1561d5','[]',NULL,'2025-08-26 02:56:11','2025-08-26 02:56:11',NULL),
(1254,100,0,'Luxo Máximo 👑','perdeu',0.00,'2025-08-26 02:56:13',50.00,'finalized','5bc08d403c4e926d2bfacb86035714d9','[]',NULL,'2025-08-26 02:56:13','2025-08-26 02:56:13',NULL),
(1255,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:26',10.00,'finalized','206a9ab4393c2ceffe3d553c9b6d5a1b','[]',NULL,'2025-08-26 02:56:26','2025-08-26 02:56:27',NULL),
(1256,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:27',10.00,'finalized','b92a8a42d8b15d482dc4e9cf8c82b427','[]',NULL,'2025-08-26 02:56:27','2025-08-26 02:56:27',NULL),
(1257,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:28',10.00,'finalized','90abb637dc8424e41a693e00f4075ddf','[]',NULL,'2025-08-26 02:56:28','2025-08-26 02:56:28',NULL),
(1258,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:29',10.00,'finalized','e8aea2dcfec857e7f941b2728a56d45a','[]',NULL,'2025-08-26 02:56:29','2025-08-26 02:56:29',NULL),
(1259,97,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 02:56:30',10.00,'finalized','549af858aa7da730d40ef130d1885968','[]',NULL,'2025-08-26 02:56:30','2025-08-26 02:56:30',NULL),
(1260,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:34',1.00,'finalized','65b4e4aa7d5a18c6d9d9f9cc0ebc204e','[]',NULL,'2025-08-26 02:56:34','2025-08-26 02:56:34',NULL),
(1261,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:35',1.00,'finalized','3202d1ec19e98b80da91c91135ecbcea','[]',NULL,'2025-08-26 02:56:35','2025-08-26 02:56:35',NULL),
(1262,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:35',1.00,'finalized','5fd92a1417e789fd3dd041745a8df59a','[]',NULL,'2025-08-26 02:56:35','2025-08-26 02:56:36',NULL),
(1263,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:36',1.00,'finalized','a0bb81c7c0fbc1a0a40877c5ac6b63a5','[]',NULL,'2025-08-26 02:56:36','2025-08-26 02:56:36',NULL),
(1264,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:37',1.00,'finalized','f55db2c33d79f55ee1390abd7171a5c8','[]',NULL,'2025-08-26 02:56:37','2025-08-26 02:56:37',NULL),
(1265,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:37',1.00,'finalized','a198e3f8a3f4e953920a02350a94b3cc','[]',NULL,'2025-08-26 02:56:37','2025-08-26 02:56:37',NULL),
(1266,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:38',1.00,'finalized','77d403264de4c5b9e052783246bfae44','[]',NULL,'2025-08-26 02:56:38','2025-08-26 02:56:38',NULL),
(1267,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:38',1.00,'finalized','10e0128b211308003b5862a1bee85df0','[]',NULL,'2025-08-26 02:56:38','2025-08-26 02:56:38',NULL),
(1268,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:39',1.00,'finalized','80e03e89bcb4369dc33db8a7e392f42a','[]',NULL,'2025-08-26 02:56:39','2025-08-26 02:56:39',NULL),
(1269,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:39',1.00,'finalized','fd3801f625e0f51f267a2bb575c56bbd','[]',NULL,'2025-08-26 02:56:39','2025-08-26 02:56:40',NULL),
(1270,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:41',1.00,'finalized','8d1afb34b0789438df0415a8ae75bd40','[]',NULL,'2025-08-26 02:56:41','2025-08-26 02:56:42',NULL),
(1271,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:42',1.00,'finalized','aeb61d13db5fd88ef10a4147cc705a76','[]',NULL,'2025-08-26 02:56:42','2025-08-26 02:56:42',NULL),
(1272,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:43',1.00,'finalized','9d37f6a7ddd784294e972f27ff167015','[]',NULL,'2025-08-26 02:56:43','2025-08-26 02:56:43',NULL),
(1273,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:43',1.00,'finalized','6cfa67953e59476c5eef565e17596a79','[]',NULL,'2025-08-26 02:56:43','2025-08-26 02:56:43',NULL),
(1274,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:44',1.00,'finalized','aead30fa161858aa68b89d6ca3b3cf9a','[]',NULL,'2025-08-26 02:56:44','2025-08-26 02:56:44',NULL),
(1275,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:45',1.00,'finalized','c8599b332446537057df3007944a8966','[]',NULL,'2025-08-26 02:56:45','2025-08-26 02:56:45',NULL),
(1276,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:50',1.00,'finalized','f1fdae87d7e3a752efdc5ce3af857b66','[]',NULL,'2025-08-26 02:56:50','2025-08-26 02:56:50',NULL),
(1277,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:51',1.00,'finalized','194336fa0ba598d7a555d1920b3a8c97','[]',NULL,'2025-08-26 02:56:51','2025-08-26 02:56:51',NULL),
(1278,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:56:52',1.00,'finalized','acafa09f1ce2d8b5416aab1a52dc75e4','[]',NULL,'2025-08-26 02:56:52','2025-08-26 02:56:52',NULL),
(1279,97,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 02:59:20',1.00,'finalized','6a4784247d3cb6da4274a8f7c7e6097b','[]',NULL,'2025-08-26 02:59:20','2025-08-26 02:59:20',NULL),
(1280,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:23',2.00,'finalized','a1a6ed7c81a769e77f1cf440b5c27c6a','[]',NULL,'2025-08-26 02:59:23','2025-08-26 02:59:24',NULL),
(1281,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:24',2.00,'finalized','0e51797fe30f324720c8015e9704635d','[]',NULL,'2025-08-26 02:59:24','2025-08-26 02:59:24',NULL),
(1282,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:25',2.00,'finalized','e5a75a7e06b1f5d884280ddbc548d99f','[]',NULL,'2025-08-26 02:59:25','2025-08-26 02:59:25',NULL),
(1283,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:25',2.00,'finalized','9271b42501bf2db74b46a353bc9debad','[]',NULL,'2025-08-26 02:59:25','2025-08-26 02:59:25',NULL),
(1284,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:26',2.00,'finalized','afadd63a9b99bb02b83d33e17899ccd7','[]',NULL,'2025-08-26 02:59:26','2025-08-26 02:59:26',NULL),
(1285,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:26',2.00,'finalized','c87542d970b9c70bdf1a985d2d06fad3','[]',NULL,'2025-08-26 02:59:26','2025-08-26 02:59:26',NULL),
(1286,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 02:59:27',2.00,'finalized','cd2c729270eae4dec83cce26349168c2','[]',NULL,'2025-08-26 02:59:27','2025-08-26 02:59:27',NULL),
(1287,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:37:07',1.00,'finalized','d20a7c4cfa654c63c2ea3082ebc02ee9','[]',NULL,'2025-08-26 03:37:07','2025-08-26 03:37:16',NULL),
(1288,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:37:27',1.00,'finalized','eb6d9f0f68fc03c83862fcfded536a16','[]',NULL,'2025-08-26 03:37:27','2025-08-26 03:38:00',NULL),
(1289,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:38:07',1.00,'finalized','8d8ce0bac6611160d36f7879d9cddb6a','[]',NULL,'2025-08-26 03:38:07','2025-08-26 03:38:29',NULL),
(1290,144,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 03:38:43',2.00,'pending','6b5efb5c4d453b8eadefa7012058b9b1','[]',NULL,'2025-08-26 03:38:43',NULL,NULL),
(1291,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:39:00',1.00,'finalized','404e8d2b7ce78054a252caf396763ef1','[]',NULL,'2025-08-26 03:39:00','2025-08-26 03:39:00',NULL),
(1292,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:39:05',1.00,'finalized','d4c1999a6832c0ad7bf43fe824f80fbc','[]',NULL,'2025-08-26 03:39:05','2025-08-26 03:39:05',NULL),
(1293,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:39:10',1.00,'finalized','82ae0279637f175db19a7eb3dd93b0cb','[]',NULL,'2025-08-26 03:39:10','2025-08-26 03:39:36',NULL),
(1294,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:39:56',1.00,'finalized','b5b0e4b6bf027a367275db5b59a2f013','[]',NULL,'2025-08-26 03:39:56','2025-08-26 03:39:56',NULL),
(1295,144,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 03:40:05',1.00,'finalized','70f57069c56b742d17d0e9bad6aadd0e','[]',NULL,'2025-08-26 03:40:05','2025-08-26 03:40:05',NULL),
(1296,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:53:33',5.00,'finalized','d43d3f53c94016e4a379b448bcca4260','[]',NULL,'2025-08-26 03:53:33','2025-08-26 03:53:33',NULL),
(1297,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:53:34',5.00,'finalized','8058204574049f99db8e7b05045b2a2b','[]',NULL,'2025-08-26 03:53:34','2025-08-26 03:53:34',NULL),
(1298,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:53:54',5.00,'finalized','b6f3636c9debb76e8ba75f616a6ce375','[]',NULL,'2025-08-26 03:53:54','2025-08-26 03:53:54',NULL),
(1299,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:53:55',5.00,'finalized','ac8d8c845772686a4df8213064fbbd7f','[]',NULL,'2025-08-26 03:53:55','2025-08-26 03:53:55',NULL),
(1300,1,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-26 03:53:59',10.00,'finalized','97ef8b01c339e0c5d8caf7bed42f7eeb','[0,5,8]','5.00','2025-08-26 03:53:59','2025-08-26 03:53:59',NULL),
(1301,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 03:54:00',10.00,'finalized','4c36cc0d592c8f1b509c2dd40ed76196','[]',NULL,'2025-08-26 03:54:00','2025-08-26 03:54:01',NULL),
(1302,1,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-26 03:54:02',10.00,'finalized','e5cf75e021d3a257970e9e3b9da79e89','[]',NULL,'2025-08-26 03:54:02','2025-08-26 03:54:02',NULL),
(1303,1,0,'Tesouro lendário 🏆','ganhou',1.00,'2025-08-26 03:54:03',10.00,'finalized','78d601564a81b683f05b2c70f42ce8c9','[2,4,7]','1.00','2025-08-26 03:54:03','2025-08-26 03:54:03',NULL),
(1304,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:54:07',5.00,'finalized','6f02ef1886e7638de7f9d4105dd1f3f8','[]',NULL,'2025-08-26 03:54:07','2025-08-26 03:54:07',NULL),
(1305,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:54:08',5.00,'finalized','33996b56f1d9801e1fcef4517d8649d6','[]',NULL,'2025-08-26 03:54:08','2025-08-26 03:54:08',NULL),
(1306,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:54:08',5.00,'finalized','95d069f8418b5f80a4dc3a6bc7d71252','[]',NULL,'2025-08-26 03:54:08','2025-08-26 03:54:08',NULL),
(1307,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:54:09',5.00,'finalized','792666f88b9d07aae5818cd10b973b16','[]',NULL,'2025-08-26 03:54:09','2025-08-26 03:54:09',NULL),
(1308,1,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 03:54:09',5.00,'finalized','ae2739d9297ad5bdd7cd6259c9d8b9cc','[]',NULL,'2025-08-26 03:54:09','2025-08-26 03:54:10',NULL),
(1309,1,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-26 03:54:10',5.00,'finalized','450150eb2d0d9e02467c5d4bd65e6a85','[3,5,7]','2.00','2025-08-26 03:54:10','2025-08-26 03:54:11',NULL),
(1310,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 04:20:58',2.00,'finalized','ea4e05d5d407e3cf92add77fca16bc31','[]',NULL,'2025-08-26 04:20:58','2025-08-26 04:20:58',NULL),
(1311,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 04:20:59',2.00,'finalized','19e0f659a1d5e0b9c16203278ff82139','[]',NULL,'2025-08-26 04:20:59','2025-08-26 04:20:59',NULL),
(1312,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 04:21:00',2.00,'finalized','361774e611d02fece4ebd477a38b6b6e','[]',NULL,'2025-08-26 04:21:00','2025-08-26 04:21:00',NULL),
(1313,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 04:21:01',2.00,'finalized','eb7f38596e4e121144809a90f700ddc6','[]',NULL,'2025-08-26 04:21:01','2025-08-26 04:21:01',NULL),
(1314,97,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 04:21:01',2.00,'finalized','6750f51e9c2d7d623cf667b0feac18df','[]',NULL,'2025-08-26 04:21:01','2025-08-26 04:21:01',NULL),
(1315,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:01',1.00,'finalized','79e032c8aae0b653d75466cd8b56cfde','[]',NULL,'2025-08-26 04:37:01','2025-08-26 04:37:03',NULL),
(1316,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:14',1.00,'finalized','677a365443f84d7d782442b3ec6a37b4','[]',NULL,'2025-08-26 04:37:14','2025-08-26 04:37:14',NULL),
(1317,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:17',1.00,'finalized','a9922251e2da83bafca7eaf72aee174c','[]',NULL,'2025-08-26 04:37:17','2025-08-26 04:37:18',NULL),
(1318,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:20',1.00,'finalized','14111c2b4a1060059ea4efc590b2775f','[]',NULL,'2025-08-26 04:37:20','2025-08-26 04:37:20',NULL),
(1319,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:22',1.00,'finalized','0604f49ea3de216931427be403354c94','[]',NULL,'2025-08-26 04:37:22','2025-08-26 04:37:22',NULL),
(1320,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:24',1.00,'finalized','2b226f2a918d0a993b7cac5d8fee165e','[]',NULL,'2025-08-26 04:37:24','2025-08-26 04:37:24',NULL),
(1321,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:26',1.00,'finalized','d388b06b1c0da67d8902f0013d4a056c','[]',NULL,'2025-08-26 04:37:26','2025-08-26 04:37:26',NULL),
(1322,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:27',1.00,'finalized','12a4aa6e3894209950e6c27bf00e785c','[]',NULL,'2025-08-26 04:37:27','2025-08-26 04:37:28',NULL),
(1323,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:29',1.00,'finalized','c46cece9a826b898780d340dac03d1d1','[]',NULL,'2025-08-26 04:37:29','2025-08-26 04:37:29',NULL),
(1324,150,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 04:37:30',1.00,'finalized','7c5f65f18e04ef369156d948e6a1e48a','[]',NULL,'2025-08-26 04:37:30','2025-08-26 04:37:30',NULL),
(1325,97,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-26 04:37:55',5.00,'finalized','30be62aa627a4a4069c84ae335277c03','[3,4,6]','2.00','2025-08-26 04:37:55','2025-08-26 04:37:58',NULL),
(1326,97,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-26 04:38:00',5.00,'finalized','e52659a45e55ddce492e3128ab22c026','[3,4,8]','2.00','2025-08-26 04:38:00','2025-08-26 04:38:00',NULL),
(1327,97,0,'Trono da Prada 👑','ganhou',2.00,'2025-08-26 04:38:01',5.00,'finalized','eca124af23977ed0916ddc1aa04ede80','[3,5,7]','2.00','2025-08-26 04:38:01','2025-08-26 04:38:02',NULL),
(1328,97,0,'Trono da Prada 👑','perdeu',0.00,'2025-08-26 04:38:03',5.00,'finalized','891a8ed887ebb1aae209d59a5fbdf9a3','[]',NULL,'2025-08-26 04:38:03','2025-08-26 04:38:03',NULL),
(1329,97,0,'Trono da Prada 👑','ganhou',5.00,'2025-08-26 04:38:05',5.00,'finalized','7cfe56ae3ce755637d4eddd755c3d1c3','[0,4,8]','5.00','2025-08-26 04:38:05','2025-08-26 04:38:05',NULL),
(1330,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:09',1.00,'finalized','8b411f88e45a99a1d994ae8afca7c2ef','[]',NULL,'2025-08-26 08:26:09','2025-08-26 08:26:09',NULL),
(1331,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:15',1.00,'finalized','c728023bf7ce964da5bab9ae3e3844d7','[]',NULL,'2025-08-26 08:26:15','2025-08-26 08:26:15',NULL),
(1332,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:20',1.00,'finalized','58b3f714809d04cbad8d537343a1c42c','[]',NULL,'2025-08-26 08:26:20','2025-08-26 08:26:21',NULL),
(1333,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:25',1.00,'finalized','5e27fa278e81779acf56794e1a236704','[]',NULL,'2025-08-26 08:26:25','2025-08-26 08:26:26',NULL),
(1334,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:30',1.00,'finalized','f2a0404d03c6bd148f6d2fe68e31b04a','[]',NULL,'2025-08-26 08:26:30','2025-08-26 08:26:30',NULL),
(1335,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:33',1.00,'finalized','aae31d6f7df5cd1966235a4e28c6dbd2','[]',NULL,'2025-08-26 08:26:33','2025-08-26 08:26:33',NULL),
(1336,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:37',1.00,'finalized','81d139071be92f2a5dfee9682cb584c0','[]',NULL,'2025-08-26 08:26:37','2025-08-26 08:26:37',NULL),
(1337,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:40',1.00,'finalized','46c83442aeaf2ebaee44d79abc4937eb','[]',NULL,'2025-08-26 08:26:40','2025-08-26 08:26:40',NULL),
(1338,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:26:44',1.00,'finalized','ca1b482684623f35a35dcbacdeef88f0','[]',NULL,'2025-08-26 08:26:44','2025-08-26 08:26:47',NULL),
(1339,152,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 08:27:20',1.00,'finalized','02b650852c0b47ac4cf1c1383fea71b6','[]',NULL,'2025-08-26 08:27:20','2025-08-26 08:27:21',NULL),
(1340,155,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 13:21:08',1.00,'finalized','cc6dea92f6eb40ba0f3b5f3d4d5a8d31','[]',NULL,'2025-08-26 13:21:08','2025-08-26 13:21:18',NULL),
(1341,155,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 13:21:23',1.00,'finalized','fa9cba2099372f4c8ebc0e846fbcb313','[]',NULL,'2025-08-26 13:21:23','2025-08-26 13:21:27',NULL),
(1342,155,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 13:21:29',1.00,'finalized','9e30d04d639d7ef3dc17b5f8999e25c7','[]',NULL,'2025-08-26 13:21:29','2025-08-26 13:21:30',NULL),
(1343,155,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 13:21:37',1.00,'finalized','605ff6d93cc59311facc9708460e1f75','[]',NULL,'2025-08-26 13:21:37','2025-08-26 13:21:43',NULL),
(1344,155,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 13:21:46',1.00,'finalized','7ef8e6a39380d91da39457effa149e46','[]',NULL,'2025-08-26 13:21:46','2025-08-26 13:21:53',NULL),
(1345,160,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-26 14:44:39',2.00,'finalized','bcc0dfa6642dc353d271d09dfdcbb722','[]',NULL,'2025-08-26 14:44:39','2025-08-26 14:45:19',NULL),
(1346,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:45:45',1.00,'finalized','b7ffd3afb8caec6a9694df2144a8a287','[]',NULL,'2025-08-26 14:45:45','2025-08-26 14:45:48',NULL),
(1347,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:21',1.00,'finalized','87d8755f46f1a249e8bd6be28247476e','[]',NULL,'2025-08-26 14:46:21','2025-08-26 14:46:22',NULL),
(1348,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:25',1.00,'finalized','202184fcd81f8cf55bf6cabd4134e7d0','[]',NULL,'2025-08-26 14:46:25','2025-08-26 14:46:27',NULL),
(1349,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:30',1.00,'finalized','b4242ae1ce7c41abd03029a328c79862','[]',NULL,'2025-08-26 14:46:30','2025-08-26 14:46:31',NULL),
(1350,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:34',1.00,'finalized','50e17d7f1a535e827fdd2d3901400274','[]',NULL,'2025-08-26 14:46:34','2025-08-26 14:46:34',NULL),
(1351,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:44',1.00,'finalized','d56e0030620f19b831c0496502b2acfd','[]',NULL,'2025-08-26 14:46:44','2025-08-26 14:46:45',NULL),
(1352,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:49',1.00,'finalized','58880bfd1d2b1fd12dc1bac13cc39665','[]',NULL,'2025-08-26 14:46:49','2025-08-26 14:46:49',NULL),
(1353,160,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 14:46:53',1.00,'finalized','38e36d1d60bebc2ad57484fed0db11b0','[]',NULL,'2025-08-26 14:46:53','2025-08-26 14:46:53',NULL),
(1354,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:01',1.00,'finalized','03e770fd4ab69648ecc60ecd9664546f','[]',NULL,'2025-08-26 15:29:01','2025-08-26 15:29:01',NULL),
(1355,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:08',1.00,'finalized','1cbea361235098d9560874911aa98b6a','[]',NULL,'2025-08-26 15:29:08','2025-08-26 15:29:08',NULL),
(1356,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:16',1.00,'finalized','dadfaad45caa19138b97c033cf5b8a9a','[]',NULL,'2025-08-26 15:29:16','2025-08-26 15:29:16',NULL),
(1357,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:20',1.00,'finalized','720693732f9053e1db3429835c16b6dd','[]',NULL,'2025-08-26 15:29:20','2025-08-26 15:29:20',NULL),
(1358,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:23',1.00,'finalized','579512104173565e70bedf64e70d3c0a','[]',NULL,'2025-08-26 15:29:23','2025-08-26 15:29:23',NULL),
(1359,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:27',1.00,'finalized','da352c7f82af280df059f12fea83aaa9','[]',NULL,'2025-08-26 15:29:27','2025-08-26 15:29:27',NULL),
(1360,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:40',1.00,'finalized','4977bbaa1c4f090b87a9095f30c69c55','[]',NULL,'2025-08-26 15:29:40','2025-08-26 15:29:41',NULL),
(1361,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:45',1.00,'finalized','5081d0512c6397dcd43872720e10492c','[]',NULL,'2025-08-26 15:29:45','2025-08-26 15:29:46',NULL),
(1362,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:49',1.00,'finalized','f9082d33fa7d31acd6bc7ccadc5a53f1','[]',NULL,'2025-08-26 15:29:49','2025-08-26 15:29:49',NULL),
(1363,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:53',1.00,'finalized','e69879d4f0aa92c92ac58f463b435187','[]',NULL,'2025-08-26 15:29:53','2025-08-26 15:29:53',NULL),
(1364,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:29:58',1.00,'finalized','b89b179b0ef5277094bc76cbe59b477f','[]',NULL,'2025-08-26 15:29:58','2025-08-26 15:29:58',NULL),
(1365,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:03',1.00,'finalized','2314832a8bb43646e27f106f45fc9d1e','[]',NULL,'2025-08-26 15:30:03','2025-08-26 15:30:03',NULL),
(1366,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:11',1.00,'finalized','df9a51d13bfb7a30a9cb867d0769a25f','[]',NULL,'2025-08-26 15:30:11','2025-08-26 15:30:11',NULL),
(1367,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:16',1.00,'finalized','987197c8e1af938c3fb2a86870d62f03','[]',NULL,'2025-08-26 15:30:16','2025-08-26 15:30:16',NULL),
(1368,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:30',1.00,'finalized','0c4cb3fb6a5e6a4dbae1c04c1aabdad2','[]',NULL,'2025-08-26 15:30:30','2025-08-26 15:30:30',NULL),
(1369,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:40',1.00,'finalized','983d5e0d35d8d629a4dcdd9d3d56c6b2','[]',NULL,'2025-08-26 15:30:40','2025-08-26 15:30:40',NULL),
(1370,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:52',1.00,'finalized','9fc0aae7fda156d679665dc81437ee45','[]',NULL,'2025-08-26 15:30:52','2025-08-26 15:30:52',NULL),
(1371,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:54',1.00,'finalized','b259583c6a9259ead361e0238561875a','[]',NULL,'2025-08-26 15:30:54','2025-08-26 15:30:55',NULL),
(1372,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:30:57',1.00,'finalized','a472728a7dc50615361a53079ed9a8b0','[]',NULL,'2025-08-26 15:30:57','2025-08-26 15:30:57',NULL),
(1373,159,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-26 15:31:02',1.00,'finalized','40e45351dfe429f5adcd2a899812f1cd','[]',NULL,'2025-08-26 15:31:02','2025-08-26 15:31:02',NULL),
(1374,113,1,'Trono da Prada 👑','ganhou',1000.00,'2025-08-26 21:02:13',5.00,'finalized','009afc1ec85206f47116d7c35668520c','[0,1,6]','1000.00','2025-08-26 21:02:13','2025-08-26 21:02:14',NULL),
(1375,113,1,'Trono da Prada 👑','ganhou',1000.00,'2025-08-26 22:09:16',5.00,'finalized','70d3846c6537527a9d6d4f929971b14e','[1,4,6]','1000.00','2025-08-26 22:09:16','2025-08-26 22:09:17',NULL),
(1376,113,1,'Trono da Prada 👑','ganhou',1000.00,'2025-08-26 22:09:27',5.00,'finalized','38a40a56d1ec74ce827a7e936c54c7dc','[4,5,6]','1000.00','2025-08-26 22:09:27','2025-08-26 22:09:28',NULL),
(1377,113,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-26 22:45:00',2.00,'finalized','5ff7e015cd094cfc346c6b80ac581476','[3,6,7]','1000.00','2025-08-26 22:45:00','2025-08-26 22:45:01',NULL),
(1378,113,1,'Baú da sorte 🎁','ganhou',5000.00,'2025-08-26 22:45:07',2.00,'finalized','a504ea984dbf9845b7cb0d6ce6c04f69','[2,4,8]','5000.00','2025-08-26 22:45:07','2025-08-26 22:45:07',NULL),
(1379,113,1,'Baú da sorte 🎁','ganhou',200.00,'2025-08-26 22:56:06',2.00,'finalized','10eeb00c633b6537d48ef2663e2bdcf6','[2,3,6]','200.00','2025-08-26 22:56:06','2025-08-26 22:56:06',NULL),
(1380,113,1,'Baú da sorte 🎁','ganhou',100.00,'2025-08-26 22:56:13',2.00,'finalized','ac0ed1fe853abc2d69ce0bd9b8035ff9','[2,5,8]','100.00','2025-08-26 22:56:13','2025-08-26 22:56:13',NULL),
(1381,113,1,'Baú da sorte 🎁','ganhou',100.00,'2025-08-26 22:56:17',2.00,'finalized','cde0cc3d28a3737012d225f3b0070fd3','[4,7,8]','100.00','2025-08-26 22:56:17','2025-08-26 22:56:18',NULL),
(1382,113,1,'Baú da sorte 🎁','ganhou',1000.00,'2025-08-26 22:56:21',2.00,'finalized','b6b0fc7515f4e18bb13422421974b934','[1,2,3]','1000.00','2025-08-26 22:56:21','2025-08-26 22:56:22',NULL),
(1383,113,1,'Baú da sorte 🎁','ganhou',200.00,'2025-08-26 22:56:36',2.00,'finalized','0826c5e533c941ddaee1450d32875ab3','[0,3,8]','200.00','2025-08-26 22:56:36','2025-08-26 22:56:43',NULL),
(1384,113,1,'Baú da sorte 🎁','ganhou',200.00,'2025-08-26 22:56:54',2.00,'finalized','609465b7d30a76110505596dc0e86951','[0,1,2]','200.00','2025-08-26 22:56:54','2025-08-26 22:57:00',NULL),
(1385,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:19:57',1.00,'finalized','b836bf9be1343923952dda8e17714625','[]',NULL,'2025-08-27 00:19:57','2025-08-27 00:20:15',NULL),
(1386,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:20:22',1.00,'finalized','336c12c14a5af9f50ccd869e3fe0b2bd','[]',NULL,'2025-08-27 00:20:22','2025-08-27 00:20:27',NULL),
(1387,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:20:36',1.00,'finalized','7f0688787cbaaf6bf258c374d93250d6','[]',NULL,'2025-08-27 00:20:36','2025-08-27 00:20:57',NULL),
(1388,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:01',1.00,'finalized','a1fc7a075f25388270ec1c4b4ca9dc01','[]',NULL,'2025-08-27 00:21:01','2025-08-27 00:21:02',NULL),
(1389,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:07',1.00,'finalized','f3e71231b2b1670b400bf0a9eac1f829','[]',NULL,'2025-08-27 00:21:07','2025-08-27 00:21:08',NULL),
(1390,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:16',1.00,'finalized','3c3a190fc86670ccd5f0a3739bde1121','[]',NULL,'2025-08-27 00:21:16','2025-08-27 00:21:33',NULL),
(1391,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:36',1.00,'finalized','76b25613b53733b3b65ad6b14b6c90b0','[]',NULL,'2025-08-27 00:21:36','2025-08-27 00:21:36',NULL),
(1392,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:42',1.00,'finalized','9077408bba8e34191e5656b2e880cd5e','[]',NULL,'2025-08-27 00:21:42','2025-08-27 00:21:42',NULL),
(1393,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:45',1.00,'finalized','ee029e5d11f8377f3617533bcc02c892','[]',NULL,'2025-08-27 00:21:45','2025-08-27 00:21:46',NULL),
(1394,168,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 00:21:49',1.00,'finalized','5debc7c8fd7cd8627694e62c4f8c4619','[]',NULL,'2025-08-27 00:21:49','2025-08-27 00:21:50',NULL),
(1395,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:45:43',1.00,'finalized','f4a922ced3d82cb175c95a01c6796ee1','[]',NULL,'2025-08-27 04:45:43','2025-08-27 04:45:50',NULL),
(1396,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:45:59',1.00,'finalized','4d988a07fe1d9a090cef089dc777a40d','[]',NULL,'2025-08-27 04:45:59','2025-08-27 04:45:59',NULL),
(1397,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:46:04',1.00,'finalized','42d4a16099c312bffcc69bdf7a32f1df','[]',NULL,'2025-08-27 04:46:04','2025-08-27 04:46:05',NULL),
(1398,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:46:12',1.00,'finalized','520d171d152b3f104811e520f36198b1','[]',NULL,'2025-08-27 04:46:12','2025-08-27 04:46:12',NULL),
(1399,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:46:19',1.00,'finalized','a32656f4d65705619ef9a4923a0baeca','[]',NULL,'2025-08-27 04:46:19','2025-08-27 04:46:20',NULL),
(1400,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:46:23',1.00,'finalized','a090316aa14cef219be3839820774117','[]',NULL,'2025-08-27 04:46:23','2025-08-27 04:46:24',NULL),
(1401,172,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 04:46:28',1.00,'finalized','f052e5a6a5451a905af8dcb7b5425283','[]',NULL,'2025-08-27 04:46:28','2025-08-27 04:46:29',NULL),
(1402,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:16',1.00,'finalized','77268c3ae643ce4e2773692668d8feb9','[]',NULL,'2025-08-27 11:48:16','2025-08-27 11:48:16',NULL),
(1403,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:20',1.00,'finalized','7fc2aff87d45f6b80e214dd3519b6757','[]',NULL,'2025-08-27 11:48:20','2025-08-27 11:48:21',NULL),
(1404,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:24',1.00,'finalized','fd5445d3569813c0be86a690b5ddde70','[]',NULL,'2025-08-27 11:48:24','2025-08-27 11:48:25',NULL),
(1405,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:29',1.00,'finalized','fd54599220c5d31ceb0aa27cba94801a','[]',NULL,'2025-08-27 11:48:29','2025-08-27 11:48:29',NULL),
(1406,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:32',1.00,'finalized','3c59797cc5a6dc4ebdef5d17895e631b','[]',NULL,'2025-08-27 11:48:32','2025-08-27 11:48:32',NULL),
(1407,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:41',1.00,'finalized','2132b499335330ab14058cf371afa5fa','[]',NULL,'2025-08-27 11:48:41','2025-08-27 11:48:41',NULL),
(1408,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:50',1.00,'finalized','256fb668810d4a6c4bb46ae542cc6b75','[]',NULL,'2025-08-27 11:48:50','2025-08-27 11:48:54',NULL),
(1409,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:48:58',1.00,'finalized','6ed8b9260063d132220342fc90e154e3','[]',NULL,'2025-08-27 11:48:58','2025-08-27 11:48:58',NULL),
(1410,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:01',1.00,'finalized','7af342f7527c96390ef6828e243f2e2e','[]',NULL,'2025-08-27 11:49:01','2025-08-27 11:49:01',NULL),
(1411,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:04',1.00,'finalized','578ca7ee39fc0220bddce027ec39c96b','[]',NULL,'2025-08-27 11:49:04','2025-08-27 11:49:04',NULL),
(1412,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:07',1.00,'finalized','2d591d956f4a9fd03606a29622588332','[]',NULL,'2025-08-27 11:49:07','2025-08-27 11:49:08',NULL),
(1413,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:12',1.00,'finalized','4e9e709b0a03098a12118b8903c4e629','[]',NULL,'2025-08-27 11:49:12','2025-08-27 11:49:12',NULL),
(1414,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:16',1.00,'finalized','9d9c2f34d28164c9f743f0f793880fa2','[]',NULL,'2025-08-27 11:49:16','2025-08-27 11:49:16',NULL),
(1415,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:18',1.00,'finalized','74f427402a058cbdc1db018e9c0de276','[]',NULL,'2025-08-27 11:49:18','2025-08-27 11:49:19',NULL),
(1416,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:22',1.00,'finalized','16325714cd942311530c55b63ef5f94f','[]',NULL,'2025-08-27 11:49:22','2025-08-27 11:49:22',NULL),
(1417,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:25',1.00,'finalized','c86eb09d5fe36612a9c95a5b91479171','[]',NULL,'2025-08-27 11:49:25','2025-08-27 11:49:26',NULL),
(1418,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:29',1.00,'finalized','6142d0086c13a67d514cfd7ae6c28e23','[]',NULL,'2025-08-27 11:49:29','2025-08-27 11:49:29',NULL),
(1419,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:32',1.00,'finalized','51e9fe3abadc0caa6f414f28b27981d3','[]',NULL,'2025-08-27 11:49:32','2025-08-27 11:49:32',NULL),
(1420,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:36',1.00,'finalized','6eebf8edaaae733f283efba5b53960b5','[]',NULL,'2025-08-27 11:49:36','2025-08-27 11:49:36',NULL),
(1421,174,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 11:49:39',1.00,'finalized','ee5033c30dd2912af471e52c1751d426','[]',NULL,'2025-08-27 11:49:39','2025-08-27 11:49:39',NULL),
(1422,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:17:52',1.00,'finalized','bde082d1a13e5c073071b9b0b082d24b','[]',NULL,'2025-08-27 12:17:52','2025-08-27 12:18:01',NULL),
(1423,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:06',1.00,'finalized','f50d3b614193fb8a67dfe965b506e41b','[]',NULL,'2025-08-27 12:18:06','2025-08-27 12:18:06',NULL),
(1424,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:09',1.00,'finalized','5685a0ea90b1cc9319904dbf52378e7b','[]',NULL,'2025-08-27 12:18:09','2025-08-27 12:18:09',NULL),
(1425,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:11',1.00,'finalized','5324ac7a7f59cf2d8d314c5daec01757','[]',NULL,'2025-08-27 12:18:11','2025-08-27 12:18:12',NULL),
(1426,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:14',1.00,'finalized','05c2fd0f1c5f19e5230c24143846d6e5','[]',NULL,'2025-08-27 12:18:14','2025-08-27 12:18:14',NULL),
(1427,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:17',1.00,'finalized','267aa05a97b9d0956ecdba107ce3ac29','[]',NULL,'2025-08-27 12:18:17','2025-08-27 12:18:17',NULL),
(1428,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:19',1.00,'finalized','a48a580888d2eeabea92af0ad92312b2','[]',NULL,'2025-08-27 12:18:19','2025-08-27 12:18:20',NULL),
(1429,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:22',1.00,'finalized','8ee4a683383148a5a7f3a46a861eb801','[]',NULL,'2025-08-27 12:18:22','2025-08-27 12:18:22',NULL),
(1430,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:25',1.00,'finalized','4d71850c13a3107ae27460e7868ae336','[]',NULL,'2025-08-27 12:18:25','2025-08-27 12:18:25',NULL),
(1431,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:18:27',1.00,'finalized','f6af98de17072be381b93c77422cede9','[]',NULL,'2025-08-27 12:18:27','2025-08-27 12:18:27',NULL),
(1432,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:19:36',1.00,'finalized','52538ff6478cab57abdfe53d9dea9272','[]',NULL,'2025-08-27 12:19:36','2025-08-27 12:19:36',NULL),
(1433,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:19:39',1.00,'finalized','a9de70667211a02498d3a31f0a419585','[]',NULL,'2025-08-27 12:19:39','2025-08-27 12:19:44',NULL),
(1434,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:19:48',1.00,'finalized','9b073cc9893b464e1cdd29b841187294','[]',NULL,'2025-08-27 12:19:48','2025-08-27 12:19:56',NULL),
(1435,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:19:58',1.00,'finalized','2755022b92dbc77f3a97d12d15d78ff7','[]',NULL,'2025-08-27 12:19:58','2025-08-27 12:20:16',NULL),
(1436,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:19',1.00,'finalized','4ac7c3c35c58eec06f6a4425310fdff6','[]',NULL,'2025-08-27 12:20:19','2025-08-27 12:20:19',NULL),
(1437,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:20',1.00,'finalized','a279a130352e08ed0346b64dd5d846d3','[]',NULL,'2025-08-27 12:20:20','2025-08-27 12:20:21',NULL),
(1438,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:23',1.00,'finalized','937f4ea8fb6b510099276329187dba5f','[]',NULL,'2025-08-27 12:20:23','2025-08-27 12:20:23',NULL),
(1439,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:24',1.00,'finalized','aa9b79ad01c918b49a223c01b42a572c','[]',NULL,'2025-08-27 12:20:24','2025-08-27 12:20:24',NULL),
(1440,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:26',1.00,'finalized','890e364b367f0b2bc7c9ae61604ab342','[]',NULL,'2025-08-27 12:20:26','2025-08-27 12:20:26',NULL),
(1441,175,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 12:20:28',1.00,'finalized','8162f979a32005eff396952cbfabf9cf','[]',NULL,'2025-08-27 12:20:28','2025-08-27 12:20:28',NULL),
(1442,175,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 12:20:35',2.00,'finalized','492842b2f52f2590f361d43f0443a497','[]',NULL,'2025-08-27 12:20:35','2025-08-27 12:20:35',NULL),
(1443,175,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 12:20:37',2.00,'finalized','1a0419c0382cdf408e469006843338b3','[]',NULL,'2025-08-27 12:20:37','2025-08-27 12:20:37',NULL),
(1444,175,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 12:20:39',2.00,'finalized','485a292801a7074e00978150b45c4abc','[]',NULL,'2025-08-27 12:20:39','2025-08-27 12:20:39',NULL),
(1445,175,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 12:20:41',2.00,'finalized','c79f45d1fe759123a13be46e38779ca3','[]',NULL,'2025-08-27 12:20:41','2025-08-27 12:20:41',NULL),
(1446,175,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 12:20:43',2.00,'finalized','22f2cb0a25585de81897bba880ce3f6c','[]',NULL,'2025-08-27 12:20:43','2025-08-27 12:20:43',NULL),
(1447,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 13:59:29',1.00,'finalized','64a209353cfc2932d950fe7f23fe8770','[]',NULL,'2025-08-27 13:59:29','2025-08-27 13:59:32',NULL),
(1448,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 13:59:39',1.00,'finalized','a57eef8d76508a5ba8d2451c91a6d0e6','[]',NULL,'2025-08-27 13:59:39','2025-08-27 13:59:39',NULL),
(1449,176,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-27 13:59:44',1.00,'finalized','65ae039afd367c30ddb56a0309f71610','[0,3,5]','5.00','2025-08-27 13:59:44','2025-08-27 13:59:52',NULL),
(1450,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 13:59:59',1.00,'finalized','e49b56e2972af12bc60aba8ef08d16d5','[]',NULL,'2025-08-27 13:59:59','2025-08-27 14:00:12',NULL),
(1451,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:00:18',1.00,'finalized','484553fa7b6c527de4ba0c1caad07a94','[]',NULL,'2025-08-27 14:00:18','2025-08-27 14:00:27',NULL),
(1452,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:00:43',1.00,'finalized','3fd65e3abb9356fb704394ad5322ef4c','[]',NULL,'2025-08-27 14:00:43','2025-08-27 14:00:49',NULL),
(1453,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:00:53',1.00,'finalized','7d64da5eded8af85403ab5117e753305','[]',NULL,'2025-08-27 14:00:53','2025-08-27 14:00:59',NULL),
(1454,176,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:01:04',1.00,'finalized','c1d753fdd99a30b64045f486e44749ad','[]',NULL,'2025-08-27 14:01:04','2025-08-27 14:01:04',NULL),
(1455,176,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:01:24',2.00,'finalized','ce055e481f23aa4ceeb50dcdb7d0ce37','[]',NULL,'2025-08-27 14:01:24','2025-08-27 14:01:29',NULL),
(1456,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:15:35',1.00,'finalized','fae3558c973eb99e79508fe632f7c1e4','[]',NULL,'2025-08-27 14:15:35','2025-08-27 14:16:06',NULL),
(1457,177,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:16:18',1.00,'finalized','a129478d974ef34d20f6829be2b74bba','[0,2,4]','0.50','2025-08-27 14:16:18','2025-08-27 14:16:21',NULL),
(1458,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:16:30',1.00,'finalized','66b1b0bc0c08eb56d317186a36b51d8d','[]',NULL,'2025-08-27 14:16:30','2025-08-27 14:16:30',NULL),
(1459,177,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:16:48',1.00,'finalized','872255d477f3e28e2d4415608baf96df','[0,1,2]','1.00','2025-08-27 14:16:48','2025-08-27 14:17:05',NULL),
(1460,177,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:17:13',1.00,'finalized','f627da51c39c4586aba025aa4d8bc177','[1,4,8]','0.50','2025-08-27 14:17:13','2025-08-27 14:17:14',NULL),
(1461,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:17:26',1.00,'finalized','2d093c64b8e6b3ec513d8d1c9871ccad','[]',NULL,'2025-08-27 14:17:26','2025-08-27 14:17:37',NULL),
(1462,177,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-27 14:17:47',1.00,'finalized','f04bf9490e26271974a4c356daa846b5','[3,6,7]','2.00','2025-08-27 14:17:47','2025-08-27 14:17:48',NULL),
(1463,177,0,'Cofrinho mágico 🪙','ganhou',20.00,'2025-08-27 14:17:57',1.00,'finalized','d22339914f7876e26b1d911da6a97485','[0,3,5]','20.00','2025-08-27 14:17:57','2025-08-27 14:17:59',NULL),
(1464,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:18:12',1.00,'finalized','267447ca59b55ed832630517ec6ba3af','[]',NULL,'2025-08-27 14:18:12','2025-08-27 14:18:43',NULL),
(1465,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:18:49',1.00,'finalized','8c06a6e34c9bfeb3697da49ed7f7b337','[]',NULL,'2025-08-27 14:18:49','2025-08-27 14:18:50',NULL),
(1466,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:18:56',1.00,'finalized','efbde05d43b438cea58c83f090cc85ee','[]',NULL,'2025-08-27 14:18:56','2025-08-27 14:18:56',NULL),
(1467,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:19:01',1.00,'finalized','fed9033608a24390b554c61a1971584c','[]',NULL,'2025-08-27 14:19:01','2025-08-27 14:19:02',NULL),
(1468,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:19:12',1.00,'finalized','4285a39195efb074385850c6394164b6','[]',NULL,'2025-08-27 14:19:12','2025-08-27 14:19:12',NULL),
(1469,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:19:22',1.00,'finalized','eebff16a248ee10713babdd813bad6a1','[]',NULL,'2025-08-27 14:19:22','2025-08-27 14:19:22',NULL),
(1470,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:19:26',1.00,'finalized','d5944cf95ef9dda5b5bfa793119bcd05','[]',NULL,'2025-08-27 14:19:26','2025-08-27 14:19:33',NULL),
(1471,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:19:39',1.00,'finalized','d15e6580ff5413b45fe444ee5b4e8bb5','[]',NULL,'2025-08-27 14:19:39','2025-08-27 14:20:18',NULL),
(1472,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:20:27',1.00,'finalized','2222dd224cb300b631b99bc0a96185d3','[]',NULL,'2025-08-27 14:20:27','2025-08-27 14:20:27',NULL),
(1473,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:20:33',1.00,'finalized','fb4a8279b52d8089a3ffc1e416de23d4','[]',NULL,'2025-08-27 14:20:33','2025-08-27 14:20:33',NULL),
(1474,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:20:38',1.00,'finalized','f22dc376e4efe82bd59317d77487f944','[]',NULL,'2025-08-27 14:20:38','2025-08-27 14:21:12',NULL),
(1475,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:11',1.00,'finalized','149e6d8a59e200e63baa18608262723b','[]',NULL,'2025-08-27 14:21:11','2025-08-27 14:21:19',NULL),
(1476,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:19',1.00,'finalized','34a92eaa31ed2466eb6e101b9a92380d','[]',NULL,'2025-08-27 14:21:19','2025-08-27 14:21:20',NULL),
(1477,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:25',1.00,'finalized','77f98a9009c36d0890417cb6729da09c','[]',NULL,'2025-08-27 14:21:25','2025-08-27 14:21:25',NULL),
(1478,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:26',1.00,'finalized','cdadebedb1a43b596a97b732f319e3ae','[]',NULL,'2025-08-27 14:21:26','2025-08-27 14:21:27',NULL),
(1479,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:28',1.00,'finalized','b68664bd62db37136175c7cd782b1593','[]',NULL,'2025-08-27 14:21:28','2025-08-27 14:21:29',NULL),
(1480,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:32',1.00,'finalized','db8ebee9bfbe6fa937d444ba1274c028','[]',NULL,'2025-08-27 14:21:32','2025-08-27 14:21:32',NULL),
(1481,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:34',1.00,'finalized','e8fce67ef362d54ec95e4d625e8f2dc7','[]',NULL,'2025-08-27 14:21:34','2025-08-27 14:21:34',NULL),
(1482,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:37',1.00,'finalized','d9f00709154cc79a1f2caaab03372128','[]',NULL,'2025-08-27 14:21:37','2025-08-27 14:21:45',NULL),
(1483,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:42',1.00,'finalized','66fab977b2b013419bd6ae5766be7c50','[]',NULL,'2025-08-27 14:21:42','2025-08-27 14:21:43',NULL),
(1484,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:21:51',1.00,'finalized','256394d0d20f5da41a2a18f5a2b74b42','[]',NULL,'2025-08-27 14:21:51','2025-08-27 14:21:52',NULL),
(1485,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:22:00',1.00,'finalized','504828cd5b783cc06ee1d971a4f5b27d','[]',NULL,'2025-08-27 14:22:00','2025-08-27 14:22:01',NULL),
(1486,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:22:08',1.00,'finalized','73b6371c72bed00a3598c9efc5064f3a','[]',NULL,'2025-08-27 14:22:08','2025-08-27 14:22:17',NULL),
(1487,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:22:31',1.00,'finalized','955f18ac404c35e1c5182864bf67a7b6','[]',NULL,'2025-08-27 14:22:31','2025-08-27 14:22:32',NULL),
(1488,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:22:34',1.00,'finalized','350c635a12ba6cce93d892f07b4086e7','[]',NULL,'2025-08-27 14:22:34','2025-08-27 14:22:35',NULL),
(1489,177,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-27 14:22:35',1.00,'finalized','36424e679e6c0ac69d011ed55f4a264c','[3,4,8]','2.00','2025-08-27 14:22:35','2025-08-27 14:22:39',NULL),
(1490,178,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:22:38',2.00,'finalized','b794fa3a36e4fef38ed1fa8adae257d4','[]',NULL,'2025-08-27 14:22:38','2025-08-27 14:22:46',NULL),
(1491,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:22:41',1.00,'finalized','0e834eadf4f321f8c73f1cd4e2b5ca5e','[]',NULL,'2025-08-27 14:22:41','2025-08-27 14:22:42',NULL),
(1492,177,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:22:42',1.00,'finalized','0b6f217909106c72bb20b843b604c123','[1,5,6]','1.00','2025-08-27 14:22:42','2025-08-27 14:22:43',NULL),
(1493,177,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:22:45',1.00,'finalized','d1d8616246dbc613f2985356cb70e5ca','[1,5,6]','0.50','2025-08-27 14:22:45','2025-08-27 14:22:47',NULL),
(1494,178,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:22:48',2.00,'finalized','7702f023837b003b56cdc5b6e4dd6422','[]',NULL,'2025-08-27 14:22:48','2025-08-27 14:22:49',NULL),
(1495,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:00',1.00,'finalized','040e5b0dde44c0f5b13a247dae877309','[]',NULL,'2025-08-27 14:23:00','2025-08-27 14:23:00',NULL),
(1496,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:02',1.00,'finalized','3aa504d641f5fa797d712bcc7508cbe5','[]',NULL,'2025-08-27 14:23:02','2025-08-27 14:23:03',NULL),
(1497,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:04',1.00,'finalized','f59772f2a41ffd1de5a3a792609f37cc','[]',NULL,'2025-08-27 14:23:04','2025-08-27 14:23:04',NULL),
(1498,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:05',1.00,'finalized','ff4e63bf47437d006ec7b18739763ed6','[]',NULL,'2025-08-27 14:23:05','2025-08-27 14:23:05',NULL),
(1499,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:07',1.00,'finalized','b6ec113b10b3db7937fc04e31df75362','[]',NULL,'2025-08-27 14:23:07','2025-08-27 14:23:07',NULL),
(1500,178,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:23:16',1.00,'finalized','d737f488450d62dbaa819546d5bb6287','[2,6,7]','0.50','2025-08-27 14:23:16','2025-08-27 14:23:17',NULL),
(1501,178,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-27 14:23:20',1.00,'finalized','30b8a5eaab606a8bf39cf218a6836059','[3,4,8]','2.00','2025-08-27 14:23:20','2025-08-27 14:23:21',NULL),
(1502,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:23:25',1.00,'finalized','98bdb408621980d0757ab71a0e619400','[0,1,6]','1.00','2025-08-27 14:23:25','2025-08-27 14:23:26',NULL),
(1503,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:28',2.00,'finalized','ca7f11a29c5b30ff960766c0691c52ec','[]',NULL,'2025-08-27 14:23:28','2025-08-27 14:23:29',NULL),
(1504,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:29',1.00,'finalized','76b593ee5fc8eb7d8d7cb8468218bf36','[]',NULL,'2025-08-27 14:23:29','2025-08-27 14:23:29',NULL),
(1505,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:30',2.00,'finalized','4fb454b92af68fb3e91cd6a3cc0a6ea4','[]',NULL,'2025-08-27 14:23:30','2025-08-27 14:23:30',NULL),
(1506,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:31',2.00,'finalized','0ae99de588defe19fc36bcb33d92b969','[]',NULL,'2025-08-27 14:23:31','2025-08-27 14:23:32',NULL),
(1507,178,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-27 14:23:32',1.00,'finalized','99836e03ffb72ef4cab2a3a7da0e94d2','[3,7,8]','2.00','2025-08-27 14:23:32','2025-08-27 14:23:33',NULL),
(1508,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:32',2.00,'finalized','aeeb9cb4c6acbde241e982313a72a2cc','[]',NULL,'2025-08-27 14:23:32','2025-08-27 14:23:33',NULL),
(1509,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:34',2.00,'finalized','dc59d94e1002255223e6df5bc4c17f87','[]',NULL,'2025-08-27 14:23:34','2025-08-27 14:23:34',NULL),
(1510,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:35',1.00,'finalized','e16901e30750c4075dd174c8d0d54e1a','[]',NULL,'2025-08-27 14:23:35','2025-08-27 14:23:35',NULL),
(1511,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:35',2.00,'finalized','f0ccdbced08e784d88ade0932ce21c75','[]',NULL,'2025-08-27 14:23:35','2025-08-27 14:23:36',NULL),
(1512,177,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 14:23:36',2.00,'finalized','e5add5490f4641168363476e2de955d1','[]',NULL,'2025-08-27 14:23:36','2025-08-27 14:23:36',NULL),
(1513,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:39',1.00,'finalized','5055441d95b41c30a0351262088f48e4','[]',NULL,'2025-08-27 14:23:39','2025-08-27 14:23:39',NULL),
(1514,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:23:42',1.00,'finalized','95532e05d3348dd2a3aedf2b3a9b6bfd','[]',NULL,'2025-08-27 14:23:42','2025-08-27 14:23:47',NULL),
(1515,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:23:50',1.00,'finalized','421a1b478c9bb2287ada0ba176ca4a56','[2,4,6]','1.00','2025-08-27 14:23:50','2025-08-27 14:23:51',NULL),
(1516,178,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:23:54',1.00,'finalized','358b5e2061ffd276a68300001d15d349','[3,6,8]','0.50','2025-08-27 14:23:54','2025-08-27 14:23:55',NULL),
(1517,178,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:23:57',1.00,'finalized','07d41b8a9659ed19498f75c1a6247170','[0,2,7]','0.50','2025-08-27 14:23:57','2025-08-27 14:23:58',NULL),
(1518,178,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:24:00',1.00,'finalized','eed5cc1fa8c3a4927c83a383a26c400c','[0,5,7]','0.50','2025-08-27 14:24:00','2025-08-27 14:24:01',NULL),
(1519,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:24:05',1.00,'finalized','24cf7c0bd679a325b0779737b2133402','[]',NULL,'2025-08-27 14:24:05','2025-08-27 14:24:06',NULL),
(1520,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:24:10',1.00,'finalized','859061fb6a4825daf27872fc81e50ea1','[3,5,8]','1.00','2025-08-27 14:24:10','2025-08-27 14:24:11',NULL),
(1521,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:24:33',1.00,'finalized','94112d40b2be2c8fb1fff33a642143b5','[]',NULL,'2025-08-27 14:24:33','2025-08-27 14:24:33',NULL),
(1522,178,0,'Cofrinho mágico 🪙','ganhou',2.00,'2025-08-27 14:24:36',1.00,'finalized','c6b4d8378111e3d0d0069ff72eea1c96','[6,7,8]','2.00','2025-08-27 14:24:36','2025-08-27 14:24:37',NULL),
(1523,178,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-27 14:24:40',1.00,'finalized','4845eedbce8af58ee55babd8e0eb0a2a','[2,6,7]','5.00','2025-08-27 14:24:40','2025-08-27 14:24:48',NULL),
(1524,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:24:51',1.00,'finalized','2a8407bef8de0fe766947ede0cc92104','[4,6,7]','1.00','2025-08-27 14:24:51','2025-08-27 14:25:03',NULL),
(1525,177,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:24:56',1.00,'finalized','bdca0f4fa91f9e6daa13a5498eb459de','[2,6,8]','1.00','2025-08-27 14:24:56','2025-08-27 14:24:56',NULL),
(1526,177,0,'Cofrinho mágico 🪙','ganhou',0.50,'2025-08-27 14:25:01',1.00,'finalized','f878e7409b61d44d9d81ec3693caec46','[1,2,4]','0.50','2025-08-27 14:25:01','2025-08-27 14:25:02',NULL),
(1527,177,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:25:05',1.00,'finalized','76c1146dd90d8325c5e572a7dcaaf17b','[]',NULL,'2025-08-27 14:25:05','2025-08-27 14:25:05',NULL),
(1528,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:25:06',1.00,'finalized','249db533d0b8bdce6a0b68bb5187744b','[2,5,6]','1.00','2025-08-27 14:25:06','2025-08-27 14:25:15',NULL),
(1529,178,0,'Cofrinho mágico 🪙','ganhou',5.00,'2025-08-27 14:25:18',1.00,'finalized','8b8d7c400d814909567921f7254ab37e','[4,7,8]','5.00','2025-08-27 14:25:18','2025-08-27 14:25:28',NULL),
(1530,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:25:32',1.00,'finalized','ec2ea3116c019feac20027235835e5b7','[]',NULL,'2025-08-27 14:25:32','2025-08-27 14:25:41',NULL),
(1531,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:25:47',1.00,'finalized','c310219c854f3177fe7b10c52287a924','[]',NULL,'2025-08-27 14:25:47','2025-08-27 14:25:48',NULL),
(1532,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:25:50',1.00,'finalized','1af1cafe568ed149d0f84ceb63ac7249','[]',NULL,'2025-08-27 14:25:50','2025-08-27 14:25:51',NULL),
(1533,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:25:53',1.00,'finalized','3289a7723d8dc18a41086f9d34052b8a','[0,4,8]','1.00','2025-08-27 14:25:53','2025-08-27 14:25:54',NULL),
(1534,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:25:56',1.00,'finalized','9be81ca646e8528bf21a3d3dbfaee561','[]',NULL,'2025-08-27 14:25:56','2025-08-27 14:25:56',NULL),
(1535,178,0,'Cofrinho mágico 🪙','ganhou',1.00,'2025-08-27 14:25:59',1.00,'finalized','45a50f93678fb04133257fbb105a88a9','[2,7,8]','1.00','2025-08-27 14:25:59','2025-08-27 14:26:07',NULL),
(1536,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:09',1.00,'finalized','eecbb08177ad1c1fe5e92b5233602ee8','[]',NULL,'2025-08-27 14:26:09','2025-08-27 14:26:10',NULL),
(1537,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:12',1.00,'finalized','03621aa1ffd1f4e52546ba01792d8813','[]',NULL,'2025-08-27 14:26:12','2025-08-27 14:26:16',NULL),
(1538,178,0,'Cofrinho mágico 🪙','ganhou',100.00,'2025-08-27 14:26:19',1.00,'finalized','abcc9e5ddcb7c8cfa0c29030dfddc7a2','[4,5,8]','100.00','2025-08-27 14:26:19','2025-08-27 14:26:24',NULL),
(1539,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:27',1.00,'finalized','c6b1968a42c0115bab29874a0fe13616','[]',NULL,'2025-08-27 14:26:27','2025-08-27 14:26:41',NULL),
(1540,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:45',1.00,'finalized','4a09eef87f8c75f1cd4320b2b84c4ec0','[]',NULL,'2025-08-27 14:26:45','2025-08-27 14:26:50',NULL),
(1541,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:52',1.00,'finalized','bc4192438a7b36df67d017932cfe7c4b','[]',NULL,'2025-08-27 14:26:52','2025-08-27 14:26:52',NULL),
(1542,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:54',1.00,'finalized','766df3f1c9fbb50c69e1a14a647273b4','[]',NULL,'2025-08-27 14:26:54','2025-08-27 14:26:54',NULL),
(1543,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:56',1.00,'finalized','d55999d5db402f8f0b0b8385e55d3b7e','[]',NULL,'2025-08-27 14:26:56','2025-08-27 14:26:57',NULL),
(1544,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:26:59',1.00,'finalized','060652e025830663dcb20893c9aa6892','[]',NULL,'2025-08-27 14:26:59','2025-08-27 14:26:59',NULL),
(1545,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:01',1.00,'finalized','2503176a28749156fb8ca159eeeba217','[]',NULL,'2025-08-27 14:27:01','2025-08-27 14:27:05',NULL),
(1546,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:08',1.00,'finalized','747092b78fa1e75c7af7887632c6d332','[]',NULL,'2025-08-27 14:27:08','2025-08-27 14:27:13',NULL),
(1547,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:16',1.00,'finalized','fec7c4774cf2b4f08101acca826cfdc8','[]',NULL,'2025-08-27 14:27:16','2025-08-27 14:27:20',NULL),
(1548,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:23',1.00,'finalized','7a766edeb607ca4b3092e15cb4cdc6c4','[]',NULL,'2025-08-27 14:27:23','2025-08-27 14:27:23',NULL),
(1549,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:26',1.00,'finalized','481807b269da85944f110d830f52e8f1','[]',NULL,'2025-08-27 14:27:26','2025-08-27 14:27:26',NULL),
(1550,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:27:30',1.00,'finalized','67c54f1a411127d931a3e8d6ee0072d2','[]',NULL,'2025-08-27 14:27:30','2025-08-27 14:27:31',NULL),
(1551,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:35',1.00,'finalized','8b154e6271e6d1dcb4b69a03e64a0895','[]',NULL,'2025-08-27 14:31:35','2025-08-27 14:31:36',NULL),
(1552,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:39',1.00,'finalized','4cc3bc4c5647634753dd132652848cc4','[]',NULL,'2025-08-27 14:31:39','2025-08-27 14:31:43',NULL),
(1553,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:46',1.00,'finalized','36b7a33d0928c73e404d1bcc0c2794a8','[]',NULL,'2025-08-27 14:31:46','2025-08-27 14:31:47',NULL),
(1554,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:48',1.00,'finalized','1cbe7062148f8dbb85a593dcb0be4d11','[]',NULL,'2025-08-27 14:31:48','2025-08-27 14:31:49',NULL),
(1555,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:50',1.00,'finalized','bfe634867b95fd3eb9abd1ce92edfd7e','[]',NULL,'2025-08-27 14:31:50','2025-08-27 14:31:50',NULL),
(1556,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:52',1.00,'finalized','79be913d76491ce256bef564ff5987b8','[]',NULL,'2025-08-27 14:31:52','2025-08-27 14:31:52',NULL),
(1557,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:54',1.00,'finalized','72088307ea7332db53205b548ea83b0b','[]',NULL,'2025-08-27 14:31:54','2025-08-27 14:31:54',NULL),
(1558,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:55',1.00,'finalized','950a9944a8f49a0fc9ce8cd943eedc60','[]',NULL,'2025-08-27 14:31:55','2025-08-27 14:31:55',NULL),
(1559,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:56',1.00,'finalized','31bab50118bb7e948269a61802bec35a','[]',NULL,'2025-08-27 14:31:56','2025-08-27 14:31:56',NULL),
(1560,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:31:57',1.00,'finalized','5f871afb6b2bc6a71408dd9a2fc5cfa5','[]',NULL,'2025-08-27 14:31:57','2025-08-27 14:31:57',NULL),
(1561,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:01',1.00,'finalized','b602d2ff2f8fd3652b2fc48025aaa372','[]',NULL,'2025-08-27 14:32:01','2025-08-27 14:32:06',NULL),
(1562,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:09',1.00,'finalized','e395a10b6ffcce96a7923605d76fc8d8','[]',NULL,'2025-08-27 14:32:09','2025-08-27 14:32:14',NULL),
(1563,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:17',1.00,'finalized','23343f64134787540399de97c2df407d','[]',NULL,'2025-08-27 14:32:17','2025-08-27 14:32:17',NULL),
(1564,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:20',1.00,'finalized','57e1575f66fdbfe63f5c6636c3e66876','[]',NULL,'2025-08-27 14:32:20','2025-08-27 14:32:20',NULL),
(1565,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:22',1.00,'finalized','231df8cb7ec3bab6898d1a901d9a4acf','[]',NULL,'2025-08-27 14:32:22','2025-08-27 14:32:22',NULL),
(1566,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:26',1.00,'finalized','2b656816ad3b310a64f30d03b90eda0e','[]',NULL,'2025-08-27 14:32:26','2025-08-27 14:32:26',NULL),
(1567,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:27',1.00,'finalized','e3a40644fb31d476c5aed391786c5cd4','[]',NULL,'2025-08-27 14:32:27','2025-08-27 14:32:28',NULL),
(1568,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:30',1.00,'finalized','e7a95a3a1f63779266a80b0032af8a1b','[]',NULL,'2025-08-27 14:32:30','2025-08-27 14:32:31',NULL),
(1569,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:33',1.00,'finalized','c4fa80bd382d8202705f9f8892e8b532','[]',NULL,'2025-08-27 14:32:33','2025-08-27 14:32:33',NULL),
(1570,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:35',1.00,'finalized','3ec9d4dc88f5c5a3069a6610f3c2a257','[]',NULL,'2025-08-27 14:32:35','2025-08-27 14:32:35',NULL),
(1571,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:36',1.00,'finalized','0fec18312d78ff940c08f5fef6978b11','[]',NULL,'2025-08-27 14:32:36','2025-08-27 14:32:36',NULL),
(1572,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:37',1.00,'finalized','243a83dfa3876ac3a42890c194f0d149','[]',NULL,'2025-08-27 14:32:37','2025-08-27 14:32:37',NULL),
(1573,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:39',1.00,'finalized','f210460c678d68ac4018d78842138bae','[]',NULL,'2025-08-27 14:32:39','2025-08-27 14:32:39',NULL),
(1574,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:40',1.00,'finalized','f80d82d1ac977640f639dc3f8c0d31ed','[]',NULL,'2025-08-27 14:32:40','2025-08-27 14:32:40',NULL),
(1575,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:42',1.00,'finalized','e4f09e65e16657f2e6eb64f48fcf821b','[]',NULL,'2025-08-27 14:32:42','2025-08-27 14:32:42',NULL),
(1576,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:43',1.00,'finalized','e5d54c5e4dded378099267faee3485af','[]',NULL,'2025-08-27 14:32:43','2025-08-27 14:32:44',NULL),
(1577,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:46',1.00,'finalized','3c3fd743964138ddfc488f1f255e0dba','[]',NULL,'2025-08-27 14:32:46','2025-08-27 14:32:56',NULL),
(1578,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:32:58',1.00,'finalized','0a262b01a3b719ec7e728b90e276910a','[]',NULL,'2025-08-27 14:32:58','2025-08-27 14:32:59',NULL),
(1579,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:33:02',1.00,'finalized','8f03177974a257dad893f6a6c41e8350','[]',NULL,'2025-08-27 14:33:02','2025-08-27 14:33:02',NULL),
(1580,178,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 14:33:04',1.00,'finalized','af5ffcb8f53a7b4db07a794e3cc91aab','[]',NULL,'2025-08-27 14:33:04','2025-08-27 14:33:05',NULL),
(1581,179,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 21:23:51',2.00,'finalized','2a88331aa4c47ac50504dca3bc785ee5','[]',NULL,'2025-08-27 21:23:51','2025-08-27 21:24:14',NULL),
(1582,179,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 21:24:20',2.00,'finalized','f6169c50609e73d92f6a650670bd358a','[]',NULL,'2025-08-27 21:24:20','2025-08-27 21:24:27',NULL),
(1583,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:24:38',1.00,'finalized','babf27926a0bf531a18b17080ae950eb','[]',NULL,'2025-08-27 21:24:38','2025-08-27 21:24:45',NULL),
(1584,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:24:52',1.00,'finalized','45fc1b820547af4058d6f09bd8200e59','[]',NULL,'2025-08-27 21:24:52','2025-08-27 21:24:52',NULL),
(1585,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:24:58',1.00,'finalized','a00cf26fff8f169067292dd646dc417c','[]',NULL,'2025-08-27 21:24:58','2025-08-27 21:24:58',NULL),
(1586,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:25:02',1.00,'finalized','683b5c2998a33a1d58628f78cccaf7ff','[]',NULL,'2025-08-27 21:25:02','2025-08-27 21:25:02',NULL),
(1587,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:25:05',1.00,'finalized','f0cef99ad2230f5ef1f64314542e815e','[]',NULL,'2025-08-27 21:25:05','2025-08-27 21:25:05',NULL),
(1588,179,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-27 21:25:08',1.00,'finalized','c4395d69225e7c6ac0b90e66accc4580','[]',NULL,'2025-08-27 21:25:08','2025-08-27 21:25:09',NULL),
(1589,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:17',2.00,'finalized','d9302b9d345ccfdebc055824b0a89f06','[]',NULL,'2025-08-27 23:16:17','2025-08-27 23:16:18',NULL),
(1590,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:20',2.00,'finalized','4724551c8e3f58d8a7170e3c1901aef2','[]',NULL,'2025-08-27 23:16:20','2025-08-27 23:16:20',NULL),
(1591,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:22',2.00,'finalized','6e0bbc9cd6b5dc33687596f3f515a960','[]',NULL,'2025-08-27 23:16:22','2025-08-27 23:16:22',NULL),
(1592,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:23',2.00,'finalized','bccbca764190ca0e3de7c228b8d6fde2','[]',NULL,'2025-08-27 23:16:23','2025-08-27 23:16:23',NULL),
(1593,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:25',2.00,'finalized','daaeddbdd072633714daec3891d0353a','[]',NULL,'2025-08-27 23:16:25','2025-08-27 23:16:25',NULL),
(1594,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:26',2.00,'finalized','1e52aaf7127975f73f470f0124d8dc8a','[]',NULL,'2025-08-27 23:16:26','2025-08-27 23:16:26',NULL),
(1595,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:28',2.00,'finalized','078fe0be7cde2585bb755f4b4f8de12e','[]',NULL,'2025-08-27 23:16:28','2025-08-27 23:16:28',NULL),
(1596,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:29',2.00,'finalized','aacfed1202690c12dbc08d4dc259a068','[]',NULL,'2025-08-27 23:16:29','2025-08-27 23:16:30',NULL),
(1597,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:31',2.00,'finalized','bbc0a0482540118c334fc3ea4e5bface','[]',NULL,'2025-08-27 23:16:31','2025-08-27 23:16:31',NULL),
(1598,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:32',2.00,'finalized','5b8d2c9677282a9b5a023cafe154086a','[]',NULL,'2025-08-27 23:16:32','2025-08-27 23:16:33',NULL),
(1599,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:35',2.00,'finalized','b3877b98d04f101ee7d5cf1de9def29a','[]',NULL,'2025-08-27 23:16:35','2025-08-27 23:16:35',NULL),
(1600,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:37',2.00,'finalized','f67c0f0af16e1ce29b38944677318c13','[]',NULL,'2025-08-27 23:16:37','2025-08-27 23:16:37',NULL),
(1601,100,0,'Baú da sorte 🎁','perdeu',0.00,'2025-08-27 23:16:39',2.00,'finalized','f29d4fcd5a071383486010fe31180cb1','[]',NULL,'2025-08-27 23:16:39','2025-08-27 23:16:39',NULL),
(1602,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-27 23:16:55',10.00,'finalized','707d0e9a43e3eea0813c8bae24c65295','[2,3,6]','2.00','2025-08-27 23:16:55','2025-08-27 23:16:56',NULL),
(1603,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:16:58',10.00,'finalized','246a729d14ad191e4dfdeb89523ceaab','[]',NULL,'2025-08-27 23:16:58','2025-08-27 23:16:58',NULL),
(1604,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-27 23:17:00',10.00,'finalized','12959dafba650d433fc14a32b7b19610','[1,2,8]','10.00','2025-08-27 23:17:00','2025-08-27 23:17:01',NULL),
(1605,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:02',10.00,'finalized','e683101c9dafa8ca77f0388b47bb7732','[3,4,8]','5.00','2025-08-27 23:17:02','2025-08-27 23:17:03',NULL),
(1606,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:05',10.00,'finalized','abcae6164459029fb680fc4153268900','[0,1,2]','5.00','2025-08-27 23:17:05','2025-08-27 23:17:06',NULL),
(1607,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-27 23:17:08',10.00,'finalized','eb1fd6b5ba84427a1e0299fe70e5916f','[0,3,4]','10.00','2025-08-27 23:17:08','2025-08-27 23:17:09',NULL),
(1608,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:11',10.00,'finalized','d3ceed07bffb08fe4d8594f8d54ce5a5','[]',NULL,'2025-08-27 23:17:11','2025-08-27 23:17:11',NULL),
(1609,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:14',10.00,'finalized','8df20b1a7fccf1aa25f8d741a5bd857c','[]',NULL,'2025-08-27 23:17:14','2025-08-27 23:17:14',NULL),
(1610,100,0,'Tesouro lendário 🏆','ganhou',2.00,'2025-08-27 23:17:16',10.00,'finalized','b78a4accdb4d1962940c509bebecbd78','[1,2,6]','2.00','2025-08-27 23:17:16','2025-08-27 23:17:16',NULL),
(1611,100,0,'Tesouro lendário 🏆','ganhou',10.00,'2025-08-27 23:17:24',10.00,'finalized','fdd8fc4fbedf2010f6da24b79ac10dc1','[5,6,8]','10.00','2025-08-27 23:17:24','2025-08-27 23:17:25',NULL),
(1612,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:26',10.00,'finalized','aab28a57582f65b7b8994c04596f80e8','[1,4,6]','5.00','2025-08-27 23:17:26','2025-08-27 23:17:27',NULL),
(1613,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:31',10.00,'finalized','4a89a2433f7485bf513cd77f999d728f','[]',NULL,'2025-08-27 23:17:31','2025-08-27 23:17:31',NULL),
(1614,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:33',10.00,'finalized','a18c997d2c220ef8fd914845b8fd13ab','[3,4,5]','5.00','2025-08-27 23:17:33','2025-08-27 23:17:34',NULL),
(1615,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:36',10.00,'finalized','a5d4a128ae470827c714b22a7f5b8ca0','[]',NULL,'2025-08-27 23:17:36','2025-08-27 23:17:36',NULL),
(1616,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:38',10.00,'finalized','c1a39e8657cf59b788bad7c85913fdcc','[]',NULL,'2025-08-27 23:17:38','2025-08-27 23:17:38',NULL),
(1617,100,0,'Tesouro lendário 🏆','ganhou',0.50,'2025-08-27 23:17:39',10.00,'finalized','4c398d23d8775861a130c65cdcc69bc6','[0,5,8]','0.50','2025-08-27 23:17:39','2025-08-27 23:17:40',NULL),
(1618,100,0,'Tesouro lendário 🏆','perdeu',0.00,'2025-08-27 23:17:42',10.00,'finalized','b52c152eed6f886171657603b596bd0b','[]',NULL,'2025-08-27 23:17:42','2025-08-27 23:17:42',NULL),
(1619,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:44',10.00,'finalized','f86ec2e0d71f0623ae38b2a1ed343d9f','[1,4,6]','5.00','2025-08-27 23:17:44','2025-08-27 23:17:44',NULL),
(1620,100,0,'Tesouro lendário 🏆','ganhou',5.00,'2025-08-27 23:17:46',10.00,'finalized','e150332476562aab71aee20af0b49b99','[3,4,8]','5.00','2025-08-27 23:17:46','2025-08-27 23:17:47',NULL),
(1621,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:09:35',1.00,'finalized','9a6ce0cb01ff47792452a637dc8f6d6e','[]',NULL,'2025-08-28 15:09:35','2025-08-28 15:09:43',NULL),
(1622,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:09:50',1.00,'finalized','208210a3614ad72315cee54c7a4363df','[]',NULL,'2025-08-28 15:09:50','2025-08-28 15:09:50',NULL),
(1623,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:09:55',1.00,'finalized','eb537fd1c7713a4306b1652f6ab74ae7','[]',NULL,'2025-08-28 15:09:55','2025-08-28 15:09:55',NULL),
(1624,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:09:59',1.00,'finalized','8ce7f9acaeef1685e0fef367258e1866','[]',NULL,'2025-08-28 15:09:59','2025-08-28 15:10:00',NULL),
(1625,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:05',1.00,'finalized','c720cf6143bef4788e975f575ca1f2ae','[]',NULL,'2025-08-28 15:10:05','2025-08-28 15:10:05',NULL),
(1626,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:09',1.00,'finalized','3bbe63d32c5559a12b01170d73d2f784','[]',NULL,'2025-08-28 15:10:09','2025-08-28 15:10:09',NULL),
(1627,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:29',1.00,'finalized','2d20b4ebe5d053ec74a97ced434ed7b1','[]',NULL,'2025-08-28 15:10:29','2025-08-28 15:10:29',NULL),
(1628,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:33',1.00,'finalized','07a7f3ab66c705f653e525bfa5d3677c','[]',NULL,'2025-08-28 15:10:33','2025-08-28 15:10:33',NULL),
(1629,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:37',1.00,'finalized','c2944fe018c04e688e5be878eb656c5d','[]',NULL,'2025-08-28 15:10:37','2025-08-28 15:10:38',NULL),
(1630,180,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 15:10:40',1.00,'finalized','7bf5cf0fd8e43731fd548620446fd88b','[]',NULL,'2025-08-28 15:10:40','2025-08-28 15:10:41',NULL),
(1631,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:15',1.00,'finalized','80462e84c1999f2f9d369e89bc14cb4b','[]',NULL,'2025-08-28 18:51:15','2025-08-28 18:51:25',NULL),
(1632,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:28',1.00,'finalized','0ff77877c043c31e313b3965c1948fb4','[]',NULL,'2025-08-28 18:51:28','2025-08-28 18:51:29',NULL),
(1633,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:31',1.00,'finalized','7a5eaf009a0dedc90d0feae997a166c2','[]',NULL,'2025-08-28 18:51:31','2025-08-28 18:51:31',NULL),
(1634,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:35',1.00,'finalized','bce3103e71fb4da76d365f4b87eb5271','[]',NULL,'2025-08-28 18:51:35','2025-08-28 18:51:36',NULL),
(1635,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:38',1.00,'finalized','7dbcadb9966bf787f5490bfe98203753','[]',NULL,'2025-08-28 18:51:38','2025-08-28 18:51:38',NULL),
(1636,162,0,'Cofrinho mágico 🪙','perdeu',0.00,'2025-08-28 18:51:41',1.00,'finalized','a2df5174c38e93e30d208a76201abeca','[]',NULL,'2025-08-28 18:51:41','2025-08-28 18:51:41',NULL),
(1637,1,0,NULL,'perdeu',0.00,'2025-09-10 16:27:21',1.00,'pending',NULL,NULL,NULL,'2025-09-10 16:27:21',NULL,NULL),
(1638,1,0,NULL,'perdeu',0.00,'2025-09-10 16:27:37',1.00,'pending',NULL,NULL,NULL,'2025-09-10 16:27:37',NULL,NULL),
(1639,1,0,NULL,'perdeu',0.00,'2025-09-10 17:17:32',5.00,'pending',NULL,NULL,NULL,'2025-09-10 17:17:32',NULL,NULL);
/*!40000 ALTER TABLE `jogadas_raspadinha` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `payouts`
--

DROP TABLE IF EXISTS `payouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `request_date` timestamp NULL DEFAULT current_timestamp(),
  `payment_date` timestamp NULL DEFAULT NULL,
  `paid_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_id` (`affiliate_id`),
  CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payouts`
--

LOCK TABLES `payouts` WRITE;
/*!40000 ALTER TABLE `payouts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `payouts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `raspadinha_config`
--

DROP TABLE IF EXISTS `raspadinha_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `raspadinha_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sistema de prêmio forçado ativo',
  `max_premios` int(11) NOT NULL DEFAULT 10 COMMENT 'Máximo de prêmios a pagar',
  `premios_pagos` int(11) NOT NULL DEFAULT 0 COMMENT 'Prêmios já pagos',
  `valor_premio` decimal(10,2) NOT NULL DEFAULT 10.00 COMMENT 'Valor do prêmio forçado',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raspadinha_config`
--

LOCK TABLES `raspadinha_config` WRITE;
/*!40000 ALTER TABLE `raspadinha_config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `raspadinha_config` VALUES
(1,0,10,0,10.00,'2025-07-19 11:56:18','2025-08-21 13:28:47');
/*!40000 ALTER TABLE `raspadinha_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `raspadinha_jogadas`
--

DROP TABLE IF EXISTS `raspadinha_jogadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `raspadinha_jogadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `saldo_antes` decimal(10,2) NOT NULL,
  `saldo_depois` decimal(10,2) NOT NULL,
  `aposta` decimal(10,2) NOT NULL,
  `ganhou` tinyint(1) NOT NULL,
  `valor_premio` decimal(10,2) NOT NULL,
  `simbolos` varchar(255) NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raspadinha_jogadas`
--

LOCK TABLES `raspadinha_jogadas` WRITE;
/*!40000 ALTER TABLE `raspadinha_jogadas` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `raspadinha_jogadas` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `raspadinhas`
--

DROP TABLE IF EXISTS `raspadinhas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `raspadinhas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `resultado` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `raspadinhas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raspadinhas`
--

LOCK TABLES `raspadinhas` WRITE;
/*!40000 ALTER TABLE `raspadinhas` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `raspadinhas` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `referred_id` (`referred_id`),
  KEY `referrer_id` (`referrer_id`),
  CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `rounds_temp`
--

DROP TABLE IF EXISTS `rounds_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rounds_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_aposta` decimal(10,2) NOT NULL,
  `premio_definido` decimal(10,2) NOT NULL,
  `criado_em` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `tipo` (`tipo`),
  KEY `criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rounds_temp`
--

LOCK TABLES `rounds_temp` WRITE;
/*!40000 ALTER TABLE `rounds_temp` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `rounds_temp` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `rtp_bank`
--

DROP TABLE IF EXISTS `rtp_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rtp_bank` (
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apostado_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `pago_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `reservado_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rtp_bank`
--

LOCK TABLES `rtp_bank` WRITE;
/*!40000 ALTER TABLE `rtp_bank` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `rtp_bank` VALUES
('Baú da sorte 🎁',384.00,372.00,0.00,'2025-08-27 23:16:39'),
('Cofrinho mágico 🪙',673.00,567.50,0.00,'2025-08-28 18:51:41'),
('Império dourado 🏰',1320.00,856.00,0.00,'2025-08-22 17:40:44'),
('Luxo Máximo 👑',7450.00,3696.00,0.00,'2025-08-26 02:56:13'),
('Tesouro lendário 🏆',2100.00,1445.50,5.00,'2025-08-27 23:17:47'),
('Trono da Prada 👑',920.00,689.00,0.00,'2025-08-26 04:38:05');
/*!40000 ALTER TABLE `rtp_bank` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `rtp_bank_backup`
--

DROP TABLE IF EXISTS `rtp_bank_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rtp_bank_backup` (
  `tipo` varchar(100) NOT NULL,
  `apostado_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `pago_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `reservado_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rtp_bank_backup`
--

LOCK TABLES `rtp_bank_backup` WRITE;
/*!40000 ALTER TABLE `rtp_bank_backup` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `rtp_bank_backup` VALUES
('Baú da sorte 🎁',0.00,0.00,0.00,'2025-08-22 06:59:01'),
('Cofrinho mágico 🪙',7.00,0.00,0.00,'2025-08-22 07:03:20'),
('Império dourado 🏰',0.00,0.00,0.00,'2025-08-22 06:59:01'),
('Luxo Máximo 👑',0.00,0.00,0.00,'2025-08-22 06:59:01'),
('Tesouro lendário 🏆',0.00,0.00,0.00,'2025-08-22 06:59:01'),
('Trono da Prada 👑',0.00,0.00,0.00,'2025-08-22 06:59:01');
/*!40000 ALTER TABLE `rtp_bank_backup` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `rtp_test_logs`
--

DROP TABLE IF EXISTS `rtp_test_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rtp_test_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tipo_raspadinha` varchar(50) NOT NULL,
  `resultado` enum('vitoria','derrota') NOT NULL,
  `premio_ganho` decimal(10,2) DEFAULT 0.00,
  `valor_apostado` decimal(10,2) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `test_session` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rtp_test_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rtp_test_logs`
--

LOCK TABLES `rtp_test_logs` WRITE;
/*!40000 ALTER TABLE `rtp_test_logs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `rtp_test_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `saques_pix`
--

DROP TABLE IF EXISTS `saques_pix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `saques_pix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo_chave` varchar(20) NOT NULL,
  `chave_pix` varchar(255) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `status` enum('pendente','processando','concluido','cancelado') DEFAULT 'pendente',
  `data_solicitacao` timestamp NULL DEFAULT current_timestamp(),
  `data_processamento` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `saques_pix_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saques_pix`
--

LOCK TABLES `saques_pix` WRITE;
/*!40000 ALTER TABLE `saques_pix` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `saques_pix` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tipos_raspadinha`
--

DROP TABLE IF EXISTS `tipos_raspadinha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_raspadinha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `premio_maximo` decimal(10,2) NOT NULL,
  `chance_base` decimal(6,5) DEFAULT NULL,
  `valor_aposta_padrao` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `banner_personalizado` varchar(255) DEFAULT NULL COMMENT 'Caminho para banner personalizado',
  `cor_badge` varchar(7) DEFAULT NULL COMMENT 'Cor do badge em hexadecimal (#RRGGBB)',
  `imagem_card` varchar(255) DEFAULT NULL COMMENT 'Caminho para imagem do card',
  `descricao_curta` text DEFAULT NULL COMMENT 'Descri????o curta para exibi????o',
  `ordem_exibicao` int(11) DEFAULT 0 COMMENT 'Ordem de exibi????o (0 = autom??tica)',
  `exibir_carrossel` tinyint(1) DEFAULT 1 COMMENT 'Exibir no carrossel da p??gina inicial',
  PRIMARY KEY (`id`),
  KEY `idx_ordem_exibicao` (`ordem_exibicao`,`ativo`),
  CONSTRAINT `chk_chance_base` CHECK (`chance_base` is null or `chance_base` >= 0 and `chance_base` <= 1)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_raspadinha`
--

LOCK TABLES `tipos_raspadinha` WRITE;
/*!40000 ALTER TABLE `tipos_raspadinha` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tipos_raspadinha` VALUES
(1,'Cofrinho mágico 🪙',2000.00,1.00000,1.00,1,'2025-08-03 19:46:14','2025-08-22 01:26:04','uploads/raspadinhas/banners/banner_68a7c7298adc0.jpg',NULL,'uploads/raspadinhas/cards/card_68a7c72b70291.jpg','',1,0),
(2,'Trono da Prada 👑',15000.00,0.10000,5.00,1,'2025-08-03 19:46:14','2025-08-26 02:55:11','uploads/raspadinhas/banners/banner_68a7a9e86565b.webp',NULL,'uploads/raspadinhas/cards/card_68a7a9eb7afcf.webp','',3,0),
(3,'Império dourado 🏰',80000.00,1.00000,30.00,1,'2025-08-03 19:46:14','2025-08-22 15:28:59','uploads/raspadinhas/banners/banner_68a88cb9abb9d.png',NULL,'uploads/raspadinhas/cards/card_68a88cbabbf44.png','',5,0),
(5,'Baú da sorte 🎁',6000.00,1.00000,2.00,1,'2025-08-04 01:20:15','2025-08-21 23:20:36','uploads/raspadinhas/banners/banner_68a7a9bf6042b.webp',NULL,'uploads/raspadinhas/cards/card_68a7a9c22815a.webp','',2,0),
(6,'Tesouro lendário 🏆',30000.00,1.00000,10.00,1,'2025-08-04 15:09:01','2025-08-21 23:22:00','uploads/raspadinhas/banners/banner_68a7aa157f041.webp',NULL,'uploads/raspadinhas/cards/card_68a7aa1755867.webp','',4,0),
(7,'Luxo Máximo 👑',100000.00,1.00000,50.00,1,'2025-08-14 18:31:09','2025-08-22 15:26:46','uploads/raspadinhas/banners/banner_68a88c31c15e5.png',NULL,'uploads/raspadinhas/cards/card_68a88c3422099.png','',6,0);
/*!40000 ALTER TABLE `tipos_raspadinha` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `transacoes`
--

DROP TABLE IF EXISTS `transacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_data_criacao` (`data_criacao`),
  KEY `idx_transacoes_tipo_status` (`tipo`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transacoes`
--

LOCK TABLES `transacoes` WRITE;
/*!40000 ALTER TABLE `transacoes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `transacoes` VALUES
(171,1,'aposta_raspadinha',5.00,'Aposta na raspadinha','2025-09-10 17:17:29','pendente');
/*!40000 ALTER TABLE `transacoes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `min_deposit_amount` decimal(10,2) DEFAULT NULL,
  `min_withdrawal_amount` decimal(10,2) DEFAULT NULL,
  `influence_mode_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_settings` (`user_id`),
  CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `xgate_customer_id` varchar(36) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 10.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `referrer_id` int(11) DEFAULT NULL,
  `affiliate_status` tinyint(1) DEFAULT 0,
  `affiliate_balance` decimal(10,2) DEFAULT 0.00,
  `document` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `referrer_id` (`referrer_id`),
  KEY `idx_users_xgate_customer_id` (`xgate_customer_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'admin','admin@admin','68a69870dcfa6a15cf92a905',NULL,'$2a$12$6QFgjEbHX5JABIIWAHtyye0FxiUhFb1ldyhDhqjgr3lQ613epWLjO','Z2TBR6MG7PKE7POM',95.00,'2025-07-07 18:40:25',1,NULL,1,5.00,'23223232322');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Dumping routines for database 'raspadinha777'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-09-10 14:25:27
