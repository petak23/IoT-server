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

-- 2025-07-24 09:12:00

ALTER TABLE `devices`
CHANGE `id` `id` smallint(6) NOT NULL COMMENT '[A] Index' AUTO_INCREMENT FIRST,
CHANGE `passphrase` `passphrase` varchar(100) COLLATE 'utf32_bin' NOT NULL COMMENT 'Hash hesla' AFTER `id`,
CHANGE `name` `name` varchar(100) COLLATE 'utf32_bin' NOT NULL COMMENT 'Meno' AFTER `passphrase`,
CHANGE `desc` `desc` varchar(255) COLLATE 'utf32_bin' NULL COMMENT 'Popis' AFTER `name`,
CHANGE `first_login` `first_login` datetime NULL COMMENT 'Prvé prihlásenie' AFTER `desc`,
CHANGE `last_login` `last_login` datetime NULL COMMENT 'Posledné prihlásenie' AFTER `first_login`,
CHANGE `last_bad_login` `last_bad_login` datetime NULL COMMENT 'Posledné chybné prihlásenie' AFTER `last_login`,
CHANGE `json_token` `json_token` varchar(255) COLLATE 'utf32_bin' NULL AFTER `user_id`,
CHANGE `blob_token` `blob_token` varchar(255) COLLATE 'utf32_bin' NULL AFTER `json_token`,
CHANGE `app_name` `app_name` varchar(256) COLLATE 'utf32_bin' NULL AFTER `monitoring`,
CHANGE `config_data` `config_data` text COLLATE 'utf32_bin' NULL AFTER `config_ver`,
CHANGE `user_id` `user_id` smallint(6) NOT NULL COMMENT 'Id užívateľa' AFTER `last_bad_login`,
CHANGE `uptime` `uptime` int(11) NULL COMMENT 'Dobu prevádzky alebo bezporuchovosti v sekundách' AFTER `app_name`,
COLLATE 'utf32_bin';

