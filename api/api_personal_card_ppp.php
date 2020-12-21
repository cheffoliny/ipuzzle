<?php

	class ApiPersonalCardPPP
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$nIDLimitCard = Params::get( 'nIDLimitCard', 0 );
			$nIDCurrentPPP = Params::get( 'nIDCurrentPPP', 0 );
			
			$oPPP = new DBPPP();
			$oTechLimitCards = new DBTechLimitCards();
			
			$aLimitCardPPP = array();
			$bLimitCardClosed = false;
			
			if( !empty( $nIDLimitCard ) )
			{
				$nIDLimitCard = (int) $nIDLimitCard;
				$aLimitCardPPP = $oPPP->select( "SELECT id, dest_date, status FROM ppp WHERE to_arc = 0 AND status != 'cancel' AND id_limit_card = {$nIDLimitCard}" );
				
				$aTechLC = $oTechLimitCards->getRecord( $nIDLimitCard );
				
				//See if the Limit Card is Closed.
				if( isset( $aTechLC['status'] ) && $aTechLC['status'] == 'closed' )
				{
					$bLimitCardClosed = true;
				}
				else
				{
					//If the card is not closed, we set it's object ID (if it exists).
					if( isset( $aTechLC['id_object'] ) && (int) $aTechLC['id_object'] != 0 )
					{
						$oResponse->setFormElement( 'form1', 'nIDObject', array( "value" => $aTechLC['id_object'] ), $aTechLC['id_object'] );
					}
				}
				
				//See if Limit Card Creates Object
				if( isset( $aTechLC['type'] ) && $aTechLC['type'] == "create" )
				{
					$oResponse->setFormElement( 'form1', 'nLCCreateObject', array( "value" => 1 ) );
				}
			}
			else
			{
				return false;
			}
			
			//Creating Content
			$sContent = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" id=\"tabs\">";
			foreach( $aLimitCardPPP as $key => $aPPP )
			{
				$aInfo = array();
				$aInfo = $oPPP->getPPPMainInfo( $aPPP['id'] );
				
				//Setting The Tab Info
				$sTabInfo = "";
				if( !empty( $aInfo ) )
				{
					$sTabInfo = "Дата : {$aInfo['source_date']}\n";
					$sTabInfo .= "От {$aInfo['source_type']} : {$aInfo['source']}\n";
					$sTabInfo .= "Към {$aInfo['dest_type']} : {$aInfo['dest']}";
				}
				//End Setting The Tab Info
				
				//Check for Closed PPP or Limit Card
				if( $aPPP['status'] == 'confirm' || $bLimitCardClosed == true )
				{
					if( $bLimitCardClosed == true )
					{
						$oResponse->setFormElement( 'form1', 'nLCClosed', array( "value" => "1" ), "1" );
					}
					else
					{
						$oResponse->setFormElement( 'form1', 'nLCClosed', array( "value" => "0" ), "0" );
					}
					
					if( empty( $nIDCurrentPPP ) && $key == 0 )
					{
						$oResponse->setFormElement( 'form1', 'nPPPClosed', array( "value" => "1" ), "1" );
					}
					else
					{
						if( $aPPP['id'] == $nIDCurrentPPP )
						{
							$oResponse->setFormElement( 'form1', 'nPPPClosed', array( "value" => "1" ), "1" );
						}
					}
					$sBackAddition = " background-color: #aaaaaa;";
				}
				else
				{
					if( empty( $nIDCurrentPPP ) && $key == 0 )
					{
						$oResponse->setFormElement( 'form1', 'nPPPClosed', array( "value" => "0" ), "0" );
					}
					else
					{
						if( $aPPP['id'] == $nIDCurrentPPP )
						{
							$oResponse->setFormElement( 'form1', 'nPPPClosed', array( "value" => "0" ), "0" );
						}
					}
					$sBackAddition = "";
				}
				//End Check for Closed PPP or Limit Card
				
				//If the ID of the current PPP is null, none is selected. So the default is the first one.
				if( empty( $nIDCurrentPPP ) && $key == 0 )
				{
					$sContent .= "<tr><td id=\"active\" style=\"width: 50px;{$sBackAddition}\" nowrap=\"nowrap\" title=\"{$sTabInfo}\">{$aPPP['id']}</td></tr>";
					$nIDCurrentPPP = (int) $aPPP['id'];
					$oResponse->setFormElement( 'form1', 'nIDCurrentPPP', array( "value" => $nIDCurrentPPP ), $nIDCurrentPPP );
					
					if( $aInfo['source_type'] == "склад" )
					{
						$sPPPDesc = "От склад : {$aInfo['source']}<br>";
					}
					if( $aInfo['dest_type'] == "склад" )
					{
						$sPPPDesc = "Към склад : {$aInfo['dest']}";
					}
				}
				else
				{
					if( $aPPP['id'] == $nIDCurrentPPP )
					{
						$sContent .= "<tr><td id=\"active\" style=\"width: 50px;{$sBackAddition}\" nowrap=\"nowrap\" title=\"{$sTabInfo}\">{$aPPP['id']}</td></tr>";
						
						if( $aInfo['source_type'] == "склад" )
						{
							$sPPPDesc = "От склад : {$aInfo['source']}<br>";
						}
						if( $aInfo['dest_type'] == "склад" )
						{
							$sPPPDesc = "Към склад : {$aInfo['dest']}";
						}
					}
					else
					{
						$sContent .= "<tr><td id=\"inactive\" style=\"width: 50px;{$sBackAddition}\" nowrap=\"nowrap\" title=\"{$sTabInfo}\"><a href=\"#\" onclick=\"switch_ppp( {$aPPP['id']} );\" id='ppp_{$aPPP['id']}'>{$aPPP['id']}</a></td></tr>";
					}
				}
			}
			$sContent .= "</table>";
			//End Creating Content
			
			$oResponse->setFormElementAttribute( 'form1', 'ppplist', 'innerHTML', $sContent );
			$oResponse->setFormElementAttribute( 'form1', 'sPPPDesc', 'innerHTML', $sPPPDesc );
			
			$oPPP->getPersonalCardReport( $nIDCurrentPPP, $oResponse );
			
			$oResponse->printResponse( "Приемо-Предаване", "personal_card_ppp" );
		}
		
		public function processPPP( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$sProblem = "";
			
			$nIDPPP = Params::get( 'nIDPPP', 0 );
			$nIDLimitCard = Params::get( 'nIDLimitCard', 0 );
			
			//Check If Closed
			if( $nIDLimitCard != 0 )
			{
				$oTechLC = new DBTechLimitCards();
				$aTechLC = $oTechLC->getRecord( $nIDLimitCard );
				
				if( isset( $aTechLC['status'] ) && $aTechLC['status'] == 'closed' )
				{
					$sProblem = "Closed LC";
				}
			}
			
			if( $sProblem != "Closed LC" )
			{
				$oPPP = new DBPPP();
				
				if( !empty( $nIDPPP ) )
				{
					$aPPPToLink = $oPPP->getRecord( $nIDPPP );
				}
				else
				{
					$aPPPToLink = array();
					$sProblem = "Empty Field";
				}
				
				if( empty( $aPPPToLink ) )
				{
					if( empty( $sProblem ) )$sProblem = "No PPP";
				}
				else
				{
					if( $aPPPToLink['status'] == 'cancel' )
					{
						if( empty( $sProblem ) )$sProblem = "Canceled";
					}
					
					if( $aPPPToLink['source_type'] != 'object' && $aPPPToLink['dest_type'] != 'object' )
					{
						if( empty( $sProblem ) )$sProblem = "No Object";
					}
				}
				
				if( empty( $sProblem ) )
				{
					$aPPPToLink['id_limit_card'] = $nIDLimitCard;
					$oPPP->update( $aPPPToLink );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'sProblem', array( "value" => $sProblem ), $sProblem );
				}
			}
			else
			{
				$oResponse->setFormElement( 'form1', 'sProblem', array( "value" => $sProblem ), $sProblem );
			}
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nIDElement", 0 );
			
			$oPPPElements = new DBPPPElements();
			$oPPPElements->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>