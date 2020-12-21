<?php

	class ApiSetTechOperation
	{
		function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID' );
			
			$oTechOperations = new DBTechOperations();
			
			$oNomenclatures = new DBNomenclatures();
			$aNomenclatures = $oNomenclatures->getAllNames();
			
			$oResponse->setFormElement( 'form1', 'nomenclatures_all', array(), '' );
			
			if( $nID )
			{
				$aTechOperation = $oTechOperations->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array(), $aTechOperation['name'] );
				$oResponse->setFormElement( 'form1', 'nPrice', array(), $aTechOperation['price'] );
				
				if( $aTechOperation['to_contract'] )
				{
					$oResponse->setFormElement( 'form1', 'nToContract', array( "checked" => "checked" ) );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nToContract', array( "checked" => "" ) );
				}
				if( $aTechOperation['to_arrange'] )
				{
					$oResponse->setFormElement( 'form1', 'nToArrange', array( "checked" => "checked" ) );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nToArrange', array( "checked" => "" ) );
				}
				if( $aTechOperation['cable_operation'] )
				{
					$oResponse->setFormElement( 'form1', 'nCableOperation', array( "checked" => "checked" ) );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nCableOperation', array( "checked" => "" ) );
				}
				
				$aNomenclaturesC = $oNomenclatures->getNomenclaturesByIDOperation( $nID );
				
				$oResponse->setFormElement( 'form1', 'nomenclatures_current', array(), '' );
				foreach( $aNomenclatures as $key => $value )
				{
					if( in_array( $value, $aNomenclaturesC ) )
					{
						$oResponse->setFormElementChild( 'form1', 'nomenclatures_current', array( "value" => $key ), $value );
					}
					else
					{
						$oResponse->setFormElementChild( 'form1', 'nomenclatures_all', array( "value" => $key ), $value );
					}
				}
			}
			else
			{
				foreach( $aNomenclatures as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nomenclatures_all', array( "value" => $key ), $value );
				}
				
				$oResponse->setFormElement( 'form1', 'nPrice', array(), "0.00" );
			}
			
			$oResponse->printResponse();
		}
		
		function save( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID' );
			$sName = Params::get( 'sName' );
			$nPrice = Params::get( 'nPrice' );
			$nToContract = Params::get( 'nToContract' );
			$nToArrange = Params::get( 'nToArrange' );
			$nCableOperation = Params::get( 'nCableOperation' );
			$aNomenclatures_current = Params::get( 'nomenclatures_current' );
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$oTechOperations = new DBTechOperations();
			$aDataS = array();
			
			$aDataS['id'] = $nID;
			$aDataS['name'] = $sName;
			$aDataS['price'] = $nPrice;
			$aDataS['to_contract'] = $nToContract;
			$aDataS['to_arrange'] = $nToArrange;
			$aDataS['cable_operation'] = $nCableOperation;
			
			if( $nCableOperation )
			{
				$aAllOthers = $oTechOperations->select( "SELECT * FROM tech_operations WHERE to_arc = 0 AND id != {$nID}" );
				
				foreach( $aAllOthers as $aOperation )
				{
					if( $aOperation['cable_operation'] == 1 )
					{
						$aOperation['cable_operation'] = 0;
						$oTechOperations->update( $aOperation );
					}
				}
			}
			$oTechOperations->update( $aDataS );
			
			$oTechOperationNomenclatures = new DBTechOperationsNomenclatures();
			$oTechOperationNomenclatures->select( "DELETE FROM tech_operations_nomenclatures WHERE id_operation = {$nID}" );
			
			foreach( $aNomenclatures_current as $value )
			{
				$aData = array();
				if( !empty( $nID ) )
				{
					$aData['id_operation'] = $nID;
				}
				else
				{
					$aData['id_operation'] = $aDataS['id'];
				}
				
				$aData['id_nomenclature'] = $value;
				
				$oTechOperationNomenclatures->update( $aData );
			}
			
			$oResponse->printResponse();
		}
	}

?>