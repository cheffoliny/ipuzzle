<?php

	$nCount = 0;
	
	class ApiSetSetupNomenclatures
	{
		
		public function load( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$nID = Params::get( "nID", 0 );
			
			$oNomenclatures = new DBNomenclatures();
			$oNomenclatures->setFormElements( $aParams, $oResponse );
			
			$oResponse->printResponse();
		}
	
		public function save( DBResponse $oResponse )
		{
			$sName 	= 			Params::get("sName");
			$sUnit 	= 			Params::get("sUnit");
			$nPrice = 			Params::get("nPrice", 0);
			$nType 	= 			Params::get("nIDNomenclatureType");
			
			if( empty( $sName ) )
				throw new Exception("Въведете наименование!", DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $sUnit ) )
				throw new Exception("Въведете единица за измерване!", DBAPI_ERR_INVALID_PARAM);
			
			$aData = array();
			$aData['id'] = Params::get( 'nID', 0 );
			$aData['id_type'] = $nType;
			$aData['name'] = $sName;
			$aData['unit'] = $sUnit;
			$aData['last_price'] = $nPrice;
			
			$oNomenclatures = new DBNomenclatures();
			$oNomenclatures->update( $aData );
		}
	}

?>