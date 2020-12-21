<?php
	
	class DBSuggest
	{
		var $aResult = array();
		
		function assign( $aResult )
		{
			if( is_array( $aResult ) )
				$this->aResult = $aResult;
		}
		
		function toJS()
		{
			$sResult = '';
			
			if( !empty( $this->aResult ) )
			{
				$aResult = javascriptescape_deep( $this->aResult );
				
				$aKey		= array();
				$aHtmlValue = array();
				$aValue		= array();
				
				foreach( $aResult as $aEntry )
				{
					$aKey[]			= $aEntry['key'];
					$aHtmlValue[]	= $aEntry['html_value'];
					$aValue[]		= $aEntry['value'];
				}
				
				$sResult .= sprintf("var key = new Array('%s');"		, implode("', '", $aKey			));
				$sResult .= sprintf("var html_value = new Array('%s');"	, implode("', '", $aHtmlValue	));
				$sResult .= sprintf("var value = new Array('%s');"		, implode("', '", $aValue		));
			}
			else
			{
				$sResult = "var key = new Array();var html_value = new Array();var value = new Array();";
			}
			
			print $sResult;
		}
	}

?>