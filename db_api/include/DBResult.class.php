<?php

	class DBResult
	{
		var $aAccessLevels		= array();
		var $aTitles			= array();
		var $aFields			= array();	
		var $aData				= array();	//array type key-value
		var $aTotal				= array();	//array type key-value
		var $oPaging			= NULL;		//type DBPaging
		var $aDataAttributes	= array();
		var $aRowAttributes		= array();
		
		function __construct() 
		{
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				$this->aAccessLevels = $_SESSION['userdata']['access_right_levels'];
		}
	}

	
?>