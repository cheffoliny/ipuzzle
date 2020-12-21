<?php

	class DBPayDesksReports extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'pay_desks_reports' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod;
			
			$nTimeFrom	= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			
			$sQuery = "
					SELECT
						SQL_CALC_FOUND_ROWS
						pdr.id,
						DATE_FORMAT( pdr.report_date, '%d.%m.%Y' ) AS report_date,
						pd.name AS name,
						GROUP_CONCAT( CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) SEPARATOR ', ' ) AS persons,
						pdr.oborot AS oborot,
						pdr.storno AS storno,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( pdr.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
						FROM
							pay_desks_reports pdr
						LEFT JOIN
							pay_desks pd ON pd.id = pdr.id_pay_desk
						LEFT JOIN
							{$db_name_personnel}.personnel p ON pdr.updated_user = p.id
						LEFT JOIN
							pay_desks_persons pdp ON pdp.id_pay_desk = pd.id
						LEFT JOIN
							{$db_name_personnel}.personnel pe ON pe.id = pdp.id_person
						LEFT JOIN
							{$db_name_sod}.offices of ON of.id = pe.id_office
						WHERE
							pdr.to_arc = 0
							AND pd.to_arc = 0
							AND ( UNIX_TIMESTAMP( pdr.report_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( pdr.report_date ) <= {$nTimeTo} )
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
			
			if( !empty( $aParams['nIDFirm'] ) )
			{
				$sQuery .= "
							AND of.id_firm = {$aParams['nIDFirm']}
				";
			}
			
			if( !empty( $aParams['nIDPayDesk'] ) )
			{
				$sQuery .= "
							AND pd.id = {$aParams['nIDPayDesk']}
				";
			}
			
			$sQuery .= "
						GROUP BY
							pdr.id
			";
			
			$this->getResult( $sQuery, "report_date", DBAPI_SORT_DESC, $oResponse );
			
			$oResponse->setField( "report_date", 	"Дата",					"Сортирай по Дата", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
			$oResponse->setField( "name", 			"Касов Апарат",			"Сортирай по Касов Апарат", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "persons", 		"Служители", 			"Сортирай по Служители", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "oborot", 		"Сума Оборот",			"Сортирай по Сума Оборот", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "storno", 		"Сума Сторно",			"Сортирай по Сума Сторно", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "updated_user", 	"Последна редакция",	"" );
		}
		
		function jsDateEndToTimestamp( $sDate )
		{
			if( !empty( $sDate ) )
			{
				@list( $d, $m, $y ) = explode( ".", $sDate );
				
				if( @checkdate( $m, $d, $y ) )
				{
					return mktime( 23, 59, 59, $m, $d, $y );
				}
			}
			
			return 0;
		}
	}

?>