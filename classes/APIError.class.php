<?php

	class APIError
	{
		var $nCode		= 0;
		var $sMsg		= '';	
		var $sFile		= '';
		var $nLine		= 0;
		var $aContext	= array();
		
		function __construct($nCode, $sMsg, $sFile, $nLine, $aContext)
		{
			$this->nCode	= $nCode;
			$this->sMsg		= $sMsg;
			$this->sFile	= $sFile;
			$this->nLine	= $nLine;
			$this->aContext	= $aContext;
		}
	}
	
?>