INSERT INTO
	`telenet_system`.`access_level`
VALUES
	(
		NULL,
		6,
		'setup_person_leave_change',
		'Картон на служителя => Отпуски => Промяна на запазена молба'
	);

INSERT INTO
	`telenet_system`.`access_level_files`
VALUES
	(
		NULL,
		( SELECT id FROM `telenet_system`.`access_level` WHERE name = 'setup_person_leave_change' ),
		'setup_person_leave'
	);