<?php

	class ApiObjectStoreState
	{
		public function result( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			
			if( !empty( $nID ) )
			{
				$oPPP = new DBPPP();
				$bHadRowCount = array_key_exists( 'row_limit', $_SESSION['userdata'] );
				$nRowCount = $bHadRowCount ? $_SESSION['userdata']['row_limit'] : NULL;
				$_SESSION['userdata']['row_limit'] = 200;
				try
				{
					$oPPP->getReportByObject( $nID, $oResponse );
				}
				finally
				{
					if( $bHadRowCount )
						$_SESSION['userdata']['row_limit'] = $nRowCount;
					else
						unset( $_SESSION['userdata']['row_limit'] );
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>
