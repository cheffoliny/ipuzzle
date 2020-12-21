<?php

	class ApiSetSetupQuittance
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$id = ( int ) $aParams['id'];
			
			$oDBPersonLeaves 	= new DBPersonLeaves();
			$oDBLeaves			= new DBLeaves();
			
			if( !empty( $id ) )
			{
				$aData = $oDBPersonLeaves->getRecord( $id );
				
				$oResponse->setFormElement( "form1", "id",					array(), $aData["id"] );
				$oResponse->setFormElement( "form1", "year",				array(), $aData["year"] );
				$oResponse->setFormElement( "form1", "date",				array(), MySQLDatetoJSDate( $aData["date"] ) );
				$oResponse->setFormElement( "form1", "nMonth",				array(), substr( $aData["leave_from"], 5, 2 ) );
				$oResponse->setFormElement( "form1", "info",				array(), $aData["info"] );
				$oResponse->setFormElement( "form1", "application_days",	array(), $aData["application_days"] );
			}
			else
			{
				$nDaysLeft = $oDBLeaves->getRemainingLeaveDays( $aParams['year'], $aParams['id_person'] );
				$oResponse->setFormElement( "form1", "application_days", array(), $nDaysLeft );
				$oResponse->setFormElement( "form1", "nMonth", array(), date( "m" ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBPersonLeaves 	= new DBPersonLeaves();
			$oDBLeaves			= new DBLeaves();
			$oDBPersonnel 		= new DBPersonnel();
			$oDBSalaryEarning 	= new DBSalaryEarning();
			$oDBSalary 			= new DBSalary();
			
			$nRemainDays = $oDBLeaves->getRemainingLeaveDays( date( "Y" ), $aParams['id_person'], $aParams['id'] );
			
			if( empty( $aParams['id_person'] ) )
			{
				throw new Exception( "Служитела не е вкаран в системата!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( $aParams['application_days'] > $nRemainDays )
			{
				throw new Exception( "Въведения брой дни е по-голям от лимита за годината!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( empty( $aParams['application_days'] ) )
			{
				throw new Exception( "Въведете брой дни!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( empty( $aParams['year'] ) || strlen( $aParams['year'] ) != 4 )$aParams['year'] = date( "Y" );
			
			$aQuittance = array();
			$aQuittance['id']				= $aParams['id'];
			$aQuittance['id_person']		= $aParams['id_person'];
			$aQuittance['leave_types'] 		= "quittance";
			$aQuittance['year']				= $aParams['year'];
			$aQuittance['date']				= jsDateToTimestamp( $aParams['date'] );
			$aQuittance['leave_from']		= jsDateToTimestamp( "01." . $aParams['nMonth'] . "." . $aParams['year'] );
			$aQuittance['res_leave_from']	= $aQuittance['leave_from'];
			$aQuittance['info'] 			= $aParams['info'];
			$aQuittance['application_days'] = $aParams['application_days'];
			$aQuittance['type'] 			= "application";
			$aQuittance['is_confirm']		= 1;
			if( empty( $aParams['id'] ) )
			{
				$aQuittance['confirm_time'] = jsDateToTimestamp( $aParams['date'] );
				$aQuittance['confirm_user'] = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			}
			
			if( $nResult = $oDBPersonLeaves->update( $aQuittance ) != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Проблем при съхраняване на информацията!", $nResult );
			}
			
			//Add Earning
			$aPersonOffice 			= $oDBPersonnel->getPersonnelOffice( $aParams['id_person'] );
			$aCodeQuittanceEarning 	= $oDBSalaryEarning->getCompensationEarning();
			
			if( !empty( $aParams['year'] ) && strlen( $aParams['year'] ) == 4 )
			{
				$nYearMonth = ( int ) ( $aParams['year'] . $aParams['nMonth'] );
			}
			else
			{
				$nYearMonth = ( int ) ( date( "Y" ) . $aParams['nMonth'] );
			}
			
			$nIDSalaryRow = $oDBSalary->getSalaryRowByApplication( $aQuittance['id'] );
			
			$aDataSalary = array();
			$aDataSalary['id'] 				= $nIDSalaryRow;
			$aDataSalary['id_person'] 		= $aParams['id_person'];
			$aDataSalary['id_office'] 		= ( !empty( $aPersonOffice ) && isset( $aPersonOffice['id_office'] ) ) ? $aPersonOffice['id_office'] : 0;
			$aDataSalary['month'] 			= $nYearMonth;
			$aDataSalary['is_earning'] 		= 1;
			$aDataSalary['sum'] 			= 0;
			$aDataSalary['count'] 			= $aQuittance['application_days'];
			$aDataSalary['id_application'] 	= $aQuittance['id'];
			$aDataSalary['last_paid_date'] 	= jsDateToTimestamp( $aQuittance['date'] );
			
			$aDataSalary['code'] 		= isset( $aCodeQuittanceEarning['code'] ) ? $aCodeQuittanceEarning['code'] : "";
			$aDataSalary['description'] = isset( $aCodeQuittanceEarning['name'] ) ? $aCodeQuittanceEarning['name'] : "";
			
			$nResult = $oDBSalary->update( $aDataSalary );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Грешка при нанасяне на наработка!", __FILE__, __LINE__ );
				print( $oResponse->toXML() );
				return $nResult;
			}
			//End Add Earning
			
			$oResponse->printResponse();
		}
	}

?>
