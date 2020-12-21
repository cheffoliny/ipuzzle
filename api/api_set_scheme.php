<?php

	class ApiSetScheme
	{
		function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID' );
			
			$oDBScheme = new DBSchemes();
			$aDBScheme = $oDBScheme->getRecord( $nID );
			
			$oResponse->setFormElement( 'form1', 'name', array(), $aDBScheme['name'] );
			
			$oDBNomenclatures = new DBNomenclatures();
			$aDBNomenclatures = $oDBNomenclatures->getAllNames();
			
			$oResponse->setFormElement( 'form1', 'nomenclatures_all', array(), '' );
			foreach( $aDBNomenclatures as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nomenclatures_all', array_merge( array( "value" => $key ) ), $value );
			}
			
			$aDBNomenclaturesC = $oDBNomenclatures->getNomenclaturesByIDScheme( $nID );
			
			$oResponse->setFormElement( 'form1', 'nomenclatures_current', array(), '' );
			$oResponse->setFormElement( 'form1', 'nIDDetector', array(), '' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDDetector', array( "value" => 0 ), "-- Не е Посочена --" );
			
			//APILog::Log(0, $aDBNomenclaturesC);
			
			/*foreach($aDBNomenclaturesC as $key => $value)
			{
				while($value[count]--)
				$oResponse->setFormElementChild('form1', 'nomenclatures_current', array_merge(array("value"=>$key)), $value[name]);				
			}*/
			
			$aAddedIndexes = array();
			$aSortedNomenclatures = array();
			foreach( $aDBNomenclaturesC as $keys => $values )
			{
				$br = 0;
				
				foreach( $values as $key => $value )
				{
					if( $key == 'count' )
					{
						$br = $value;
					}
					else
					{
						while( $br-- )
							$oResponse->setFormElementChild( 'form1', 'nomenclatures_current', array_merge( array( "value" => $keys ) ), $value );
					}
				}
				
				if( !in_array( $keys, $aAddedIndexes ) )
				{
					//Fill Select With Nomenclatures
					$aSortedNomenclatures[$keys] = $values['name'];
					$aAddedIndexes[] = $keys;
				}
			}
			asort( $aSortedNomenclatures );
			
			foreach( $aSortedNomenclatures as $nIDNomenclature => $sNameNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDDetector', array( "value" => $nIDNomenclature ), $sNameNomenclature );
			}
			
			if( isset( $aDBScheme['id_detector'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDDetector', "value", $aDBScheme['id_detector'] );
				
				if( $aDBScheme['id_detector'] )
				{
					$oResponse->setFormElement( 'form1', 'nDefault', array( "checked" => "checked" ) );
					$oResponse->setFormElementAttribute( 'form1', 'nIDDetector', "disabled", "" );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nDefault', array( "checked" => "" ) );
					$oResponse->setFormElementAttribute( 'form1', 'nIDDetector', "disabled", "disabled" );
				}
			}
			
			if( !$nID )
			{
				$oResponse->setFormElement( 'form1', 'nDefault', array( "checked" => "" ) );
				$oResponse->setFormElementAttribute( 'form1', 'nIDDetector', "disabled", "disabled" );
			}
			
			$oResponse->printResponse();
		}
		
		function refreshDetectors( DBResponse $oResponse )
		{
			$oNomenclatures = new DBNomenclatures();
			$aDetectors = Params::get( "nomenclatures_current", array() );
			
			$oResponse->setFormElement( 'form1', 'nIDDetector', array(), '' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDDetector', array( "value" => 0 ), "-- Не е Посочена --" );
			
			$aAddedIndexes = array();
			$aSortedNomenclatures = array();
			foreach( $aDetectors as $nIDDetector )
			{
				$aDetector = $oNomenclatures->getRecord( $nIDDetector );
				
				if( !empty( $aDetector ) )
				{
					if( !in_array( $aDetector['id'], $aAddedIndexes ) )
					{
						//Fill Select With Nomenclatures
						$aSortedNomenclatures[ $aDetector['id'] ] = $aDetector['name'];
						$aAddedIndexes[] = $aDetector['id'];
					}
				}
			}
			asort( $aSortedNomenclatures );
			
			foreach( $aSortedNomenclatures as $nIDNomenclature => $sNameNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDDetector', array( "value" => $nIDNomenclature ), $sNameNomenclature );
			}
			
			$oResponse->printResponse();
		}
		
		function save( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID' );
			$name = Params::get( 'name' );
			$aNomenclatures_current = Params::get( 'nomenclatures_current' );
			
			$nIDDetector = Params::get( 'nIDDetector' );
			$nDefault = Params::get( 'nDefault' );
			
			if( empty( $name ) )
			{
				throw new Exception( "Въведете име на шаблон!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( $nDefault )
			{
				if( empty( $nIDDetector ) )
				{
					throw new Exception( "Въведете номенклатура детектор!", DBAPI_ERR_INVALID_PARAM );
				}
			}
			
			$oDBSchemes = new DBSchemes();
			$aDataS = array();
			
			$aDataS['id'] = $nID;
			$aDataS['name'] = $name;
			$aDataS['id_detector'] = $nIDDetector;
			
			if( !empty( $nIDDetector ) )
			{
				//Clear All Other Defaults
				$aSchemes = $oDBSchemes->getAllSchemes();
				if( !empty( $aSchemes ) )
				{
					foreach( $aSchemes as $aScheme )
					{
						if( $aScheme['id'] != $nID && $aScheme['id_detector'] !=0 )
						{
							$aScheme['id_detector'] = 0;
							$oDBSchemes->update( $aScheme );
						}
					}
				}
			}
			
			$oDBSchemes->update( $aDataS );
		    
			//APILog::Log(0, $aDataS);
			
			$oDBSchemeElements = new DBSchemeElements();
			$oDBSchemeElements->toArc( $nID );
			
			$aNomenclatures = array_count_values( $aNomenclatures_current );
			
			//APILog::Log(0, $aNomenclatures);
			
			foreach( $aNomenclatures as $key => $value )
			{
				$aData = array();
				if( !empty( $nID ) )
				{
					$aData['id_scheme'] = $nID;
				}
				else
				{
					$aData['id_scheme'] = $aDataS['id'];
				}
				
				$aData['id_nomenclature'] = $key;
				$aData['count'] = $value;
				
				$oDBSchemeElements->update( $aData );
			}
			
			$oResponse->printResponse();
		}
	}

?>