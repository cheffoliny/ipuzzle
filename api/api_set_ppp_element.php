<?php

	class ApiSetPPPElement
	{
		public function load( DBResponse $oResponse )
		{
			$nID = 		Params::get( "nID", 0 );
			$nIDPPP = 	Params::get( "nIDPPP", 0 );
			
			$oPPP = 			new DBPPP();
			$oTechLimitCards = 	new DBTechLimitCards();
			$oPPPElement = 		new DBPPPElements();
			$oNomenclatures = 	new DBNomenclatures();
			$oMeasures = 		new DBMeasures();
			
			$aNeededPPP = $oPPP->getRecord( $nIDPPP );
			if( !empty( $aNeededPPP ) )
			{
				$aLimitCard = $oTechLimitCards->getRecord( $aNeededPPP['id_limit_card'] );
				if( !empty( $aLimitCard ) && $aLimitCard['type'] == 'create' )
				{
					$oResponse->setFormElement( 'form1', 'nLCCreateObject', array( "value" => 1 ) );
				}
			}
			
			$oPPPElement->fillFields( $nID, $oResponse );
			
			//Client Owned
			if( $nID )
			{
				$aPPPElement = $oPPPElement->getRecord( $nID );
				
				if( isset( $aPPPElement['client_own'] ) )
				{
					if( $aPPPElement['client_own'] )
					{
						$oResponse->setFormElement( 'form1', 'nClientOwn', array( "checked" => "checked" ) );
					}
					else
					{
						$oResponse->setFormElement( 'form1', 'nClientOwn', array( "checked" => "" ) );
					}
				}
				
				//Take Care of Measure
				$nIDNomenclature = isset( $aPPPElement['id_nomenclature'] ) ? $aPPPElement['id_nomenclature'] : 0;
				if( !empty( $nIDNomenclature ) )
				{
					$sMeasure = $oMeasures->fixMeasureShortening( $oNomenclatures->getNomenclatureMeasure( $nIDNomenclature ) );
				}
				
				if( !empty( $sMeasure ) )
				{
					$oResponse->setFormElement( 'form1', 'sHMeasure', array( 'value' => $sMeasure ) );
				}
				//End Take Care of Measure
			}
			//End Client Owned
			
			$oResponse->printResponse();
		}
		
		public function refresh( DBResponse $oResponse )
		{
			$nIDType = Params::get( 'nIDNomenclatureType', 0 );
			
			$oPPPElement = new DBPPPElements();
			$oPPPElement->refreshNomenclatures( $nIDType, $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function getMeasure( DBResponse $oResponse )
		{
			$nIDNomenclature = Params::get( "nIDNomenclature", 0 );
			$oNomenclatures = new DBNomenclatures();
			$oMeasures = new DBMeasures();
			
			if( !empty( $nIDNomenclature ) )
			{
				$sMeasure = $oMeasures->fixMeasureShortening( $oNomenclatures->getNomenclatureMeasure( $nIDNomenclature ) );
			}
			
			if( !empty( $sMeasure ) )
			{
				$oResponse->setFormElement( 'form1', 'sHMeasure', array( 'value' => $sMeasure ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			global $db_storage;
			
			$aParams = Params::getAll();
			
			//Database Objects
			$oPPPElement = 		new DBPPPElements();			//За обновяване на номенклатурите за заредения ППП.
			$oNomenclatures = 	new DBNomenclatures();			//За извличане на информация от номенклатури.
			$oPPP = 			new DBPPP();					//За извличане на информация от ППП, като ID на лимитна карта.
			$oTechLimitCards = 	new DBTechLimitCards();			//За извличане на информация от Лимитна Карта.
			$oMeasures = 		new DBMeasures();				//За проверка, как е написана бройката в базата.
			//End Database Objects
			
			//Load Parameters
			$nMode = 			Params::get( 'nMode', 0 );
			$nID = 				Params::get( 'nID', 0 );
			$nIDPPP = 			Params::get( 'nIDPPP', 0 );
			
			$nCount = 			Params::get( 'nCount', 0 );
			$nInitCount = 		Params::get( 'nInitCount', 0 );
			
//			$nIDNomenclature = 	Params::get( 'nIDNomenclature', 0 );
			$nIDScheme = 		Params::get( 'nIDScheme', 0 );
			
			$nClientOwn = 		Params::get( 'nClientOwn', 0 );
			$nCallerID =		Params::get( 'nCallerID', 0 );
			//End Load Parameters
			
			//Как е написана бройката в базата. По подразбиране, "бр."
//			$oNomenclatures = new DBNomenclatures();
//			$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			$aElement = array();
			
			//Common Exceptions
//			if( !$nMode )if( empty( $nIDNomenclature ) )throw new Exception( "Няма въведена номенклатура!", DBAPI_ERR_INVALID_PARAM );
			if( $nMode )if( empty( $nIDScheme ) )throw new Exception( "Няма въведена номенклатура!", DBAPI_ERR_INVALID_PARAM );
			//End Common Exceptions
			
			if( !$nMode )
			{
				//Export Nomenclatures
				$aNomenclatures = array();
				$nIndex = 0;
				foreach( $aParams as $key => $value )
				{
					if( substr( $key, 0, 5 ) == "sInfo" )
					{
						$aTemp = array();
						$aTemp = explode( ",", $value );
						
						if( !empty( $aTemp ) )
						{
							if( isset( $aTemp[0] ) )$aNomenclatures[$nIndex]['nIDNomenclature'] = $aTemp[0];
							if( isset( $aTemp[1] ) )$aNomenclatures[$nIndex]['nClientOwn'] = $aTemp[1];
							if( isset( $aTemp[2] ) )$aNomenclatures[$nIndex]['nCount'] = $aTemp[2];
						}
					}
					
					$nIndex++;
				}
				//End Export Nomenclatures
				
				if( empty( $aNomenclatures ) )
				{
					throw new Exception( "Няма въведена номенклатура!", DBAPI_ERR_INVALID_PARAM );
				}
				
				foreach( $aNomenclatures as $value )
				{
					$nIDNomenclature 	= isset( $value['nIDNomenclature'] ) 	? $value['nIDNomenclature'] : 0;
					$nClientOwn 		= isset( $value['nClientOwn'] ) 		? $value['nClientOwn'] 		: 0;
					$nCount 			= isset( $value['nCount'] ) 			? $value['nCount'] 			: 0;
					
					//Common Operations For Mode 1
					$aNomenclature = $oNomenclatures->getRecord( $nIDNomenclature );
					if( !empty( $aNomenclature ) )
					{
						if( $aNomenclature['unit'] == $sDefaultCountMeasure )
						{
							$nCount = round( $nCount );
						}
					}
					
					$aElement = array();
					$aElement['id_ppp'] = 			$nIDPPP;
					$aElement['id_nomenclature'] = 	$nIDNomenclature;
					$aElement['count'] = 			$nCount;
					$aElement['single_price'] = 	$oNomenclatures->getNomenclaturePrice( $nIDNomenclature );
					$aElement['client_own'] = 		$nClientOwn;
					//End Common Operations For Mode 1
					
					//Check Nomenclature Existance
					$aTemp = $oPPPElement->getClientOwnPPPElement( $nIDPPP, $nIDNomenclature, $nClientOwn, $nID );
					if( !empty( $aTemp ) )
					{
						$aElement['id'] = $aTemp['id'];
						$aElement['count'] += $aTemp['count'];
						
						//Check For Object Creation
						$aNeededPPP = $oPPP->getRecord( $nIDPPP );
						if( !empty( $aNeededPPP ) )
						{
							$aLimitCard = $oTechLimitCards->getRecord( $aNeededPPP['id_limit_card'] );
							if( !empty( $aLimitCard ) && $aLimitCard['type'] == 'create' )
							{
								if( $aElement['count'] > $nInitCount && $nCallerID == "1" )throw new Exception( "Невалидно количество!", DBAPI_ERR_INVALID_PARAM );
							}
						}
						//End Check For Object Creation
					}
					else
					{
						$aElement['id'] = 0;
						
						if( !empty( $nID ) )
						{
							//Check For Object Creation
							$aNeededPPP = $oPPP->getRecord( $nIDPPP );
							if( !empty( $aNeededPPP ) )
							{
								$aLimitCard = $oTechLimitCards->getRecord( $aNeededPPP['id_limit_card'] );
								if( !empty( $aLimitCard ) && $aLimitCard['type'] == 'create' )
								{
									if( $nCount > $nInitCount && $nCallerID == "1" )throw new Exception( "Невалидно количество!", DBAPI_ERR_INVALID_PARAM );
								}
							}
							//End Check For Object Creation
						}
					}
					//End Check Nomenclature Existance
					
					//Commit Changes
					$oPPPElement->update( $aElement );
				}
			}
			else
			{
				//Common Operations For Mode 2
				$sQuery = "SELECT * FROM scheme_elements WHERE to_arc = 0 AND id_scheme = {$nIDScheme}";
				
				$aSchemeElements = $oPPPElement->select( $sQuery );
				//End Common Operations For Mode 2
				
				foreach( $aSchemeElements as $aSchemeElement )
				{
					$aElement = array();
					
					$nIDNomenclature = $aSchemeElement['id_nomenclature'];
					$nCount = $aSchemeElement['count'] * $nCount;
					
					//Common Operations For Mode 1
					$aNomenclature = $oNomenclatures->getRecord( $nIDNomenclature );
					if( !empty( $aNomenclature ) )
					{
						if( $aNomenclature['unit'] == $sDefaultCountMeasure )
						{
							$nCount = round( $nCount );
						}
					}
					
					$aElement['id_ppp'] = 			$nIDPPP;
					$aElement['id_nomenclature'] = 	$nIDNomenclature;
					$aElement['count'] = 			$nCount;
					$aElement['single_price'] = 	$oNomenclatures->getNomenclaturePrice( $nIDNomenclature );
					$aElement['client_own'] = 		$nClientOwn;
					//End Common Operations For Mode 1
					
					//Check Nomenclature Existance
					$aTemp = $oPPPElement->getClientOwnPPPElement( $nIDPPP, $nIDNomenclature, $nClientOwn, $nID );
					if( !empty( $aTemp ) )
					{
						$aElement['id'] = $aTemp['id'];
						$aElement['count'] += $aTemp['count'];
						
						//Check For Object Creation
						$aNeededPPP = $oPPP->getRecord( $nIDPPP );
						if( !empty( $aNeededPPP ) )
						{
							$aLimitCard = $oTechLimitCards->getRecord( $aNeededPPP['id_limit_card'] );
							if( !empty( $aLimitCard ) && $aLimitCard['type'] == 'create' )
							{
								if( $aElement['count'] > $nInitCount && $nCallerID == "1" )throw new Exception( "Невалидно количество!", DBAPI_ERR_INVALID_PARAM );
							}
						}
						//End Check For Object Creation
					}
					else
					{
						$aElement['id'] = 0;
						
						if( !empty( $nID ) )
						{
							//Check For Object Creation
							$aNeededPPP = $oPPP->getRecord( $nIDPPP );
							if( !empty( $aNeededPPP ) )
							{
								$aLimitCard = $oTechLimitCards->getRecord( $aNeededPPP['id_limit_card'] );
								if( !empty( $aLimitCard ) && $aLimitCard['type'] == 'create' )
								{
									if( $nCount > $nInitCount && $nCallerID == "1" )throw new Exception( "Невалидно количество!", DBAPI_ERR_INVALID_PARAM );
								}
							}
							//End Check For Object Creation
						}
					}
					//End Check Nomenclature Existance
					
					//Commit Changes
					$oPPPElement->update( $aElement );
				}
			}
			
			//Delete The Old Object
			if( !empty( $nID ) )
			{
				$oPPPElement->delete( $nID );
				$nID = 0;
			}
			
			$oResponse->printResponse();
		}
	}

?>