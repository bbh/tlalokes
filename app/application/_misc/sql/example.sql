DROP SCHEMA IF EXISTS `tlalokes_test`;
CREATE SCHEMA IF NOT EXISTS `tlalokes_test` CHARACTER SET=`utf8` COLLATE=`utf8_general_ci`;
USE `tlalokes_test`;

CREATE TABLE `example` (

  `id_example` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `vc_name` VARCHAR(32) NOT NULL DEFAULT '',

  `t_description` TEXT DEFAULT NULL,

  PRIMARY KEY (`id_example`)

) ENGINE = InnoDB, COMMENT = 'Example table';

INSERT INTO `example` (`id_example`,`vc_name`,`t_description`) VALUES (1,'Foo','Bar');
