<?php

	class DBLimitCardOperations extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			parent::__construct($db_sod,'limit_card_operations');
			
		}
		
		public function getReport( $aLimitCard, $oResponse ) {
			
			$nIDLimitCard = $aLimitCard['id'];
			
			$oDBRequests = new DBTechRequests();
			$aRequest = $oDBRequests->getRecord($nIDLimitCard);
			
			$sQuery = "
				SELECT 
					lco.id,
					tеo.name,
					lco.quantity,
					lco.is_done,
					lco.is_done AS chk,
					tеo.price * lco.quantity AS price
				FROM limit_card_operations lco
				LEFT JOIN tech_operations tеo ON tеo.id = lco.id_operation
				WHERE 1
					AND lco.to_arc = 0
					AND lco.id_limit_card = {$nIDLimitCard}
					
			";
			
		
			$aOperations = $this->select($sQuery);

			APILog::Log(0,$aOperations);
			APILog::Log(0,$aRequest);
			
			$oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
			$oResponse->setFieldData('chk', 'input', array('type' => 'checkbox'));
			$oResponse->setFieldAttributes('chk', array('style' => 'width: 25px;'));
			//$oResponse->setField( 'confirm', '', '', 'images/confirm.gif' ,'','');
			$oResponse->setField('name','операция','сортирай по операция');
			$oResponse->setField('quantity','количество','сортирай по количество');
			
			if( $aLimitCard['type'] == 'create' ) {
				$oResponse->setField('price','цена','сортирай по цена', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1  ));
				if($aRequest['tech_type'] != 'contract') {
					$oResponse->setField( 'delete', '', '', 'images/cancel.gif', '','');
				}
			} else {
				$oResponse->setField( 'delete', '', '', 'images/cancel.gif', '','');
			}
			$aData = array();
			
			foreach ($aOperations as $key => &$operation ) {
				if( $operation['is_done'] ==  0 ) {
					$oResponse->setDataAttributes($key, 'chk', array("style" => "background:#bd2937;") );
					$oResponse->setDataAttributes($key, 'name',array( "onclick" => "editOperation({$operation['id']})" , "style" => "cursor:pointer ;font-weight:bold;background:#bd2937;color:white;") );
					$oResponse->setDataAttributes($key, 'quantity', array("style" => "font-weight:bold;background:#bd2937;color:white;") );
			//		$oResponse->setDataAttributes($key, 'confirm',array( "onclick" => "confirmOperation({$operation['id']})" , "style" => "cursor:pointer;background:#bd2937;color;white;") );
					if( $aLimitCard['type'] == 'create') {
						$oResponse->setDataAttributes($key, 'price', array("style" => "font-weight:bold;background:#bd2937;color:white;") );
						if($aRequest['tech_type'] != 'contract') {
							$oResponse->setDataAttributes($key, 'delete',array( "onclick" => "delOperation({$operation['id']})" , "style" => "cursor:pointer ;background:#bd2937;color:white;") );
						}
					} else {
						$oResponse->setDataAttributes($key, 'delete',array( "onclick" => "delOperation({$operation['id']})" , "style" => "cursor:pointer ;background:#bd2937;color:white;") );
					}
				} else {
					$oResponse->setDataAttributes($key, 'chk', array("style" => "background:#569457;", "checked" => "checked") );
					$oResponse->setDataAttributes($key, 'name',array( "style" => "cursor:pointer ;background:#569457;color:white;font-weight:bold;") );
				//	$oResponse->setDataAttributes($key, 'confirm',array( "onclick" => "unConfirmOperation({$operation['id']})" , "style" => "cursor:pointer; background:#569457;color:white;font-weight:bold;") );
					$oResponse->setDataAttributes($key, 'quantity', array("style" => "background:#569457;color:white;font-weight:bold;") );
					//$oResponse->setDataAttributes($key, 'price', array("style" => "background:#569457;color:white;font-weight:bold;") );
					//$oResponse->setDataAttributes($key, 'delete',array( "style" => "cursor:pointer ;background:#569457;color:white;") );
					if( $aLimitCard['type'] == 'create') {
						$oResponse->setDataAttributes($key, 'price', array("style" => "font-weight:bold;background:#569457;color:white;") );
						if($aRequest['tech_type'] != 'contract') {
							$oResponse->setDataAttributes($key, 'delete',array( "onclick" => "delOperation({$operation['id']})" , "style" => "cursor:pointer ;background:#569457;color:white;") );
						}
					} else {
						$oResponse->setDataAttributes($key, 'delete',array( "onclick" => "delOperation({$operation['id']})" , "style" => "cursor:pointer ;background:#569457;color:white;") );
					}
				}
			}
			$oResponse->setData($aOperations);
		}
		
		public function getLimitCardOperation( $nID) {
			
			$sQuery = "
				SELECT 
					lco.id,
					tеo.name,
					lco.quantity,
					tеo.price
				FROM limit_card_operations lco
				LEFT JOIN tech_operations tеo ON tеo.id = lco.id_operation
				WHERE 1
					AND lco.to_arc = 0
					AND lco.id = {$nID}
					
			";
			
			return $this->selectOnce($sQuery);
		}

		public function getPriceOperationByLC( $nIDLimitCard ) {
			
			$sQuery = "
				SELECT
					SUM(teo.price * lco.quantity) AS price
				FROM limit_card_operations lco
				LEFT JOIN tech_operations teo ON teo.id = lco.id_operation
				LEFT JOIN personnel.personnel up ON up.id = lco.updated_user
				WHERE 1
					AND lco.to_arc = 0
					AND lco.id_limit_card = {$nIDLimitCard}
					AND lco.is_done = 1				
			";
			
			return $this->selectOne($sQuery);
		}
	
		public function getReportByLC( $nIDLimitCard, $oResponse ) {
			$nWork = Params::get('nIDWork', 0);
			
			$oLock = new DBTechLimitCards();
			$aLock = array();
			
			$aLock = $oLock->getStatus($nIDLimitCard);
			
			$sQuery = "
				SELECT 
					lco.id,
					tеo.name,
					lco.quantity,
					lco.is_done,
					tеo.price * lco.quantity AS price,
					CONCAT( CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(lco.updated_time, '%d.%m.%Y %H:%i:%s'), ']' ) AS updated_user
				FROM limit_card_operations lco
				LEFT JOIN tech_operations tеo ON tеo.id = lco.id_operation
				LEFT JOIN personnel.personnel up ON up.id = lco.updated_user
				WHERE 1
					AND lco.to_arc = 0
					AND lco.id_limit_card = {$nIDLimitCard}
					
			";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$total = 0;
					
			foreach ( $oResponse->oResult->aData as $key => $aRow ) {
				if( $aRow['is_done'] == 0 )  {
					$oResponse->setRowAttributes( $aRow['id'], array( "style"=>"font-weight:bold" ) );
				}
				
				$oResponse->setDataAttributes( $key, 'quantity', array("style" => "width: 40px; text-align: right;") );
				$oResponse->setDataAttributes( $key, 'price', array("style" => "width: 70px; text-align: right;") );
				$oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'text-align: center; width: 30px;') );			
				$total += $aRow['price'];
			}
			
			$oResponse->setField( 'name', 'операция', 'сортирай по операция' );
			$oResponse->setField( 'quantity', 'кол', 'сортирай по количество' );
			$oResponse->setField( 'price', 'цена', 'сортирай по цена', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1) );
			$oResponse->setField( 'updated_user', '...', 'Сортиране по последно редкатирал', 'images/dots.gif' );
			$oResponse->addTotal( 'price', $total );
			
			if ( ($nWork == 0) && !empty($aLock) && (($aLock != 'cancel') && ($aLock != 'closed')) ) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteOperation', 'Изтрий' );
				$oResponse->setFieldLink( 'name', 'addOperation' );
			}
		}
		
		public function getEarning($nIDLimitCard) {
			
			$sQuery = "
				SELECT 
					SUM(IF(lco.is_done = 1,o.price*lco.quantity,0)) AS price1,
					SUM(o.price*lco.quantity) AS price2
				FROM limit_card_operations lco
				LEFT JOIN tech_operations o ON o.id = lco.id_operation
				WHERE 1
					AND lco.id_limit_card = {$nIDLimitCard}
					AND lco.to_arc = 0
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getLimitCardOperations($nIDLimitCard) {
			
			$sQuery = "
				SELECT 
					*
				FROM limit_card_operations
				WHERE 1
					AND to_arc = 0
					AND id_limit_card = {$nIDLimitCard}
			";
			
			return $this->select($sQuery);
		}
		
		public function countNotDone($nIDLimitCard) {
			$sQuery = "
				SELECT
					count(*)
				FROM limit_card_operations lco
				WHERE 1
					AND lco.id_limit_card = {$nIDLimitCard}
					AND lco.is_done = 0
					AND lco.to_arc = 0
			";
			
			return $this->selectOne($sQuery);			
		}
	
		public function countQuantity($nIDLimitCard) {
			
			$sQuery = "
				SELECT
					SUM(lco.quantity) AS quantity
				FROM limit_card_operations lco
				WHERE lco.to_arc = 0
					AND lco.is_done = 1
					AND lco.id_limit_card = {$nIDLimitCard}
			";
			
			return $this->selectOne($sQuery);
		}
	}

?>