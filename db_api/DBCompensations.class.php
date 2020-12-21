<?php
	
	class DBCompensations extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct($db_finance, 'compensations');
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('setup_compensations', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}

			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						c.id,
						CASE c.type
							WHEN 'mdo' THEN 'Месечна Денонощна Охрана'
							WHEN 'tp' THEN 'Инфра ПЛАН'
						END AS type,
						CONCAT( c.yearly, ' лв.' ) as yearly,
						CONCAT( c.single, ' лв.' ) as single,
						c.factor,
			";
			
			for( $i = 1; $i <= 10; $i++ )
			{
				$b = $i - 1;
				$sQuery .= "
					CONCAT( ( cmc.base_price + ( cmc.factor_detector * {$b} ) ) * c.factor, ' лв.' ) AS detectors{$i},
				";
			}
			
			$sQuery .= "
						IF( 
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								'(',
								DATE_FORMAT( c.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM compensations c
					LEFT JOIN {$db_name_personnel}.personnel p ON c.updated_user = p.id
					LEFT JOIN contract_month_charges cmc ON c.type = cmc.type
					WHERE c.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'type, yearly, single', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField("type", 			"Тип", 					"Сортирай по тип");
			$oResponse->setField("yearly", 			"Годишна", 				"Сортирай");
			$oResponse->setField("single", 			"Еднократна", 			"Сортирай");
			$oResponse->setField("factor", 			"Коефициент", 			"Сортирай");
			
			for( $i = 1; $i <= 10; $i++ )
			{
				$oResponse->setField("detectors{$i}", "$i дет.", "Сортирай");
			}
			
			$oResponse->setField("updated_user", 	"Последна редакция", 	"Сортирай по последна редакция");
			
			if ($right_edit)
			{
				$oResponse->setField( '', '', '', 'images/glyphicons/row.delete.png', 'deleteCompensation', '');
				$oResponse->setFieldLink("type", "openCompensation");
			}
		}
	}

?>