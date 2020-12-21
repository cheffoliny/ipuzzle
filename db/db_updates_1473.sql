INSERT INTO
	access_level_files
VALUES
	(
		NULL,
		( SELECT id FROM access_level WHERE name = 'admin_vouchers' ),
		'admin_vouchers_filter'
	);