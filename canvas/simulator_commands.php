<?php
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	require_once ("../config/function.autoload.php");
	require_once ("../config/config.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	require_once ("classes/DBObjects.class.php");
	
	$oBase = new DBBase2($db_sod,"objects");
	if (empty($_POST['cmd'])) return;
	$cmd = $_POST['cmd'];
	
	switch ($cmd) {
		case "addObject":
			if (empty($_POST['objNum'])) return;			
			
			$sQuery = "
				SELECT id , name, address, num, geo_lat, geo_lan
				FROM objects
				WHERE num=".(int)$_POST['objNum']." AND id_office=".(int)$_POST['region']."
				";		
			$aObject = $oBase->select($sQuery);		
			
			$oDBArchive = new DBMonthTable($db_name_sod,'archiv_',$db_sod);
			$aArchiveMsg = array(
				'id_msg'	=> (int)$_POST['id_msg'],
				'msg_time'	=> date('Y-m-d H:i:s'),
				'num'		=> $aObject[0]['num'],
				'status'	=> $_POST['alarm_code'],
				'alarm'		=> (int)$_POST['alarm'],
				'msg'		=> $_POST['msg']
			);
			$oDBArchive->update($aArchiveMsg);
			
			if (empty($aObject)) return;
			echo json_encode($aObject);
		break;		
		case "getMsg":			
			$region = $_POST['region'];
			$objNum = $_POST['objNum'];
			$sQuery = "
				(SELECT 	
					m.id,
					1 as alarm,
					m.code_al AS code,
					m.msg_al AS msg
				FROM objects AS o
				LEFT JOIN messages AS m ON m.id_obj=o.id
				WHERE o.num=$objNum AND o.id_office=$region)
				UNION
				(SELECT 	
					m.id,
					0 as alarm,
					m.code_rest AS code,
					m.msg_rest AS msg
				FROM objects AS o
				LEFT JOIN messages AS m ON m.id_obj=o.id
				WHERE o.num=$objNum AND o.id_office=$region)
				";				
			$aMsg = $oBase->select($sQuery);									
			echo json_encode($aMsg);				
		break;
		case "getPatrol":
			$sQuery = "SELECT * FROM patruls WHERE id_office=".$_POST['region'];
			$aPatrol = $oBase->select($sQuery);
			$region = (int)$_POST['region'];
			
			$sQuery = " 
				SELECT 
					a.id, 
					a.geo_lat,
					a.geo_lan,					
					concat(ro.id_patrul,'  ',a.reg_num,' (',amk.name,')') AS info
					FROM auto_trans.auto a
					JOIN auto_trans.auto_models am ON am.id = a.id_model
					JOIN auto_trans.auto_marks amk ON amk.id = am.id_mark
					JOIN auto_trans.auto_types amt ON amt.id = am.id_type
					JOIN auto_trans.relation_offices ro ON ro.id_division = a.id_division AND ro.id_region = a.id_region AND ro.to_arc = 0
				WHERE 1
					AND ro.id_telenet_office = $region
					AND a.id_function = 2
				GROUP BY a.id
			";							
			$oBase2 = new DBBase2($db_auto_trans,"auto");			
			$aCars = $oBase2->select($sQuery);						
			//if (empty($aPatrol) || empty()) return;
			echo json_encode(array('patrols'=>$aPatrol,'cars'=>$aCars));	
		break;
		case "moveCar":
			$aUpd = array(
				'id' => (int)$_POST['idCar'],
				'geo_time' => date('Y-m-d H:i:s'),
				'geo_real_time' => date('Y-m-d H:i:s'),
				'gps_fix' => 1,
				'geo_lan' => (double) $_POST['geo_lan'],
				'geo_lat' => (double) $_POST['geo_lat']
			);
			if(!empty($aUpd['id'])) {
				$oDBCars = new DBBase2($db_auto_trans,'auto');
				$res = $oDBCars->update($aUpd);
			}
			return json_encode($res);
		break;
		case "createRoadList":
			$oAutoTrans = new DBBase2($db_auto_trans,'auto');
			$aYearMonths = array();
			$nYearFrom = date('Y', time() - 60*60*24);
			$nMonthFrom = date('m', time() - 60*60*24);
			$aRealTables = $oAutoTrans->select("SHOW TABLES LIKE 'road\_lists\_____'");
			
			foreach ($aRealTables as $k => $aRealTableRow) $aRealTables[$k] = reset($aRealTableRow);
			rsort($aRealTables);
			
			foreach ($aRealTables as $sTable) {
				$sYearMonth = preg_replace("/[^\d]/",'',$sTable);
				$nYear = '20'.substr($sYearMonth,0,2);
				$nMonth = substr($sYearMonth,2,2);
				$aYearMonths[] = substr($sYearMonth,0,2).$nMonth;
				if($nYearFrom > $nYear || $nMonthFrom > $nMonth) break;
			}
			
			$id_auto = (int)$_POST['id_auto'];
			$id_patrol = (int)$_POST['id_patrol'];
			$sQuery="
				SELECT 
					id AS __key,
					id_auto,
					id_division,
					id_region,
					persons,
					start_km,
					start_time,
					end_km,
					end_time,
					<yearmonth> as yearmonth
				FROM auto_trans.road_lists_<yearmonth>
				WHERE id_auto = $id_auto AND id_function = 2 AND end_time=0
			";
			
			$aQueries = array();
			foreach ($aYearMonths as $sYearMonths) $aQueries[] = str_replace('<yearmonth>',$sYearMonths,$sQuery);						
			$aPatrolCars = $oAutoTrans->selectAssoc(implode("\nUNION\n",$aQueries));
			$aUpd = array();
			$persons;			
			if (!empty($aPatrolCars)) {				
				foreach ($aPatrolCars as $idRL => $aCar) {												
					$sTable				= "road_lists_".$aCar['yearmonth'];
					$oAutoTrans			= new DBBase2($db_auto_trans,'road_lists_'.$aCar['yearmonth']);
					$aUpd['id']			= $idRL;
					$aUpd['end_km']		= (int)$aCar['start_km'] + 100;
					$aUpd['end_time']	= time();		
//					$aUpd['id_division']= (int)$aCar['id_division'];
//					$aUpd['id_region']	= (int)$aCar['id_region'];
//					$aUpd['persons']	= (int)$aCar['persons'];					
					$oAutoTrans->update($aUpd);
				}		
			}
			
			$oAutoTrans	= new DBBase2($db_auto_trans,'road_lists_'.date('y').date('m'));
			$aIns['id_auto'] = $id_auto;
			$aIns['id_division'] = 0;
			$aIns['id_region'] = 0;
			$aIns['id_function'] = 2;
			$aIns['id_patrul'] = (int)$_POST['id_patrol'];						
			$aIns['persons'] = "65,62";
			$aIns['start_km'] = $aUpd['end_km'] +5 || 2346565;
			$aIns['start_time'] = time();						
			$oAutoTrans->update($aIns);						
		break;
		case "closeRoadList":
			$oAutoTrans = new DBBase2($db_auto_trans,'auto');
			$aYearMonths = array();
			$nYearFrom = date('Y', time() - 60*60*24);
			$nMonthFrom = date('m', time() - 60*60*24);
			$aRealTables = $oAutoTrans->select("SHOW TABLES LIKE 'road\_lists\_____'");
			foreach ($aRealTables as $k => $aRealTableRow) $aRealTables[$k] = reset($aRealTableRow);
			rsort($aRealTables);
			foreach ($aRealTables as $sTable) {
				$sYearMonth = preg_replace("/[^\d]/",'',$sTable);
				$nYear = '20'.substr($sYearMonth,0,2);
				$nMonth = substr($sYearMonth,2,2);
				$aYearMonths[] = substr($sYearMonth,0,2).$nMonth;
				if($nYearFrom > $nYear || $nMonthFrom > $nMonth) break;
			}
			$id_auto = (int)$_POST['id_auto'];
			$sQuery="
				SELECT 
					id AS __key,
					id_auto,
					id_division,
					id_region,
					persons,
					start_km,
					start_time,
					end_km,
					end_time,
					<yearmonth> as yearmonth
				FROM auto_trans.road_lists_<yearmonth>
				WHERE id_auto = $id_auto AND id_function = 2 AND end_time=0
			";
			$aQueries = array();
			foreach ($aYearMonths as $sYearMonths) $aQueries[] = str_replace('<yearmonth>',$sYearMonths,$sQuery);			
			$aPatrolCars = $oAutoTrans->selectAssoc(implode("\nUNION\n",$aQueries));
			if (!empty($aPatrolCars)) {				
				foreach ($aPatrolCars as $idRL => $aCar) {												
					$sTable				= "road_lists_".$aCar['yearmonth'];
					$oAutoTrans			= new DBBase2($db_auto_trans,'road_lists_'.$aCar['yearmonth']);
					$aUpd['id']			= $idRL;
					$aUpd['end_km']		= (int)$aCar['start_km'] + 100;
					$aUpd['end_time']	= time();		
//					$aUpd['id_division']= (int)$aCar['id_division'];
//					$aUpd['id_region']	= (int)$aCar['id_region'];
//					$aUpd['persons']	= (int)$aCar['persons'];
					$oAutoTrans->update($aUpd);
				}		
			}
		break;
	}

?>
