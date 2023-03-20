-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           5.7.36 - MySQL Community Server (GPL)
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage de la structure de table bsfrtournament. maps
DROP TABLE IF EXISTS `maps`;
CREATE TABLE IF NOT EXISTS `maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapID` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapKey` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty` char(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poolID` int(100) DEFAULT NULL,
  `mapStyle` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table bsfrtournament.maps : 13 rows
/*!40000 ALTER TABLE `maps` DISABLE KEYS */;
INSERT INTO `maps` (`id`, `mapID`, `mapKey`, `difficulty`, `poolID`, `mapStyle`) VALUES
	(1, '3B93BF274894D6AAC6CC54F242D99C87ECF0086F', '304d4', 'Easy', 1, 'True ACC'),
	(2, 'E8D7473298D482484345BB9EFF41F44CE433D04F', '301c1', 'Normal', 1, 'Standard ACC'),
	(3, 'D181036068F7E9977F58C2752A678D2261EDB1BE', '3024a', 'Normal', 1, 'High ACC'),
	(4, 'FC1C12BD819BE2563CEA4FAAB44FDAAEE63D7DE0', '30274', 'Expert', 1, 'Low Midspeed'),
	(5, 'C9E97FAB32D430D489F0FDFC11EE8366B8035039', '2eec0', 'Expert+', 1, 'Standard Midspeed'),
	(6, '7ED22F02175D613061AE676BC6F518730B780FAC', '3053e', 'Expert+', 1, 'High Midspeed'),
	(7, 'CC6AC8A0464389752B173F9604A4E85E3452AF73', '1efcb', 'Expert+', 1, 'Low Tech'),
	(8, '7A66F89A54BEE6D9F6351F5E9B2271133953D899', '30324', 'Expert+', 1, 'Standard Tech'),
	(9, '51E0E4B444A27366A8468F2429DED3C8D0EB0F5C', '1a37c', 'Expert+', 1, 'High Tech'),
	(10, '4F103EE83BA4FDF95ACD45CD81E38F72FDD5FDB0', '30149', 'Expert+', 1, 'Low Speed'),
	(11, 'D89BD95053CD98217983B4E9B2E10C45C14FA82A', '3020d', 'Expert+', 1, 'Standard Speed'),
	(12, '53444A571168EE0DB0E345FA65C813BA0AF0E3A5', '26c47', 'Expert+', 1, 'High Speed'),
	(14, '43AFE4A3921DB77BE41300D6C9A154C1319CA323', 'qzd', 'Expert+', 1, 'qzd');
/*!40000 ALTER TABLE `maps` ENABLE KEYS */;

-- Listage de la structure de table bsfrtournament. maps_pools
DROP TABLE IF EXISTS `maps_pools`;
CREATE TABLE IF NOT EXISTS `maps_pools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poolID` int(100) DEFAULT NULL,
  `poolName` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poolCategory` int(100) DEFAULT NULL,
  `poolMapNumber` int(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table bsfrtournament.maps_pools : 1 rows
/*!40000 ALTER TABLE `maps_pools` DISABLE KEYS */;
INSERT INTO `maps_pools` (`id`, `poolID`, `poolName`, `poolCategory`, `poolMapNumber`) VALUES
	(1, 1, 'DTierQualification', 1, 12);
/*!40000 ALTER TABLE `maps_pools` ENABLE KEYS */;

-- Listage de la structure de table bsfrtournament. scores
DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scoresaberID` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapID` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty` char(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int(12) DEFAULT NULL,
  `accuracy` float DEFAULT NULL,
  `miss` int(10) DEFAULT NULL,
  `pause` int(10) DEFAULT NULL,
  `try` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table bsfrtournament.scores : 0 rows
/*!40000 ALTER TABLE `scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `scores` ENABLE KEYS */;

-- Listage de la structure de table bsfrtournament. users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discordID` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scoresaberID` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profilPicture` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table bsfrtournament.users : 14 rows
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `pseudo`, `discordID`, `scoresaberID`, `profilPicture`) VALUES
	(1, 'Hyldra Zolxy', '481533074249875486', '76561198235823594', '03741076f0e5a2d9268a81498ff0e421'),
	(2, 'Brase', '525714158998781954', '76561199116794382', '5683d08b45a71ffd4c7fa96389510688'),
	(3, 'Goikuas', '140902175093161984', '76561198067751802', 'f6d077f270baafd73c639bc3aa214b7a'),
	(4, 'Risi', '517320770196865024', '76561198964267559', '24a69c9fa511b486e94d30a5ee00efbc'),
	(5, 'Cadavren', '176052745982181376', '76561198079942161', 'a62b08516fe9d8918da86056cf7813b2'),
	(6, 'ScooWard', '408655646502682650', '76561198381861023', '4de1b52689e5ab4b1926deb07f7a6d18'),
	(7, 'Krixs', '220151545486901248', '76561198073345392', 'a84db101442de86768e307e49938d2eb'),
	(8, 'Pryd', '275335326740774922', '76561198317122281', 'de74d5b6f864a9637cc33939c6887830'),
	(9, 'Blobby', '705156508337438810', '76561199080950125', '2faf8c78c437c8a7bcf1e3fee138b4ff'),
	(10, 'Brindille', '265558001837015040', '76561198188420174', '5b2d5a94038fff81c1fcde194adbe111'),
	(11, 'Mehdis6k9', '275369612282167298', '3718851351526610', 'fc68360b3c0b9df32764fcd6bab2eb67'),
	(12, 'Kinaro_', '618067456682098708', '76561199100396335', 'c1b6f4f2bdb48e31905e064c39f17fe1'),
	(13, 'Axsparta', '345508162310504448', '4003475469769114', '360e65bf6cf151cd27d3455b2d96ce78'),
	(14, 'Fakoz', '348464808154103808', '76561198084634262', 'a_a849333d57980a4843b1e3ede1d50d80');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
