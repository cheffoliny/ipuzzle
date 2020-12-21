<?php

	class ApiSetupNomenclatureTypes
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oNomenclatureTypes = new DBNomenclatureTypes();
			$oNomenclatureTypes->getReport( $aParams, $oResponse );
			 
			$oResponse->printResponse( "Типове номенклатури", "nomenclature_types" );
		
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oNomenclatureTypes = new DBNomenclatureTypes();
			$oNomenclatures = new DBNomenclatures();
			
			//Delete Type
			$oNomenclatureTypes->delete( $nID );
			
			//Delete Subtypes
			$aSubTypes = $oNomenclatureTypes->putIDListInArray( $oNomenclatureTypes->searchIDsDeep( $nID ) );
			if( !empty( $aSubTypes ) )
			{
				foreach( $aSubTypes as $nIDSubType )
				{
					if( !empty( $nIDSubType ) )
					{
						$oNomenclatureTypes->delete( $nIDSubType );
						
						//Delete Nomenclatures Of Subtype (If Any)
						$aNomenclatures = $oNomenclatures->getNomenclaturesOfType( $nIDSubType );
						if( !empty( $aNomenclatures ) )
						{
							foreach( $aNomenclatures as $aNomenclature )
							{
								$nIDNomenclature = $aNomenclature['id'];
								if( !empty( $nIDNomenclature ) )
								{
									$oNomenclatures->delete( $nIDNomenclature );
								}
							}
						}
						//End Delete Nomenclatures Of Subtype (If Any)
					}
				}
			}
			//End Delete Subtypes
			
			//Delete Nomenclatures Of Type
			$aNomenclatures = $oNomenclatures->getNomenclaturesOfType( $nID );
			if( !empty( $aNomenclatures ) )
			{
				foreach( $aNomenclatures as $aNomenclature )
				{
					$nIDNomenclature = $aNomenclature['id'];
					if( !empty( $nIDNomenclature ) )
					{
						$oNomenclatures->delete( $nIDNomenclature );
					}
				}
			}
			//End Delete Nomenclatures Of Type
			
			$oResponse->printResponse();
		}
	}

?>