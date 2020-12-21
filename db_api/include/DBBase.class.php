<?php
	require_once('db_api/include/db_include.inc.php');
	
	
	class DBBase 
	{
		var $_sTableName;
		var $_oDB;
		
		function __construct($oDB, $sTableName)
		{
			assert( !empty( $oDB ) );
			assert( !empty( $sTableName ) );
			
			$this->_oDB = $oDB;
			$this->_sTableName = $sTableName;
		}
		
		function getRecord( $nID, &$aData )
		{
			
			$aData = array();
			
			if( empty( $nID ) || !is_numeric( $nID ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = sprintf("
					SELECT  * FROM %s 
					WHERE id = %d
				",
				$this->_sTableName,
				$nID
			);
			
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( !$oRes )
			{
				APILog::Log( DBAPI_ERR_SQL_QUERY, $this->_oDB->errorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if( !$oRes->EOF )
				$aData = $oRes->fields;
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function getRecordByField( $sFieldContent, $sFieldName, &$aData )
		{
			
			$aData = array();
						
			$sQuery = sprintf("
					SELECT  * FROM %s 
					WHERE %s = '%s'
				",
				$this->_sTableName,
				$sFieldName,
				$sFieldContent
			);
			//debug($sQuery);
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( !$oRes )
			{
				APILog::Log( DBAPI_ERR_SQL_QUERY, $this->_oDB->errorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if( !$oRes->EOF )
				$aData = $oRes->fields;
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function getResult( &$aData, $sSelectStatement = NULL, $aWhereStatement = NULL, $sOrderStatement = NULL, $sLimitStatement = NULL, $sGroupStatement = NULL, $oDB = NULL )
		{
			$aData = array();
	
			$sQuery = empty( $sSelectStatement ) ? 
		 				sprintf(" SELECT t.id as _id, t.* FROM %s t ", $this->_sTableName ) : 
		 				$sSelectStatement; 
			
			if( !empty( $aWhereStatement ) && is_array( $aWhereStatement ) )
				$sQuery = sprintf( " %s \n WHERE \n %s \n ", $sQuery, implode( ' AND ', $aWhereStatement) );
			
			if( !empty( $sGroupStatement ) )
				$sQuery = sprintf( " %s \n GROUP BY \n %s \n ", $sQuery, $sGroupStatement );
			
			if( !empty( $sOrderStatement ) )
				$sQuery = sprintf( " %s \n ORDER BY %s \n ", $sQuery, $sOrderStatement );
			
			if( !empty( $sLimitStatement ) )
				$sQuery = sprintf( " %s \n LIMIT %s \n ", $sQuery, $sLimitStatement );
			
//			APILog::Log( 0, $sQuery);
			
			if($oDB == NULL) {
				$oRes = $this->_oDB->Execute( $sQuery );
			} else {
				$oRes = $oDB->Execute( $sQuery);
			}
			if( !$oRes )
			{
				APILog::Log( DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
			if( !$oRes->EOF )
				$aData = $oRes->GetAssoc();
							
			return DBAPI_ERR_SUCCESS;
		}		

		function getData( $qClause, $qpreWhere, $sFieldContent, $sFieldName, &$aData ) {
			
			$aData = array();
						
			$sQuery = sprintf("
					SELECT %s FROM %s t
					%s
					WHERE %s = '%s'
					AND t.to_arc = 0
				",
				$qClause,
				$this->_sTableName,
				$qpreWhere,
				$sFieldName,
				$sFieldContent
			);
			//debug($sQuery);
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( !$oRes )
			{
				APILog::Log( DBAPI_ERR_SQL_QUERY, $this->_oDB->errorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if( !$oRes->EOF )
				$aData = $oRes->fields;
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/*
			$type: 
				fields;
				getArray;
				getAssoc;
		 */
		function processData( $query, $type, &$aData ) {
			
			$aData = array();
			//APILog::Log(0, $type);
						
			$sQuery = $query;
			
			//debug($sQuery);
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( !$oRes ) {
				APILog::Log( DBAPI_ERR_SQL_QUERY, $this->_oDB->errorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if ( !$oRes->EOF ) {
				if ( $type == strtolower("getassoc") ) {
					$aData = $oRes->getAssoc();
				} elseif ( $type == strtolower("getarray") ) {
					$aData = $oRes->getArray();
				} else {
					$aData = $oRes->fields;
				}
				
				//APILog::Log(0, $type);
			}
			return DBAPI_ERR_SUCCESS;
		}


		function updateByField( $sFieldContent, $sFieldName, &$aData ) {
			$nID = !empty( $sFieldContent ) ? $sFieldContent : 0;

			$aData['updated_time'] = time();
			$aData['updated_user'] = $_SESSION['userdata']['id_person'];
			
			if( empty($aData['to_arc']) ) 
				$aData['to_arc'] = 0;
			
			if( $nID ) {
				// UPDATE
				$sQuery = sprintf(" SELECT * FROM %s WHERE %s = '%s' ", $this->_sTableName, $sFieldContent, $nID );
				$oRes = $this->_oDB->Execute( $sQuery );
				
				if( ! $oRes ) {
					APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					return DBAPI_ERR_SQL_QUERY;
				}
				
				
				$sQuery = $this->_oDB->GetUpdateSQL( $oRes, $aData );
				
				if( !empty($sQuery) ) {
					$oRes = $this->_oDB->Execute( $sQuery );
					if( ! $oRes ) {
						APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
						APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
				}
			} else {
				// INSERT
				$aData['created_time'] = time();
				$aData['created_user'] = $_SESSION['userdata']['id_person'];
				
				$sQuery = sprintf(" SELECT * FROM %s WHERE id=-1 ", $this->_sTableName );
				$oRes = $this->_oDB->Execute( $sQuery );
				
				if( ! $oRes ) {
					APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					return DBAPI_ERR_SQL_QUERY;
				}
				
				
				$sQuery = $this->_oDB->GetInsertSQL( $oRes, $aData );
				
				if( !empty($sQuery) ) {
					$oRes = $this->_oDB->Execute( $sQuery );
					if( ! $oRes ) {
						APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
						APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
				}
			}
			
			$aData['id'] = $this->_oDB->Insert_ID();			
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function update( &$aData )
		{
			$nID = !empty( $aData['id'] ) ? $aData['id'] : 0;

			$aData['updated_time'] = time();
			$aData['updated_user'] = $_SESSION['userdata']['id_person'];
			
			if( empty($aData['to_arc']) ) 
				$aData['to_arc'] = 0;
			
			if( $nID )
			{
				// UPDATE
				$sQuery = sprintf(" SELECT * FROM %s WHERE id=%s ", $this->_sTableName, $nID );
				
				$oRes = $this->_oDB->Execute( $sQuery );
				
				if( ! $oRes )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					return DBAPI_ERR_SQL_QUERY;
				}
				
				
				$sQuery = $this->_oDB->GetUpdateSQL( $oRes, $aData );
				APILog::Log(0, $aData);
				if( !empty($sQuery) )
				{
					$oRes = $this->_oDB->Execute( $sQuery );
					if( ! $oRes )
					{
						APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
						APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
				}
			}
			else
			{
				// INSERT
				$aData['created_time'] = time();
				$aData['created_user'] = $_SESSION['userdata']['id_person'];
				
				$sQuery = sprintf(" SELECT * FROM %s WHERE id=-1 ", $this->_sTableName );
				$oRes = $this->_oDB->Execute( $sQuery );
				
				if( ! $oRes )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					return DBAPI_ERR_SQL_QUERY;
				}
				
				
				$sQuery = $this->_oDB->GetInsertSQL( $oRes, $aData );
				//APILog::Log(0, $sQuery);
				if( !empty($sQuery) )
				{
					$oRes = $this->_oDB->Execute( $sQuery );
					if( ! $oRes )
					{
						APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
						APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
				}
				$aData['id'] = $this->_oDB->Insert_ID();
			}
						
			return DBAPI_ERR_SUCCESS;
		}
		
		function delete( $nID )
		{
			$sQuery = sprintf(" DELETE FROM %s WHERE id=%s ", $this->_sTableName, $nID );
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( ! $oRes )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}

			return DBAPI_ERR_SUCCESS;
		}
		
		function toARC( $nID )
		{
			$sQuery = sprintf(" UPDATE %s SET to_arc=1 WHERE id=%s ", $this->_sTableName, $nID );
			//APILog::Log(0, $sQuery );
			$oRes = $this->_oDB->Execute( $sQuery );
			
			if( ! $oRes )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}

			return DBAPI_ERR_SUCCESS;
		}
		
		function getReport( $aParams, $sSelectStatement = NULL, $aWhereStatement = NULL, $sGroupStatement = NULL, &$aData = NULL, $oDB = NULL )
		{			
			global $oResponse;	
			
			// $sSelectStatement
			if( empty( $sSelectStatement ) )
		 		$sSelectStatement = sprintf(" SELECT SQL_CALC_FOUND_ROWS t.id as _id, t.* FROM %s t ", $this->_sTableName ); 
		 		
			// Order Statemen
			$sOrderStatement = NULL;

			if( !empty( $aParams['sfield'] ) && (substr($aParams['sfield'], 0, 1) != "&") )
			{	
				if( empty( $aParams['stype'] ) )
					$aParams['stype'] = DBAPI_SORT_ASC;
				
				if ( $aParams['sfield'] == "num" ) {
					$sOrderStatement = sprintf(
						" %s+0 %s "
						, $aParams['sfield']
						, $aParams['stype'] == DBAPI_SORT_DESC ? "DESC" : "ASC"
					);
				} else {
					$sOrderStatement = sprintf(
						" %s %s "
						, trim($aParams['sfield'],'_')
						, $aParams['stype'] == DBAPI_SORT_DESC ? "DESC" : "ASC"
					);
				}
				
				$oResponse->setSort( $aParams['sfield'], $aParams['stype'] );
			}
			
			// Limit Statemen
			$sLimitStatement = NULL;

			if( !empty( $aParams['current_page'] ) )
			{	
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ( $aParams['current_page']-1 ) * $nRowCount;
				 
				$sLimitStatement .= sprintf(
					" %s, %s "
					, $nRowOffset
					, $nRowCount 
				);	
			};
			
			// Извличане на резултата
			$aData = array();
			
			if(( $nResult = $this->getResult( $aData, $sSelectStatement, $aWhereStatement, $sOrderStatement, $sLimitStatement, $sGroupStatement, $oDB )) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult , NULL, __FILE__, __LINE__ );
				return $nResult;
			}
			
			
			// Извличане на броя на записите за цялата справка
			
			if($oDB == NULL) {
				$oRes = $this->_oDB->Execute("SELECT FOUND_ROWS()");
			} else {
				$oRes = $oDB->Execute("SELECT FOUND_ROWS()");
			}
			
			if( !$oRes || $oRes->EOF )
			{
				$oResponse->setError( $nResult , NULL, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}

			// Установяване на паремтрите по paging-a
			if( !empty( $aParams['current_page'] ) )
			{	
				$nRowTotal = current( $oRes->FetchRow() );

				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
				);
			}
			
			$oResponse->setData( $aData );
						
			return DBAPI_ERR_SUCCESS;
		}		
			
	}
	
?>