<?php
	
	class DBContractMonthCharges extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct($db_finance, 'contract_month_charges');
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('month_charges', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}

			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						id,
						CASE type
							WHEN 'mdo' THEN 'Месечна Денонощна Охрана'
							WHEN 'tp' THEN 'Инфра ПЛАН'
							WHEN 'mon' THEN 'Мониторинг на Обект'
						END AS type,
						CONCAT( base_price, ' лв.' ) AS base_price,
						factor_detector,
						k_client_tech,
						CONCAT( price_radio_panic, ' лв.' ) AS price_radio_panic,
						CONCAT( price_static_panic, ' лв.' ) AS price_static_panic,
						CONCAT( price_kbd_panic, ' лв.' ) AS price_kbd_panic,
						CONCAT( price_online_bill, ' лв.' ) AS price_online_bill,
						CONCAT( price_telepol_vest, ' лв.' ) AS price_telepol_vest,
						CONCAT( price_mon, ' лв.' ) AS price_mon,
						CONCAT( expres_order_price, ' лв.' ) AS express_order_price,
						CONCAT( fast_order_price, ' лв.' ) AS fast_order_price
					FROM contract_month_charges
			";
			
			$this->getResult( $sQuery, 'type', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField("type", 				"Тип", 						"Сортирай по тип");
			$oResponse->setField("base_price", 			"Такса", 					"Сортирай по такса");
			$oResponse->setField("factor_detector", 	"Стъпка оскъпяване", 		"Сортирай по Стъпка Оскъпяване");
			$oResponse->setField("k_client_tech", 		"Коеф. поевтиняване", 		"Сортирай по Коефициент Поевтиняване");
			$oResponse->setField("price_radio_panic", 	"Радио Паник Бутон", 		"Сортирай");
			$oResponse->setField("price_static_panic", 	"Стац. Паник Бутон", 		"Сортирай");
			$oResponse->setField("price_kbd_panic", 	"Клавиатурен Паник", 		"Сортирай");
			$oResponse->setField("price_online_bill", 	"Сметка Онлайн", 			"Сортирай");
			$oResponse->setField("price_telepol_vest", 	"\"Инфра Вест\"", 		"Сортирай");
			$oResponse->setField("express_order_price", "Експресна Поръчка", 		"Сортирай");
			$oResponse->setField("fast_order_price", 	"Бърза Поръчка", 			"Сортирай");
			
			if ($right_edit)
			{
				$oResponse->setField( '', '', '', 'images/glyphicons/row.delete.png', 'deleteCharge', '');
				$oResponse->setFieldLink("type", "openCharge");
			}
		}
	}

?>