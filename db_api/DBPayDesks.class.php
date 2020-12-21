<?php
	class DBPayDesks extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'pay_desks' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod;
			
			$sQuery = "
					SELECT
						SQL_CALC_FOUND_ROWS
						pd.id,
						pd.name AS num,
						GROUP_CONCAT( CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) SEPARATOR ', ' ) AS persons,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( pd.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
						FROM
							pay_desks pd
						LEFT JOIN
							{$db_name_personnel}.personnel p ON pd.updated_user = p.id
						LEFT JOIN
							pay_desks_persons pdp ON pdp.id_pay_desk = pd.id
						LEFT JOIN
							{$db_name_personnel}.personnel pe ON pe.id = pdp.id_person
						LEFT JOIN
							{$db_name_sod}.offices of ON of.id = pe.id_office
						WHERE
							pd.to_arc = 0
			";
			
			if( !empty( $aParams['nIDPerson'] ) )
			{
				$sQuery .= "
							AND pe.id = {$aParams['nIDPerson']}
				";
			}
			if( !empty( $aParams['nIDOffice'] ) )
			{
				$sQuery .= "
							AND pe.id_office = {$aParams['nIDOffice']}
				";
			}
			if( !empty( $aParams['sNum'] ) )
			{
				$sQuery .= "
							AND pd.name LIKE '%{$aParams['sNum']}%'
				";
			}
			
			$sQuery .= "
						GROUP BY
							pd.id
			";
			
			$this->getResult( $sQuery, "num", DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "num", 			"Номер к.ап.", 			"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "persons", 		"Служители", 			"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"" );
			$oResponse->setField( '', 				'', 					'', 'images/cancel.gif', 'deletePayDesk', '' );
			
			$oResponse->setFieldLink( "num", "openPayDesk" );
		}
	}
?>