<?php	
	abstract class APIHandler
	{	
		var $_sDefaultSortField;
		var $_sReportFileName;
		var $_sReportTitle;
		var $_oBase;
		
		function __construct( $oBase, $sDefaultSortField = 'name', $sReportFileName='doc', $sReportTitle='Справка' )
		{
			$this->_oBase 				= $oBase;
			$this->_sDefaultSortField 	= $sDefaultSortField;
			$this->_sReportFileName 	= $sReportFileName;
			$this->_sReportTitle 		= $sReportTitle;
		}
		
		abstract protected function setFields( $aParams );

		function Handler( $aParams )
		{
			global $oResponse;
			
			$nResult = DBAPI_ERR_SUCCESS;

			switch ( $aParams['api_action'] )
			{
				case 'result' :
				case 'export_to_xls' :
				case 'export_to_pdf' :
						if( empty( $aParams['sfield'] ) )
							$aParams['sfield'] = $this->_sDefaultSortField;
									
						if( empty( $aParams['stype'] ) )
							$aParams['stype'] = DBAPI_SORT_ASC; 
				
						if( empty( $aParams['current_page'] ) )
							$aParams['current_page'] = "1";	
				
						if( $aParams['api_action'] == "export_to_xls" || $aParams['api_action'] == "export_to_pdf" )
							$aParams['current_page'] = "0";
						
						
						$this->setFields( $aParams );
						$nResult = $this->getReport( $aParams );

						switch ( $aParams['api_action'] )
						{

							case 'export_to_xls' :
									$oResponse->toXLS($this->_sReportFileName."_".date('y_m_d').".xls", $this->_sReportTitle);
								break;
								
							case 'export_to_pdf' :
									if( $nResult == DBAPI_ERR_SUCCESS )
										$oResponse->toPDF($this->_sReportTitle,'P', $this->_sReportFileName."_".date('y_m_d'), 'pdf_general_result');
									else 
										$oResponse->toPDF($this->_sReportTitle,'P', $this->_sReportFileName."_".date('y_m_d'), 'error');
								break;

							default :
									print( $oResponse->toXML() );
								break;
						}
					break;					
			}
			
		}
		
		function getReport( $aParams )
		{
//			return DBAPI_ERR_SUCCESS;
			return $this->_oBase->getReport( $aParams );
		}
	}
?>