<?php
	class ApiSetupLeaveEarningsFilter
	{
		// remote method
		public function init( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$nID = ( int ) isset( $aParams['id'] ) ? $aParams['id'] : 0;
			
			$oResponse->SetHiddenParam( "nID", $nID );
			
			if( !empty( $nID ) )
			{
				$aFilter = $oDBFilters->getRecord( $nID );
				
				$aFilterFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nID );
				
				$aData['id'] = $nID;
				$aData['sFilterName'] = isset( $aFilter['name'] ) ? $aFilter['name'] : "";
				$aData['nIsDefault'] = isset( $aFilter['is_default'] ) ? $aFilter['is_default'] : 0;
				
				//Default Fields
				$aData['person_name'] 			= "0";
				$aData['firm'] 					= "0";
				$aData['office'] 				= "0";
				$aData['leave_type'] 			= "0";
				$aData['leave_from_all'] 		= "0";
				$aData['sum'] 					= "0";
				$aData['application_days'] 		= "0";
				
				$aData['leave_num'] 			= "0";
				$aData['date'] 					= "0";
				$aData['person_position'] 		= "0";
				$aData['object'] 				= "0";
				$aData['leave_from'] 			= "0";
				$aData['leave_to'] 				= "0";
				$aData['code_leave_name'] 		= "0";
				$aData['created_user'] 			= "0";
				$aData['status'] 				= "0";
				$aData['time_confirm'] 			= "0";
				$aData['res_leave_from'] 		= "0";
				$aData['res_leave_to'] 			= "0";
				$aData['res_application_days'] 	= "0";
				$aData['person_confirm'] 		= "0";
				//End Default Fields
				foreach( $aFilterFields as $nID => $sFilterField )
				{
					$aData[$sFilterField] = "1";
				}
			}
			else
			{
				$aData['id'] = 0;
				$aData['sFilterName'] = "";
				$aData['nIsDefault'] = 0;
				
				//Default Fields
				$aData['person_name'] 			= "1";
				$aData['firm'] 					= "1";
				$aData['office'] 				= "1";
				$aData['leave_type'] 			= "1";
				$aData['leave_from_all'] 		= "0";
				$aData['sum'] 					= "1";
				$aData['application_days'] 		= "1";
				
				$aData['leave_num'] 			= "1";
				$aData['date'] 					= "0";
				$aData['person_position'] 		= "0";
				$aData['object'] 				= "1";
				$aData['leave_from'] 			= "1";
				$aData['leave_to'] 				= "1";
				$aData['code_leave_name'] 		= "0";
				$aData['created_user'] 			= "0";
				$aData['status'] 				= "0";
				$aData['time_confirm'] 			= "0";
				$aData['res_leave_from'] 		= "0";
				$aData['res_leave_to'] 			= "0";
				$aData['res_application_days'] 	= "0";
				$aData['person_confirm'] 		= "0";
				//End Default Fields
			}
			
			$oResponse->setFlexVar( "aData", $aData );
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$aParams['id'] = isset( $aParams['hiddenParams']->nID ) ? $aParams['hiddenParams']->nID : 0;
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$aFilterData = array();
			$aFilterData['id'] 				= isset( $aParams['id'] ) ? $aParams['id'] : 0;
			$aFilterData['name']			= isset( $aParams['sFilterName'] ) ? $aParams['sFilterName'] : 0;
			$aFilterData['id_person'] 		= isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$aFilterData['is_default']  	= isset( $aParams['nIsDefault'] ) ? $aParams['nIsDefault'] : 0;
			$aFilterData['report_class'] 	= "SetupLeaveEarnings";
			$aFilterData['is_auto'] 		= 0;
			
			$oDBFilters->update( $aFilterData );
			
			//Fields
			$Fields = array();
			if( isset( $aParams['person_name'] ) && !empty( $aParams['person_name'] ) )$aFields[] = "person_name";
			if( isset( $aParams['firm'] ) && !empty( $aParams['firm'] ) )$aFields[] = "firm";
			if( isset( $aParams['office'] ) && !empty( $aParams['office'] ) )$aFields[] = "office";
			if( isset( $aParams['leave_type'] ) && !empty( $aParams['leave_type'] ) )$aFields[] = "leave_type";
			if( isset( $aParams['leave_from_all'] ) && !empty( $aParams['leave_from_all'] ) )$aFields[] = "leave_from_all";
			if( isset( $aParams['sum'] ) && !empty( $aParams['sum'] ) )$aFields[] = "sum";
			if( isset( $aParams['application_days'] ) && !empty( $aParams['application_days'] ) )$aFields[] = "application_days";
			
			if( isset( $aParams['leave_num'] ) && !empty( $aParams['leave_num'] ) )$aFields[] = "leave_num";
			if( isset( $aParams['date'] ) && !empty( $aParams['date'] ) )$aFields[] = "date";
			if( isset( $aParams['person_position'] ) && !empty( $aParams['person_position'] ) )$aFields[] = "person_position";
			if( isset( $aParams['object'] ) && !empty( $aParams['object'] ) )$aFields[] = "object";
			if( isset( $aParams['leave_from'] ) && !empty( $aParams['leave_from'] ) )$aFields[] = "leave_from";
			if( isset( $aParams['leave_to'] ) && !empty( $aParams['leave_to'] ) )$aFields[] = "leave_to";
			if( isset( $aParams['code_leave_name'] ) && !empty( $aParams['code_leave_name'] ) )$aFields[] = "code_leave_name";
			if( isset( $aParams['created_user'] ) && !empty( $aParams['created_user'] ) )$aFields[] = "created_user";
			if( isset( $aParams['status'] ) && !empty( $aParams['status'] ) )$aFields[] = "status";
			if( isset( $aParams['time_confirm'] ) && !empty( $aParams['time_confirm'] ) )$aFields[] = "time_confirm";
			if( isset( $aParams['res_leave_from'] ) && !empty( $aParams['res_leave_from'] ) )$aFields[] = "res_leave_from";
			if( isset( $aParams['res_leave_to'] ) && !empty( $aParams['res_leave_to'] ) )$aFields[] = "res_leave_to";
			if( isset( $aParams['res_application_days'] ) && !empty( $aParams['res_application_days'] ) )$aFields[] = "res_application_days";
			if( isset( $aParams['person_confirm'] ) && !empty( $aParams['person_confirm'] ) )$aFields[] = "person_confirm";
			
			if( !empty( $aFilterData['id'] ) )$oDBFiltersVisibleFields->delByIDFilter( $aFilterData['id'] );
			
			foreach( $aFields as $sField )
			{
				$aFieldData = array();
				$aFieldData['id'] = 0;
				$aFieldData['id_filter'] = $aFilterData['id'];
				$aFieldData['field_name'] = $sField;
				
				$oDBFiltersVisibleFields->update( $aFieldData );
			}
			//End Fields
			
			Params::set( "id", isset( $aFilterData['id'] ) ? $aFilterData['id'] : 0 );
			
			$this->init( $oResponse );
		}
	}
?>