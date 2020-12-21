ALTER TABLE
	`salary_unstored`
ADD
	`type` TINYINT(2) UNSIGNED DEFAULT '0' NOT NULL COMMENT '0 - Otpusk; 1 - Bolnichen; 2 - Obezshtetenie' AFTER `total_sum`;