<?php
	require_once('include/db_include.inc.php');
	
	
	class DBSystemEvents
		extends DBMonthTable
	{
		function __construct() 
		{
			global $db_name_system,$db_system;
			
			parent::__construct($db_name_system, PREFIX_SYSTEM_EVENTS,$db_system);
		}

		function getSystemEventsResult($aParams, $sSortField, $nSortType, $nPage, &$oResponse)
		{
			global $db;

			$aParams['date_from']	= ( !empty( $aParams['date_from'] ) )? jsDateToTimestamp( $aParams['date_from'] ) : 0;
			$aParams['date_to']		= ( !empty( $aParams['date_to']   ) )? jsDateToTimestamp( $aParams['date_to']   )+(24 * 60 * 60) : 0;
						
			$sSelectStatement = "
				SELECT SQL_CALC_FOUND_ROWS  
					l.id, l.time as origin_time, DATE_FORMAT(l.time,'%d.%m.%y %H:%i:%s') as event_time, l.id_user, l.server_address, l.ip, 
					l.session_id, l.page, l.is_api_request, a.username, CONCAT_WS(' ', per.fname, per.mname, per.lname) as name
				FROM <table> l
					LEFT JOIN {$this->_sDB}.access_account a ON a.id=l.id_user
					LEFT JOIN {$db_name_eol}.personnel per ON per.id=a.id_person
				WHERE 1 
			";
		
			if( !empty($aParams['id_office']) && $aParams['id_office'] < 0)
				$sSelectStatement .= sprintf(" AND l.id_user = '0' \n");

			if( !empty($aParams['id_office']) && $aParams['id_office'] > 0)
				$sSelectStatement .= sprintf(" AND per.id_office = '%s' \n", $aParams['id_office']);

			if( !empty($aParams['person']) && $aParams['person'] > 0)
				$sSelectStatement .= sprintf(" AND per.code = '%s' \n", $aParams['person']);

			if( !empty($aParams['date_from']) && $aParams['date_from'] > 0)
				$sSelectStatement .= sprintf(" AND DATE( l.time ) >= DATE( FROM_UNIXTIME( %u ) )\n", $aParams['date_from'] );
			
			if( !empty($aParams['date_to']) && $aParams['date_to'] > 0)
				$sSelectStatement .= sprintf(" AND DATE( l.time ) <= DATE( FROM_UNIXTIME( %u ) )\n",  $aParams['date_to'] );
			
			if( !empty( $sSortField ) ){	
				switch($sSortField){
					case  "event_time" :
								$sSelectStatement .= sprintf(" \n ORDER BY 'origin_time' %s \n", $nSortType == DBAPI_SORT_DESC ? "DESC" : "ASC");
							break;
					default : 
								$sSelectStatement .= sprintf(" \n ORDER BY '%s' %s \n", $sSortField , $nSortType == DBAPI_SORT_DESC ? "DESC" : "ASC");
							break;
				}				
				$oResponse->setSort( $sSortField, $nSortType );
			}
			
			if( !empty( $nPage ) ) {	
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ($nPage-1) * $nRowCount;
				 
				$sSelectStatement .= sprintf(
					"\nLIMIT %s, %s \n"
					, $nRowOffset
					, $nRowCount 
					);	
			};
			
			$aData = array();
			if( $nResult=$this->select($sSelectStatement, $aData, $aParams['date_from'], $aParams['date_to']) != DBAPI_ERR_SUCCESS )
				return $nResult;

			$oResponse->setData( $aData );
			
			// Извличане на броя на записите за цялата справка
			$rs = $db->Execute("SELECT FOUND_ROWS()");
			
			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			// Установяване на паремтрите по paging-a
			if( !empty( $nPage ) )
			{	
				$nRowTotal = current( $rs->FetchRow() );;
				
				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
				);
			}
			
			return DBAPI_ERR_SUCCESS;
		}

		function InsertSystemEvent($page, $bAPI )
		{
			global $db_system, $_SERVER, $_SESSION, $_GET, $_POST;
			
			$log=array(
				"time"=>time(),
				"id_user" => !empty( $_SESSION['userdata']['id'] ) ? $_SESSION['userdata']['id']: 0,
				"server_address" => $_SERVER["SERVER_ADDR"], 
				"ip" => $_SERVER["REMOTE_ADDR"],
				"session_id" => session_id(),
				"page" => $page,
				"get_params" => print_r($_GET,true),
				"post_params" => print_r($_POST,true),
				"is_api_request" => $bAPI ? 1 : 0
			);
			
			$this->update( $log );

			if( !empty( $_SESSION['userdata']['id'] ) )
			{
				$rs = $db_system->Execute("SELECT * FROM access_account WHERE id = {$_SESSION['userdata']['id']}");
					
				if( !$rs )
					return DBAPI_ERR_SUCCESS;
				
				$log=array(
					"last_online"=>time(),
					"last_ip"=>$_SERVER["REMOTE_ADDR"],
					"last_session"=>session_id()
				);
			
				$sQuery = $db_system->GetUpdateSQL($rs, $log);
				if( !empty($sQuery) )		
					$db_system->Execute( $db_system->GetUpdateSQL($rs, $log) );
			}

			return DBAPI_ERR_SUCCESS;
		}
	}

?>
