<?
	define('PPP_ID',			0x01);
	define('PPP_NUM',			0x02);
	define('PPP_CREATED_TIME',	0x04);
	define('PPP_UPDATED_TIME',	0x08);
	
	class DBDataField
	{
		var $sField;
		var $sTable;
		var $nFlag;
		
		function DBDataField($sField, $sTable, $nFlag)
		{
			$this->sField = $sField;
			$this->sTable = $sTable;
			$this->nFlag  = $nFlag;
		}
	}

	class PPP 
	{
		var $aContent = array(
				'id'			=> new DBDataField('id', 'ppp', PPP_ID),
				'num'			=> new DBDataField('num', 'ppp', PPP_NUM),
				'created_time'	=> new DBDataField('created_time', 'ppp', PPP_CREATED_TIME),
				'updated_time'	=> new DBDataField('updated_time', 'ppp', PPP_UPDATED_TIME)
				);
								
		var $aData = array();
		
		function getData( $nFlags )
		{
		}
	}
	
	
	$vArray = getData(PPP_ID|PPP_CREATED_TIME|PPP_UPDATED_TIME);
	
	
?>