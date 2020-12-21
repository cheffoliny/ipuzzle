<?php

	class ApiSchemes
	{
		public function result( DBResponse $oResponse )
		{
			$oDBSchemes = new DBSchemes();
			$oDBSchemes->getReport( $oResponse );
			
			$oResponse->printResponse( "Шаблони", "schemes" );
		}
		
		function delete() 
		{
			$nID = Params::get( 'nID' );
			$oDBScheme = new DBSchemes();
			$oSchemeElements = new DBSchemeElements();
			
			$oDBScheme->delete( $nID );
			$aSchemeElements = $oSchemeElements->select( "SELECT * FROM scheme_elements WHERE to_arc = 0 AND id_scheme = {$nID}" );
			if( !empty( $aSchemeElements ) )
			{
				foreach( $aSchemeElements as $aSchemeElement )
				{
					$oSchemeElements->delete( $aSchemeElement['id'] );
				}
			}
		}
	}

?>