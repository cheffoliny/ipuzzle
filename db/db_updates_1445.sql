/* Tables */

CREATE TABLE `sod`.`firms_object_statuses`
(
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_firm` INT (11) UNSIGNED DEFAULT '0' NOT NULL,
	`id_status` INT (11) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY(`id`),
	UNIQUE(`id`)
) TYPE = InnoDB;

/* Access Levels */

/* -- Level -- */
INSERT INTO
	`access_level` ( `id`, `id_group`, `name`, `description` )
VALUES
	( NULL, 1, 'setup_firms_object_statuses', 'Номенклатури => Финанси => Обобщена справка' );

/* -- Files -- */
INSERT INTO
	access_level_files ( id, id_level, filename )
VALUES
	(
		NULL,
		( SELECT id FROM access_level WHERE name = 'setup_firms_object_statuses' ),
		'setup_firms_object_statuses'
	);