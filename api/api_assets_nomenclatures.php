<?php
	class ApiAssetsNomenclatures
	{
		public function result(DBResponse $oResponse)
		{
			$oDBAssetsNomenclatures= new DBAssetsNomenclatures();
			$oDBAssetsNomenclatures->getREPORT($oResponse);
			APILog::Log(0,$oDBAssetsNomenclatures);
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{	
			$nID = Params::get( 'nID', 0 );
			$oDBAssetsNomenclatures = new DBAssetsNomenclatures();
			$oDBAssets				= new DBAssets();
			
			$nCount = $oDBAssets->getCountAssetsByIDNomenclatures($nID);
			
			if (empty($nCount)) {
				$oDBAssetsNomenclatures->delete( $nID );
			} else if ($nCount==1) throw new Exception("Не може да премахнете тази номенклатура!\nКъм нея има {$nCount} актив");
				else throw new Exception("Не може да премахнете тази номенклатура!\nКъм нея има {$nCount} актива");
			
			$oResponse->printResponse();
		}
	}
?>