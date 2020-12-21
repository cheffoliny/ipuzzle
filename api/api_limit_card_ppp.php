<?php

	class ApiLimitCardPPP
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oPPP = new DBPPP();
			$oTechLimitCards = new DBTechLimitCards();
			
			$oPPP->getLimitCardReport($aParams, $oResponse);
			if( !empty( $aParams['nID'] ) && is_numeric( $aParams['nID'] ) )
			{
				$aTechLimitCard = $oTechLimitCards->getLimitCard( $aParams['nID'] );
				
				$oResponse->setFormElement( 'form1', 'nIDObject', array(), $aTechLimitCard['id_object'] );
			}
			
			$oResponse->printResponse();
		}
		
		public function linkppp( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oPPP = new DBPPP();
			
			$aPPPToLink = $oPPP->getRecord( $aParams['nIDPPPLink'] );
			
			if( empty($aPPPToLink) )
			{
				throw new Exception( "Няма приемо-предавателен протокол с този номер!" , DBAPI_ERR_INVALID_PARAM );
			}
			if( $aPPPToLink['source_type'] != 'object' && $aPPPToLink['dest_type'] != 'object' )
			{
				throw new Exception( "Приемо-предавателния протокол не е свързан с обект!" , DBAPI_ERR_INVALID_PARAM );
			}
			if( $aPPPToLink['status'] == 'cancel' )
			{
				throw new Exception( "Приемо-предавателния протокол е анулиран!" , DBAPI_ERR_INVALID_PARAM );
			}
			
			$aPPPToLink['id_limit_card'] = $aParams['nID'];
			
			$oPPP->update( $aPPPToLink );
		}
	}

?>