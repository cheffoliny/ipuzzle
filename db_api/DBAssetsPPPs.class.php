<?php

	class DBAssetsPPPs extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_ppps' );
		}
		
		public function getByType( $aParams, DBResponse $oResponse, $sType) {
			
			global $db_name_personnel;
			
			$sQuery = "
			
				SELECT SQL_CALC_FOUND_ROWS
					ap.id,
					ap.id AS id_,
					ap.confirm_user,
					'' as sum,
					DATE_FORMAT( ap.created_time, '%d.%m.%Y %H:%i:%s' ) AS created_time_,
					CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS created_user
				FROM assets_ppps ap
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ap.created_user
				WHERE 1
					AND ap.to_arc = 0
					AND ap.ppp_type = '{$sType}'
			";
			
			if( !empty( $aParams['sFromDate'] ) ) {
				$nDateFrom = jsDateToTimestamp( $aParams['sFromDate'] );
				
				if( !empty( $nDateFrom ) ) {
					$sQuery .= "AND DATE( ap.created_time ) >= DATE( FROM_UNIXTIME( $nDateFrom ) )\n";
				}
			}
			
			if( !empty( $aParams['sToDate'] ) )	{
				$nDateTo = jsDateToTimestamp( $aParams['sToDate'] );
				
				if( !empty( $nDateTo ) ) {
					$sQuery .= "AND DATE( ap.created_time ) <= DATE( FROM_UNIXTIME( $nDateTo ) )\n";
				}
			}
			
			$this->getResult( $sQuery, 'created_time_', DBAPI_SORT_DESC, $oResponse );
			
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			$Total = 0;
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['id_'] = zero_padding($val['id_'],6);
				if(empty($val['confirm_user'])) {
					$oResponse->setRowAttributes($val['id'],array('style' => 'font-weight:bold;'));
				}
				
				$nSum = $oDBAssetsPPPElements->getSumByIDPPP($val['id']);
			    if (!empty ($nSum)) $val['sum'] = $nSum.' лв.';
			    $Total+=$nSum;
			}	

			if ($Total) {
				
				$sQueryTotal = "
					SELECT
						ap.id,
						ap.id AS id_,
						'' as sum
					FROM assets_ppps ap
					WHERE 1
					AND ap.to_arc = 0
					AND ap.ppp_type = '{$sType}'
				";
				
				if( !empty( $aParams['sFromDate'] ) ) {
					$nDateFrom = jsDateToTimestamp( $aParams['sFromDate'] );
				
					if( !empty( $nDateFrom ) ) {
						$sQueryTotal .= "AND DATE( ap.created_time ) >= DATE( FROM_UNIXTIME( $nDateFrom ) )\n";
					}
				}
			
				if( !empty( $aParams['sToDate'] ) )	{
					$nDateTo = jsDateToTimestamp( $aParams['sToDate'] );
				
					if( !empty( $nDateTo ) ) {
						$sQueryTotal .= "AND DATE( ap.created_time ) <= DATE( FROM_UNIXTIME( $nDateTo ) )\n";
					}
				}
						
				$oStates=new DBStates();
				$aTotal = $oStates->select( $sQueryTotal );
				APILog::Log($sQueryTotal);
				$Total = 0;
				foreach( $aTotal as $key => &$val ) {
					$val['id_'] = zero_padding($val['id_'],6);				
					$nSum = $oDBAssetsPPPElements->getSumByIDPPP($val['id']);
			    	if (!empty ($nSum)) $val['sum'] = $nSum.' лв.';
			    	$Total+=$nSum;
			    }	
			
				if( isset( $aTotal[0]) )
					$oResponse->addTotal( 'sum', $oStates->mround( $Total) . ' лв.' );
			
			}
			
			$oResponse->setField( "id_", 			"Номер", 					"Сортирай по Номер" ,NULL,NULL,NULL,array('style' => 'width: 70px;'));
			$oResponse->setField( "created_time_", 	"Дата на създаване", 		"Сортирай по Дата на Създаване",NULL,NULL,NULL,array('style' => 'width: 140px;') );
			$oResponse->setField( "created_user", 	"Създал", 					"Сортирай по Създал" );
			$oResponse->setField( "sum", 			"Сума", 					"Сортирай по Сума", NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1) );
			$oResponse->setFieldLink("id_",'editAssetsPPP');
		}
	}
	
?>