<?php

	class DBBase2
	{
		protected $oDB = NULL;
		protected $sTableName = NULL;
		protected $bHasToArc = NULL;
		protected $oEmptyResult = NULL;
		
		public function __construct( $oConnection, $sTableName )
		{
			$this->oDB = $oConnection;
			$this->sTableName = $sTableName;
		}
		
		public function update( &$aData )
		{
			$aData['updated_time'] = time();
			if(isset($aData['updated_user_hardcore'])) {
				$aData['updated_user'] = $aData['updated_user_hardcore'];
				unset($aData['updated_user_hardcore']);
			} else {
				$aData['updated_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			}
			
			if( empty( $aData['id'] ) )	//Insert
			{
				$aData['created_time'] = $aData['updated_time'];
				$aData['created_user'] = $aData['updated_user'];
			
				if( $this->oEmptyResult == NULL )
					$this->oEmptyResult = $this->oDB->Execute("SELECT * FROM {$this->oDB->database}.{$this->sTableName} WHERE 0");
					
				$sQuery = $this->oDB->GetInsertSQL($this->oEmptyResult, $aData);
				
				if( empty( $sQuery ) )
					return;
				
				$this->oDB->Execute( $sQuery );
				
				$aData['id'] = $this->oDB->Insert_ID();
//                return $aData['id'];
			}
			else	//Update
			{
				$oRs = $this->oDB->Execute("SELECT * FROM {$this->oDB->database}.{$this->sTableName} WHERE id = {$aData['id']}");
				
				$sQuery = $this->oDB->GetUpdateSQL($oRs, $aData);
				
				if( empty( $sQuery ) )
					return;	
					
				$this->oDB->Execute( $sQuery );
//                return  $aData['id'];
			}
		}
		
		public function hasToArc()
		{
			if( $this->bHasToArc === NULL )
			{
				$oRs = $this->oDB->Execute("SHOW FIELDS FROM {$this->oDB->database}.{$this->sTableName} LIKE 'to_arc'");
				$this->bHasToArc = ( bool ) !$oRs->EOF;	//няма поле `to_arc`
			}
							
			return $this->bHasToArc;
		}
		
		public function delete( $nID )
		{
			if( $this->hasToArc() )	//има поле `to_arc`
			{	
				$aData = array();
				$aData['id'] = $nID;
				$aData['to_arc'] = 1;
				$this->update( $aData );
			}
			else			//няма поле `to_arc`
			{
				$this->oDB->Execute("DELETE FROM {$this->oDB->database}.{$this->sTableName} WHERE id = {$nID} LIMIT 1");
			}
		}
		
		public function delete_permanently( $nID )
		{
			if( !empty( $nID ) || is_numeric( $nID ) )
			{
				$this->oDB->Execute( "DELETE FROM {$this->oDB->database}.{$this->sTableName} WHERE id = {$nID} LIMIT 1" );
			}
		}
		
		public function select( $sQuery )
		{
			$oRs = $this->oDB->Execute( $sQuery );
			//debug($sQuery);
			
			return !$oRs->EOF ? $oRs->getArray() : array();
		}
		
		public function selectAssoc( $sQuery )
		{
			$oRs = $this->oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->getAssoc() : array();
		}
		
		public function selectOnce( $sQuery )
		{
			$oRs = $this->oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->fields : array();
		}
		
		public function selectOne( $sQuery )
		{
			$aData = $this->selectOnce( $sQuery );
			
			return !empty( $aData )? current( $aData ) : NULL;
		}
		
		public function selectFromDB( $oDB, $sQuery ) {
			
			$oRs = $oDB->Execute($sQuery);
			return !$oRs->EOF ? $oRs->getArray() : array();
		}
		
		public function selectAssocFromDB( $oDB, $sQuery )
		{
			$oRs = $oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->getAssoc() : array();
		}
		
		public function selectOnceFromDB( $oDB, $sQuery )
		{
			$oRs = $oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->fields : array();
		}
		
		public function selectOneFromDB( $oDB, $sQuery )
		{
			$aData = $this->selectOnceFromDB( $oDB, $sQuery );
			
			return !empty( $aData )? current( $aData ) : NULL;
		}
		
		public function _getAll( $bAssoc ) {
			$sQuery = "SELECT t.id as _id, t.* FROM {$this->oDB->database}.{$this->sTableName} t";
			
			if ( $this->hasToArc() ) {
				$sQuery .= " WHERE t.to_arc = 0";
			}
			
			$sQuery .= " ORDER BY t.name ";
				
			return $bAssoc ? $this->selectAssoc( $sQuery ) : $this->selectAssoc( $sQuery );
		}
		
		public function getAll()
		{
			return $this->_getAll( FALSE );
		}
		
		public function getAllAssoc()
		{
			return $this->_getAll( TRUE );
		}
		
		public function getRecord( $nID )
		{
			$sQuery = "SELECT * FROM {$this->sTableName} WHERE id = {$nID} LIMIT 1";
			
			$oRs = $this->oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->fields : array();
		}

        /*
         * $nForceSetLimit - ако в заявката има използват лимит не се добавя странициране!
         * Ако $nForceSetLimit = 1 се добавя допълнителен лимит без значение дали заявката съдържа LIMIT
         *
         */
		public function getResult($sQuery, $sDefaultSortField, $nDefaultSortType, DBResponse $oResponse, $oDB = NULL , $nForceSetLimit = 0)
		{
			if( empty( $sQuery ) || !is_string( $sQuery ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$oParams = Params::getInstance();
			$aParams = Params::getAll();
			
			$sRpcPrefix = $oParams->get("rpc_prefix",'');
			$sSortField = $oParams->get($sRpcPrefix."sfield", $sDefaultSortField);
			$nSortType	= $oParams->get($sRpcPrefix."stype", $nDefaultSortType);
			
			$nPage = NULL;
			$nCutPage = 0;			
			
			switch( $oParams->get("api_action", "") )
			{
				case 'export_to_xls' :
				case 'export_to_pdf' :
					$nPage = 0;
					break;
				default:
					$nPage = $oParams->get($sRpcPrefix."current_page", 1);
					if( $nPage == NULL ) $nPage = 1;
					
					$nCutPage = $oParams->get( "cut_page", 0 );
			}
			//die($nPage);
			
			
			if(empty($sSortField)) {
				$sSortField = $sDefaultSortField;
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";


			$bLimited = (!$nForceSetLimit)? (!empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery ) && ( $nCutPage == 0 )) : (!empty( $nPage ) && ( $nCutPage == 0 )) ;
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);
				
			if( $bLimited )
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
				//die($sQuery);
			if($oDB == NULL) {
				$aData = $this->select( $sQuery );
				$nRowTotal = $this->oDB->foundRows();	
			} else {
				$aData = $this->selectFromDB($oDB,$sQuery);
				$nRowTotal = $oDB->foundRows();
			}

//			throw new Exception($sQuery);
			$oResponse->setData( $aData );
			
//			try {
//				$nRowTotal = $this->oDB->foundRows();
//				$nRowTotal = $this->_oDB->Execute("SELECT FOUND_ROWS()");
//			} catch( exception $e ) {
//				var_dump($e); 
//				$nRowTotal = 20;
//			}
			
			if( $bLimited )
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
		}
		
		public function getResultArray( $aData, $sDefaultSortField, $nDefaultSortType, DBResponse $oResponse )
		{
			if( !is_array( $aData ) ) throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			$oParams = Params::getInstance();
			$aParams = Params::getAll();
			
			$nPage = NULL;
			$nCutPage = 0;
			
			switch( $oParams->get("api_action", "") )
			{
				case 'export_to_xls' :
				case 'export_to_pdf' :
					$nPage = 0;
					break;
				default:
					$nPage = $oParams->get( "current_page", 1 );
					$nCutPage = $oParams->get( "cut_page", 0 );
			}
			
			// Paging
			$bLimited = !empty( $nPage ) && ( $nCutPage == 0 );
			
			if( $bLimited )
			{
				$nRowCount  = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ( $nPage - 1 ) * $nRowCount;
				$nRowTotal = count( $aData );
				
				$nIndex = 0;
				$aPagedData = array();
				foreach( $aData as $FDKey => $FDValue )
				{
					if( $nIndex >= $nRowOffset && $nIndex < ( $nRowOffset + $nRowCount ) ) $aPagedData[$FDKey] = $FDValue;
					$nIndex++;
				}
				
				$oResponse->setPaging( $nRowCount, $nRowTotal, ceil( $nRowOffset / $nRowCount ) + 1 );
			}
			else $aPagedData = $aData;
			// End Paging
			
			$oResponse->setData( $aPagedData );
		}
		
		public function __call($sMethodName, $aArguments) 
		{
			if( method_exists( $this->oDB, $sMethodName) )
				return call_user_func_array( array(&$this->oDB, $sMethodName), $aArguments);
		}
		
		public function setDebug( $bDebug )
		{
			$this->oDB->debug = $bDebug;
		}
		
		public function multiInsert($aData,$sMode = 'insert') {
			
			if(empty($aData)) return false;
			
			$aColumns = array();
			$aValues = array();
			
			foreach($aData[0] as $sColumn => $sValue) {
				$aColumns[] = "`".$sColumn."`";
			}
			
			$sColumns = implode(',',$aColumns);
			
			foreach($aData as $key => $aRow) {
				foreach($aRow as $field_name => $field_value) {
					$aData[$key][$field_name] = $this->oDB->Quote($field_value);
				}
			}
			
			
			foreach($aData as $aRow) {
				$aValues[] =  " (".implode(",",$aRow).") ";
			}
			
			if($sMode == 'insert') {
				$sQuery = " INSERT INTO ";
			} 
			if($sMode == 'ignore') {
				$sQuery = " INSERT IGNORE INTO ";
			}

			$sQuery .= " ".$this->sTableName." ";
			$sQuery .= " (".$sColumns.") VALUES \n";
			$sQuery .= implode(",\n",$aValues) ."\n";
			
			$this->oDB->Execute($sQuery);
		}
		
	}
	
?>