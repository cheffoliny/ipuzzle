<?php
	
	class DBRequests extends DBBase2
	{
		
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, 'requests');
		}
		
		public function getOffice( $nIDStoragehouse )
		{
			global $db_name_sod;
			
			$nIDStoragehouse = is_numeric( $nIDStoragehouse ) ? $nIDStoragehouse : 0;
			
			$sQuery = "
					SELECT
						o.id
					FROM storagehouses s
					LEFT JOIN {$db_name_sod}.offices o ON o.id = s.id_office
					WHERE s.to_arc = 0
						AND o.to_arc = 0
						AND s.id = {$nIDStoragehouse}
					";
			
			$aRes = $this->selectOnce( $sQuery );
			
			return $aRes['id'];
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'setup_requests_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						r.id, 
						DATE_FORMAT( r.request_time, '%d.%m.%Y %H:%i:%s' ) AS request_time_,
						sh.name as storagehouse_name,
						sh2.name as receive_storagehouse_name,
						CONCAT_WS(' ', prq.fname, prq.mname, prq.lname) AS request_user,
						CONCAT_WS(' ', prc.fname, prc.mname, prc.lname) AS receive_user,
						CONCAT(
							CONCAT_WS( ' ', pu.fname, pu.mname, pu.lname ),
							'(',
							DATE_FORMAT( r.updated_time, '%d.%m.%Y %H:%i:%s' ),
							')'
							) AS updated_user,
						r.is_readed
						FROM requests r
						LEFT JOIN {$db_name_personnel}.personnel prq ON r.request_user = prq.id
						LEFT JOIN {$db_name_personnel}.personnel prc ON r.receive_user = prc.id
						LEFT JOIN {$db_name_personnel}.personnel pu ON r.updated_user = pu.id
						LEFT JOIN storagehouses sh ON r.request_storagehouse = sh.id
						LEFT JOIN storagehouses sh2 ON r.receive_storagehouse = sh2.id
						WHERE r.to_arc=0
					";
			
			$sLoggedUser = $_SESSION['userdata']['id_person'];
			$sQuery .= " AND ( r.receive_user = {$sLoggedUser} OR r.request_user = {$sLoggedUser} ) ";
			
			$this->getResult($sQuery, 'request_time_', DBAPI_SORT_DESC, $oResponse);

			foreach( $oResponse->oResult->aData as $aRow )
			{
				if( empty( $aRow['is_readed'] ) )
					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-weight:bold" ) );
			}

			$oResponse->setField( 'request_time_', 				'дата', 			'Сортирай по Дата' );
			$oResponse->setField( 'storagehouse_name', 			'от склад', 		'Сортирай по склад' );
			$oResponse->setField( 'request_user', 				'от МОЛ', 			'Сортирай по МОЛ' );
			$oResponse->setField( 'receive_storagehouse_name', 	'към склад', 		'Сортирай по склад' );
			$oResponse->setField( 'receive_user', 				'към МОЛ', 			'Сортирай по МОЛ' );
			$oResponse->setField( 'updated_user', 				'...',				'Сортиране по последно редкатирал', 'images/dots.gif' );
			
			if ($right_edit) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteRequest', '' );
				$oResponse->setFieldLink( "request_time_", "openRequest" );
			}
		}
		
	}
?>