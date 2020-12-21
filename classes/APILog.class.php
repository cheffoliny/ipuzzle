<?php
	include_once("APIError.class.php");
	
	class APILog
	{
		static $aLogs = array();
		
		function __construct()
		{
			set_error_handler( array('APILog', 'ErrorHandler') );
		}
		
		static function ErrorHandler($nCode, $sMsg, $sFile, $nLine, $aContext = NULL)
		{
			if( $nCode != E_STRICT )
				array_push( APILog::$aLogs, new APIError( DBAPI_ERR_PHP_INTERNAL, $sMsg.' ('.$nCode.')', $sFile, $nLine, $aContext));
		}
		
		static function Log( $nCode, $sMsg = NULL, $sFile = NULL, $nLine = NULL )
		{
			if( is_array( $sMsg ) )
				$sMsg = print_r($sMsg, true);
				
			if( empty( $sFile ) && empty( $nLine ) ) 
			{
			    $aTrace = debug_backtrace();
			    
			    if( !empty( $aTrace ) && !empty( $aTrace[ 0 ] ) )
			    {
			        if( !empty( $aTrace[ 0 ]["file"] ) ) {
			             $sFile = $aTrace[ 0 ]["file"];
			        }
			        if( !empty( $aTrace[ 0 ]["line"] ) ) {
			             $nLine = $aTrace[ 0 ]["line"];
			        }
			    }
			}
				
			array_push(APILog::$aLogs, new APIError($nCode, $sMsg, $sFile, $nLine, NULL));
		}
	}
	
?>