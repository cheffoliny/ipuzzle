<?php
	require_once('include/db_include.inc.php');
	
	class DBMenu {
	 

		function getResult($sSortField, $nSortType, $nPage, &$oResponse) {
			global $db_system;
		 
			$sQuery = "(
						select
						  concat(lpad(m0.menu_order,3,'0'),'_000_000') as sort_id,
						  0 as level, m0.id, m0.parent_id, m0.menu_order, m0.title, concat('', m0.title) as tab_title, m0.filename
						from head_menu m0
						where m0.parent_id = 0
						order by m0.menu_order
						) union (
						select
						  concat(lpad(m0.menu_order,3,'0'),'_',lpad(m1.menu_order,3,'0'),'_000') as sort_id,
						  1 as level, m1.id, m1.parent_id, m1.menu_order, m1.title, concat('   ',m1.title) as tab_title, m1.filename
						from head_menu m0
						left join head_menu as m1 on m1.parent_id = m0.id
						where m0.parent_id = 0
						order by m0.menu_order, m1.menu_order
						) union (
						select
						  concat(lpad(m0.menu_order,3,'0'),'_',lpad(m1.menu_order,3,'0'),'_',
						    if(m2.menu_order is null,'000',lpad(m2.menu_order,3,'0'))
						  ) as sort_id,
						  2 as level, m2.id, m2.parent_id, m2.menu_order, m2.title, concat('      ',m2.title) as tab_title, m2.filename
						from head_menu m0
						left join head_menu as m1 on m1.parent_id = m0.id
						left join head_menu as m2 on m2.parent_id = m1.id
						where m0.parent_id = 0 and m2.id is not null
						order by m0.menu_order, m1.menu_order, m2.menu_order
						) ";
		
						
//			$sQuery = "
//					SELECT 
//						SQL_CALC_FOUND_ROWS * 
//					FROM menu
//				";

			if( !empty( $sSortField ) ) {
				if ($sSortField == "menu_order")
						$sSortField = "sort_id";
				$sQuery .= sprintf(
					"ORDER BY %s %s\n"
					, $sSortField
					, $nSortType == DBAPI_SORT_DESC ? "DESC" : "ASC"
				);
			
				if ($sSortField == "sort_id")
						$sSortField = "menu_order";
						
				$oResponse->setSort( $sSortField, $nSortType );
			
			}
			APILog::Log(0,$sQuery);
			
			if( !empty( $nPage ) ) {	
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ($nPage - 1) * $nRowCount;
				 
				$sQuery .= sprintf(
					"LIMIT %s, %s"
					, $nRowOffset
					, $nRowCount 
					);
			};
			
			
			// Извличане на резултата
			$rs = $db_system->Execute( $sQuery );
			if($rs && !$rs->EOF){			
//				print_r($rs->GetArray());

				$aData = $rs->GetArray();
					
				$oResponse->setData( $aData );
			} else {
				return DBAPI_ERR_SQL_QUERY;
			}

			
			// Извличане на броя на записите за цялата справка
			$rs = $db_system->Execute("SELECT FOUND_ROWS()");
			
			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			// Установяване на паремтрите по paging-a
			if( !empty( $nPage ) ) {	
				$nRowTotal = current( $rs->FetchRow() );;
				
				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
					);
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function getMenu(&$aData, $nPage) {
			global $db_system;
						
			/* $sQuery = "SELECT * FROM menu order by menu_order";
			$rs = $db_system->Execute( $sQuery );
			if(!$rs) return DBAPI_ERR_SQL_QUERY;
			$aData = $rs->GetAssoc();
			
			$this->processMenu($aData);	
			*/
		
			$sQuery = "(
						select
						  concat(lpad(m0.menu_order,3,'0'),'_000_000') as sort_id,
						  0 as level, m0.id, m0.parent_id, m0.menu_order, m0.title, concat('', m0.title) as tab_title
						from head_menu m0
						where m0.parent_id = 0
						order by m0.menu_order
						) union (
						select
						  concat(lpad(m0.menu_order,3,'0'),'_',lpad(m1.menu_order,3,'0'),'_000') as sort_id,
						  1 as level, m1.id, m1.parent_id, m1.menu_order, m1.title, concat('   ',m1.title) as tab_title
						from head_menu m0
						left join head_menu as m1 on m1.parent_id = m0.id
						where m0.parent_id = 0
						order by m0.menu_order, m1.menu_order
						) union (
						select
						  concat(lpad(m0.menu_order,3,'0'),'_',lpad(m1.menu_order,3,'0'),'_',
						    if(m2.menu_order is null,'000',lpad(m2.menu_order,3,'0'))
						  ) as sort_id,
						  2 as level, m2.id, m2.parent_id, m2.menu_order, m2.title, concat('      ',m2.title) as tab_title
						from head_menu m0
						left join head_menu as m1 on m1.parent_id = m0.id
						left join head_menu as m2 on m2.parent_id = m1.id
						where m0.parent_id = 0 and m2.id is not null
						order by m0.menu_order, m1.menu_order, m2.menu_order
						)
						order by sort_id";
			
	
			
			$rs = $db_system->Execute( $sQuery );
			if(!$rs) return DBAPI_ERR_SQL_QUERY;
			
			$aData = $rs->GetAssoc();
			
			return DBAPI_ERR_SUCCESS;
		}

		function update(&$aData) {
			global $db_system;
			
			$id = empty( $aData['id'] ) ? -1 : $aData['id'];
			$aData['menu_order'] = isset($aData['menu_order']) ? $aData['menu_order'] + 1 : 999999;

			$rs = $db_system->Execute("SELECT * FROM head_menu WHERE id = {$id}");
			if( !$rs ) return DBAPI_ERR_SQL_QUERY;
				
			$db_system->StartTrans();
				$db_system->Execute("UPDATE head_menu SET menu_order = menu_order+1 WHERE menu_order >='{$aData['menu_order']}'");
				$aData['updated_user'] = $_SESSION['userdata']['id_person'];	
				$aData['updated_time'] = date('Y-m-d H:i:s');
				if( $id > 0 ){
					$db_system->Execute( $db_system->GetUpdateSQL($rs, $aData) );
				} else {
					if( !$db_system->Execute( $db_system->GetInsertSQL($rs, $aData) ) )
						$aData['id'] = $db_system->Insert_ID();	
				}
			$result = $db_system->CompleteTrans();

			return $result ? DBAPI_ERR_SUCCESS : DBAPI_ERR_SQL_QUERY;
		}

		function delete($id) {
			global $db_system;
			
			$rs = $db_system->Execute("DELETE FROM head_menu WHERE id = {$id}");
			return $rs ? DBAPI_ERR_SUCCESS : DBAPI_ERR_SQL;
		}

		function getBeforeElement(&$nBeforeElement, $nMenuOrder) {
			global $db_system;

			$sQuery = "SELECT max(menu_order) as prev_order FROM head_menu WHERE menu_order < '".$nMenuOrder."'";
			$rs = $db_system->Execute( $sQuery );
			if (!$rs) return DBAPI_ERR_SQL_QUERY;
			$nBeforeElement = $rs->fields['prev_order'];
	
			return DBAPI_ERR_SUCCESS;
		}

		function getResultOnce($id, &$oData) {
			global $db_system;

			$sQuery = " SELECT * FROM head_menu WHERE id = '$id'";
			$rs = $db_system->Execute( $sQuery );
			if( !$rs ) return DBAPI_ERR_SQL_QUERY;	
			$oData = $rs->fields;
			return DBAPI_ERR_SUCCESS;
		}
	}
?>