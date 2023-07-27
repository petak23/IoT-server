-- Adminer 4.8.1 MySQL 8.0.33-0ubuntu0.22.04.2 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `main_menu`;
CREATE TABLE `main_menu` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL COMMENT 'Zobrazený názov položky',
  `link` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL COMMENT 'Odkaz',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Hlavné menu';

INSERT INTO `main_menu` (`id`, `name`, `link`) VALUES
(1,	'Môj účet',	'Admin:Inventory:User'),
(2,	'Zariadenia',	'Admin:Device:List'),
(3,	'Grafy',	'View:Views'),
(4,	'Kódy jednotiek',	'Admin:Units:Default'),
(5,	'Uživatelia',	'Admin:User:Default'),
(6,	'Editácia ACL',	'Admin:UserAcl:');

-- 2023-07-27 11:30:32
