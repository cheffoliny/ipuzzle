<?php

	class ApiSetSetupRequest
	{
		public function refreshStoragehouses( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oRequestNomenclatures = new DBRequestNomenclatures();
			
			$nSelectedStorage = $oRequestNomenclatures->getStoragehouses( $aParams, $oResponse, 0 );
			$oRequestNomenclatures->getMOL( $aParams, $oResponse, $nSelectedStorage );
			
			$oResponse->printResponse();
		}
		
		public function refreshMOL( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oRequestNomenclatures = new DBRequestNomenclatures();
			
			$oRequestNomenclatures->getMOL( $aParams, $oResponse, 0 );
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$nID = Params::get( 'nID', '' );
			
			$oStoragehouses = new DBStoragehouses();
			$oRequestNomenclatures = new DBRequestNomenclatures();
			$oRequests = new DBRequests();
			
			// Установяване на складовете, на които логнатия потребител е МОЛ
			$nReceiveUser = $_SESSION['userdata']['id_person'];
			$aReceiveStoragehouse = $oStoragehouses->getByMOLID( $_SESSION['userdata']['id_person'] );
			
			$oResponse->setFormElement( 'form1', 'nIDToStoragehouse' );
			foreach( $aReceiveStoragehouse as $aStoragehouse )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDToStoragehouse', array( 'value' => $aStoragehouse['id'] ), $aStoragehouse['name'] );
			}
			
			$nSelectedOffice = $oRequestNomenclatures->getOffices( $oResponse );
			$nSelectedStorage = $oRequestNomenclatures->getStoragehouses( $aParams, $oResponse, $nSelectedOffice );
			$oRequestNomenclatures->getMOL( $aParams, $oResponse, $nSelectedStorage );
			
			if( $nID )
			{
				$aRequest = $oRequests->getRecord( $nID );
				$nIDReceiveStorage = $aRequest['receive_storagehouse'];
				
				if( empty( $aReceiveStoragehouse[ $nIDReceiveStorage ] ) )
				{
					$aReceiveStorage = $oStoragehouses->getRecord( $nIDReceiveStorage );
					
					$oResponse->setFormElementChild( 'form1', 'nIDToStoragehouse', array( 'value' => $aReceiveStorage['id'] ), $aReceiveStorage['name'] );
				}
				
				$oResponse->setFormElementAttribute( 'form1', 'nIDToStoragehouse', 'value', $nIDReceiveStorage );
				
				$nIDOffice = $oRequests->getOffice( $aRequest['request_storagehouse'] );
				$oResponse->setFormElementAttribute( 'form1', 'nIDOffice', 'value', $nIDOffice );
				$aParams['nIDOffice'] = $nIDOffice;
				
				$oRequestNomenclatures->getStoragehouses( $aParams, $oResponse );
				
				$oResponse->setFormElementAttribute( 'form1', 'nIDStoragehouse', 'value', $aRequest['request_storagehouse'] );
				$aParams['nIDStoragehouse'] = $aRequest['request_storagehouse'];
				
				$oRequestNomenclatures->getMOL( $aParams, $oResponse );
				
				$oRequestNomenclatures->getReport( $aParams, $oResponse );
				
				$oResponse->setFormElement( 'form1', 'sComment', array( "value" => $aRequest['text'] ), $aRequest['text'] );
				$oResponse->setFormElement( 'form1', 'btnOk', array( "value" => '' ), $aRequest['text'] );
				
				//Проверка дали Задачата е получена или изпратена
				if( $_SESSION['userdata']['id_person'] == $aRequest['request_user'] )
				{
					$oResponse->setFormElement( 'form1', 'nForRead', array( "value" => "1" ), "1" );
					
					$aRequest['is_readed'] = 1;
					$aRequest['receive_time'] = date( 'Y-m-d H:i:s' );
					
					$oRequests->update( $aRequest );
				}
				
				if( $_SESSION['userdata']['id_person'] == $aRequest['receive_user'] )
				{
					$oResponse->setFormElement( 'form1', 'nForRead', array( "value" => "0" ), "0" );
				}
			}
			
			$oResponse->printResponse();
		}

		public function save( DBResponse $oResponse )
		{
			$oPersonnel = new DBPersonnel();
			$oRequest = new DBRequests();
			$oStoragehouses = new DBStoragehouses();
			
			$nID = Params::get( "nID", 0 );
			
			//Receive Data
			$nReceiveUser = $_SESSION['userdata']['id_person'];
			
			$nIDToStoragehouse = Params::get( "nIDToStoragehouse", 0 );
			//End Receive Data
			
			//Request Data
			$sRequestTime = date( 'Y-m-d H:i:s' );
			
			$sRequestUser = Params::get( "sMOL", '' );
			$aRequestUser = $oPersonnel->getPersonnelByNames( $sRequestUser );
			$nRequestUser = isset( $aRequestUser['id'] ) ? $aRequestUser['id'] : 0;
			
			$nIDStoragehouse = Params::get( "nIDStoragehouse", 0 );
			//End Request Data
			
			if( empty( $nIDToStoragehouse ) )
				throw new Exception( "Не сте МОЛ на склад!", DBAPI_ERR_INVALID_PARAM );
			
			if( $nIDToStoragehouse == $nIDStoragehouse )
				throw new Exception( "Не се допуска Задача от към един и същ склад!", DBAPI_ERR_INVALID_PARAM );
			
			$aData['id'] = $nID;
			
			$aData['request_time'] = $sRequestTime;
			$aRequestStoragehouse = $oStoragehouses->getByMOLID( $nRequestUser );
			
			$aData['request_storagehouse'] = $nIDStoragehouse;
			$aData['request_user'] = $nRequestUser;
			
			$aData['receive_storagehouse'] = $nIDToStoragehouse;
			$aData['receive_user'] = $nReceiveUser;
			
			$aData['text'] = Params::get("sComment", '');
			$aData['is_readed'] = 0;
			
			$oRequest->update( $aData );
			$oResponse->setFormElement( 'form1', 'nID', array( 'value' => $aData['id'] ) );
			
			$oResponse->printResponse();
		}

		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nIDElement", 0 );
			
			$oRequestNomenclatures = new DBRequestNomenclatures();
			$oRequestNomenclatures->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>