/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `mart_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint unsigned NOT NULL,
  `question_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_version` int NOT NULL,
  `answer_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mart_answers_entry_id_question_uuid_index` (`entry_id`,`question_uuid`),
  KEY `mart_answers_question_uuid_question_version_index` (`question_uuid`,`question_version`),
  CONSTRAINT `mart_answers_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `mart_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_device_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_device_info` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `participant_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `os` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `os_version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manufacturer` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_updated` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mart_device_info_participant_id_index` (`participant_id`),
  KEY `mart_device_info_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `main_entry_id` bigint unsigned NOT NULL,
  `schedule_id` bigint unsigned NOT NULL,
  `questionnaire_id` int NOT NULL,
  `participant_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NOT NULL,
  `duration_ms` int NOT NULL,
  `timezone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mart_entries_main_entry_id_unique` (`main_entry_id`),
  KEY `mart_entries_schedule_id_participant_id_index` (`schedule_id`,`participant_id`),
  KEY `mart_entries_questionnaire_id_index` (`questionnaire_id`),
  CONSTRAINT `mart_entries_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `mart_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mart_project_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_on_first_app_start` tinyint(1) NOT NULL DEFAULT '0',
  `button_text` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Continue',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_success_page` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mart_pages_mart_project_id_sort_order_index` (`mart_project_id`,`sort_order`),
  KEY `idx_success_page` (`is_success_page`),
  CONSTRAINT `mart_pages_mart_project_id_foreign` FOREIGN KEY (`mart_project_id`) REFERENCES `mart_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_success_page` BEFORE INSERT ON `mart_pages` FOR EACH ROW BEGIN
                    IF NEW.is_success_page = TRUE THEN
                        UPDATE mart_pages
                        SET is_success_page = FALSE
                        WHERE mart_project_id = NEW.mart_project_id
                        AND is_success_page = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_update_success_page` BEFORE UPDATE ON `mart_pages` FOR EACH ROW BEGIN
                    IF NEW.is_success_page = TRUE AND (OLD.is_success_page = FALSE OR OLD.is_success_page IS NULL) THEN
                        UPDATE mart_pages
                        SET is_success_page = FALSE
                        WHERE mart_project_id = NEW.mart_project_id
                        AND id != NEW.id
                        AND is_success_page = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `mart_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `main_project_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mart_projects_main_project_id_unique` (`main_project_id`),
  KEY `mart_projects_main_project_id_index` (`main_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_question_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_question_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config` json DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL,
  `changed_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mart_question_history_question_uuid_version_index` (`question_uuid`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_questions` (
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_id` bigint unsigned NOT NULL,
  `position` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config` json DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `is_ios_data_collection` tinyint(1) NOT NULL DEFAULT '0',
  `is_android_data_collection` tinyint(1) NOT NULL DEFAULT '0',
  `item_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  KEY `mart_questions_schedule_id_position_index` (`schedule_id`,`position`),
  KEY `mart_questions_version_index` (`version`),
  KEY `idx_ios_data_collection` (`is_ios_data_collection`),
  KEY `idx_android_data_collection` (`is_android_data_collection`),
  CONSTRAINT `mart_questions_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `mart_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_ios_data_collection_question` BEFORE INSERT ON `mart_questions` FOR EACH ROW BEGIN
                    IF NEW.is_ios_data_collection = TRUE THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
                        )
                        AND mq.is_ios_data_collection = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_android_data_collection_question` BEFORE INSERT ON `mart_questions` FOR EACH ROW BEGIN
                    IF NEW.is_android_data_collection = TRUE THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
                        )
                        AND mq.is_android_data_collection = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_update_ios_data_collection_question` BEFORE UPDATE ON `mart_questions` FOR EACH ROW BEGIN
                    IF NEW.is_ios_data_collection = TRUE AND (OLD.is_ios_data_collection = FALSE OR OLD.is_ios_data_collection IS NULL) THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
                        )
                        AND mq.uuid != NEW.uuid
                        AND mq.is_ios_data_collection = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_update_android_data_collection_question` BEFORE UPDATE ON `mart_questions` FOR EACH ROW BEGIN
                    IF NEW.is_android_data_collection = TRUE AND (OLD.is_android_data_collection = FALSE OR OLD.is_android_data_collection IS NULL) THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
                        )
                        AND mq.uuid != NEW.uuid
                        AND mq.is_android_data_collection = TRUE;
                    END IF;
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `mart_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mart_project_id` bigint unsigned NOT NULL,
  `questionnaire_id` int NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introductory_text` text COLLATE utf8mb4_unicode_ci,
  `type` enum('single','repeating') COLLATE utf8mb4_unicode_ci NOT NULL,
  `timing_config` json NOT NULL,
  `notification_config` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mart_schedules_mart_project_id_questionnaire_id_unique` (`mart_project_id`,`questionnaire_id`),
  KEY `mart_schedules_type_index` (`type`),
  CONSTRAINT `mart_schedules_mart_project_id_foreign` FOREIGN KEY (`mart_project_id`) REFERENCES `mart_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mart_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mart_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mart_project_id` bigint unsigned NOT NULL,
  `participant_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `android_usage_stats` json DEFAULT NULL,
  `android_event_stats` json DEFAULT NULL,
  `ios_activations` int DEFAULT NULL,
  `ios_screen_time` int DEFAULT NULL,
  `ios_stats` json DEFAULT NULL,
  `device_id` text COLLATE utf8mb4_unicode_ci,
  `timestamp` bigint NOT NULL,
  `timezone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mart_stats_mart_project_id_participant_id_index` (`mart_project_id`,`participant_id`),
  KEY `mart_stats_timestamp_index` (`timestamp`),
  CONSTRAINT `mart_stats_mart_project_id_foreign` FOREIGN KEY (`mart_project_id`) REFERENCES `mart_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- WARNING: can't read the INFORMATION_SCHEMA.libraries table. It's most probably an old server 8.0.33.
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

