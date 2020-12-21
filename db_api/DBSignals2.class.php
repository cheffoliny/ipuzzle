<?php
	class DBSignals2
		extends DBBase2 {
			
		public function __construct() {
			global $db_telepol;
			//$db_sod->debug=true;
			
			parent::__construct($db_telepol, 'signals');
		}
		
		public function getPic( $nID)	{
			$sQuery = "
				SELECT
					pic
				FROM signals
				WHERE id = {$nID}
			";
			return $this->selectOne($sQuery);
		}

		public function getSignals()	{
			$sQuery = "
				SELECT
					id_sig as id,
					msgAl as msg_al,
					msgRest as msg_rest,
					pic,
					testFlag as test_flag,
					playAlarm
				FROM signals
			";
			
			$aData = $this->select( $sQuery );
			
			foreach ( $aData as &$val ) {
				$val['msg_al'] = iconv('cp1251', 'utf-8', $val['msg_al']);
				$val['msg_rest'] = iconv('cp1251', 'utf-8', $val['msg_rest']);
			}
			
			return $aData;
		}

		public function getSignalById( $nID )	{
			$sQuery = "
				SELECT
					id_msg as id,
					id_sig,
					msgAl as msg_al,
					codeAl as code_al,
					msgRest as msg_rest,
					codeRest as code_rest,
					testFlag as test_flag,
					test,
					IF (is_phone, IF(isCID, 'cid', 'phone'), 'radio') as channel,
					isCID
				FROM messages
				WHERE id_msg = {$nID}
			";

			$aData = $this->select( $sQuery );
			
			foreach ( $aData as &$val ) {
				$val['msg_al'] = iconv('cp1251', 'utf-8', $val['msg_al']);
				$val['msg_rest'] = iconv('cp1251', 'utf-8', $val['msg_rest']);
			}
			
			return $aData;			
		}


		public function getSignalsBySig( $nIDObj, $nIDs )	{
			$sQuery = "
				SELECT
					id_msg as id,
					id_sig,
					msgAl as msg_al,
					codeAl as code_al,
					testFlag as test_flag,
					test,
					DATE_FORMAT(timeAl, '%d.%m.%Y %H:%i:%s') as time_al,
					IF (is_phone, IF(isCID, 'cid', 'phone'), 'radio') as channel,
					isCID
				FROM messages
				WHERE id_sig IN ({$nIDs})
					AND id_obj = {$nIDObj}
					AND flag = 1
				ORDER BY timeAl ASC
			";

			$aData = $this->select( $sQuery );
			
			foreach ( $aData as &$val ) {
				$val['msg_al'] = iconv('cp1251', 'utf-8', $val['msg_al']);
			}
			
			return $aData;			
		}
	}
?>