<?php
        require_once(dirname(__FILE__)."/../../config/config.inc.php");
        require_once(dirname(__FILE__)."/../../config/connect.inc.php");
//	require_once("config/config.inc.php");
//	require_once("config/connect.inc.php");

	define('PATERN_QUERY_LIMIT', '/(?i)\s+LIMIT\s+\d+(?:\s*,\s*\d+|)/');
	define('PATERN_QUERY_ORDER', '/(?i)\s+ORDER\s+BY\s+\w+\s+(?i:ASC|DESC)?(\s*\,\s*\w+\s+(?i:ASC|DESC)?)*/');

	// -- ERRORS --
	define('DBAPI_ERR_SUCCESS'				, 0x00);	// Успех
	define('DBAPI_ERR_FILE_NOT_FOUND'		, 0x01);	// Ненамерен файл
	define('DBAPI_ERR_FUNC_NOT_FOUND'		, 0x02);	// Ненамерена функция
	define('DBAPI_ERR_INVALID_PARAM'		, 0x03);	// Невалидни входни данни
	define('DBAPI_ERR_SQL_QUERY'			, 0x04);	// Грешка при заявка
	define('DBAPI_ERR_SQL_DATA'				, 0x05);	// Некоректни данни върнати от заявка
	define('DBAPI_ERR_WRONG_FILE'			, 0x06);	// Невалиден файл
	define('DBAPI_ERR_PASSWORD_INCORRECT'	, 0x07);	// Невалидна парола
	define('DBAPI_ERR_FAILED_TRANS'			, 0x08);	// Неуспешна транзакция
	define('DBAPI_ERR_ASSERT'				, 0x09);	// Логическа грешка
	define('DBAPI_ERR_ACCESS_DENIED'		, 0x0a);	// Отказан достъп до ресурс
	define('DBAPI_ERR_SESSION_DATA'			, 0x0b);	// Некоректни данни в сесията на потребителя
	define('DBAPI_ERR_FTP_UPLOAD'			, 0x0c);	// Проблем при upload на FTP сървър
	define('DBAPI_ERR_UNKNOWN'				, 0x0d);	// Неизвестен проблем
    define('DBAPI_ERR_PHP_INTERNAL'         , 0x0e);    // Грешка идваща от PHP интерпретатора

	define('DBAPI_ERR_ALERT'				, 0x64);

	// -- SORT --
	define('DBAPI_SORT_ASC'	, 0x00);
	define('DBAPI_SORT_DESC', 0x01);

	//-- PREFIXES --

	define('PREFIX_SYSTEM_EVENTS',	'system_log_');
	define('PREFIX_SALES_DOCS',		'sales_docs_');
	define('PREFIX_SALES_DOCS_ROWS','sales_docs_rows_');
	define('PREFIX_BUY_DOCS',		'buy_docs_');
	define('PREFIX_BUY_DOCS_ROWS',	'buy_docs_rows_');
	define('PREFIX_ORDERS',			'orders_');
	define('PREFIX_ORDERS_ROWS',	'orders_rows_');
	define('PREFIX_TRANSFERS',		'transfers_');
	define('PREFIX_LOGS',			'logs_');
	define('PREFIX_FUNDS_DOCS',		'funds_');


	// -- DOCUMENT_TYPES
	define('DOCUMENT_TYPE_DANACHNA'		, 'danachna'	);
	define('DOCUMENT_TYPE_OPROSTENA'	, 'oprostena'	);
	define('DOCUMENT_TYPE_D_DEBITNO'	, 'd_debitno'	);
	define('DOCUMENT_TYPE_O_DEBUTNO'	, 'o_debitno'	);
	define('DOCUMENT_TYPE_D_KREDITNO'	, 'd_kreditno'	);
	define('DOCUMENT_TYPE_O_KREDITNO'	, 'o_kreditno'	);
	define('DOCUMENT_TYPE_KVITANCIA'	, 'kvitancia'	);


	//-- FORMATS --
	define('FMT_MONTH_TABLE', '%04u%02u');

	//-- SUFIXES --
	function SUFIX_MONTH_TABLE($nYear, $nMonth) { return sprintf(FMT_MONTH_TABLE, $nYear, $nMonth); }

	//-- MACROSES --
	function MONTH_TABLE($sPrefix, $nYear, $nMonth)	{ return $sPrefix.SUFIX_MONTH_TABLE($nYear, $nMonth); }

	// CONSTATS
	define('OPTION_NULL', 	"--- Изберете ---"	); // използва се при <select></select> като нулев елемент
	define('OPTION_ALL'	, 	"--- Всички ---"	); // използва се при <select></select> --Всички--
	define('OPTION_OTHER', 	"--- Други ---"		); // използва се при <select></select> --Други--
	define('ZEROPADDING', 8						); // Дължина на числата заедно с водещите нули

	// SESSION KEYS
	define('SK_INVOICE_CLIENTS_GROUP', 'SK_INVOICE_CLIENTS_GROUP');

	// DATA FORMATS
	define('DF_STRING', 		1);  	// 'string'
	define('DF_FLOAT', 			2);  	// 0.000
	define('DF_DIGIT', 			3);  	// 0.00
	define('DF_NUMBER', 		4);  	// 0
	define('DF_CURRENCY', 		5);  	// 0.00 лв
	define('DF_DATETIME', 		6);  	// dd.mm.yyyy hh:mm:ss
	define('DF_DATE', 			7);  	// dd.mm.yyyy
	define('DF_TIME', 			8);  	// hh:mm:ss
	define('DF_MONTH',			9);		// mm.yyyy
	define('DF_CURRENCY4',		10);	// 0.0000 лв.
	define('DF_CENTER',			11);	// 'text-align:center'
	define('DF_ZEROLEADNUM',	12);	// 'text-align:center'
	define('DF_PERCENT',		13);	// 'd%'
	define('DF_CURRENCY6',		14);	// 0.000000 лв.
	define('DF_MEASURE_BR',		15);	// Мерна единица бр.

?>
