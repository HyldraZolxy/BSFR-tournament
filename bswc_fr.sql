-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.0.31 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage de la structure de la table dev_bswc_fr. maps
CREATE TABLE IF NOT EXISTS `maps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `author` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `mapper` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `cover` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `hash` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `bsrkey` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `length` mediumint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='All maps per pools';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. mapsbypool
CREATE TABLE IF NOT EXISTS `mapsbypool` (
  `mapID` int NOT NULL,
  `poolID` int NOT NULL,
  `tournamentID` int NOT NULL,
  `difficulty` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `notes` int NOT NULL DEFAULT '0',
  `tags` tinytext COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='This is the relation between maps and pools';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. maps_pools
CREATE TABLE IF NOT EXISTS `maps_pools` (
  `id` int NOT NULL AUTO_INCREMENT,
  `poolID` int DEFAULT NULL,
  `poolName` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poolCategory` int DEFAULT NULL,
  `poolMapNumber` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. poolbytournament
CREATE TABLE IF NOT EXISTS `poolbytournament` (
  `poolID` int NOT NULL,
  `tournamentID` int NOT NULL,
  `starting_at` datetime NOT NULL DEFAULT '2030-01-01 00:00:00',
  `finishing_at` datetime NOT NULL DEFAULT '2030-02-01 00:00:00',
  `team` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `teamVS` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `type` int NOT NULL,
  `status` int NOT NULL,
  `teamVSPoint` int NOT NULL,
  `teamFRPoint` int NOT NULL,
  `requireLogin` int NOT NULL,
  `requireRoles` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='This is the relation between pools and tournaments';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. pools
CREATE TABLE IF NOT EXISTS `pools` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `picture` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL DEFAULT '2030-01-01',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='All maps pools per tournaments';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. scores
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `scoresaberID` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `accuracy` float NOT NULL DEFAULT '0',
  `best` float NOT NULL DEFAULT '0',
  `average` float NOT NULL DEFAULT '0',
  `worst` float NOT NULL DEFAULT '0',
  `miss` int NOT NULL DEFAULT '0',
  `try` int NOT NULL DEFAULT '0',
  `mapLaunched` int NOT NULL DEFAULT '0',
  `dateFirstTry` datetime NOT NULL DEFAULT '2030-01-01 00:00:00',
  `dateLastTry` datetime NOT NULL DEFAULT '2030-01-01 00:00:00',
  `mapID` int NOT NULL DEFAULT '0',
  `poolID` int NOT NULL DEFAULT '0',
  `tournamentID` int NOT NULL DEFAULT '0',
  `team` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='All scores maps per player';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. teambyuser
CREATE TABLE IF NOT EXISTS `teambyuser` (
  `userID` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `team` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `isCaptain` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='This is the relation between users and teams';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. tournaments
CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `picture` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` date NOT NULL DEFAULT '2030-01-01',
  `starting_at` datetime NOT NULL DEFAULT '2030-01-01 00:00:00',
  `finishing_at` datetime NOT NULL DEFAULT '2030-01-02 00:00:00',
  `status` tinyint NOT NULL DEFAULT '-1',
  `team` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `rank` int NOT NULL,
  `requireLogin` int NOT NULL,
  `requireRoles` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='All tournaments is stored here';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `scoresaberID` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `discordID` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='contains users of tournament';

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table dev_bswc_fr. userslogin
CREATE TABLE IF NOT EXISTS `userslogin` (
  `userID` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `code` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `roles` int NOT NULL,
  `websiteCode` tinytext COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='Store all code for login system';

-- Les données exportées n'étaient pas sélectionnées.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
