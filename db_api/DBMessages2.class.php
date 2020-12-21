<?php
	class DBMessages2 extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'messages');
		}
		
		public function insertMessages( $nIDTemplet, $nIDObject) {

			$sQuery = "
				INSERT 	INTO messages 
								(id_sig,
								id_obj,
								isCID,
								id_cid,
								zone,
								codeAl,
								msgAl,
								codeRest,
								msgRest,
								timeAl,
								flag,
								testFlag,
								test,
								restoreAfter,
								restoreTime,
								playWeek,
								playFrom,
								playTo,
								is_phone)
				SELECT 
						tv.id_sig,
						'{$nIDObject}',
						tv.isCID,
						tv.id_cid,
						tv.zone,
						tv.codeAl,
						tv.msgAl,
						tv.codeRest,
						tv.msgRest,
						tv.timeAl,
						tv.flag,
						tv.testFlag,
						tv.test,
						tv.restoreAfter,
						tv.restoreTime,
						tv.playWeek,
						tv.playFrom,
						tv.playTo,
						tv.is_phone
				FROM templets_val tv
				WHERE tv.id_templet = {$nIDTemplet}
			";
			
			$this->oDB->Execute( $sQuery );
		}

		public function del( $nID ) {
			global $db_telepol;
			
			$sQuery = "
				DELETE FROM messages
				WHERE id_msg = {$nID}
			";
			
			$db_telepol->Execute( $sQuery );
		}

		public function del2( $nID ) {
			global $db_telepol;
			
			if ( !empty($nID) ) {
				$sQuery = "
					DELETE FROM messages
					WHERE id_msg IN ({$nID})
				";
				
				$db_telepol->Execute( $sQuery );
			}
		}

	}
?>