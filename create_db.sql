DROP DATABASE IF EXISTS sqli_benchmark;
CREATE DATABASE sqli_benchmark CHARACTER SET utf8;

USE sqli_benchmark;

CREATE TABLE IF NOT EXISTS `generation`(
	`id` int NOT NULL AUTO_INCREMENT,
	`num_of_tests` int NOT NULL,
	`process` int NOT NULL,
	`state` int NOT NULL,
	UNIQUE KEY `id` (`id`)
);
