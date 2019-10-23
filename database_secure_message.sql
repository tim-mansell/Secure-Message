
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for secure_message
CREATE DATABASE IF NOT EXISTS `secure_message` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `secure_message`;

-- Dumping structure for table secure_message.data
CREATE TABLE IF NOT EXISTS `data` (
  `token` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `secret` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(3358) COLLATE utf8_unicode_ci NOT NULL,
  `creationtime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `timetolive` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table secure_message.notify
CREATE TABLE IF NOT EXISTS `notify` (
  `token` char(32) NOT NULL,
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table secure_message.stats
CREATE TABLE IF NOT EXISTS `stats` (
  `operationNumber` int(11) NOT NULL AUTO_INCREMENT,
  `passwordProtected` tinyint(1) NOT NULL,
  `operation` char(6) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`operationNumber`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table secure_message.tokens
CREATE TABLE IF NOT EXISTS `tokens` (
  `token` char(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
