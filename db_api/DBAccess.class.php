<?php
	require_once('include/db_include.inc.php');
	
	class DBAccess {
		function login( $username, $userpass, &$userdata )
		{
			global $db_system, $db_name_personnel, $db_name_sod;
			
			$sQuery = "
				SELECT 
					a.id,
					a.id_person,
					p.code,
					a.username,
					a.is_save_file,
					a.id_profile,
					p.id_office,
					a.row_limit,
					a.id_schet_account,
					CONCAT(p.fname,' ',p.mname,' ',p.lname) name,
					r.name AS 'region',
					c.name AS 'city',
					a.has_debug,
					If( ( ( p.vacate_date != '0000-00-00' ) AND ( NOW() > p.vacate_date ) ) OR ( p.status = 'vacate' ), 1 , 0 ) AS vacate
				FROM 
					access_account a 
				LEFT JOIN {$db_name_personnel}.personnel p ON a.id_person=p.id 
				LEFT JOIN {$db_name_sod}.offices r ON (p.id_office = r.id AND r.to_arc = 0) 
				LEFT JOIN {$db_name_sod}.cities c ON (r.address_city = c.id AND c.to_arc = 0) 
				WHERE 
					username = '{$username}' AND 
					password = MD5('{$userpass}') AND 
					p.to_arc=0 AND a.to_arc=0
			";

			$rs = $db_system->Execute($sQuery);

			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			$userdata =$rs->fields;
			$userdata['access_right_regions']= array();
			$userdata['access_right_all_regions']= false;
			$userdata['access_right_levels']= array();
			$userdata['access_right_files']= array();

			//**************** Нива на достъп 
			$sQuery = "
					SELECT * FROM access_level_profile 
					WHERE id_profile='{$userdata['id_profile']}' AND id_level=0
				   ";
			$rs = $db_system->Execute($sQuery);
			
			if($rs && !$rs->EOF)
			{
				// ВСИЧКИ НИВА level_profile.id_level=0
				$sQuery = "
					SELECT 
						l.name, f.filename 
					FROM  
						access_level l LEFT JOIN access_level_files f ON l.id=f.id_level
				";
			}
			else
			{
				// НИВА СПОРЕД ПРОФИЛА
				$sQuery = "
					SELECT 
						l.name, f.filename 
					FROM 
						access_level_profile lp 
						LEFT JOIN access_level l ON lp.id_level=l.id 
						LEFT JOIN access_level_files f ON l.id=f.id_level 
					WHERE 
						id_profile={$userdata['id_profile']}
					";
			}
			
			$rs = $db_system->Execute($sQuery);

			$data = $rs && !$rs->EOF ? $rs->GetArray() : array();
			
			foreach($data as $value)
			{
				$userdata['access_right_levels'][ $value['name'] ] = $value['name'];
				$userdata['access_right_files'][ $value['filename'] ] = $value['filename'];
			}
			
			//**************** Достъп до офиси 
			$sQuery = "
					SELECT * 
					FROM account_office
					WHERE 
						id_account='{$userdata['id']}' AND id_office=0
				   ";
			$rs = $db_system->Execute($sQuery);
			
			if($rs && !$rs->EOF)
			{
				// Всички офиси account_office.id_office=0
				$sQuery = "
					SELECT id 
					FROM {$db_name_sod}.offices
				";
				
				$userdata['access_right_all_regions'] = true;
			}
			else
			{
				// Офиси според потребителя
				$sQuery = "
					SELECT 
						id_office as id 
					FROM 
						account_office 
					WHERE 
						id_account='{$userdata['id']}' 
				";
			}

			$rs = $db_system->Execute($sQuery);

			$data = $rs && !$rs->EOF ? $rs->GetArray() : array();

			foreach($data as $value)
				$userdata['access_right_regions'][$value['id']] = $value['id'];
			
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function updateOnlineStatusAccount( &$aData )
		{
			global $db_system;

			$id = $aData['id'];
			
			if( empty( $id ) )
				$id = -1;
			
			$rs = $db_system->Execute("SELECT * FROM access_account WHERE id = {$id}");
				
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			$db_system->Execute( $db_system->GetUpdateSQL($rs, $aData) );
			
			return DBAPI_ERR_SUCCESS;
		}
	
		function getAccountOnce($id, &$oData, $sPassword='')
		{
			global $db_system;
			
			$sQuery = "
				SELECT 
					a.id, a.id_person, 
					a.id_profile, 
					a.username, 
					a.row_limit, 
					 a.last_ip, 
					 a.last_session, 
					 a.updated_user, 
					 a.updated_time, 
					 GROUP_CONCAT(o.id_office) as offices
				FROM 
					access_account  a 
					LEFT JOIN account_office o ON a.id = o.id_account
				WHERE a.id = '$id'
				GROUP BY a.id 
			";
			
			// Извличане на резултата
			$rs = $db_system->Execute( $sQuery );
			if(!$rs)
				return DBAPI_ERR_SQL_QUERY;

			$oData = $rs->fields;

			return DBAPI_ERR_SUCCESS;
		}
		
		function getLevelsResult($nSelectedGroup, $sSortField, $nSortType, $nPage, &$oResponse)
		{
			global $db_system;
			global $params;
			
			$sName = '';
			if( isset($params['name']) && !empty($params['name']))
				$sName = addslashes( $params['name'] );
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					l.id, l.name, l.description, l.id_group, g.name as group_name, GROUP_CONCAT(f.filename) as filenames
				FROM access_level l
				LEFT JOIN 
					access_level_groups g ON l.id_group=g.id
				LEFT JOIN 
					access_level_files f ON l.id=f.id_level
				";

			if( $nSelectedGroup > 0 )
			{
				$sQuery .= sprintf(
					"WHERE id_group = %s \n"
					, $db_system->Quote( $nSelectedGroup )
				);
			}

			$sQuery .= " GROUP BY l.id ";
			
			if( !empty($sName))
				$sQuery .= " \n HAVING name LIKE '%$sName%' \n";

			if( !empty( $sSortField ) )
			{
				$sQuery .= sprintf(
					"ORDER BY %s %s\n"
					, $sSortField
					, $nSortType == DBAPI_SORT_DESC ? "DESC" : "ASC"
				);
				$oResponse->setSort( $sSortField, $nSortType );

			}
			
			if( !empty( $nPage ) )
			{
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ($nPage-1) * $nRowCount;
				 
				$sQuery .= sprintf(
					"LIMIT %s, %s"
					, $nRowOffset
					, $nRowCount 
					);
			};
			
			// Извличане на резултата
			$rs = $db_system->Execute( $sQuery );
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			$aData = $rs->GetAssoc();
			foreach($aData as $key => $value) {$aData[$key]['id']=$key; $aData[$key]['filenames']='';}

			// Извличане на броя на записите за цялата справка
			$rs = $db_system->Execute("SELECT FOUND_ROWS()");
			
			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			// Установяване на паремтрите по paging-a
			if( !empty( $nPage ) )
			{
				$nRowTotal = current( $rs->FetchRow() );
				
				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
					);
			}
			

			$sQuery = "
				SELECT *
				FROM access_level_files 
			";
			$rs = $db_system->Execute( $sQuery );
			$aFiles = $rs ? $rs->GetArray() : array();

			foreach($aFiles as $value)
			{
				if( isset($aData[$value['id_level']]) ) $aData[$value['id_level']]['filenames'] = !empty($aData[$value['id_level']]['filenames']) ? $aData[$value['id_level']]['filenames'].", ".$value['filename'] : $value['filename'];
			}
			
			$oResponse->setData( $aData );
			
			return DBAPI_ERR_SUCCESS;
		}

		function getLevelGroups(&$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level_groups 
				ORDER BY name
				";
						
			// Извличане на резултата
			$rs = $db_system->Execute( $sQuery );
			if($rs)
			{
				$oData = $rs->GetArray();
			}
			else
			{
				return DBAPI_ERR_SQL_QUERY;
			}

			return DBAPI_ERR_SUCCESS;
		}

		function DublicateGroup($id, $name)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level_groups 
				WHERE id != '$id' AND name='$name'
			";
						
			$rs = $db_system->Execute( $sQuery );
			if(!$rs || $rs->EOF)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		function updateGroup( &$aData )
		{
			global $db_system;
			$id = $aData['id'];
			
			if( empty( $id ) )
				$id = -1;
							
			$rs = $db_system->Execute("SELECT * FROM access_level_groups WHERE id = {$id}");
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			if( $id == -1 )	//INSERT
			{
				$db_system->Execute( $db_system->GetInsertSQL($rs, $aData) );
				$aData['id'] = $db_system->Insert_ID();	
			}
			else	//UPDATE
			{
				$db_system->Execute( $db_system->GetUpdateSQL($rs, $aData) );
			}
			
			return DBAPI_ERR_SUCCESS;
		}

		function getGroupOnce($id, &$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level_groups 
				WHERE id = '$id'
			";
			
			$rs = $db_system->Execute( $sQuery );
			
			if(!$rs)
				return DBAPI_ERR_SQL_QUERY;

			$oData = $rs->fields;

			return DBAPI_ERR_SUCCESS;
		}
		
		function deleteLevel( $nID )
		{
			global $db_system;
			$nID = (int) $nID;
			
			if( empty( $nID ) )
				return DBAPI_ERR_INVALID_PARAM;

			if($rs = $db_system->Execute("DELETE FROM access_level WHERE id = {$nID}"))
			{
				$db_system->Execute("DELETE FROM access_level_files WHERE id_level = {$nID}");
			} 
			else
				return DBAPI_ERR_SQL_QUERY;
			
			return DBAPI_ERR_SUCCESS;
		}

		function deleteGroup( $nID )
		{
			global $db_system;
			$nID = (int) $nID;
			
			if( empty( $nID ) )
				return DBAPI_ERR_INVALID_PARAM;

			if(!$rs = $db_system->Execute("DELETE FROM access_level_groups WHERE id = {$nID}"))
				return DBAPI_ERR_SQL_QUERY;
			
			return DBAPI_ERR_SUCCESS;
		}

		function getFiles(&$oData)
		{

			$files=array();
			if ($handle = opendir('../engine/'))
			{
				while (false !== ($file = readdir($handle)))
					if ($file != "." && $file != ".." && $file != ".svn")
					{
						$f=explode('.',$file);
						$oData[$f[0]]=$f[0];
					}
				array_multisort($oData,SORT_ASC);
				closedir($handle);
			}
		}
		
		function getLevelOnce($id, &$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level 
				WHERE id = '$id'
			";
			
			$rs = $db_system->Execute( $sQuery );
			if(!$rs)
				return DBAPI_ERR_SQL_QUERY;

			$oData = $rs->fields;

			return DBAPI_ERR_SUCCESS;
		}

		function getLevelFiles($id, &$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level_files 
				WHERE id_level = '$id'
			";
			
			$rs = $db_system->Execute( $sQuery );
			if($rs)
			{
				$oData = $rs->GetArray();
			}
			else
			{
				return DBAPI_ERR_SQL_QUERY;
			}

			return DBAPI_ERR_SUCCESS;
		}
		
		function updateLevel( &$aData )
		{
			global $db_system;
			$id = $aData['id'];

			if( empty( $id ) )
				$id = -1;
							
			$rs = $db_system->Execute("SELECT * FROM access_level WHERE id = {$id}");
				
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			if( $id == -1 )	//INSERT
			{
				if( !$db_system->Execute( $db_system->GetInsertSQL($rs, $aData) ) )
					return DBAPI_ERR_SQL_QUERY;
				$id = $db_system->Insert_ID();
				$aData['id'] = $id;
			}
			else	//UPDATE
			{
				if( $db_system->Execute( $db_system->GetUpdateSQL($rs, $aData) ) )
					return DBAPI_ERR_SQL_QUERY;
			}
			

			$db_system->Execute("DELETE FROM access_level_files WHERE id_level = '{$id}'");
			$rs = $db_system->Execute("SELECT * FROM access_level_files WHERE id_level = -1");
			
			$file=array();
			$file['id_level']=$id;
			foreach($aData['level_files'] as $value)
			{
				$file['filename']=$value;
				$db_system->Execute( $db_system->GetInsertSQL($rs, $file) );
			}
			
			
			return DBAPI_ERR_SUCCESS;
		}

		function DublicateLevel($id, $name)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level 
				WHERE id != '$id' AND name='$name'
			";
						
			$rs = $db_system->Execute( $sQuery );
			if(!$rs || $rs->EOF)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		function getProfileResult($sSortField, $nSortType, $nPage, &$oResponse)
		{
			global $db_system;
			$sQuery = "
				SELECT  SQL_CALC_FOUND_ROWS 
						p.id,
						p.name,
						p.description,
						p.is_default,
						COUNT(a.id) as count_users,
						GROUP_CONCAT(a.username) as users 
				FROM access_profile p 
					LEFT JOIN access_account a ON (a.id_profile=p.id AND a.to_arc=0)
				GROUP BY p.id 
			";

			if( !empty( $sSortField ) )
			{	
				$sQuery .= sprintf(
					"ORDER BY %s %s\n"
					, $sSortField
					, $nSortType == DBAPI_SORT_DESC ? "DESC" : "ASC"
				);
				$oResponse->setSort( $sSortField, $nSortType );

			}
			
			if( !empty( $nPage ) )
			{	
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ($nPage-1) * $nRowCount;
				 
				$sQuery .= sprintf(
					"LIMIT %s, %s"
					, $nRowOffset
					, $nRowCount 
					);	
			};
			

			$rs = $db_system->Execute( $sQuery );
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			if($rs)
			{
				$aData = $rs->GetArray();
				
				$oResponse->setData( $aData );

			}
			else
			{
				return DBAPI_ERR_SQL_QUERY;
			}
			
			$rs = $db_system->Execute("SELECT FOUND_ROWS()");
			
			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			if( !empty( $nPage ) )
			{
				$nRowTotal = current( $rs->FetchRow() );
				
				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
					);
			}
			
			return DBAPI_ERR_SUCCESS;
		}

		function DublicateProfile($id, $name)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_profile 
				WHERE id != '$id' AND name='$name'
			";
			
			$rs = $db_system->Execute( $sQuery );
			if(!$rs || $rs->EOF)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		function updateProfile( &$aData )
		{
			global $db_system;

			$id = $aData['id'];
			
			if( empty( $id ) )
				$id = -1;
			
			$rs = $db_system->Execute("SELECT * FROM access_profile WHERE id = {$id}");
				
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			if( $id == -1 )	//INSERT
			{
				$db_system->Execute( $db_system->GetInsertSQL($rs, $aData) );
				$id = $db_system->Insert_ID();	
				$aData['id'] = $id;
			}
			else	//UPDATE
			{
				$sQuery = $db_system->GetUpdateSQL($rs, $aData);
				if( !empty($sQuery) )
					$db_system->Execute( $db_system->GetUpdateSQL($rs, $aData) );
			}

			$db_system->Execute("DELETE FROM access_level_profile WHERE id_profile = '{$id}'");
			$rs = $db_system->Execute("SELECT * FROM access_level_profile WHERE id_level = -1");
			
			$level_profile=array();

			$level_profile['id_profile']=$id;
			if( $aData['all_levels'] == 1 )
			{
				$level_profile['id_level']=0;
				$db_system->Execute( $db_system->GetInsertSQL($rs, $level_profile) );
			}
			else 
				foreach($aData as $key => $value)
				{
					$level=explode("_",$key);
					if( ($level[0] == "level") && !empty($level[1]) && $value)
					{
						$level_profile['id_level']=$level[1];
						$db_system->Execute( $db_system->GetInsertSQL($rs, $level_profile) );
					}
				}
			
			if( !empty( $aData['is_default'] ) && $aData['is_default'] )
			{
				$db_system->Execute("UPDATE access_profile SET is_default=0 WHERE id != '{$id}'");
			}
			
			return DBAPI_ERR_SUCCESS;
		}

		function getProfileOnce($id, &$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_profile 
				WHERE id = '$id'
			";
						
			$rs = $db_system->Execute( $sQuery );
			if(!$rs)
				return DBAPI_ERR_SQL_QUERY;

			$oData = $rs->fields;

			return DBAPI_ERR_SUCCESS;
		}

		function getProfileLevels($id, &$bAllLevels, &$oData)
		{
			global $db_system;
			
			$sQuery = "
				SELECT *
				FROM access_level_profile 
				WHERE id_profile = '$id'
			";
						
			$rs = $db_system->Execute( $sQuery );
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;

			$oData = $rs->GetArray();
			foreach( $oData as $value)
				if( $value['id_level'] == 0 ) 
					$bAllLevels=true;

			return DBAPI_ERR_SUCCESS;
		}
		
		function getLevels( &$aLevels )
		{
			global $db_system;

			$rs = $db_system->Execute("
				SELECT *
				FROM access_level
				ORDER BY id_group, name
				");
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			$aLevels = $rs->GetAssoc();

			foreach( $aLevels as $key=>$value )
				$aLevels[$key]['id'] = $key;

			return DBAPI_ERR_SUCCESS;
		}

		function deleteProfile( $nID )
		{
			global $db_system, $oResponse;
			$nID = (int) $nID;
			
			if( empty( $nID ) )
				return DBAPI_ERR_INVALID_PARAM;
				
			$oDBAccessAccount = new DBAccessAccount();
			$nCount = $oDBAccessAccount->getCountProfileByID($nID);

			if (empty($nCount)) {
				if(!$rs = $db_system->Execute("DELETE FROM access_profile WHERE id = {$nID}"))
					return DBAPI_ERR_SQL_QUERY;
				if(!$rs = $db_system->Execute("DELETE FROM access_level_profile WHERE id_profile = {$nID}"))
					return DBAPI_ERR_SQL_QUERY;
			} else if ($nCount==1) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не може да премахнете този тип потребителски профил!\nИма {$nCount} потребител от този профил!", __FILE__, __LINE__ );
			} else {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не може да премахнете този тип потребителски профил!\nИма {$nCount} потребители от този профил!", __FILE__, __LINE__ );
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		// Pavel - marmalad s pytishtata
		public function getLastServiceDate() {
			global $db_sod;
			$date = array();
			
			$sQuery = "
				SELECT 
					DATE_FORMAT(MAX(created_time), '%Y%m') as mon 
				FROM hc_service
				WHERE to_arc = 0
				LIMIT 1
			";	
			
			$rs = $db_sod->Execute( $sQuery );
			
			if ( $rs ) {
				if ( !$rs->EOF ) {
					$date = $rs->fields;
				} 
			}
			
			if ( isset($date['mon']) ) {
				return $date['mon'];
			} else return 197001;
		}

		public function createService( $auto ) {
			global $db_sod, $db_telepol;
			
			$count = array();
			$tech = 0;
			$guard = 0;
			$objects = "";		
			
			$tables = array();
			$tables = SQL_get_tables($db_telepol, "mp20", "____", "ASC");

			foreach ( $tables as $keyTbl => $valTbl ) {
				if ( substr($valTbl, -6) < 200806 ) unset($tables[$keyTbl]);
			}

			$tCountQuery = "
				( SELECT
					DISTINCT mp.id_obj as id
				FROM <%tablename%> mp
				LEFT JOIN objects o ON o.id_obj = mp.id_obj
				WHERE mp.single_pay = 0
					AND mp.confirm = 1
					AND mp.id_obj > 0
					AND o.id_region IN (113,112)
					AND o.id_status = 1
			";
			
			$sCount = "";
			$count = array();
			$cNUM = 0;
			$tab = "";
			
			if ( count($tables) > 1 ) {
				foreach ( $tables as $vvval ) {				
					$sCount .= preg_replace("/<%tablename%>/", $vvval, $tCountQuery).") UNION ";
					$tab = $vvval;
				}
							
				$sCount .= preg_replace("/<%tablename%>/", $tab, $tCountQuery).") ";
			} else {
				foreach ( $tables as $vval ) {									
					$sCount = preg_replace("/<%tablename%>/", $vval, $tCountQuery).") ";
				}
			}				

			$rs = $db_telepol->Execute( $sCount );
			$count = $rs->getArray();
			
			foreach ( $count as $cval) {
				$objects .= !empty($objects) ? ",".$cval['id'] : $cval['id'];
				$tech += 6;
				$guard += 12;
			}				
			
			//$aData = is_numeric($date) && !empty($date) ? $date + 86400 : -1;
			$aDataReceipt = array();
			
			if ( isset($auto['user']) ) {
				$user = $auto['user'];
			} else {
				$user = !empty( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			}

			$time = isset($auto['time']) ? $auto['time'] : time();
					
			$aRecord = array();
			$aRecord['objects'] = $objects;
			$aRecord['tech_price'] = 6;
			$aRecord['guard_price'] = 12;
			$aRecord['sum_tech_price'] = $tech;
			$aRecord['sum_guard_price'] = $guard;
			$aRecord['created_time'] = $time;
			$aRecord['created_user'] = $user;
			$aRecord['to_arc'] = 0;
			
			
			$rsRecord = $db_sod->Execute( "SELECT * FROM hc_service WHERE 0" );
			
			$sQuery = $db_sod->GetInsertSQL($rsRecord, $aRecord);
			$db_sod->Execute($sQuery);
		}


	}

?>