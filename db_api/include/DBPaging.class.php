<?php
	require_once("db_include.inc.php");
	
	
	class DBPaging 
	{
		var $nRowLimit	=  0;
		var $nRowTotal	=  0;
		var $nCurPage	=  0;
		var $nPageTotal =  0;
		var $sSortField = "";
		var $nSortType	= DBAPI_SORT_ASC;
		
		function __construct()
		{
			global $_SESSION;
			$this->nRowLimit = $_SESSION['userdata']['row_limit'];
		}
	}
	
?>
