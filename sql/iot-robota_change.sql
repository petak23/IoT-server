SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

/* Added 01.07.2022 */
ALTER TABLE `sensors`
CHANGE `value_type` `id_value_types` int(11) NOT NULL COMMENT 'Jednotky' AFTER `id_device_classes`,
ADD FOREIGN KEY (`id_value_types`) REFERENCES `value_types` (`id`);