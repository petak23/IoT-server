ALTER TABLE `rausers`
CHANGE `state_id` `id_rauser_state` int NOT NULL DEFAULT '10' AFTER `prefix`;

ALTER TABLE `rauser_state`
CHANGE `id` `id` int NOT NULL FIRST;


ALTER TABLE `rauser_state`
ADD PRIMARY KEY `id` (`id`);


ALTER TABLE `rausers`
ADD FOREIGN KEY (`id_rauser_state`) REFERENCES `rauser_state` (`id`);

ALTER TABLE `devices`
ADD FOREIGN KEY (`user_id`) REFERENCES `rausers` (`id`);

ALTER TABLE `value_types`
CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `updates`
ADD FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);

ALTER TABLE `device_classes`
CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `sensors`
CHANGE `device_class` `device_class` int(11) NOT NULL AFTER `name`,
CHANGE `value_type` `value_type` int(11) NOT NULL AFTER `device_class`,
ADD FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`),
ADD FOREIGN KEY (`device_class`) REFERENCES `device_classes` (`id`),
ADD FOREIGN KEY (`value_type`) REFERENCES `value_types` (`id`);

-- Nový systém užívateľov
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL COMMENT 'Index',
  `role` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'guest' COMMENT 'Rola pre ACL',
  `inherited` varchar(30) COLLATE utf8_bin DEFAULT NULL COMMENT 'Dedí od roli',
  `name` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'Registracia cez web' COMMENT 'Názov úrovne registrácie',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Úrovne registrácie a ich názvy';

INSERT INTO `user_roles` (`id`, `role`, `inherited`, `name`) VALUES
(0,	'guest',	NULL,	'Bez registrácie'),
(1,	'register',	'guest',	'Registrácia cez web'),
(2,	'passive',	'register',	'Pasívny užívateľ'),
(3,	'active',	'passive',	'Aktívny užívateľ'),
(4,	'manager',	'active',	'Správca obsahu'),
(5,	'admin',	'manager',	'Administrátor');

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `name` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'Názov zdroja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Zdroje oprávnení';

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1,	'Homepage'),
(2,	'User'),
(3,	'Sign'),
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
(3,	6,	3,	NULL),
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
(18,	2,	17,	NULL);

DROP TABLE IF EXISTS `user_state`;
CREATE TABLE `user_state` (
  `id` tinyint(4) NOT NULL COMMENT 'Index',
  `desc` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Popis',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Stav užívateľa';

INSERT INTO `user_state` (`id`, `desc`) VALUES
(1,	'čeká na zadání kódu z e-mailu'),
(10,	'aktivní'),
(90,	'zakázán administrátorem'),
(91,	'dočasně uzamčen');

DROP TABLE IF EXISTS `user_main`;
CREATE TABLE `user_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `id_user_roles` int(11) NOT NULL DEFAULT 0 COMMENT 'Úroveň registrácie a rola',
  `password` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hash hesla',
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `email` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Email',
  `prefix` varchar(20) COLLATE utf8_bin NOT NULL COMMENT 'Prefix',
  `id_user_state` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Stav užívateľa',
  `banned` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Ak 1 tak zazázaný',
  `ban_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Dôvod zákazu',
  `new_password_key` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Kľúč nového hesla',
  `new_password_requested` datetime /* mariadb-5.3 */ DEFAULT NULL COMMENT 'Čas požiadavky na nové heslo',
  `new_email` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Nový email',
  `new_email_key` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Kľúč nového emailu',
  `created` datetime /* mariadb-5.3 */ NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Vytvorenie užívateľa',
  `modified` timestamp /* mariadb-5.3 */ NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Posledná zmena',
  `bad_pwds_count` smallint(6) NOT NULL DEFAULT '0',
  `locked_out_until` datetime DEFAULT NULL,
  `measures_retention` int(11) NOT NULL DEFAULT '90' COMMENT 'jak dlouho se drží data v measures',
  `sumdata_retention` int(11) NOT NULL DEFAULT '731' COMMENT 'jak dlouho se drží data v sumdata',
  `blob_retention` int(11) NOT NULL DEFAULT '14' COMMENT 'jak dlouho se drží bloby',
  `self_enroll` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 = self-enrolled',
  `self_enroll_code` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `self_enroll_error_count` tinyint(4) DEFAULT '0',
  `cur_login_time` datetime DEFAULT NULL,
  `cur_login_ip` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `cur_login_browser` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `prev_login_time` datetime DEFAULT NULL,
  `prev_login_ip` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `prev_login_browser` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `last_error_time` datetime DEFAULT NULL,
  `last_error_ip` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `last_error_browser` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `monitoring_token` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_registracia` (`id_user_roles`),
  KEY `id_user_state` (`id_user_state`),
  CONSTRAINT `user_main_ibfk_2` FOREIGN KEY (`id_user_state`) REFERENCES `user_state` (`id`),
  CONSTRAINT `user_main_ibfk_3` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hlavné údaje užívateľa';