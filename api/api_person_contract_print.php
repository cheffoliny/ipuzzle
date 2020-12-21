<?php

	require_once( "pdf/pdf_person_contract.php" );
	require_once( "pdf/pdf_person_contract_addition.php" );
	require_once( "pdf/pdf_person_contract_order.php" );
	
	class ApiPersonContractPrint
	{
		public function result( DBResponse $oResponse )
		{
			//Objects
			$oContractPrint 		= new DBContractPrint();
			$oPersonnel 			= new DBPersonnel();
			$oWorkContracts 		= new DBWorkContracts();
			$oWorkContractsExtend 	= new DBWorkContractsExtend();
			$oWorkContractsEnd 		= new DBWorkContractsEnd();
			//End Objects
			
			//Params
			$aParams = Params::getAll();
			
			$nID 		= Params::get( "nID", 			0 	);
			$sApiAction = Params::get( "api_action", 	"" 	);
			
			$aParams['nIsOnNightSchedule'] = isset( $aParams['nIsOnNightScheduleSet'] );
			//End Params
			
			//Merge Params with Stored Values
			$aStoredData = $oPersonnel->getPersonContractPDFData( $nID );
			
			if( empty( $aStoredData ) )throw new Exception( NULL, DBAPI_ERR_SQL_DATA );
			
			//--List Values
			$nLengthOfService = explode( ",", $aStoredData['sPersonLengthOfService'] );
			
			if( !empty( $nLengthOfService ) )
			{
				$aParams['LOS_Y'] = $nLengthOfService[0];
				$aParams['LOS_M'] = $nLengthOfService[1];
				$aParams['LOS_D'] = $nLengthOfService[2];
			}
			else
			{
				$aParams['LOS_Y'] = 0;
				$aParams['LOS_M'] = 0;
				$aParams['LOS_D'] = 0;
			}
			//--End List Values
			
			$aParams = array_merge( $aParams, $aStoredData );
			//End Merge Params with Stored Values
			
			//Relate to PDF Document
			if( $sApiAction == 'export_to_pdf' )
			{
				switch( $aParams['nType'] )
				{
					case 0:
						$personContractPDF = new personContractPDF( "L" );
						$personContractPDF->PrintReport( $aParams );
						break;
					case 1:
						$personContractAdditionPDF = new personContractAdditionPDF( "L" );
						$personContractAdditionPDF->PrintReport( $aParams );
						break;
					case 2:
						$personContractOrderPDF = new personContractOrderPDF( "L" );
						$personContractOrderPDF->PrintReport( $aParams );
						break;
					default:
						break;
				}
			}
			//End Relate to PDF Document
			
			//Get Default Values
			$aDefaultData = array();
			$aContractPrint = $oContractPrint->selectOnce( "SELECT * FROM contract_print" );
			
			if( !empty( $aContractPrint ) )
			{
				//--List Values
				list( $aDefaultData['nClause'], $aDefaultData['nParagraph'] ) = explode( ",", $aContractPrint['clause_paragraph'] );
				//--End List Values
				
				//--Serialized Values
				$aHeadNames 	= unserialize( $aContractPrint['head_name'] );
				$aHeadPositions = unserialize( $aContractPrint['head_position'] );
				if( !$aHeadNames )$aHeadNames = array();
				if( !$aHeadPositions )$aHeadPositions = array();
				
				$aDefaultData['sHeadName'] 		= "";
				$aDefaultData['sHeadPosition'] 	= "";
				
				foreach( $aHeadNames as $nKey => $sValue )
				{
					if( $nKey == $aStoredData['nFirmID'] )$aDefaultData['sHeadName'] = $sValue;
				}
				foreach( $aHeadPositions as $nKey => $sValue )
				{
					if( $nKey == $aStoredData['nFirmID'] )$aDefaultData['sHeadPosition'] = $sValue;
				}
				//--End Serialized Values
				
				//--Direct Values
				$aDefaultData['sHeadTRZ'] 			= $aContractPrint['head_trz'];
				$aDefaultData['sHeadAccountant'] 	= $aContractPrint['head_accountant'];
				$aDefaultData['nLastNum'] 			= $aContractPrint['last_num'];
				$aDefaultData['nTestPeriodMonths'] 	= $aContractPrint['test_period_months'];
				$aDefaultData['nFullDayHours'] 		= $aContractPrint['full_day_hours'];
				$aDefaultData['nYearLeaveDays'] 	= $aContractPrint['year_leave_days'];
				$aDefaultData['sPersonnelClerk'] 	= $aContractPrint['personnel_clerk'];
				//--End Direct Values
			}
			else
			{
				//--Null Values
				$aDefaultData['nClause'] 			= "";
				$aDefaultData['nParagraph'] 		= "";
				$aDefaultData['sHeadName'] 			= "";
				$aDefaultData['sHeadPosition'] 		= "";
				$aDefaultData['sHeadTRZ'] 			= "";
				$aDefaultData['sHeadAccountant'] 	= "";
				$aDefaultData['nLastNum'] 			= "";
				$aDefaultData['nTestPeriodMonths'] 	= "";
				$aDefaultData['nFullDayHours'] 		= "";
				$aDefaultData['nYearLeaveDays'] 	= "";
				$aDefaultData['sPersonnelClerk'] 	= "";
				//--End Null Values
			}
			
			if( empty( $aDefaultData['sHeadName'] ) && !empty( $aStoredData['sFirmMOL'] ) )
			{
				$aDefaultData['sHeadName'] = $aStoredData['sFirmMOL'];
			}
			
			//--Non-database Values
			$aDefaultData['sTodayDate'] = date( "d.m.Y" );
			//--End Non-database Values
			//End Get Default Values
			
			//Set Default Values
			if( $aParams['nType'] == 0 )
			{
				//--Generate Contract History
				$aWorkContracts = $oWorkContracts->getWorkContracts( $aParams['nID'] );
				$sContent = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" id=\"tabs\"><tr>";
				foreach( $aWorkContracts as $aWorkContract )
				{
					$sContent .= "<td id=\"inactive\" style=\"width: 50px;\" nowrap=\"nowrap\" title=\"{$aWorkContract['date_contract']}\"><a href=\"#\" onclick=\"printContract( {$aWorkContract['id']} );\" id='wc_{$aWorkContract['id']}'>{$aWorkContract['num']}</a></td>";
				}
				$sContent .= "</tr></table>";
				
				$oResponse->setFormElementAttribute( 'form1', 'contracts', 'innerHTML', $sContent );
				//--End Generate Contract History
				
				//--Common Defaults
				$oResponse->setFormElement( 'form1', 'sDate', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sToday', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sClause', array(), $aDefaultData['nClause'] );
				$oResponse->setFormElement( 'form1', 'sParagraph', array(), $aDefaultData['nParagraph'] );
				$oResponse->setFormElement( 'form1', 'nTestPeriodMonths', array(), $aDefaultData['nTestPeriodMonths'] );
				$oResponse->setFormElement( 'form1', 'nFullDayHours', array(), $aDefaultData['nFullDayHours'] );
				$oResponse->setFormElement( 'form1', 'nLeave', array(), $aDefaultData['nYearLeaveDays'] );
				$oResponse->setFormElement( 'form1', 'sLeaderName', array(), $aDefaultData['sHeadName'] );
				$oResponse->setFormElement( 'form1', 'sLeaderPosition', array(), $aDefaultData['sHeadPosition'] );
				$oResponse->setFormElement( 'form1', 'sStartDate', array(), $aDefaultData['sTodayDate'] );
				//--End Common Defaults
				
				//--User Information
				if( !empty( $aStoredData ) )
				{
					$oResponse->setFormElement( 'form1', 'nBulstat', array(), $aStoredData['nFirmIDN'] );
					$oResponse->setFormElement( 'form1', 'sPosition', array(), $aStoredData['sPersonPosition'] );
					$oResponse->setFormElement( 'form1', 'nCode', array(), $aStoredData['sPersonPositionCode'] );
					$oResponse->setFormElement( 'form1', 'nSalary', array(), $aStoredData['nPersonSalary'] );
					$oResponse->setFormElement( 'form1', 'sObject', array(), $aStoredData['sPersonObject'] );
				}
				//--End User Information
			}
			
			if( $aParams['nType'] == 1 )
			{
				//--Generate Contract History
				$aWorkContracts = $oWorkContractsExtend->getWorkContracts( $aParams['nID'] );
				$sContent = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" id=\"tabs\"><tr>";
				foreach( $aWorkContracts as $aWorkContract )
				{
					$sContent .= "<td id=\"inactive\" style=\"width: 50px;\" nowrap=\"nowrap\" title=\"{$aWorkContract['date']}\"><a href=\"#\" onclick=\"printContract( {$aWorkContract['id']} );\" id='wc_{$aWorkContract['id']}'>{$aWorkContract['num']}</a></td>";
				}
				$sContent .= "</tr></table>";
				
				$oResponse->setFormElementAttribute( 'form1', 'contracts', 'innerHTML', $sContent );
				//--End Generate Contract History
				
				//--Common Defaults
				$oResponse->setFormElement( 'form1', 'sDate', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sToday', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sStartDate', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'nFullDayHours', array(), $aDefaultData['nFullDayHours'] );
				$oResponse->setFormElement( 'form1', 'sLeaderName', array(), $aDefaultData['sHeadName'] );
				$oResponse->setFormElement( 'form1', 'sLeaderPosition', array(), $aDefaultData['sHeadPosition'] );
				//--End Common Defaults
				
				//--User Information
				if( !empty( $aStoredData ) )
				{
					$aLastContract = $oWorkContracts->getLastWorkContract( $nID );
					
					$oResponse->setFormElement( 'form1', 'nBulstat', array(), $aStoredData['nFirmIDN'] );
					$oResponse->setFormElement( 'form1', 'sPosition', array(), $aStoredData['sPersonPosition'] );
					$oResponse->setFormElement( 'form1', 'nCode', array(), $aStoredData['sPersonPositionCode'] );
					
					if( !empty( $aLastContract ) )
					{
						$oResponse->setFormElement( 'form1', 'nNum', array(), $aLastContract['num'] );
					}
					
					$oResponse->setFormElement( 'form1', 'nBasicSalary', array(), $aStoredData['nPersonSalary'] );
					$oResponse->setFormElement( 'form1', 'nSalary', array(), $aStoredData['nPersonSalary'] );
				}
				//--End User Information
			}
			
			if( $aParams['nType'] == 2 )
			{
				//--Generate Contract History
				$aWorkContracts = $oWorkContractsEnd->getWorkContracts( $aParams['nID'] );
				$sContent = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" id=\"tabs\"><tr>";
				foreach( $aWorkContracts as $aWorkContract )
				{
					$sContent .= "<td id=\"inactive\" style=\"width: 50px;\" nowrap=\"nowrap\" title=\"{$aWorkContract['date']}\"><a href=\"#\" onclick=\"printContract( {$aWorkContract['id']} );\" id='wc_{$aWorkContract['id']}'>{$aWorkContract['num']}</a></td>";
				}
				$sContent .= "</tr></table>";
				
				$oResponse->setFormElementAttribute( 'form1', 'contracts', 'innerHTML', $sContent );
				//--End Generate Contract History
				
				//--Common Defaults
				$oResponse->setFormElement( 'form1', 'sDate', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sStartDate', array(), $aDefaultData['sTodayDate'] );
				$oResponse->setFormElement( 'form1', 'sLeaderName', array(), $aDefaultData['sHeadName'] );
				$oResponse->setFormElement( 'form1', 'sLeaderPosition', array(), $aDefaultData['sHeadPosition'] );
				$oResponse->setFormElement( 'form1', 'sLeaderTRZ', array(), $aDefaultData['sHeadTRZ'] );
				$oResponse->setFormElement( 'form1', 'sAccountant', array(), $aDefaultData['sHeadAccountant'] );
				//--End Common Defaults
				
				//--User Information
				if( !empty( $aStoredData ) )
				{
					$oResponse->setFormElement( 'form1', 'nBulstat', array(), $aStoredData['nFirmIDN'] );
					$oResponse->setFormElement( 'form1', 'sPosition', array(), $aStoredData['sPersonPosition'] );
					$oResponse->setFormElement( 'form1', 'nCode', array(), $aStoredData['sPersonPositionCode'] );
				}
				//--End User Information
			}
			
			$oResponse->printResponse( "Трудов Договор", "PersonContract" );
		}
	}

?>