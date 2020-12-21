<?php
	class ApiSetupPositionsNC
	{
		public function result( DBResponse $oResponse )
		{
			$oSetupPositionsNC = new DBSetupPositionsNC();
			$oSetupPositionsNC->getReport( $oResponse );				
			
			//APILog::Log(0,$_SESSION);
			
			$oResponse->printResponse( "Длъжности по НКИД", "setup_position_nc" );
		}

		function delete( DBResponse $oResponse )
		{
			$nID = Params::get('id');
			
			$oSetupPositionsNC	= new DBSetupPositionsNC();
			$oDBPersonnel		= new DBPersonnel();
			$nCount				= $oDBPersonnel->getCountPersonPositionNCByID($nID);
			
			if (empty($nCount)) {
				$oSetupPositionsNC->delete( $nID );
			}
			 else if ($nCount==1) throw new Exception("Не може да премахнете тази длъжност! \nНа тази длъжност има назначен {$nCount} човек!");
			 	else throw new Exception("Не може да премахнете тази длъжност! \nНа тази длъжност има назначени {$nCount} човека!");
			 
			$oResponse->printResponse();
		}
	}
?>