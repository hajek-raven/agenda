-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `act_notebooks`;
CREATE TABLE `act_notebooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Notebooks chief',
  PRIMARY KEY (`id`),
  KEY `fk_act_notebooks_user1_idx` (`user_id`),
  KEY `id` (`id`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `act_works`;
CREATE TABLE `act_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` text,
  `approval` tinyint(1) NOT NULL DEFAULT '0',
  `preapproval` tinyint(1) NOT NULL DEFAULT '0',
  `note` text COMMENT 'píše ji předseda pk, ředitel ji vidí',
  `timeInM` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `act_notebooks_id` int(11) NOT NULL,
  `reward` int(11) DEFAULT NULL,
  `created_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_act_works_act_notebooks1_idx` (`act_notebooks_id`),
  KEY `user_id` (`user_id`),
  KEY `created_id` (`created_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primární klíč',
  `original` varchar(500) COLLATE utf8_czech_ci NOT NULL COMMENT 'původní název souboru',
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'čas vytvoření/nahrání souboru',
  `user_id` int(11) DEFAULT NULL COMMENT 'vlastník souboru',
  `extension` varchar(10) COLLATE utf8_czech_ci NOT NULL COMMENT 'koncovka názvu',
  `mime` varchar(100) COLLATE utf8_czech_ci NOT NULL COMMENT 'MIME pro odeslání souboru',
  `public` int(11) NOT NULL DEFAULT '0' COMMENT 'soubor mohou stáhnout všichni',
  `size` bigint(20) NOT NULL DEFAULT '0' COMMENT 'velikost souboru',
  `locked` bit(1) NOT NULL DEFAULT b'0' COMMENT 'se souborem není možné manipulovat (smazat, přepsat)',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `file_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primární klíč',
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL COMMENT 'zobrazovaný název skupiny',
  `role_name` varchar(200) COLLATE utf8_czech_ci NOT NULL COMMENT 'unikátní název skupiny pro použití jako role',
  `active` bit(1) NOT NULL COMMENT 'se skupinou lze pracovt, používat ji pro výběry',
  `open` bit(1) NOT NULL COMMENT 'do skupiny se může kdokoli přihlásit nebo odhlásit',
  `user_id` int(11) DEFAULT NULL COMMENT 'správce skupiny',
  `visible_all` bit(1) NOT NULL DEFAULT b'0' COMMENT 'všichni mohou vidět, že skupina existuje',
  `visible_members` bit(1) NOT NULL DEFAULT b'0' COMMENT 'jen členové mohou vidět, že skupina existuje',
  `list_all` bit(1) NOT NULL DEFAULT b'0' COMMENT 'seznam členů mohou vidět všichni',
  `list_members` bit(1) NOT NULL DEFAULT b'0' COMMENT 'seznam členů mohou vidět jen členové',
  `permanent` bit(1) NOT NULL DEFAULT b'0' COMMENT 'skupinu nelze odstranit',
  `pk` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `group_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `login_imap`;
CREATE TABLE `login_imap` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `login_imap_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `login_local`;
CREATE TABLE `login_local` (
  `user_id` int(11) NOT NULL COMMENT 'ID uživatele (do tab. user)',
  `password` varchar(100) COLLATE utf8_czech_ci NOT NULL COMMENT 'hash hesla',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'čas registrace',
  `token` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'hash tokenu odeslaného mailem',
  `token_expiration` datetime DEFAULT NULL COMMENT 'čas vypršení tokenu',
  `validated` datetime DEFAULT NULL COMMENT 'email ověřen',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `login_local_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `membership`;
CREATE TABLE `membership` (
  `user_id` int(11) NOT NULL COMMENT 'ID uživatele (do tab. user)',
  `group_id` int(11) NOT NULL COMMENT 'ID skupiny (do tab. group)',
  `signed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'čas zápisu',
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_class`;
CREATE TABLE `sch_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `shortname` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `year` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `bakalari_code` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `invalidated` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `sch_class_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_group`;
CREATE TABLE `sch_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `shortname` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `bakalari_code` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `sch_class_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sch_class_id` (`sch_class_id`),
  CONSTRAINT `sch_group_ibfk_2` FOREIGN KEY (`sch_class_id`) REFERENCES `sch_class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_group_membership`;
CREATE TABLE `sch_group_membership` (
  `sch_group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`sch_group_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sch_group_membership_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sch_group_membership_ibfk_3` FOREIGN KEY (`sch_group_id`) REFERENCES `sch_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_load`;
CREATE TABLE `sch_load` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sch_teacher_id` int(11) DEFAULT NULL,
  `sch_subject_id` int(11) DEFAULT NULL,
  `sch_group_id` int(11) DEFAULT NULL,
  `hours` int(11) NOT NULL,
  `cycle` int(11) DEFAULT NULL,
  `bakalari_code` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sch_teacher_id` (`sch_teacher_id`),
  KEY `sch_subject_id` (`sch_subject_id`),
  KEY `sch_group_id` (`sch_group_id`),
  CONSTRAINT `sch_load_ibfk_6` FOREIGN KEY (`sch_group_id`) REFERENCES `sch_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sch_load_ibfk_4` FOREIGN KEY (`sch_teacher_id`) REFERENCES `sch_teacher` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sch_load_ibfk_5` FOREIGN KEY (`sch_subject_id`) REFERENCES `sch_subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_student`;
CREATE TABLE `sch_student` (
  `user_id` int(11) NOT NULL,
  `network_login` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `catalog_number` int(11) NOT NULL,
  `bakalari_code` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `invalidated` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`user_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `sch_student_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `sch_class` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `sch_student_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_subject`;
CREATE TABLE `sch_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `shortname` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `bakalari_code` varchar(7) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `sch_teacher`;
CREATE TABLE `sch_teacher` (
  `user_id` int(11) NOT NULL,
  `work_phone` bigint(20) DEFAULT NULL,
  `shortname` varchar(3) COLLATE utf8_czech_ci DEFAULT NULL,
  `network_login` varchar(3) COLLATE utf8_czech_ci NOT NULL,
  `bakalari_code` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `invalidated` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `sch_teacher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `firstname` varchar(200) COLLATE utf8_czech_ci NOT NULL COMMENT 'jméno',
  `lastname` varchar(200) COLLATE utf8_czech_ci NOT NULL COMMENT 'příjmení',
  `title` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'titul před jménem',
  `title_after` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'titul za jménem',
  `gender` char(1) COLLATE utf8_czech_ci NOT NULL COMMENT 'pohlaví (Male/Female)',
  `birthdate` date DEFAULT NULL COMMENT 'datum narození',
  `active` bit(1) NOT NULL DEFAULT b'0' COMMENT 'účet je aktivní, uživatel s ním pracuje',
  `enabled` bit(1) NOT NULL DEFAULT b'0' COMMENT 'účet není zablokovaný',
  `email` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'kontaktní email',
  `phone` bigint(20) DEFAULT NULL COMMENT 'kontaktní telefon',
  `personal_identification_number` bigint(20) DEFAULT NULL COMMENT 'rodné číslo',
  `bakalari_code` varchar(7) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_assignment`;
CREATE TABLE `wrk_assignment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `resources` text COLLATE utf8_czech_ci,
  `coworkers` int(11) NOT NULL DEFAULT '1',
  `subject` varchar(5) COLLATE utf8_czech_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wrk_assignment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_assignment_goal`;
CREATE TABLE `wrk_assignment_goal` (
  `wrk_assignment_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `description` varchar(1000) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`wrk_assignment_id`,`order`),
  CONSTRAINT `wrk_assignment_goal_ibfk_2` FOREIGN KEY (`wrk_assignment_id`) REFERENCES `wrk_assignment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_assignment_outline`;
CREATE TABLE `wrk_assignment_outline` (
  `wrk_assignment_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `description` varchar(1000) COLLATE utf8_czech_ci NOT NULL,
  KEY `wrk_assignment_id` (`wrk_assignment_id`),
  CONSTRAINT `wrk_assignment_outline_ibfk_2` FOREIGN KEY (`wrk_assignment_id`) REFERENCES `wrk_assignment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_set`;
CREATE TABLE `wrk_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'0',
  `template` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `max_grade` int(11) NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_set_role`;
CREATE TABLE `wrk_set_role` (
  `id` int(11) NOT NULL,
  `wrk_set_id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `printed_application` bit(1) NOT NULL,
  `class_teacher` bit(1) NOT NULL,
  `required_print` bit(1) NOT NULL,
  PRIMARY KEY (`id`,`wrk_set_id`),
  KEY `wrk_set_id` (`wrk_set_id`),
  CONSTRAINT `wrk_set_role_ibfk_1` FOREIGN KEY (`wrk_set_id`) REFERENCES `wrk_set` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_work`;
CREATE TABLE `wrk_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `wrk_assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wrk_set_id` int(11) NOT NULL,
  `class` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '0',
  `class_teacher` int(11) DEFAULT NULL,
  `consultant` varchar(200) COLLATE utf8_czech_ci DEFAULT NULL,
  `year` year(4) NOT NULL,
  `application` int(11) DEFAULT NULL,
  `review` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wrk_assignment_id` (`wrk_assignment_id`),
  KEY `user_id` (`user_id`),
  KEY `wrk_set_id` (`wrk_set_id`),
  KEY `created_by` (`created_by`),
  KEY `class_teacher` (`class_teacher`),
  CONSTRAINT `wrk_work_ibfk_1` FOREIGN KEY (`wrk_assignment_id`) REFERENCES `wrk_assignment` (`id`),
  CONSTRAINT `wrk_work_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `wrk_work_ibfk_3` FOREIGN KEY (`wrk_set_id`) REFERENCES `wrk_set` (`id`),
  CONSTRAINT `wrk_work_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  CONSTRAINT `wrk_work_ibfk_5` FOREIGN KEY (`class_teacher`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `wrk_work_role`;
CREATE TABLE `wrk_work_role` (
  `wrk_work_id` int(11) NOT NULL,
  `wrk_set_id` int(11) NOT NULL,
  `wrk_set_role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `wrk_work_id` (`wrk_work_id`),
  KEY `wrk_set_id` (`wrk_set_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wrk_work_role_ibfk_1` FOREIGN KEY (`wrk_work_id`) REFERENCES `wrk_work` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wrk_work_role_ibfk_2` FOREIGN KEY (`wrk_work_id`) REFERENCES `wrk_work` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wrk_work_role_ibfk_3` FOREIGN KEY (`wrk_set_id`) REFERENCES `wrk_set` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wrk_work_role_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2015-12-29 13:38:16
