<?php

	class ApiClientObjects
	{
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset( $aParams['nID'] ) && !empty( $aParams['nID'] ) )
			{
				$oObjects = new DBObjects();
				
				$oObjects->getClientsReport( $oResponse, $aParams );
			}
			
			$oResponse->printResponse( "Картон на клиента - Обекти", "clients_objects" );
		}
		
		public function detach( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset( $aParams['nIDObject'] ) && isset( $aParams['nID'] ) )
			{
				$oClients = new DBClients();
				
				$nResult = $oClients->detachObjectFromClient( $aParams['nID'], $aParams['nIDObject'] );
				
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при отвързване на обекта!", $nResult );
				}
			}
		}
		
		public function getClient( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjects = new DBObjects();
			$oClients = new DBClients();
			
			if( isset( $aParams['nIDObjectToAttach'] ) && isset( $aParams['nID'] ) )
			{
				$nIDObject = $aParams['nIDObjectToAttach'];
				$nIDClient = $aParams['nID'];
				
				//Object is attached to another client :
				$nIDClientOfObject = $oObjects->getObjectsIDClient( $nIDObject );
				
				if( !empty( $nIDClientOfObject ) && $nIDClientOfObject != $nIDClient )
				{
					$aClient = $oClients->getRecord( $nIDClientOfObject );
					
					if( !empty( $aClient ) && isset( $aClient['name'] ) )
					{
						$oResponse->setFormElement( "form1", "nIDClient", array( "value" => $nIDClientOfObject ), $nIDClientOfObject );
						$oResponse->setFormElement( "form1", "sClient", array( "value" => $aClient['name'] ), $aClient['name'] );
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function attach( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjects = new DBObjects();
			$oClients = new DBClients();
			
			if( isset( $aParams['nIDObjectToAttach'] ) && isset( $aParams['nID'] ) )
			{
				$nIDObject = $aParams['nIDObjectToAttach'];
				$nIDClient = $aParams['nID'];
				
				//Empty object :
				if( empty( $nIDObject ) )
				{
					throw new Exception( "Моля, въведете обект!" );
				}
				
				//Object doesn't exit :
				$aObject = $oObjects->getRecord( $nIDObject );
				
				if( empty( $aObject ) )
				{
					throw new Exception( "Посочения обект не съществува!" );
				}
				
				//Object is already attached :
				$bIsAlreadyAdded = $oClients->isObjectAttachedToClient( $nIDClient, $nIDObject );
				
				if( $bIsAlreadyAdded )
				{
					throw new Exception( "Посочения обект вече е добавен!" );
				}
				
				//If there is an old client, detach it :
				if( !empty( $aParams['nIDClient'] ) )
				{
					$nResult = $oClients->detachObjectFromClient( $aParams['nIDClient'], $nIDObject );
					
					if( $nResult != DBAPI_ERR_SUCCESS )
					{
						throw new Exception( "Грешка при отвързване на обекта!", $nResult );
					}
				}
				
				//Finally, attach the object :
				$nResult = $oClients->attachObjectToClient( $nIDClient, $nIDObject );
				
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при привързване на обекта!" );
				}
			}
		}
		
	}

?>