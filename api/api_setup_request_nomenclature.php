<?php

	class ApiSetupRequestNomenclature
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oRequestNomenclatures = new DBRequestNomenclatures();
			$oRequestNomenclatures->fillFields( $nID, $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function refresh( DBResponse $oResponse )
		{
			$nIDType = Params::get('nIDNomenclatureType', 0);
			
			$oRequestNomenclatures = new DBRequestNomenclatures();
			$oRequestNomenclatures->refreshNomenclatures( $nIDType, $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			global $db_storage;
			
			$oRequestNomenclatures = new DBRequestNomenclatures();
			$oNomenclatures = new DBNomenclatures();
			
			$nMode = Params::get('nMode', 0);
			$nID = Params::get('nID', 0);
			
			if( !$nMode )
			{
				$nIDRequest = Params::get('nIDRequest', 0);
				$nIDNomenclature = Params::get('nIDNomenclature', 0);
				
				if( empty( $nIDNomenclature ) )
					throw new Exception( "Няма въведена номенклатура!", DBAPI_ERR_INVALID_PARAM );
				
				$nCount = Params::get('nCount', 0);
				if( empty( $nCount ) || $nCount < 0 || (int) $nCount == 0 )
					throw new Exception( "Въведете количество!", DBAPI_ERR_INVALID_PARAM );
				
				$aData = array();
				$aData['id'] = $nID;
				$aData['id_request'] = $nIDRequest;
				$aData['id_nomenclature'] = $nIDNomenclature;
				$aData['count'] = $nCount;
				
				$oRequestNomenclatures->update( $aData );
			}
			else
			{
				$nIDScheme = Params::get('nIDScheme', 0);
				
				if( empty( $nIDScheme ) )
					throw new Exception( "Няма въведена номенклатура!" , DBAPI_ERR_INVALID_PARAM );

				$sQuery = "SELECT * FROM scheme_elements
						WHERE to_arc=0 AND id_scheme={$nIDScheme}";
				
				$aSchemeElements = $oRequestNomenclatures->select( $sQuery );
				
				if( !empty($nID) )
				{
					$oRequestNomenclatures->delete( $nID );
					$nID = 0;
				}
				$nIDRequest = Params::get('nIDRequest', 0);
				foreach( $aSchemeElements as $aSchemeElement )
				{
					$nIDNomenclature = $aSchemeElement['id_nomenclature'];
					$nCount = $aSchemeElement['count'] * Params::get('nCount', 0);
					if( empty( $nCount ) || $nCount < 0 || (int) $nCount == 0 )
						throw new Exception( "Въведете количество!", DBAPI_ERR_INVALID_PARAM );
					
					$aElement = $oRequestNomenclatures->getRequestElement( $nIDRequest, $nIDNomenclature );
					if( empty($aElement) )
					{
						$aElement['id_request'] = $nIDRequest;
						$aElement['id_nomenclature'] = $nIDNomenclature;
						$aElement['count'] = $nCount;
					}
					else
					{
						$aElement['count'] += $nCount;
					}
					
					$oRequestNomenclatures->update( $aElement );
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>