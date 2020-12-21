<?php
	class DBMessageSchemes
		extends DBBase2 {

		public function __construct() {
			global $db_sod;
			//$db_telepol->debug=true;

			parent::__construct($db_sod, 'schemes_names');
		}

		public function getSchemes() {
			global $db_name_sod;

			$sQuery = "
				SELECT
					id,
					name
				FROM {$db_name_sod}.schemes_names
				WHERE to_arc = 0
				ORDER BY name
			";

			$aData = $this->selectAssoc($sQuery);

			return $aData;
		}

		public function getiMessages( DBResponse $oResponse, $nID, $nReact ) {
			$nID 		= is_numeric( $nID ) 	? $nID 		: 0;
			$nReact 	= is_numeric( $nReact ) ? $nReact 	: 0;

			$right_edit = false;

			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('object_messages_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}

			if ( $nReact == 1 ) {
				$wh = " AND m.flag = 1 \n";
			} else $wh = "";

			$sQuery = "
				SELECT
				  	m.id as _id,
					m.id as id,
					m.id as chk,
					m.id_sig,
					CONCAT( m.code_al, ' / ', m.part, ' / ', m.zone ) as code_al,
					m.msg_al as msg_al,
					m.code_rest as code_rest,
					m.msg_rest as msg_rest,
					DATE_FORMAT(m.time_al, '%d.%m.%Y %H:%i:%s') as _time_al,
					m.flag,
					s.pic,
					s.ico AS fa_ico,
					m.is_phone,
                    m.is_cid    as is_cid   ,
					m.is_sector as is_sector,
					m.is_zone   as is_zone  ,
					m.part      as m_sector ,
					m.zone      as m_zone   ,
					IF( m.is_zone!= 0,
					    IF( m.id_cid>=400 AND m.id_cid<=500, COALESCE( ou.name, m.zone ), COALESCE( oz.name , m.zone ) ),
					    '---'
                    ) AS zu_name,
					IF( m.is_sector != 0, COALESCE( os.name, m.part ), '---') AS s_name
				FROM messages m
				LEFT JOIN objects_users ou ON ou.id_object = m.id_obj AND m.zone = ou.user AND ou.to_arc = 0
				LEFT JOIN objects_zones oz ON oz.id_object = m.id_obj AND m.zone = oz.zone AND oz.to_arc = 0
				LEFT JOIN objects_sectors os ON os.id_object = m.id_obj AND m.part = os.sector AND os.to_arc = 0
				LEFT JOIN signals s ON m.id_sig = s.id
				WHERE 1
					AND m.to_arc = 0
					AND m.id_obj = {$nID}
					{$wh}
				ORDER BY m.code_al ASC
				LIMIT 120
			";

			$oDBObjects = new DBObjects();
			$aData 		= $oDBObjects->select( $sQuery );

			$oResponse->setData( $aData );
			$oResponse->setSort("m.msg_al", "ASC");

//			$nRowCount = $_SESSION['userdata']['row_limit'];
//			$_SESSION['userdata']['row_limit'] = 100;
//
//			$this->getResult($sQuery, 'msg_al', DBAPI_SORT_ASC, $oResponse);
//
//			$_SESSION['userdata']['row_limit'] = $nRowCount;

			if ( $right_edit ) {
				$oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
				$oResponse->setFieldData('chk', 'input', array('type' => 'checkbox', 'exception' => 'false'));
				$oResponse->setFieldAttributes('chk', array('style' => 'width: 25px;'));

				$oResponse -> setFormElement('form1', 'sel', array(), '');
				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '1'), "--- Маркирай всички ---");
				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '2'), "--- Отмаркирай всички ---");
				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '0'), "------");
				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '3'), "--- Изтриване ---");
			}

            $oResponse->setField("fa_ico", "");
            $oResponse->setField("_time_al", "Последен сигнал", "Сортирай по сигнал");
            $oResponse->setField("s_name", "Сектор", "Сортирай по сектор");
            $oResponse->setField("code_al", "", "Сортирай по код");
            $oResponse->setField("msg_al", "Алармиращо съобщение", "Сортирай по съобщение");
            $oResponse->setField("zu_name", "З / П", "Сортирай по зона / потребител");
//            $oResponse->setField("code_rest", "", "Сортирай по код");
//            $oResponse->setField("msg_rest", "Възстановяващо съобщение", "Сортирай по съобщение");

            if ( $right_edit ) {
                $oResponse->setField("id", "", "Редактирай сигнала", "images/edit.gif", "editSignal", "");
                $oResponse->setField("", "", "Изтрий сигнала", "images/cancel.gif", "deleteSignal", "");
            }


            $signal = array();
			$signal[1] = "Alarm Zone 1";
			$signal[17] = "Restore Zone 1";
			$signal[2] = "Alarm Zone 2";
			$signal[18] = "Restore Zone 2";
			$signal[3] = "Alarm Zone 3";
			$signal[19] = "Restore Zone 3";
			$signal[4] = "Alarm Zone 4";
			$signal[20] = "Restore Zone 4";
			$signal[5] = "Alarm Zone 5";
			$signal[21] = "Restore Zone 5";
			$signal[6] = "Alarm Zone 6";
			$signal[22] = "Restore Zone 6";
			$signal[7] = "Alarm Zone 7";
			$signal[23] = "Restore Zone 7";
			$signal[8] = "Alarm Zone 8";
			$signal[24] = "Restore Zone 8";
			$signal[57] = "Testing";
			$signal[58] = "Opening";
			$signal[66] = "Closing";
			$signal[51] = "AC Loss";
			$signal[59] = "AC Normal";
			$signal[52] = "Low Batt";
			$signal[60] = "Batt Normal";
			$signal[55] = "Starting";

			$signal[33] = "Tamp Zone 1";
			$signal[34] = "Tamp Zone 2";
			$signal[35] = "Tamp Zone 3";
			$signal[36] = "Tamp Zone 4";
			$signal[37] = "Tamp Zone 5";
			$signal[38] = "Tamp Zone 6";
			$signal[39] = "Tamp Zone 7";
			$signal[40] = "Tamp Zone 8";

			$signal[208] = "Fire (wega 6)";
			$signal[209] = "Restore Fire (wega 6)";
			$signal[210] = "Panic (wega 6)";
			$signal[211] = "Restore Panic (wega 6)";
			$signal[228] = "Fuse Trouble (wega 6)";
			$signal[229] = "Restore Fuse (wega 6)";
			$signal[214] = "Bypass Zone (wega 6)";
			$signal[215] = "Restore Bypass (wega 6)";
			$signal[230] = "Engineer Entry (wega 6)";
			$signal[231] = "Exit Engineer (wega 6)";
			$signal[232] = "Entry Time (wega 6)";
			$signal[233] = "Restore Entry Time (wega 6)";

			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$t_al			= $val['msg_al'];
				$t_rest			= $val['msg_rest'];

                $aAttributes = array();
                if( $oResponse->oResult->aData[$key]['fa_ico']) {
                    $aAttributes['data-rl-ico'] = $oResponse->oResult->aData[$key]['fa_ico'];
                    $aAttributes['data-rl-sector'] = $oResponse->oResult->aData[$key]['m_sector'];
                } else {
                    $aAttributes['data-rl-ico'] = $oResponse->oResult->aData[$key]['fa_ico'];
                    $aAttributes['data-rl-sector'] = $oResponse->oResult->aData[$key]['m_sector'];
                }


                if ( $val['flag'] == 1 ) {
                    $str_background = array('style' => 'background: #EF9A9A !important;', 'title' => $t_al." \n ".$t_rest);
                } else {
                    $str_background = array('style' => '', 'title' => $t_al." \n ".$t_rest);
                }

                $oResponse->setDataAttributes($key, 'fa_ico', $aAttributes, $str_background );



				if ( $val['is_phone'] == 0 ) {
					$val['msg_al'] 		= array_key_exists( $val['code_al'], $signal ) ? $signal[$val['code_al']]."  ".$val['msg_al'] : dechex($val['code_al'])."  ".$val['msg_al'];
					$val['msg_rest'] 	= array_key_exists( $val['code_rest'], $signal ) && $val['is_phone'] == 0 ? $signal[$val['code_rest']]."  ".$val['msg_rest'] : dechex($val['code_rest'])."  ".$val['msg_rest'];
					$val['code_al'] 	= dechex($val['code_al']);
					$val['code_rest'] 	= dechex($val['code_rest']);
				} else {
					if ( $val['is_cid'] == 0 ) {
						$val['code_al'] 	= dechex($val['code_al']);
						$val['code_rest'] 	= dechex($val['code_rest']);
					} else {
						$val['code_al'] 	= "[cID] ".$val['code_al'];
						$val['code_rest'] 	= "[cID] ".$val['code_rest'];
                        $val['zu_name']     =  $val['zu_name']." ";
                        $val['s_name']      =  $val['s_name']." ";
					}
				}


				if ( mb_strlen($val['msg_al'], 'UTF-8') > 30 ) {
					$val['msg_al'] 	= mb_substr($val['msg_al'], 0, 25, 'UTF-8')."...";
				}

				if ( mb_strlen($val['msg_rest'], 'UTF-8') > 40 ) {
					$val['msg_rest'] = mb_substr($val['msg_rest'], 0, 37, 'UTF-8')."...";
				}

				$oResponse->setDataAttributes( $key, 'chk', $str_background );

                $oResponse->setDataAttributes( $key, '_time_al', $str_background);
                $oResponse->setDataAttributes( $key, $val['m_sector'], $aAttributes, $str_background );
                $oResponse->setDataAttributes( $key, 's_name', 	$str_background );
                $oResponse->setDataAttributes( $key, $val['msg_al'], 	$str_background );
                $oResponse->setDataAttributes($key, 'code_al', 	$str_background );
                $oResponse->setDataAttributes($key, 'zu_name', 	$str_background );

			}

			$oResponse->setFieldAttributes( 'chk',	$str_background );
		}

		public function addScheme( $data ) {
			global  $db_sod;

			$nObj = $data['obj'];
			$sName = $data['name'];
			//APILog::Log(0, $sName);
			$sQuery = "
				INSERT INTO schemes_names
					(name)
				VALUES ('{$sName}')
			";

			$db_sod->Execute( $sQuery );

			$nID = $db_sod->Insert_ID();
			//APILog::Log(0, $nID);

			$sQuery = "
				INSERT INTO schemes (
					`id_scheme`,
					`id_sig`,
					`is_cid`,
					`id_cid`,
					`zone`,
					`code_al`,
					`msg_al`,
					`code_rest`,
					`msg_rest`,
					`time_al`,
					`flag`,
					`test_flag`,
					`test`,
					`is_phone`
				)
				SELECT
					{$nID},
					`id_sig`,
					`is_cid`,
					`id_cid`,
					`zone`,
					`code_al`,
					`msg_al`,
					`code_rest`,
					`msg_rest`,
					`time_al`,
					`flag`,
					`test_flag`,
					`test`,
					`is_phone`
				FROM messages
				WHERE id_obj = {$nObj} AND to_arc = 0
			";
			//APILog::Log(0, $sQuery);
			$db_sod->Execute( $sQuery );
		}


		public function delScheme( $nID ) {
			global  $db_sod;

			$sQuery = "
				UPDATE schemes_names SET to_arc = 1
				WHERE id = {$nID}
			";

			$db_sod->Execute( $sQuery );

			$sQuery = "
				UPDATE schemes SET to_arc = 1
				WHERE id_scheme = {$nID}
			";

			$db_sod->Execute( $sQuery );

		}

		public function editScheme( $data ) {
			global  $db_sod;

			$nIDObject = $data['nIDObject'];
			$nIDScheme = $data['nIDScheme'];

			$sQuery = "
				UPDATE schemes SET to_arc = 1
				WHERE id_scheme = {$nIDScheme}
			";
			//APILog::Log(0, $sQuery);
			$db_sod->Execute( $sQuery );

			$sQuery = "
				INSERT INTO schemes (
					`id_scheme`,
					`id_sig`,
					`is_cid`,
					`id_cid`,
					`zone`,
					`code_al`,
					`msg_al`,
					`code_rest`,
					`msg_rest`,
					`time_al`,
					`flag`,
					`test_flag`,
					`test`,
					`is_phone`
				)
				SELECT
					{$nIDScheme},
					`id_sig`,
					`is_cid`,
					`id_cid`,
					`zone`,
					`code_al`,
					`msg_al`,
					`code_rest`,
					`msg_rest`,
					`time_al`,
					`flag`,
					`test_flag`,
					`test`,
					`is_phone`
				FROM messages
				WHERE id_obj = {$nIDObject} AND to_arc = 0
			";
			//APILog::Log(0, $sQuery);
			$db_sod->Execute( $sQuery );
		}
/*
		public function setTnetMessagesFromScheme( $nIDObject, $nIDScheme ) {
			global $db_sod, $db_name_sod;

			if ( empty($nIDScheme) || !is_numeric($nIDScheme) ) {
				return false;
				//ob_toFile("Nqma setnat shablon: ".$nIDScheme, "scheme.log");
			}

			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return false;
				//ob_toFile("Nqma setnat obekt: ".$nIDObject, "scheme.log");
			}

			$nIDPerson	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

			$oSignals	= new DBObjectSignals();

			$aItems 	= $this->getSignalsByScheme($nIDScheme);

			$db_sod->StartTrans();

			try {
				foreach ($aItems as $val) {
					$aData					= array();

					$aData['id'] 			= 0;
					$aData['id_obj'] 		= $nObj;
					$aData['id_signal'] 	= isset($val['id_sig']) 	? $val['id_sig'] 		: 0;
					$aData['id_old_signal'] = 0;
					$aData['code_al'] 		= isset($val['code_al']) 	? $val['code_al'] 	 : 0;
					$aData['code_old_al'] 	= 0;
					$aData['code_rest'] 	= isset($val['code_rest']) 	? $val['code_rest'] 	: 0;
					$aData['code_old_rest'] = 0;
					$aData['msg_al'] 		= isset($val['msg_al']) 	? iconv('cp1251', 'utf-8', $val['msg_al']) 		: "";
					$aData['msg_old_al'] 	= "";
					$aData['type'] 			= "new";
					$aData['to_arc'] 		= 0;

					$oSignals->update($aData);
					//ob_toFile($aData, "scheme.log");

					$zone		= isset($val['zone']) 		? $val['zone'] 									: 0;
					$is_cid		= isset($val['isCID']) 		? $val['isCID'] 								: 0;
					$id_cid		= isset($val['id_cid']) 	? $val['id_cid'] 								: 0;
					$flag		= isset($val['flag']) 		? $val['flag'] 									: 0;
					$test_flag	= isset($val['testFlag']) 	? $val['testFlag'] 								: 0;
					$test		= isset($val['test']) 		? $val['test'] 									: 0;
					$is_phone	= isset($val['is_phone']) 	? $val['is_phone'] 								: 0;
					$msg_rest 	= isset($val['msg_rest']) 	? iconv('cp1251', 'utf-8', $val['msg_rest']) 	: "";

					$sQuery = "
						INSERT INTO {$db_name_sod}.messages (
							`id_sig`,
							`id_obj`,
							`is_cid`,
							`id_cid`,
							`zone`,
							`code_al`,
							`msg_al`,
							`code_rest`,
							`msg_rest`,
							`time_al`,
							`flag`,
							`test_flag`,
							`test`,
							`is_phone`,
							`updated_user`,
							`updated_time`
						) VALUES (
							{$aData['id_signal']},
							{$nIDObject},
							{$is_cid},
							{$id_cid},
							{$zone},
							{$aData['code_al']},
							'{$aData['msg_al']}',
							{$aData['code_rest']},
							'{$msg_rest}',
							'0000-00-00 00:00:00',
							{$flag},
							{$test_flag},
							{$test},
							{$is_phone},
							{$nIDPerson},
							NOW()
						)
					";

					$db_sod->Execute( $sQuery );
					//ob_toFile($sQuery, "scheme.log");
				}

				$db_sod->CompleteTrans();
			} catch (Exception $e) {
				//APILog::Log(0, $e->getMessage());
				//ob_toFile($e->getMessage(), "scheme.log");
				$db_sod->FailTrans();
			}
		}*/

		public function getSignalsByScheme( $nID )	{
			global $db_name_sod;

			$sQuery = "
				SELECT
					id_sig,
					msg_al as msg_al,
					code_al as code_al,
					msg_rest as msg_rest,
					code_rest as code_rest,
					test_flag as test_flag,
					test,
					zone,
					IF (is_phone, IF(is_cid, 'cid', 'phone'), 'radio') as channel,
					is_cid,
					id_cid,
					flag,
					test,
					is_phone
				FROM {$db_name_sod}.schemes
				WHERE id_scheme = {$nID}
					AND to_arc = 0
			";

			return $this->select( $sQuery );
		}

		public function fromScheme( DBResponse $oResponse, $data ) {
			global $db_sod, $db_name_sod;

			$nID 		= isset($data['nID']) 		? $data['nID'] 		 : 0;
			//$nIDObject= isset($data['nIDObject']) ? $data['nIDObject'] : 0;
			$nIDScheme 	= isset($data['nIDScheme']) ? $data['nIDScheme'] : 0;
			$nIDPerson 	= isset($data['nIDPerson']) ? $data['nIDPerson'] : 0;

			$oSig 		= new DBObjectSignals();
			$oObj		= new DBObjects();

			$aItems		= array();
			//$nObj 		= $oObj->getIDByIDOldObj($nIDObject);
			$aItems 	= $this->getSignalsByScheme($nIDScheme);

			$db_sod->StartTrans();
			//$oResponse->setAlert(ArrayToString($aItems));
			try {
				foreach ($aItems as $val) {
					$aData					= array();

					$aData['id'] 			= 0;
					$aData['id_obj'] 		= $nID;
					$aData['id_signal'] 	= isset($val['id_sig']) 	? $val['id_sig'] 		: 0;
					$aData['id_old_signal'] = 0;
					$aData['code_al'] 		= isset($val['code_al']) 	? $val['code_al'] 		: 0;
					$aData['code_old_al'] 	= 0;
					$aData['code_rest'] 	= isset($val['code_rest']) 	? $val['code_rest'] 	: 0;
					$aData['code_old_rest'] = 0;
					$aData['msg_al'] 		= isset($val['msg_al']) 	? $val['msg_al']		: "";
					$aData['msg_old_al'] 	= "";
					$aData['type'] 			= "new";
					$aData['to_arc'] 		= 0;

					$oSig->update($aData);

					$zone		= isset($val['zone']) 		? $val['zone'] 				: 0;
					$is_cid		= isset($val['is_cid']) 	? $val['is_cid'] 			: 0;
					$id_cid		= isset($val['id_cid']) 	? $val['id_cid'] 			: 0;
					$flag		= isset($val['flag']) 		? $val['flag'] 				: 0;
					$test_flag	= isset($val['test_flag']) 	? $val['test_flag'] 		: 0;
					$test		= isset($val['test']) 		? $val['test'] 				: 0;
					$is_phone	= isset($val['is_phone']) 	? $val['is_phone'] 			: 0;
					$msg_rest 	= isset($val['msg_rest']) 	? $val['msg_rest'] 			: "";

					$sQuery = "
						INSERT INTO {$db_name_sod}.messages (
							`id_sig`,
							`id_obj`,
							`is_cid`,
							`id_cid`,
							`zone`,
							`code_al`,
							`msg_al`,
							`code_rest`,
							`msg_rest`,
							`time_al`,
							`flag`,
							`test_flag`,
							`test`,
							`is_phone`,
							`updated_user`,
							`updated_time`
						) VALUES (
							{$aData['id_signal']},
							{$nID},
							{$is_cid},
							{$id_cid},
							{$zone},
							{$aData['code_al']},
							'{$aData['msg_al']}',
							{$aData['code_rest']},
							'{$msg_rest}',
							'0000-00-00 00:00:00',
							{$flag},
							{$test_flag},
							{$test},
							{$is_phone},
							{$nIDPerson},
							NOW()
						)
					";
					//$oResponse->setAlert(ArrayToString($sQuery));
					$db_sod->Execute( $sQuery );
					/*
					$sQuery = "
						INSERT INTO {$db_name_telepol}.messages (
							`id_sig`,
							`id_obj`,
							`isCID`,
							`id_cid`,
							`zone`,
							`codeAl`,
							`msgAl`,
							`codeRest`,
							`msgRest`,
							`timeAl`,
							`flag`,
							`testFlag`,
							`test`,
							`is_phone`
						)
						VALUES
						(
							{$aData['id_signal']},
							{$nIDObject},
							{$is_cid},
							{$id_cid},
							{$zone},
							{$aData['code_al']},
							'".iconv("UTF-8", "CP1251", $aData['msg_al'])."',
							{$aData['code_rest']},
							'".iconv("UTF-8", "CP1251", $msg_rest)."',
							'0000-00-00 00:00:00',
							{$flag},
							{$test_flag},
							{$test},
							{$is_phone}
						)
					";

					$db_telepol->Execute( $sQuery );
					*/
				}

				$db_sod->CompleteTrans();
			} catch (Exception $e) {
				//APILog::Log(0, $e->getMessage());

				$db_sod->FailTrans();
			}
		}

	}
?>