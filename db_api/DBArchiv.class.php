<?php
	class DBArchiv
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'messages');
		}
		
		public function getObjectReceiver( $nID ) {
			$nID 	= is_numeric( $nID ) ? $nID : 0;
			$aData 	= array();
			
			$sQuery = "
				SELECT 
					o.id_receivers
				FROM objects o
				WHERE o.id = {$nID}
			";
			
			$aData = $this->selectOne( $sQuery );
			
			return $aData;
		}
		
		public function getArchiv( DBResponse $oResponse, $data ) {
			global  $db_sod, $db_name_personnel, $db_name_sod;
			
			$nID 	= is_numeric( $data['obj'] ) ? $data['obj'] : 0; 
			$from 	= isset($data['from']) ? $data['from'] : "";
			$to 	= isset($data['to']) ? $data['to'] : "";
			$noTest = isset($data['no_test']) && !empty($data['no_test']) ? 1 : 0;
			$num	= isset($data['num']) ? $data['num'] : 0;
			$sQuery = "";
			$nIDReceiver = $this->getObjectReceiver( $nID );
			
			$tables = !empty($data['tables']) ? $data['tables'] : array();
			
			$where1 = " \n AND m.id_obj = {$nID} \n";
			
			$where 	= !empty($data['react']) ? " AND UNIX_TIMESTAMP(arh.response) > 0 \n" : ""; 
			
			if ( !empty($from) ) {
				$where .= " AND arh.msg_time >= '{$from}' ";
			}
			
			if ( !empty($to) ) {
				$where .= " AND arh.msg_time <= '{$to}' ";
			}
			
			if ( $noTest ) {
				$where .= " AND m.code_rest != 57 ";
			}
			
			$getMax	= Params::get("max", 0);
			
			if ( count($tables) > 1 ) {
				
				for ( $i = 0; $i <= count($tables) -2; $i++ ) {
					$sQuery .= "	
						( SELECT
							arh.id as id,
							DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
							arh.msg_time as tsig,
							arh.msg as msg,
							IF (m.is_phone, 'телефон', 'радио') as channel,
							'Prichinata ne se zapazva nikade!' as reason,
							arh.alarm as alarm,
							arh.pass
						FROM {$tables[$i]} arh
						LEFT JOIN messages m ON m.id = arh.id_msg
						WHERE 1 
							{$where1}
							{$where}
						) 
						
						UNION
						
					";	
				}
				
				$sQuery .= "	
					( SELECT
						arh.id as id,
						DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						arh.msg_time as tsig,
						arh.msg as msg,
						IF (m.is_phone, 'телефон', 'радио') as channel,
						'Prichinata ne se zapazva nikade!' as reason,
						arh.alarm as alarm,
						arh.pass
					FROM {$tables[$i]} arh
					LEFT JOIN messages m ON m.id = arh.id_msg
					WHERE 1 
						{$where1}
						{$where}
					) 
				";
				
				
			} elseif ( count($tables) == 1 ) {
				$sQuery = "	
					SELECT
						arh.id as id,
						DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						arh.msg_time as tsig,
						arh.msg as msg,
						IF (m.is_phone, 'телефон', 'радио') as channel,
						'Prichinata ne se zapazva nikade!' as reason,
						arh.alarm as alarm,
						arh.pass
					FROM {$tables[0]} arh
					LEFT JOIN messages m ON m.id = arh.id_msg
					WHERE 1 
						{$where1}
						{$where}
				";
			}
			
			$sQuery .= " ORDER BY tsig DESC ";
			
			//APILog::Log(0, $sQuery);
			
			//ORDER BY arh.id_arhiv DESC
			//AND m.id_obj = {$nID}
			//LIMIT 100
			
			if ( count($tables) > 0 ) {
				$aData = $this->select( $sQuery );
			} else {
				$aData = array();
			}
			//APILog::Log(0, $aData);
			
			$max = 0;
			
			foreach ( $aData as &$val ) {
				$val['msg'] = $val['msg'];
				//$val['channel'] = iconv('cp1251', 'utf-8', $val['channel']);
				$val['reason'] = $val['reason'];
				
				if ( $val['id'] > $max ) $max = $val['id'];
			}
			
			$oResponse->setData( $aData );
			$oResponse->setSort("id", "ASC");
			
		//	$oResponse->setField("type", "Тип", "Сортирай по тип");
			$oResponse->setField("msg_time", "Време", "Сортирай по време");
			$oResponse->setField("msg", "Съобщение", "Сортирай по съобщение");
			$oResponse->setField("channel", "Канал", "Сортирай по канал");
			$oResponse->setField("reason", "...", "Сортирай по причина", "images/dots.gif");
			$oResponse->setField("pass", "Видимост", "Сортирай по видимост");
					
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['pass'] = $val['pass']." %";
				
				if ( $val['alarm'] == 1 ) {
					$oResponse->setDataAttributes( $key, 'msg_time', array('style' => 'text-align: center; width: 130px; background: #FFE8E8;') );
					$oResponse->setDataAttributes( $key, 'msg', array('style' => 'background: #FFE8E8;') );
					$oResponse->setDataAttributes( $key, 'channel', array('style' => 'text-align: center; width: 68px; background: #FFE8E8;') );
					$oResponse->setDataAttributes( $key, 'pass', array('style' => 'text-align: right; width: 68px; background: #FFE8E8;') );				
					$oResponse->setDataAttributes( $key, 'reason', array('style' => 'background: #FFE8E8; text-align: center; width: 30px;') );
				} else {
					$oResponse->setDataAttributes( $key, 'channel', array('style' => 'text-align: center; width: 68px;') );				
					$oResponse->setDataAttributes( $key, 'pass', array('style' => 'text-align: right; width: 68px;') );		
					$oResponse->setDataAttributes( $key, 'reason', array('style' => 'text-align: center; width: 30px;') );		
					$oResponse->setDataAttributes( $key, 'msg_time', array('style' => 'text-align: center; width: 130px;') );				
				}
				
				if ( ($val['id'] > $getMax) && ($getMax > 0) ) {
					$oResponse->setRowAttributes( $val['id'], array("style" => "font-weight: bold; color: green;" ) );
				}
				
			}
			
			$oResponse->setFormElement("form1", "max", array(), $max );
		}

        public function getArchivTNet( DBResponse $oResponse, $data )
        {
            $nID 	= is_numeric( $data['obj'] ) ? $data['obj'] : 0;
            $from 	= isset($data['from']) ? $data['from'] : "";
            $to 	= isset($data['to']) ? $data['to'] : "";
          //  $noTest = isset($data['no_test']) && !empty($data['no_test']) ? 1 : 0;
          //  $num	= isset($data['num']) ? $data['num'] : 0;
            $sQuery = "";
            $nIDReceiver = $this->getObjectReceiver( $nID );

            $tables = !empty($data['tables']) ? $data['tables'] : array();

            $where1 = " \nAND m.id_obj = {$nID} \n";

            $where 	= !empty($data['react']) ? " AND UNIX_TIMESTAMP(arh.response) > 0 \n" : "";

            if ( !empty($from) ) {
                $where .= " AND arh.msg_time >= '{$from}' ";
            }

            if ( !empty($to) ) {
                $where .= " AND arh.msg_time <= '{$to}' ";
            }

//            if ( $noTest ) {
//                $where .= " AND m.code_rest != 57 ";
//            }

            $getMax	= Params::get("max", 0);

            if ( count($tables) > 1 ) {

                for ( $i = 0; $i <= count($tables) -2; $i++ ) {
                    $sQuery .= "
						( SELECT
							arh.id as id,
							DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
							arh.msg_time as tsig,
							arh.msg as msg,
							CONCAT( IF( arh.alarm > 0, arh.alarm, 3), ' / ', m.id_cid, ' / ', m.part, ' / ', m.zone ) AS m_z_s,
							IF (m.is_phone, 'телефон', 'радио') as channel,
							'Prichinata ne se zapazva nikade!' as reason,
							arh.alarm as alarm,
							arh.pass
						FROM {$tables[$i]} arh
						LEFT JOIN messages m ON m.id = arh.id_msg
						WHERE 1
							{$where1}
							{$where}
						)

						UNION

					";
                }

                $sQuery .= "
					( SELECT
						arh.id as id,
						DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						arh.msg_time as tsig,
						arh.msg as msg,
						CONCAT( IF( arh.alarm > 0, arh.alarm, 3), ' / ', m.id_cid, ' / ', m.part, ' / ', m.zone ) AS m_z_s,
						IF (m.is_phone, 'телефон', 'радио') as channel,
						'Prichinata ne se zapazva nikade!' as reason,
						arh.alarm as alarm,
						arh.pass
					FROM {$tables[$i]} arh
					LEFT JOIN messages m ON m.id = arh.id_msg
					WHERE 1
						{$where1}
						{$where}
					)
				";


            } elseif ( count($tables) == 1 ) {
                $sQuery = "
					SELECT
						arh.id as id,
						DATE_FORMAT(arh.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						arh.msg_time as tsig,
						arh.msg as msg,
						CONCAT( IF( arh.alarm > 0, arh.alarm, 3), ' / ', m.id_cid, ' / ', m.part, ' / ', m.zone ) AS m_z_s,
						IF (m.is_phone, 'телефон', 'радио') as channel,
						'Prichinata ne se zapazva nikade!' as reason,
						arh.alarm as alarm,
						arh.pass,
						s.play_alarm AS play_alarm
					FROM {$tables[0]} arh
					LEFT JOIN messages m ON m.id = arh.id_msg
					LEFT JOIN signals s ON s.id = m.id_sig
					WHERE 1
						{$where1}
                        {$where}
				";
            }

            $sQuery .= " ORDER BY tsig DESC ";

            //APILog::Log(0, $sQuery);

            //ORDER BY arh.id_arhiv DESC
            //AND m.id_obj = {$nID}
            //LIMIT 100

            $oDBObjects = new DBObjects();

            if ( count($tables) > 0 ) {
                $aData = $oDBObjects->select( $sQuery );
            } else {
                $aData = array();
            }
            //APILog::Log(0, $aData);

            $max = 0;

            /*
            foreach ( $aData as &$val ) {
                $val['msg'] = iconv('cp1251', 'utf-8', $val['msg']);
                //$val['channel'] = iconv('cp1251', 'utf-8', $val['channel']);
                $val['reason'] = iconv('cp1251', 'utf-8', $val['reason']);

                if ( $val['id'] > $max ) $max = $val['id'];
            }
            */

            $oResponse->setData( $aData );
            $oResponse->setSort("id", "ASC");

            //	$oResponse->setField("type", "Тип", "Сортирай по тип");
            $oResponse->setField("msg_time", "Време", "Сортирай по време");
            $oResponse->setField("msg", "Съобщение", "Сортирай по съобщение");
            $oResponse->setField("m_z_s", "A/CID/P/UZ", "Сортирай");
            $oResponse->setField("channel", "Канал", "Сортирай по канал");
//            $oResponse->setField("reason", "...", "Сортирай по причина", "images/dots.gif");
            $oResponse->setField("pass", "Видимост", "Сортирай по видимост");

            foreach( $oResponse->oResult->aData as $key => &$val ) {
                $val['pass'] = $val['pass']." %";

                if ( $val['alarm'] == 1 && $val['play_alarm'] == 2 ) {
                    $oResponse->setDataAttributes( $key, 'msg_time', array('class' => 'bg-danger text-center') );
                    $oResponse->setDataAttributes( $key, 'msg', array('class' => 'bg-danger') );
                    $oResponse->setDataAttributes( $key, 'm_z_s', array('class' => 'bg-danger') );
                    $oResponse->setDataAttributes( $key, 'channel', array('class' => 'bg-danger text-center') );
                    $oResponse->setDataAttributes( $key, 'pass', array('class' => 'bg-danger') );
//                    $oResponse->setDataAttributes( $key, 'reason', array('style' => 'background: #FFE8E8; text-align: center; width: 30px;') );
                } else if ( $val['alarm'] == 0 && $val['play_alarm'] == 1 ) {
                    $oResponse->setDataAttributes( $key, 'msg_time', array('class' => 'table-warning text-dark text-center') );
                    $oResponse->setDataAttributes( $key, 'msg', array('class' => 'table-warning text-dark') );
                    $oResponse->setDataAttributes( $key, 'm_z_s', array('class' => 'table-warning text-dark') );
                    $oResponse->setDataAttributes( $key, 'channel', array('class' => 'table-warning text-dark text-center') );
                    $oResponse->setDataAttributes( $key, 'pass', array('class' => 'table-warning text-dark') );
//                    $oResponse->setDataAttributes( $key, 'reason', array('style' => 'background: #FFE8E8; text-align: center; width: 30px;') );
                } else if ( $val['alarm'] == 1 && $val['play_alarm'] == 1 ) {
                    $oResponse->setDataAttributes( $key, 'msg_time', array('class' => 'bg-warning text-dark text-center') );
                    $oResponse->setDataAttributes( $key, 'msg', array('class' => 'bg-warning text-dark') );
                    $oResponse->setDataAttributes( $key, 'm_z_s', array('class' => 'bg-warning text-dark') );
                    $oResponse->setDataAttributes( $key, 'channel', array('class' => 'bg-warning text-dark text-center') );
                    $oResponse->setDataAttributes( $key, 'pass', array('class' => 'bg-warning text-dark') );
//                    $oResponse->setDataAttributes( $key, 'reason', array('style' => 'background: #FFE8E8; text-align: center; width: 30px;') );
                } else {
                    $oResponse->setDataAttributes( $key, 'channel', array('class' => 'text-center') );
                    $oResponse->setDataAttributes( $key, 'pass', array('class' => 'text-right') );
//                    $oResponse->setDataAttributes( $key, 'reason', array('style' => 'text-align: center; width: 30px;') );
                    $oResponse->setDataAttributes( $key, 'msg_time', array('class' => 'text-center') );
                }

                if ( ($val['id'] > $getMax) && ($getMax > 0) ) {
                    $oResponse->setRowAttributes( $val['id'], array("style" => "font-weight: bold; color: green;" ) );
                }

            }

            $oResponse->setFormElement("form1", "max", array(), $max );
        }

		public function getArchivImages( $data ) {
			global  $db_sod, $db_name_personnel, $db_name_sod;
			
			$nID = is_numeric( $data['obj'] ) ? $data['obj'] : 0; 
			$tables = SQL_get_tables($db_sod, 'archiv_20', '____', 'DESC');
			$tmpArr = array();
			$Data = array();
			
			$table  = !empty($data['table']) ? $data['table'] : $tables[0];
			//$where = !empty($data['react']) ? " AND UNIX_TIMESTAMP(arh.response) > 0 \n" : ""; 
			
			$getMax	= Params::get("max", 0);
			
//			$sQuery = "	
//				SELECT
//					m.id_sig as id,
//					UNIX_TIMESTAMP(arh.MsgTime) as msg_time,
//					arh.Msg as msg,
//					s.playAlarm as alarm
//				FROM {$table} arh
//				LEFT JOIN messages m ON m.id_msg = arh.id_msg
//				LEFT JOIN signals s ON m.id_sig = s.id_sig
//				WHERE 1 
//					AND m.id_obj = {$nID}
//					AND arh.alarm = 1
//				ORDER BY arh.id_arhiv DESC
//			";
			//AND m.id_obj = {$nID}
			//{$where}
			
			$sQuery = "
				SELECT 
					m.id as id,
					m.id_sig,
					UNIX_TIMESTAMP(a.msg_time) as msg_time,
					a.msg as msg,
					s.play_alarm as alarm
				FROM {$table} a
				LEFT JOIN messages m ON m.id = a.id_msg
				LEFT JOIN signals s ON s.id = m.id_sig
				WHERE m.id_obj = {$nID}
					AND m.flag = 1
				ORDER BY a.id DESC
			";
			
			
			$tmpArr = $this->select( $sQuery );

			foreach ( $tmpArr as  $val ) {
				$val['msg'] = $val['msg'];
				if ( !isset($Data[$val['id']]) ) {
					$Data[$val['id']] = $val;
				} else {
					if ( $Data[$val['id']]['msg_time'] <= $val['msg_time'] ) {
						$Data[$val['id']] = $val;
					}
				}
			}
			
//			APILog::Log(0, $sQuery);
			//APILog::Log(0, $Data);
			return $Data;	
		}
		
	}
?>