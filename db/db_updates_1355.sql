CREATE TABLE `personnel`.`salary_unstored`
(
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_person` INT (11) UNSIGNED DEFAULT '0' NOT NULL,
	`id_salary_row` BIGINT (11) UNSIGNED DEFAULT '0' NOT NULL,
	`total_sum` DOUBLE (10,2) UNSIGNED DEFAULT '0.00' NOT NULL,
	PRIMARY KEY(`id`),
	UNIQUE(`id`)
) COMMENT = "Nezapazeni promeni po rabotni zaplati." TYPE = InnoDB;