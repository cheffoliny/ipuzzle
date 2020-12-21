<?php
	class ApiScheduleHours {
		
		public function result( DBResponse $oResponse ) {
//			$nIDFirm   = Params::get("nIDFirm", 0);
//			$nIDOffice = Params::get("nIDOffice", 0);
			$nIDObject = Params::get("nIDObject", 0);
			
			$oSchedule = new DBObjectScheduleHours();
			$oSchedule->getReport( $nIDObject, $oResponse );				
			
			$oResponse->printResponse( "Отработени часове", "schedule_hours" );
		}
		
		public function init( DBResponse $oResponse ) {
			$aParams = &Params::getAll();
			
			$this->_getFirms( $oResponse );
			$this->_getOffices( $oResponse );
			$this->_getObjects( $oResponse );
				
			$oResponse->printResponse();
		}		
		
		public function _getFirms( DBResponse $oResponse ) {
			$oFirms = new DBFirms();
			$oOffices	= new DBOffices();
			$aFirms = $oFirms->getAllAssoc();

			$nIDFirm = Params::get("nIDFirm2", 0);
				
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
			
			$oResponse->setFormElement('form1', 'nIDOffice');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), ' --  изберете -- ');
				
			$oResponse->setFormElement('form1', 'nIDObject');
			$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), ' --  изберете -- ');
			
			$oResponse->printResponse();
		}
		
		public function _getOffices( DBResponse $oResponse ) {
			$nIDFirm = Params::get("nIDFirm", 0);
			$nIDOffice = Params::get("nIDOffice2", 0);

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
			
			$oResponse->setFormElement('form1', 'nIDObject');
			$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), ' --  изберете -- ');
				
			$oResponse->printResponse();
		}
		
		public function _getObjects( DBResponse $oResponse ) {
			$nIDOffice 	= Params::get("nIDOffice");
			$nIDObject 	= Params::get("nIDObject2");
			
			if ( $nIDOffice == 0 ) {
				$nIDOffice = Params::get("nIDOffice2", 0);
			}
			
			$oResponse->setFormElement('form1', 'nIDObject');
			$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), ' --  изберете -- ');
				
			if( !empty( $nIDOffice ) ) {
				$oObjects = new DBObjects();
				$aObjects = $oObjects->getFoObjectsByOfficeAssoc( $nIDOffice );
				
				foreach( $aObjects as $aObject ) {
					if ( $aObject['id'] == $nIDObject ) {
						$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => $aObject['id'], 'selected' => 'selected'), sprintf("[ %s ] %s", $aObject['num'], $aObject['name']));
					} else {
						$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => $aObject['id']), sprintf("[ %s ] %s", $aObject['num'], $aObject['name']));
					}
				}
			}
		}
		
		public function getObjects( DBResponse $oResponse ) {
			$this->_getObjects( $oResponse );
			
			$oResponse->printResponse();
		}		

	}
?>