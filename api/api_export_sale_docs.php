<?php
	class ApiExportSaleDocs {
		public function result( DBResponse $oResponse ) {
			
			$this->getFirms( $oResponse );
			$this->_getOffices( $oResponse );

			$oExport = new DBExportDocuments();
			$oExport->getReport( 0, $oResponse );			
			//APILog::Log(0, $oResponse);
			
			$oResponse->printResponse("Експорт на документи", "export_sale_docs");	
		}
		
				
		public function export( DBResponse $oResponse ) {
			$nScheme	= Params::get("nIDScheme", 0);
			$nIDOffice	= Params::get("nIDOffice", 0);
			$nIDFirm	= Params::get("nIDFirm", 0);

			$periodFrom	= Params::get("sPeriodFrom", "");
			$periodFromH= Params::get("sPeriodFromH", "");
			$periodTo	= Params::get("sPeriodTo", "");
			$periodToH	= Params::get("sPeriodToH", "");
			
			$periodFrom = !empty($periodFrom) ? date("Y-m-d", jsDateToTimestamp($periodFrom)) : date("Y-m")."-01";
			$periodTo	= !empty($periodTo) ? date("Y-m-d", jsDateToTimestamp($periodTo)) : date("Y-m")."-31";
			$periodFromH= !empty($periodFromH) ? $periodFromH : "00:00:00";
			$periodToH	= !empty($periodToH) ? $periodToH : "23:59:00";

			$from	= $periodFrom." ".$periodFromH;
			$to		= $periodTo." ".$periodToH;
			//APILog::Log(0, $from);
			//APILog::Log(0, $to);
			

			$aData = array();
			$aData['nIDOffice'] = $nIDOffice;
			$aData['nIDFirm']	= $nIDFirm;
			$aData['from']		= $from;
			$aData['to']		= $to;
			//APILog::Log(0, $aData);
			$oExport = new DBExportDocuments();
			$oExport->ExportSaleDocs( $aData, $oResponse );

			$oResponse->printResponse("Експорт на документи", "export_sale_docs");	
					
		}
		
		public function _getFirms( DBResponse $oResponse ) {
			$oFirms = new DBFirms();
			$oOffices	= new DBOffices();
			$aFirms = $oFirms->getAllAssoc();

			$nIDOffice = $_SESSION['userdata']['id_office'];
			$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
				
			$oResponse->setFormElement('form1', 'nIDFirm');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0), ' --  изберете -- ');
			
			foreach( $aFirms as $aFirm ) {
				if ( $aFirm['id'] == $nIDFirm ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $aFirm['id'], 'selected' => 'selected'), $aFirm['name']);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $aFirm['id']), $aFirm['name']);
				}
			}
		}
		
		public function getFirms( DBResponse $oResponse ) {
			$this->_getFirms( $oResponse );			
		}
		
		public function _getOffices( DBResponse $oResponse ) {
			$nIDFirm = Params::get("nIDFirm", 0);
			$nIDOffice = $_SESSION['userdata']['id_office'];

			if ( $nIDFirm == 0 ) {
				$oOffices	= new DBOffices();
				$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
			}

			$oResponse->setFormElement('form1', 'nIDOffice');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), ' --  изберете -- ');
				
			if ( !empty( $nIDFirm ) ) {
				$oOffices = new DBOffices();
				$aOffices = $oOffices->getFirmOfficesRightAssoc( $nIDFirm );
				
				foreach( $aOffices as $aOffice ) {
					if ( $aOffice['id'] == $nIDOffice ) {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $aOffice['id'], 'selected' => 'selected'), $aOffice['name']);
					} else {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $aOffice['id']), $aOffice['name']);
					}
				}
				
			}
		}
		
		public function getOffices( DBResponse $oResponse ) {
			$this->_getOffices( $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse ) {
			$sFile = Params::get("sFile", "");
			
			$oExport = new DBExportDocuments();
			$oExport->DeleteFile( $sFile );

			$oResponse->printResponse("Експорт на документи", "export_sale_docs");
		}		
		
		public function view() {
			$sFile = Params::get("sFile", "");
			
			$oExport = new DBExportDocuments();
			$oExport->ViewFile( $sFile );
		}				
		

	}
	
?>