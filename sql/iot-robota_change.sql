SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

/*DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL COMMENT 'Index',
  `role` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'guest' COMMENT 'Rola pre ACL',
  `inherited` varchar(30) COLLATE utf8_bin DEFAULT NULL COMMENT 'Dedí od roli',
  `name` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'Registracia cez web' COMMENT 'Názov úrovne registrácie',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Úrovne registrácie a ich názvy';

INSERT INTO `user_roles` (`id`, `role`, `inherited`, `name`) VALUES
(1,	'guest',	NULL,	'Bez registrácie'),
(2,	'register',	'guest',	'Registrácia cez web'),
(3,	'admin',	'register',	'Administrátor');

-- ALTER TABLE `rausers`
-- ADD `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Rola užívateľa' AFTER `role`,
-- ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

UPDATE `rausers` SET `id_user_roles` = '3' WHERE `id` = '1';

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `name` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'Názov zdroja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Zdroje oprávnení';

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1,	'Sign'),
(2,	'Homepage'),
(3,	'User'),
(4,	'Crontask'),
(5,	'Device'),
(6,	'Enroll'),
(7,	'Error4xx'),
(8,	'Error'),
(9,	'Gallery'),
(10,	'Chart'),
(11,	'Inventory'),
(12,	'Json'),
(13,	'Monitor'),
(14,	'Ra'),
(15,	'Sensor'),
(16,	'View'),
(17,	'Vitem'),
(18, 'UserAcl');

DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `id_user_roles` int(11) NOT NULL DEFAULT 0 COMMENT 'Užívateľská rola',
  `id_user_resource` int(11) NOT NULL COMMENT 'Zdroj oprávnenia',
  `actions` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Povolenie na akciu. (Ak viac oddelené čiarkou, ak null tak všetko)',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_user_resource` (`id_user_resource`),
  CONSTRAINT `user_permission_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `user_permission_ibfk_2` FOREIGN KEY (`id_user_resource`) REFERENCES `user_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Užívateľské oprávnenia';

INSERT INTO `user_permission` (`id`, `id_user_roles`, `id_user_resource`, `actions`) VALUES
-- Sign
(1,	1,	1,	NULL), 
-- Homepage
(2,	2,	2,	NULL),
-- User
(3,	3,	3,	NULL),
-- Crontask
(4,	1,	4,	NULL),
-- Device
(5,	1,	5,	'deleteupdate'),
(6,	2,	5,	NULL),
-- Enroll
(7,	1,	6,	NULL),
-- Error4xx
(8,	1,	7,	NULL),
-- Error
(9,	1,	8,	NULL),
-- Gallery
(10,	1,	9,	NULL),
-- Chart
(11,	1,	10,	NULL),
-- Inventory
(12,	2,	11,	NULL),
-- Json
(13,	1,	12,	NULL),
-- Monitor
(14,	1,	13,	NULL),
-- Ra
(15,	1,	14,	NULL),
-- Sensor
(16,	2,	15,	NULL),
-- View
(17,	2,	16,	NULL),
-- Vitem
(18,	2,	17,	NULL),
-- UserAcl
(19,	3,	18,	NULL);

ALTER TABLE `rausers`
CHANGE `id_user_roles` `id_user_roles` int(11) NOT NULL DEFAULT '1' COMMENT 'Rola užívateľa' AFTER `role`;

ALTER TABLE `rausers`
CHANGE `role` `role` varchar(100) COLLATE 'utf8_bin' NOT NULL DEFAULT 'user' AFTER `phash`;*/

-- ------------------
/*
ALTER TABLE `user_roles`
CHANGE `name` `name` varchar(80) COLLATE 'utf8_bin' NOT NULL DEFAULT 'Registracia cez web' COMMENT 'Názov úrovne registrácie' AFTER `inherited`;

ALTER TABLE `user_roles`
ADD `color` varchar(15) COLLATE 'utf8_bin' NOT NULL DEFAULT 'fff' COMMENT 'Farba pozadia';

INSERT INTO `user_roles` (`id`, `role`, `inherited`, `name`)
VALUES ('4', 'sadmin', NULL, 'SAdministrátor');

UPDATE `user_roles` SET `name` = 'Registrovaný ale neaktivovaný užívateľ' WHERE `id` = '2';
UPDATE `user_roles` SET `name` = 'Aktivovaný užívateľ' WHERE `id` = '3';

UPDATE `user_roles` SET `role` = 'active' WHERE `id` = '3';
UPDATE `user_roles` SET `inherited` = 'active', `name` = 'Administrátor' WHERE `id` = '4';

UPDATE `user_roles` SET `role` = 'admin' WHERE `id` = '4';

UPDATE `user_permission` SET `id_user_roles` = '4' WHERE `id_user_roles` = '3';
UPDATE `user_permission` SET `id_user_roles` = '3' WHERE `id_user_roles` = '2';

UPDATE `rausers` SET `id_user_roles` = '4' WHERE `id` = '1';

UPDATE `user_roles` SET `color` = 'fffc29' WHERE `id` = '2';

UPDATE `user_roles` SET `color` = '7ce300' WHERE `id` = '3';

UPDATE `user_roles` SET `color` = 'ff6a6a' WHERE `id` = '4';
*/

/*DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `acronym` varchar(3) COLLATE utf8_bin NOT NULL DEFAULT 'sk' COMMENT 'Skratka jazyka',
  `name` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT 'Slovenčina' COMMENT 'Miestny názov jazyka',
  `name_en` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT 'Slovak' COMMENT 'Anglický názov jazyka',
  `accepted` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Ak je > 0 jazyk je možné použiť na Frond',
  PRIMARY KEY (`id`),
  UNIQUE KEY `acronym` (`acronym`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Jazyky pre web';

INSERT INTO `lang` (`id`, `acronym`, `name`, `name_en`, `accepted`) VALUES
(1,	'sk',	'Slovenčina',	'Slovak',	1);*/

/*ALTER TABLE `rausers`
ADD `new_password_key` varchar(100) COLLATE 'utf8_bin' NULL COMMENT 'Kľúč nového hesla',
ADD `new_password_requested` datetime NULL COMMENT 'Čas požiadavky na nové heslo' AFTER `new_password_key`,
COMMENT='Hlavné údaje užívateľa';


ALTER TABLE `rausers`
RENAME TO `user_main`;

ALTER TABLE `user_main`
CHANGE `id_rauser_state` `id_user_state` int(11) NOT NULL DEFAULT '10' AFTER `prefix`;

ALTER TABLE `rauser_state`
RENAME TO `user_state`;*/

UPDATE `user_permission` SET `id_user_roles` = '1' WHERE `id` = '2';

UPDATE `main_menu` SET `link` = 'Device:List' WHERE `id` = '2';

/* add 27.06.2022 */
INSERT INTO `user_resource` (`name`)
VALUES ('Units');

INSERT INTO `user_permission` (`id_user_roles`, `id_user_resource`, `actions`)
VALUES ('4', '19', NULL);

UPDATE `main_menu` SET `link` = 'Units:' WHERE `id` = '4';