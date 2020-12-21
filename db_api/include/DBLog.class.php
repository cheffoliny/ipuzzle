<?php

	function _DBLog($sMsg, $bNewLine = TRUE)
	{
		DBLog::Log($sMsg, $bNewLine);
	}
	
	class DBLog
	{
		static $aLines = array();
		
		function __construct()
		{
			if( !defined('ADODB_OUTP') )
				define('ADODB_OUTP', '_DBLog');
		}
		
		static function Log($sMsg, $bNewLine = TRUE)
		{
			$sMsg = htmlspecialchars( $sMsg );
			
			if( $bNewLine || empty( $aLines ))
			{
				DBLog::$aLines[] = $sMsg;
			}
			else
			{
				DBLog::$aLines[ count( DBLog::$aLines ) - 1 ] .= $sMsg;
			}
		}
	}

?>