<?php

	require_once( "./vo/telenet/api/FlexResponse.php" );
	require_once( "./vo/telenet/api/FlexVar.php" );
	require_once( "./vo/telenet/api/FlexControl.php" );
	
	if( !isset( $_SESSION ) )
	{
		session_start();
	}
	
	restore_include_path();
	$aPath = pathinfo( $_SERVER["SCRIPT_FILENAME"] );
	set_include_path( get_include_path() . PATH_SEPARATOR . $aPath["dirname"] . "/../" . PATH_SEPARATOR . $aPath["dirname"] . "/../../" );
	
	require_once( "config/function.autoload.php" );
	require_once( "include/adodb/adodb-exceptions.inc.php" );
	require_once( "config/connect.inc.php" );
	require_once( "include/general.inc.php" );
	
	class setup_code_leave
	{
		public function __construct()
		{
			global $oResponse;
			
			$oResponse = new DBResponse();
		}
		
		public function init()
		{
			global $oResponse;
			
			$aData = array();
			
			$oDBCodeLeave = new DBCodeLeave();
			$aData = $oDBCodeLeave->getResultData();
			
			$oResponse->setFlexVar( "aData", $aData );
			
			return $oResponse->toAMF();
		}
		
		public function save( $aParams )
		{
			global $oResponse;
			
			//if( !$handle = fopen( "Test.txt", "w+" ) )exit;
			
			$oDBCodeLeave = new DBCodeLeave();
			
			foreach( $aParams['result_data'] as $nKey => $aValue )
			{
				if( $aValue['isEditted'] || $aValue['id'] == 0 )
				{
					$oDBCodeLeave->update( $aValue );
				}
			}
			
			if( isset( $aParams['sDelIDs'] ) && !empty( $aParams['sDelIDs'] ) )
			{
				$aIDsToDel = explode( ",", $aParams['sDelIDs'] );
				
				foreach( $aIDsToDel AS $nIDToDel )
				{
					$oDBCodeLeave->delete( $nIDToDel );
				}
			}
			
			//fclose( $handle );
			
			return $this->init();
		}
	}

?>