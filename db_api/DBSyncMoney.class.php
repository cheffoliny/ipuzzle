<?php
 
	class DBSyncMoney extends DBBase2 {
		public function __construct() {
			global $db_finances;
			
			parent::__construct($db_finances, "bank_accounts");
		}	
		
		/**
		 * Анулиране на платежен документ (в месечните таблици!!!
		 * 
		 * @author Павел Петров
		 * @name invalidate()
		 *
		 * @param int $nID - Пълно ID на документа (таблица+ID)
		 * 
		 * @return void
		 */
		public function invalidate( $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$aSum	= array();
			$table 	= "mp".substr($nID, 0, 6);
			$nIDDoc = substr($nID, -7);
			$nIDDoc	= intval($nIDDoc);
			$aSum 	= $this->getAccountByDoc( $table, $nIDDoc );
			
			$nIDRow	= isset($aSum['id']) 		? $aSum['id'] 					: 0;
			$nIDAcc	= isset($aSum['account']) 	? $aSum['account'] 				: 0;
			$sum	= isset($aSum['valid_sum']) ? ( $aSum['valid_sum'] * -1) 	: 0;
			$nIDObj	= isset($aSum['id_obj']) 	? $aSum['id_obj'] 				: 0;
			$month	= isset($aSum['paid_month'])? $aSum['paid_month'] 			: 0;
			$br		= isset($aSum['taxes'])		? $aSum['taxes'] 				: 1;
			
			if ( !empty($nIDAcc) ) {
				$this->increaseSaldo( $nIDAcc, $sum );
			}
			
			$aData	= array();
			$aData['id'] 		= $nID;
			$aData['zero'] 		= 1;
			$aData['confirm']	= 0;
			$aData['zero_date'] = time();
			$aData['id_obj'] 	= $nIDObj;
			$aData['valid_sum']	= 0;
			$aData['paid_month']= date("Y-m-d", mktime(0, 0, 0, date("m", $month) - $br, 1, date("Y", $month)));

			if ( !empty($nID) ) {
				$this->updateSchetMonth( $aData );
			}
		}
		
		/**
		 * Добавяне/Промяна на запис в месечните таблици в счета 
		 * по подготвен асоацитивен масив!
		 * 
		 * @author Павел Петров
		 * @name updateSchetMonth()
		 *
		 * @param array $aData - асоацитивен масив с данните за записа
		 * 
		 * @return int - ID-то на добавения/променен запис
		 */		
		public function updateSchetMonth( $aData ) {
			global $db_telepol, $db_name_telepol;
				
			$nIDDoc	= isset($aData['id']) && is_numeric($aData['id']) ? $aData['id'] : 0;
			
			if ( !empty($nIDDoc) ) {
				$table 		 = "mp".substr($nIDDoc, 0, 6);
				$nID 		 = substr($nIDDoc, -7);
				$nID		 = intval($nID);	
				$aData['id'] = $nID;	
			} else {
				$nID 		 = 0;
				$table 		 = "mp".date("Ym");
				$aData['id'] = 0;
			}
			
			if ( !empty($nID) ) {
				$oRes 		= $db_telepol->Execute("SELECT * FROM {$db_name_telepol}.{$table} WHERE id = {$nID};");
				$updateSQL 	= $db_telepol->GetUpdateSQL($oRes, $aData); 
				$oRes		= $db_telepol->Execute($updateSQL);
			} else {
				$oRes 		= $db_telepol->Execute("SELECT * FROM {$db_name_telepol}.{$table} WHERE id = -1;");
				$insertSQL 	= $db_telepol->GetInsertSQL($oRes, $aData); 
				$oRes 		= $db_telepol->Execute($insertSQL);
					
				$nID 		= $db_telepol->Insert_ID();			
			}
			
			if ( isset($aData['id_obj']) && !empty($aData['id_obj']) ) {
				$nObj	= $aData['id_obj'];
				$pMon	= $aData['paid_month'];
				
				$sQuery = "UPDATE {$db_name_telepol}.objects SET paid_month = '{$pMon}' WHERE id_obj = {$nObj}";
				$db_telepol->Execute($sQuery);
				
				$sQuery = "UPDATE {$db_name_telepol}.objects SET paid_month = '{$pMon}' WHERE id_master_obj = {$nObj}";
				$db_telepol->Execute($sQuery);				
			}
			
			return $nID;
		}
		
		public function getRow($nIDDoc) {
			global $db_telepol, $db_name_telepol;
			
			$aData 		= array();
			
			if ( strlen($nIDDoc) < 12) {
				return array();
			}
			
			$table 		 = "mp".substr($nIDDoc, 0, 6);
			$nID 		 = substr($nIDDoc, -7);
			$nID		 = intval($nID);	
			
			$aData 		= $db_telepol->getArray("SELECT * FROM {$db_name_telepol}.{$table} WHERE id = {$nID};");	

			if ( isset($aData[0]['id']) && !empty($aData[0]['id']) ) {
				return $aData[0];
			} else {
				return array();
			}
		}
		
		public function moveDocument($nIDDoc) {
			global $db_telepol, $db_name_telepol;
			
			$aData = array();
			
			if ( strlen($nIDDoc) < 13) {
				return array();
			}	
			
			$table1 = "mp".substr($nIDDoc, 0, 6);
			$table2 = "mp".date("Ym");
			$nID 	= substr($nIDDoc, -7);
			$nID	= intval($nID);		
			$nLast	= 0;	
					
			if ( $table1 != $table2 ) {
				$sMove = "
					INSERT into {$table2}
					SELECT 
					  `id_obj`,
					  `data`,
					  `mataksa`,
					  `bank`,
					  `confirm`,
					  `confirm_date`,
					  `normal`,
					  `sum`,
					  `taxes`,
					  `paid_month`,
					  `tax_num`,
					  `faktura_type`,
					  `f_name`,
					  `f_address`,
					  `f_dn`,
					  `f_bulstat`,
					  `f_mol`,
					  `p_name`,
					  `p_lk`,
					  `p_year`,
					  `p_num`,
					  `measure`,
					  `br`,
					  NULL,
					  `zero`,
					  `zero_date`,
					  `faktura`,
					  `valid_sum`,
					  `smetka_id`,
					  `direction_id`,
					  `typepay_id`,
					  `info`,
					  `single_pay`,
					  `user_id`,
					  `nareditel`,
					  `poluchatel`, 
					  `razhod_num`,
					  `saldo`
					FROM {$table1}
					WHERE id = '{$nID}'				
				";
				
				$db_telepol->Execute($sMove);
				
				$nLast = $db_telepol->Insert_ID();
				
				if ( !empty($nLast) ) {
					// Трием
					$db_telepol->Execute("DELETE FROM {$table1} WHERE id = '{$nID}' ");
				}
			}

			return $nLast;
		}
		
		/**
		 * Добавяне/Промяна на запис в singles в счета 
		 * по подготвен асоацитивен масив!
		 * 
		 * @author Павел Петров
		 * @name updateSchetSingles()
		 *
		 * @param array $aData - асоацитивен масив с данните за записа
		 * 
		 * @return int - ID-то на добавения/променен запис
		 */			
		public function updateSchetSingles( $aData ) {
			global $db_telepol, $db_name_telepol;
				
			$nIDDoc = isset($aData['id_single']) && is_numeric($aData['id_single']) ? $aData['id_single'] : 0;
			
			if ( !empty($nIDDoc) ) {
				$nID 		 = substr($nIDDoc, -7);
				$nID		 = intval($nID);	
				$aData['id'] = $nID;	
			} else {
				$nID 		 = 0;
				$aData['id'] = 0;
			}			
			
			if ( !empty($nID) ) {
				$oRes 		= $db_telepol->Execute("SELECT * FROM {$db_name_telepol}.singles WHERE id_single = {$nID};");
				$updateSQL 	= $db_telepol->GetUpdateSQL($oRes, $aData); 
				$oRes 		= $db_telepol->Execute($updateSQL);
			} else {
				$oRes 		= $db_telepol->Execute("SELECT * FROM {$db_name_telepol}.singles WHERE id_single = -1;");
				$insertSQL 	= $db_telepol->GetInsertSQL($oRes, $aData); 
				$oRes 		= $db_telepol->Execute($insertSQL);
					
				$nID 		= $db_telepol->Insert_ID();			
			}
			
			return $nID;
		}	

		public function delRow($nID) {
			global $db_telepol, $db_name_telepol;
			
			if ( strlen($nID) == 13) {
				$table 		 = "mp".substr($nID, 0, 6);
				$nID 		 = substr($nID, -7);
				$nID		 = intval($nID);	
				
				$sQuery = "DELETE FROM {$db_name_telepol}.{$table} WHERE id = {$nID}";
				$db_telepol->Execute($sQuery);
			}
		}
		
		/**
		 * Връща сметка и сума по зададен документ
		 * 
		 * @author Павел Петров
		 * @name getAccountByDoc()
		 *
		 * @param string $table - име на периодична таблица
		 * @param int $nID - ID на документ
		 * 
		 * @return array - масив с данни за сметка/сума по ID на документ
		 */			
		public function getAccountByDoc( $table, $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$nID 	= is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					id, smetka_id, valid_sum, id_obj, taxes, UNIX_TIMESTAMP(paid_month) as paid_month
				FROM {$db_name_telepol}.{$table}
				WHERE id = {$nID}
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['smetka_id']) && !empty($sData[0]['smetka_id']) && isset($sData[0]['valid_sum']) ) {
				return array( 
					"id" 		=> $sData[0]['id'], 
					"account" 	=> $sData[0]['smetka_id'], 
					"valid_sum" => $sData[0]['valid_sum'],
					"id_obj" 	=> $sData[0]['id_obj'],
					"taxes" 	=> $sData[0]['taxes'],
					"paid_month" => $sData[0]['paid_month']
				);
			} elseif ( isset($sData[0]['id_obj']) && !empty($sData[0]['id_obj']) ) {
				return array(
					"id_obj" 	=> $sData[0]['id_obj'],
					"taxes" 	=> $sData[0]['taxes'],
					"paid_month" => $sData[0]['paid_month']				
				);
			}
		}		
		
		public function getMasterFromID( $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$nIDObjs 	= isset($nID) && !empty($nID) ? $nID : 0;
			$nIDObject 	= 0;
	
			$sQuery = "
				SELECT 
					IF ( id_master_obj > 0, id_master_obj, id_obj ) as id
				FROM {$db_name_telepol}.objects
				WHERE id_obj = {$nID}
			";
				
			$sData = $db_telepol->getArray( $sQuery );
				//APILog::Log(0, $sData);
			if ( isset($sData[0]['id']) && !empty($sData[0]['id']) ) {
				$nIDObject = $sData[0]['id'];
			}
			
			return $nIDObject;
		}		
		
		public function delSingle($nID) {
			global $db_telepol, $db_name_telepol;
			
			$sQuery = "DELETE FROM {$db_name_telepol}.singles WHERE id_single = {$nID}";
			$db_telepol->Execute($sQuery);
		}
		
		public function payNow($nID) {
			global $db_telepol, $db_name_telepol;
			
			$sQuery = "UPDATE {$db_name_telepol}.singles SET sum_p = sum, confirm = 1, confirm_date = NOW() WHERE id_single = {$nID}";
			$db_telepol->Execute($sQuery);
		}		
		
		public function getMasterFromObjs( $nIDs ) {
			global $db_telepol, $db_name_telepol;
			
			$nIDObjs 	= isset($nIDs) && !empty($nIDs) ? explode(",", $nIDs) : array();
			$nIDObject 	= 0;
			$tmpID		= 0;
			
			foreach ( $nIDObjs as $key => $val ) {
				$tmpID = $val;
				
				$sQuery = "
					SELECT 
						IF ( id_master_obj > 0, 0, id_obj ) as id
					FROM {$db_name_telepol}.objects
					WHERE id_obj = {$val}
				";
				
				$sData = $db_telepol->getArray( $sQuery );
				//APILog::Log(0, $sData);
				if ( isset($sData[0]['id']) && !empty($sData[0]['id']) ) {
					$nIDObject = $sData[0]['id'];
				}
			}

			if ( empty($nIDObject) ) {
				$nIDObject = $tmpID;
			}
			
			return $nIDObject;
		}
		
		public function getDirectionByOffice( $nID ) {
			global $db_sod, $db_name_sod;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					id_schet
				FROM {$db_name_sod}.offices
				WHERE id = {$nID}
			";
			
			$sData = $db_sod->getArray($sQuery);
			

			if ( isset($sData[0]['id_schet']) && !empty($sData[0]['id_schet']) ) {
				return $sData[0]['id_schet'];
			} else return 0;		
		}	
		
		public function getSchetByDirection( $nID ) {
			global $db_sod, $db_name_sod;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					id_schet
				FROM {$db_name_sod}.directions
				WHERE id = {$nID}
			";
			
			$sData = $db_sod->getArray($sQuery);
			

			if ( isset($sData[0]['id_schet']) && !empty($sData[0]['id_schet']) ) {
				return $sData[0]['id_schet'];
			} else return 0;		
		}				
		
		public function getMoneyFromMP( $table, $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					(sum - valid_sum) as sum
				FROM {$db_name_telepol}.{$table}
				WHERE id = {$nID}
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['sum']) && !empty($sData[0]['sum']) ) {
				return $sData[0]['sum'];
			} else return 0;
		}
		
		public function payMonth($aData) {
			global $db_telepol, $db_name_telepol;
			
			$nIDDoc	= isset($aData['id']) 		&& is_numeric($aData['id']) 	? $aData['id'] : 0;
			$nSum	= isset($aData['sum']) 		&& is_numeric($aData['sum']) 	? $aData['sum'] : 0;
			$smetka	= isset($aData['smetka']) 	&& is_numeric($aData['smetka']) ? $aData['smetka'] : 0;
				
			if ( empty($nIDDoc) || empty($nSum) || empty($smetka) ) {
				return false;
			}
			
			if ( strlen($nIDDoc) == 13 ) {
				$table 		 = "mp".substr($nIDDoc, 0, 6);
				$nID 		 = substr($nIDDoc, -7);
				$nID		 = intval($nID);	
				$aData['id'] = $nID;	
			} else {
				return false;
			}

			$wait 	= $this->getMoneyFromMP($table, $nID);
			$saldo 	= $this->getSaldoById($smetka);
				
			if ( $nSum >= $wait ) {
				$saldo 	+= $wait;
				$this->increaseSaldo($smetka, $wait);
					
				$sQuery = "UPDATE {$db_name_telepol}.{$table} SET valid_sum = sum, confirm = 1, confirm_date = NOW(), smetka_id = {$smetka}, saldo = '{$saldo}'  WHERE id = {$nID}";
				$db_telepol->Execute($sQuery);
			} else {
				if ( $nSum > 0 ) {
					$saldo 	+= $nSum;
					$this->increaseSaldo( $smetka, $nSum );
					
					$sQuery = "UPDATE {$db_name_telepol}.{$table} SET valid_sum = valid_sum + '{$nSum}', smetka_id = '{$smetka}', saldo = '{$saldo}'  WHERE id = {$nID}";
					$db_telepol->Execute($sQuery);	
				}			
			}
			
			return true;
		}	
				
//		public function getAccountByDoc( $table, $nID ) {
//			global $db_telepol, $db_name_telepol;
//			
//			$nID = is_numeric($nID) ? $nID : 0;
//			
//			$sQuery = "
//				SELECT 
//					id, smetka_id, valid_sum
//				FROM {$db_name_telepol}.{$table}
//				WHERE id = {$nID}
//			";
//			
//			$sData = $db_telepol->getArray( $sQuery );
//
//			if ( isset($sData[0]['smetka_id']) && !empty($sData[0]['smetka_id']) && isset($sData[0]['valid_sum']) ) {
//				return array( "id" => $sData[0]['id'], "account" => $sData[0]['smetka_id'], "valid_sum" => $sData[0]['valid_sum'] );
//			} else return array();
//		}		
		
		public function findSingle( $nID, $nSum ) {
			global $db_telepol, $db_name_telepol;
			
			$nID 	= is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					id_single
				FROM {$db_name_telepol}.singles
				WHERE id_obj = {$nID}
					AND sum = '{$nSum}'
					AND sum != sum_p
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['id_single']) && !empty($sData[0]['id_single']) ) {
				return $sData[0]['id_single'];
			} else return 0;
		}	
		
		public function getSaldoById( $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					saldo
				FROM {$db_name_telepol}.saldo
				WHERE id = {$nID}
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['saldo']) && !empty($sData[0]['saldo']) ) {
				return $sData[0]['saldo'];
			} else return 0;
		}	

		public function getSingleById( $nID ) {
			global $db_telepol, $db_name_telepol;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					sum
				FROM {$db_name_telepol}.single
				WHERE id_single = {$nID}
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['sum']) && !empty($sData[0]['sum']) ) {
				return $sData[0]['sum'];
			} else return 0;
		}	

		public function getMonthSumById( $nIDDoc ) {
			global $db_telepol, $db_name_telepol;

			if ( is_numeric($nIDDoc) && (strlen($nIDDoc) == 13) ) {
				$table 		 = "mp".substr($nIDDoc, 0, 6);
				$nID 		 = substr($nIDDoc, -7);
				$nID		 = intval($nID);	
				$aData['id'] = $nID;	
			} else {
				return false;
			}			
			
			$sQuery = "
				SELECT 
					sum
				FROM {$db_name_telepol}.{$table}
				WHERE id = {$nID}
			";
			
			$sData = $db_telepol->getArray( $sQuery );

			if ( isset($sData[0]['sum']) && !empty($sData[0]['sum']) ) {
				return $sData[0]['sum'];
			} else return 0;
		}			
		
		public function setPaidMonth( $aData ) {
			global $db_telepol, $db_name_telepol;
			
			$nID 	= isset($aData['id_obj']) && is_numeric($aData['id_obj']) 		? $aData['id_obj'] 		: 0;
			$sPaid 	= isset($aData['paid_month']) && !empty($aData['paid_month']) 	? $aData['paid_month'] 	: "";
			
			if ( !empty($sPaid) ) {
				$sQuery = "UPDATE {$db_name_telepol}.objects SET paid_month = '{$sPaid}' WHERE id_obj = {$nID}";

				$db_telepol->Execute($sQuery);
			}
		}
		
		public function increaseSaldo( $nID, $nSum ) {
			global $db_telepol, $db_name_telepol;
			
			$nID 	= is_numeric($nID) ? $nID : 0;
			$nSum	= is_numeric($nSum) ? $nSum : 0;
			
			$sQuery = "UPDATE {$db_name_telepol}.saldo SET saldo = saldo + {$nSum} WHERE id = {$nID}";

			$db_telepol->Execute($sQuery);
		}		
		
		
		public function getInfoByDoc( $nIDDoc ) {
			global $db_sod, $db_name_sod;
			
			$nIDObj = is_numeric($nIDObj) ? $nIDObj : 0;
			
			$sQuery = "
				SELECT 
					o.id,
					o.id_oldobj,
					IF ( os.id_office > 0, os.id_office, o.id_office ) as id_office,
					os.service_name,
					os.last_paid
				FROM {$db_name_sod}.objects o
				LEFT JOIN {$db_name_sod}.objects_services os ON ( os.id_object = o.id AND os.to_arc = 0 )
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
	}
	
?>