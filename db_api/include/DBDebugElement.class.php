<?php

	class DBDebugElement
	{
		var $sInfo;
		var $sFile;
		var $nLine;
		
		function __construct($sInfo, $sFile = NULL, $nLine = NULL)
		{
			$this->sInfo = $sInfo;
			$this->sFile = $sFile;
			$this->nLine = $nLine;
		}
	}


?>