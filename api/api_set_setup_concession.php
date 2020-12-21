<?php

	class ApiSetSetupConcession
	{
		public function get( DBResponse $oResponse )
		{
			$aParams 	= Params::getAll();
			$nID 		= Params::get( "nID", 0 );
			
			$oDBConcession 	= new DBConcession();
			//$oDBNomenclaturesEarnings 	= new DBNomenclaturesEarnings();
			$oServices		= new DBNomenclaturesServices();
			$aServices		= array();
			
			//Fill Nomenclatures
			//$aNomenclatures = $oDBNomenclaturesEarnings->getAllWithCode();
			$aNomenclatures	= $oServices->getAll();
			
			$oResponse->setFormElement( "form1", "nIDNomenclatureEarning" );
			$oResponse->setFormElementChild( "form1", "nIDNomenclatureEarning", array( "value" => 0 ), "-- Изберете --" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( "form1", "nIDNomenclatureEarning", array( "value" => $aNomenclature['id'] ), $aNomenclature['name'] );
			}
			
			if( !isset( $aParams['nIDNomenclatureEarning'] ) && !empty( $aParams['nIDNomenclatureEarning'] ) )
			{
				$oResponse->setFormElementAttribute( "form1", "nIDNomenclatureEarning", "value", $aParams['nIDNomenclatureEarning'] );
			}
			//End Fill Nomenclatures
			
			if( !empty( $nID ) )
			{
				$aConcession = $oDBConcession->getRecord( $nID );
				if( !empty( $aConcession ) )
				{
					$oResponse->setFormElement( "form1", "sName", 			array( "value" => $aConcession["name"] ) 			);
					$oResponse->setFormElement( "form1", "nMonthsCount", 	array( "value" => $aConcession["months_count"] ) 	);
					$oResponse->setFormElement( "form1", "nPercent", 		array( "value" => $aConcession["percent"] ) 		);
					
					$oResponse->setFormElementAttribute( "form1", "nIDNomenclatureEarning", "value", $aConcession['id_service'] );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$oDBConcession = new DBConcession();
			
			//Params
			$nID 				= Params::get( "nID", 0 );
			$sName 				= Params::get( "sName", "" );
			$nIDNomenclature 	= Params::get( "nIDNomenclatureEarning", 0 );
			$nMonthsCount 		= Params::get( "nMonthsCount", 0 );
			$nPercent 			= Params::get( "nPercent", 0 );
			//End Params
			
			//Validation
			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nIDNomenclature ) )
				throw new Exception( "Въведете услуга!", DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nMonthsCount ) )
				throw new Exception( "Въведете брой месеци!", DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nPercent ) || $nPercent < 0 || $nPercent > 100 )
				throw new Exception( "Въведете валиден процент!", DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nID ) && !$oDBConcession->isMonthsCountUnique( $nMonthsCount ) )
			{
				throw new Exception( "Вече съществува такава отстъпка!", DBAPI_ERR_INVALID_PARAM );
			}
			//End Validation
			
			//Data Array
			$aData = array();
			$aData["id"] 				= $nID;
			$aData["name"] 				= $sName;
			$aData["id_service"] 		= $nIDNomenclature;
			$aData["months_count"] 		= $nMonthsCount;
			$aData["percent"] 			= $nPercent;
			//End Data Array
			
			$oDBConcession->update( $aData );
		}
	}
	
?>