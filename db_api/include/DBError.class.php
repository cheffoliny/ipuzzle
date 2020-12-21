<?php
	require_once("db_include.inc.php");
	
	
	class DBError 
	{
		var $nCode = DBAPI_ERR_SUCCESS;
		var $sMsg;
		
		function __construct($nCode	= DBAPI_ERR_SUCCESS, $sMsg = NULL, $sFile = NULL, $nLine = NULL)
		{
			$this->setError($nCode, $sMsg, $sFile, $nLine);
		}
		
		function getCode() {
			return $this->nCode;
		}
		
		function setError($nCode = DBAPI_ERR_SUCCESS, $sMsg = NULL, $sFile = NULL, $nLine = NULL)
		{
			if( ( $nCode == DBAPI_ERR_SUCCESS ) && ( $this->nCode != $nCode ) )
				return;
				
			$this->nCode = $nCode;
			$this->sMsg = empty( $sMsg )? "Грешка при изпълнение на операцията!" : $sMsg;
			
			if( defined('EOL_DEBUG') && EOL_DEBUG )
			{
				if( !empty( $sFile ) )
				{
					$this->sMsg .= sprintf("\n\nFile: %s", $sFile);
					
					if( !empty( $nLine ) )
						$this->sMsg .= sprintf("\nLine: %u", $nLine);
				}
			}
		}
	}
?>