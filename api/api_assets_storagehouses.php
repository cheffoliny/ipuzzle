<?php
	class ApiAssetsStorageHouses
	{
		public function result(DBResponse $oResponse)
		{
			$oDBAssetsStorageHouses=new DBAssetsStoragehouses();
			$oDBAssetsStorageHouses->getREPORT($oResponse);
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{	
			$nID = Params::get( 'nID', 0 );
			
			$oDBAssetsStoragehouses = new DBAssetsStoragehouses();
			$oDBAssets				= new DBAssets();
			$nCount	= $oDBAssets->getCountAssetsByIDStoragehouse($nID);
			if (empty($nCount)) {
				$oDBAssetsStoragehouses->delete( $nID );
			}
			 else if ($nCount==1) throw new Exception ("Не може да премахнете този склад! \nВ него има {$nCount} актив!");
				else throw new Exception ("Не може да премахнете този склад! \nВ него има {$nCount} активa!");
			$oResponse->printResponse();
		}
			
	}
?>