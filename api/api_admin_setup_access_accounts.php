<?php
	$oAccess = New DBBase( $db_system, 'access_account' );
	
	switch($aParams['api_action']) {
		case 'delete' : 
				$nID = (int) $aParams['id'];
				if( $nResult = $oAccess->toARC( $nID ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при премахването на записа!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
				}
				$aParams['api_action'] = 'result';
			break;
		default:
			break;
	}

	class MyHandler extends APIHandler {
		function setFields( $aParams ) {
			global $oResponse;
					
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}

			$oResponse->setField( 'person',			'Име',			'Сортирай по име' );
			$oResponse->setField( 'position',		'Длъжност',		'Сортирай по длъжност' );
			$oResponse->setField( 'firm', 			'Фирма',		'Сортирай по фирма' );
			$oResponse->setField( 'username',		'Потр. име',	'Сортирай по потребителско име' );
			$oResponse->setField( 'profile',		'Профил',		'Сортирай по профил' );
			$oResponse->setField( 'online',			'Активен',		'Сортирай по активност', 'images/online.gif' );
			$oResponse->setField( 'last_online_',	'Последна активност','Сортирай по последна активност' );
			$oResponse->setField( 'ip',				'Последнo IP',	'Сортирай по IP' );
			$oResponse->setField( 'updated_user', 	'...',			'Сортиране по последно редкатирал', 'images/dots.gif' );
			
			if ($right_edit) {
				$oResponse->setField( 'btn_password',  	'', 			'Парола', "images/password.gif", "changePassword", 'Парола');
				$oResponse->setField( 'btn_delete',  	'', 			'Изтрий', "images/cancel.gif", "deleteAccount", '');
				$oResponse->setFIeldLink( 'person',		'setupAccount' );
			}
		}			
			
		function getReport( $aParams ) {
			global $aParams, $db_name_personnel, $db_name_sod;
			
			$aWhere = array();

			if ( empty($aParams['sfield']) ) {
				$aParams['sfield'] = "person";
			}
			
			if ( empty($aParams['current_page']) ) {
				$aParams['current_page'] = 1;
			}
			
			//APILog::log(0, $aParams);
			$aWhere[] = sprintf(" t.to_arc = 0 " );
			
			if( $aParams['id_profile'] )
			{
				$aWhere[] = " t.id_profile = {$aParams['id_profile']} ";
			}

			$nSessionTime = ini_get('session.gc_maxlifetime');
			
			$sQuery = sprintf(" 
				SELECT 
					SQL_CALC_FOUND_ROWS
					t.id AS _id,
					t.id AS id,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS person,
					pp.name AS position,
					CONCAT('[', r.name,'] ', f.name) AS firm,
					t.username,
					ap.name AS profile,
					(
						(UNIX_TIMESTAMP(t.last_online) + {$nSessionTime}) > UNIX_TIMESTAMP(now()) 
						AND
						UNIX_TIMESTAMP(t.last_online) > UNIX_TIMESTAMP(t.last_logout)
					) AS online, 
					DATE_FORMAT(t.last_online, '%%d.%%m.%%Y %%H:%%i:%%s') AS last_online_,
					t.last_ip AS ip,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time, '%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user
				FROM 
					%s t
				LEFT JOIN {$db_name_personnel}.personnel as up on up.id = t.updated_user
				LEFT JOIN {$db_name_personnel}.personnel as p on p.id = t.id_person
				LEFT JOIN {$db_name_personnel}.positions as pp on pp.id = p.id_position
				LEFT JOIN {$db_name_sod}.offices as r on r.id = p.id_office
				LEFT JOIN {$db_name_sod}.firms as f on f.id = r.id_firm
				LEFT JOIN access_profile as ap on ap.id = t.id_profile
			", 
			$this->_oBase->_sTableName
			);
	
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere ); //
		}
	}
	
	$oHandler = new MyHandler( $oAccess, 'person', 'access_account', 'Потребители за системата' );
	$oHandler->Handler( $aParams );	
?>