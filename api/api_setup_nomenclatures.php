<?php

	require_once( 'include/parse_excel/reader.php' );
	require_once( 'include/unzip.inc.php' );
	require_once( 'include/import.inc.php' );
	
	class ApiSetupNomenclatures
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oNomenclatures = new DBNomenclatures();
			
			//Извличане на йерархията на Типовете Номенклатури
			$oResponse->setFormElement( 'form1', 'nIDNomenclatureType' );
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => 0 ), "--- Всички ---" );
			
			$nSelect = isset( $aParams['nIDNomenclatureType'] ) ? $aParams['nIDNomenclatureType'] : 0;
			$oNomenclatures->getHierarchy( 0, 0, $nSelect, $oResponse );
			
			$oNomenclatures->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Номенлатури", "nomenclatures" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oNomenclatures = new DBNomenclatures();
			$aRes=$oNomenclatures->getCountNomenclaturesByID($nID);
			if (empty($aRes['count'])) {
				$oNomenclatures->delete( $nID );	
			}
			 else if ($aRes['places']==1) throw new Exception("Не може да премахнете записа! \nНоменклатурата се използва на {$aRes['places']} място!");
				else throw new Exception("Не може да премахнете записа! \nНоменклатурата се използва на {$aRes['places']} места!");
			$oResponse->printResponse();
		}
		
		public function import( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( !empty( $aParams['file_name'] ) && !empty( $aParams['file_type'] ) )
			{
				$aFile = array();
				$aFile['tmp_name'] 	= $aParams['file_name'];
				$aFile['type'] 		= $aParams['file_type'];
				
				$aData = array();
				$sError = GetImportedData( $aFile, $aData );
				
				$aErrorMsg = array();
				if( empty( $sError ) )
					$this->ProcessData( $aData, $aErrorMsg );
				else
					$aErrorMsg[]['msg'] = $sError;
				
				if( function_exists( 'unlink' ) )
					unlink( $aFile['tmp_name'] );
				else
					exec( "rm {$aFile['tmp_name']}" );
				
				$oResponse->setField( 'msg', "съобщение" );
				$oResponse->setField( 'row', "ред" );
				$oResponse->setField( 'col', "колона" );
				
				if( empty( $aErrorMsg ) )
				{
					$aErrorMsg[] = array(
						'msg' => " Операцията премина успешно! ",
						'row' => 0,
						'col' => 0
					);
				}
				
				$oResponse->setData( $aErrorMsg );
			}
			
			$oResponse->printResponse();
		}
		
		public function ProcessData( $aData, &$aErrorMsg )
		{
			$oNomenclatures = 		new DBNomenclatures();
			$oNomenclatureTypes = 	new DBNomenclatureTypes();
			
			$aHeader = array();
			for( $i = 1; $i <= $aData['numCols']; $i++ )
				$aHeader[ $aData['cells'][3][$i] ] = $i;
			
			$nIDExist = 0;
			for( $i = 4; $i <= $aData['numRows']; $i++ )
			{
				//Parameters
				//Nomenclature Name
				if( isset( $aHeader['Име'] ) )
				{
					$sName = $aData['cells'][$i][ $aHeader['Име'] ];
				}
				else
				{
					throw new Exception( "Във файла няма колона, задаваща Име!", DBAPI_ERR_UNKNOWN );
				}
				if( empty( $sName ) )
				{
					$aErrorMsg[] = array(
						'msg' => "Невалидно Име!",
						'row' => $i,
						'col' => $aHeader['Име']
					);
					continue;
				}
				else
				{
					$nIDExist = $oNomenclatures->getIDByName( $sName );
				}
				//End Nomenclature Name
				
				//Nomenclature Type
				if( isset( $aHeader['Тип'] ) )
				{
					$sType = $aData['cells'][$i][ $aHeader['Тип'] ];
				}
				else
				{
					throw new Exception( "Във файла няма колона, задаваща Тип!", DBAPI_ERR_UNKNOWN );
				}
				$aType = $oNomenclatureTypes->getIDByName( $sType );
				if( !empty( $aType ) && isset( $aType['id'] ) )
				{
					$nType = $aType['id'];
				}
				else $nType = 0;
				
				if( empty( $nType ) )
				{
					$aErrorMsg[] = array(
						'msg' => "Невалиден Тип Номенклатура!",
						'row' => $i,
						'col' => $aHeader['Тип']
					);
					continue;
				}
				//End Nomenclature Type
				
				//Nomenclature Unit
				if( isset( $aHeader['Единица'] ) )
				{
					$sUnit = $aData['cells'][$i][ $aHeader['Единица'] ];
					
					$oMeasures = new DBMeasures();
					$bMeasureExist = $oMeasures->doesMeasureExist( $sUnit );
					if( !$bMeasureExist )
					{
						//Reject Unit if it does not exist.
						$sUnit = "";
					}
				}
				else
				{
					throw new Exception( "Във файла няма колона, задаваща Единица!", DBAPI_ERR_UNKNOWN );
				}
				if( empty( $sUnit ) )
				{
					$aErrorMsg[] = array(
						'msg' => "Невалидна Единица!",
						'row' => $i,
						'col' => $aHeader['Единица']
					);
					continue;
				}
				//End Nomenclature Unit
				
				//Nomenclature Price
				if( isset( $aHeader['Цена'] ) )
				{
					$nLastPrice = (float) $aData['cells'][$i][ $aHeader['Цена'] ];
				}
				else
				{
					throw new Exception( "Във файла няма колона, задаваща Цена!", DBAPI_ERR_UNKNOWN );
				}
				//EndNomenclature Price
				//End Parameters
				
				//Set The Row
				$aNomenclature = array();
				$aNomenclature['id'] 				= $nIDExist;
				$aNomenclature['name'] 				= $sName;
				$aNomenclature['id_type'] 			= $nType;
				$aNomenclature['unit'] 				= $sUnit;
				$aNomenclature['last_price'] 		= $nLastPrice;
				
				if( $oNomenclatures->update( $aNomenclature ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => " Проблем при запазване на номенклатура",
						'row' => $i,
						'col' => 0
					);
				}
				//End Set The Row
			}
		}
	}

?>