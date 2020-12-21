<?php

	require_once ("../config/session.inc.php");
	if (empty($_SESSION['telenet_valid_session']) || empty($_SESSION['userdata'])) {
		echo "Неоторизиран достъп!";
		die();
	}
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');

	require_once ("../config/function.autoload.php");
	require_once ("../config/config.inc.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php"); 
	$ADODB_EXCEPTION = 'DBException';
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	
if (get_magic_quotes_gpc()) {
	$_POST = stripslashes_deep($_POST);
}
try {
	$result = null;
	$aRequest = json_decode($_POST['request'], true);
	if($aRequest) {
		$id_alarm = $aRequest['data']['id_alarm'];
		$id_alarm_patrul = $aRequest['data']['id_alarm_patrul'];
		$layer_type = $aRequest['data']['layer_type'];
		$id_contract = $aRequest['data']['id_contract'];
		$oDB = new DBBase2($db_sod, 'admin_register');

		if(!empty($id_alarm)) {
			$sQuery = "SELECT *, start_time as alarm_time FROM tech_register WHERE id = $id_alarm";		
			$result['object'] = $oDB->select($sQuery);		
			$result['object'] = $result['object'][0];
		}
		else 
		{

			if($layer_type == 'object') {

                $sQuery = "SELECT id_object FROM admin_register where id = $id_alarm_patrul";

                $nIDObject = $oDB->selectOne($sQuery);

                if($nIDObject != 0 )
                {
                    $sQuery = "
                        SELECT
                            ap.id_object,
                            o.id_office,
                            ap.start_time,
                            o.geo_lan AS obj_geo_lan,
                            o.geo_lat AS obj_geo_lat,
                            o.name AS obj_name
                        FROM admin_register ap
                        JOIN objects o ON o.id = ap.id_object
                        WHERE ap.id = $id_alarm_patrul
                    ";
                    //var_dump($sQuery);
                    $result['object'] = $oDB->select($sQuery);
                    $result['object'] = $result['object'][0];
                }
			} elseif ($layer_type == 'layer') {

				$sQuery = "
					SELECT
						ap.id_object,
						ap.start_time,
						lo.id_office,
						lo.geo_lan AS obj_geo_lan,
						lo.geo_lat AS obj_geo_lat
					FROM admin_register ap
					JOIN layers_objects lo ON lo.id = ap.id_object
					WHERE ap.id = $id_alarm_patrul
				";

				$result['object'] = $oDB->select($sQuery);
				$result['object'] = $result['object'][0];
			} else if( $layer_type == 'unknown')
            {

                $sQuery = "
                    SELECT
                        ap.start_time,
                        ap.end_geo_lan AS obj_geo_lan,
                        ap.end_geo_lat AS obj_geo_lat
                    FROM admin_register ap
                     WHERE ap.id = {$id_alarm_patrul} ";

                $aTmp = $oDB->selectOnce( $sQuery );
                $result['object'] =$aTmp;

                $oDBPersonnel = new DBPersonnel();
                $result['object']['id'] = 0; // da ima obekt

                $aUserInfo = $oDBPersonnel->getRecord($_SESSION['userdata']['id_person']);
                $result['object']['id_office'] = $aUserInfo['id_office'];
                $result['object']['obj_name'] = "Невъведен";

            }
		}
		/*
		if(!empty($id_contract)){
			//ако е от договор вместо id_object id на договора
			$sQuery = "
				SELECT 
					ah.*,
					$id_contract AS id_object,
					a.reg_num AS patrul_num,
					tech_time AS alarm_time,
					ah.tech_status AS alarm_status,
					a.reg_num AS auto_reg_num,
				    ah.geo_lan AS patrul_geo_lan,
				 	ah.geo_lat AS patrul_geo_lat
				FROM tech_history AS ah
				LEFT JOIN $db_name_auto_trans.auto AS a ON a.id = ah.id_auto
			";
		}
		else
		{
		*/
			$sQuery = "
				SELECT 
					ah.*,
					a.reg_num AS patrul_num,
					admin_time AS alarm_time,
					ah.admin_status AS alarm_status,
					a.reg_num AS auto_reg_num,
				    ah.geo_lan AS patrul_geo_lan,
				 	ah.geo_lat AS patrul_geo_lat
				FROM admin_history AS ah
				LEFT JOIN $db_name_auto_trans.auto AS a ON a.id = ah.id_auto
			";
		//}
		
		if(!empty($id_alarm)) {
			$sQuery .= " WHERE ah.id_admin_register = $id_alarm";
		} else {
			$sQuery .= " WHERE ah.id_admin_register = $id_alarm_patrul";
		}

		$result['events'] = $oDB->select($sQuery);

		$id_office = $result['object']['id_office'];
		
		$sQuery = "
			SELECT lo.id_office, lo.geo_lat, lo.geo_lan, lo.name, lo.description, lo.id
				FROM sod.layers AS l
			LEFT JOIN sod.layers_objects AS lo ON lo.id_layer=l.id
			WHERE lo.to_arc=0  #AND  l.is_alpha=1
			 AND lo.id_office= $id_office
		";

        //var_dump($sQuery);
		$result['wp'] = $oDB->select($sQuery);

		$oReasons = new DBObjects();
		$result['alarmReasons'] = $oReasons->selectAssoc("SELECT id as __key, r.* FROM tech_reason r WHERE to_arc = 0");
		$result['alarmReasonsCancel'] = $oReasons->selectAssoc("SELECT id as __key, r.* FROM alarm_reasons_cancel r WHERE to_arc = 0");
	}
		
	$response = array(
	    'type' => 'response',
	    'data' => $result
	);
} catch(Exception $ex) {
	$response = array(
	    'type' => 'error',
	    'data' => array(
			'type' => get_class($ex),
			'message' => $ex->getMessage(),
			'file' => $ex->getFile(),
			'line' => $ex->getLine(),
	    )
	);
}
die(json_encode($response));
