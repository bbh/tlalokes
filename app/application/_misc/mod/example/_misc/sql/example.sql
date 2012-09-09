DROP SCHEMA IF EXISTS `tf_example`;
CREATE SCHEMA IF NOT EXISTS `tf_example` CHARACTER SET=`utf8` COLLATE=`utf8_general_ci`;
USE `tf_example`;

CREATE TABLE `example` (

  `id_example` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `vc_name` VARCHAR(32) NOT NULL DEFAULT '',

  `t_description` TEXT DEFAULT NULL,

  PRIMARY KEY (`id_example`)

) ENGINE = InnoDB, COMMENT = 'Example table';

INSERT INTO `example` (`id_example`,`vc_name`,`t_description`) VALUES (1,'Foo','Bar');
