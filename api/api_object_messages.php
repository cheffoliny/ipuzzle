<?php
	class ApiObjectMessages {
		public function result(DBResponse $oResponse) {
			
			$nID 			= Params::get('nID', 	'0');
			$nIDScheme 		= Params::get('scheme', '0');
			$nReact 		= Params::get('nReact', '0');
			
			$aReceivers 	= array();
			$rNames 		= array();

			$oScheme 		= new DBMessageSchemes();			
			$oReceivers 	= new DBReceivers();	// !new!
			$oObjects		= new DBObjects();
					
			$schemes 		= $oScheme->getSchemes();
			$aReceivers 	= $oReceivers->getReceiversByObj($nID);
			//$aObjectInfo 	= $oObjects->getInfoByID($nID);
			
			foreach ( $aReceivers as $val ) {
				$rNames[] 	= $val['name'];
			}

			$aReceivers 	= !empty($aReceivers) ? implode("; ", $rNames) : "";
			
			$oResponse->setFormElement('form1', 'scheme', array(), '');
			$oResponse->setFormElement('form1', 'receivers', array(), $aReceivers);
			$oResponse->setFormElementChild('form1', 'scheme', array_merge( array("value" => 0) ), "- Изберете схема -");
			
			foreach($schemes as $key => $value) {
				if( $key == $nIDScheme ) {
					$ch = array( "selected" => "selected" );
				} else {
					$ch = array();
				}

				$oResponse->setFormElementChild('form1', 'scheme', array_merge( array("value" => $key), $ch ), $value);
			}
			
			$oScheme->getiMessages( $oResponse, $nID, $nReact );
							
			$oResponse->printResponse(); 
		}

        public function closeServiceStatus() {
            $nIDObject = Params::get('nID', 0);
            $oDBObjects = new DBObjects();
            if ((int)$nIDObject > 0) {
                $oDBObjects->closeServiceStatus($nIDObject);
            }

        }

        public function setServiceStatus(DBResponse $oResponse){
            $nIDObject = Params::get('nID', 0);
            $oDBObjects = new DBObjects();
            //APILog::Log("1","da");
            if ((int)$nIDObject > 0) {
                $oDBObjects->setServiceStatus($nIDObject);
            }
            $oResponse->printResponse();
        }

		function delete( DBResponse $oResponse ) {
			$nIDSignal = Params::get('nIDSignal');

			$oMessage = new DBMessages();
			$oMessage->del( $nIDSignal );
			
			$oResponse->printResponse();
		}

		function delete2( DBResponse $oResponse ) {
			$nIDSignals = Params::get('chk', array());
			$tmpArr = array();
			
			foreach ( $nIDSignals as $key => $val ) {
				if ( $val == 1) $tmpArr[] = $key; 	
			}
			
			$signals = implode(",", $tmpArr);

			//APILog::Log(0, $signals);
			$oMessage = new DBMessages();
			$oMessage->del2( $signals );
			
			$oResponse->printResponse();
		}
		
		function newScheme( DBResponse $oResponse ) {
			$nIDObject = Params::get('nID');
			$sSchemeName = Params::get('sSchemeName');
			
			if ( empty($nIDObject) ) {
				throw new Exception("Проблем със шаблона!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($sSchemeName) ) {
				throw new Exception("Въведете име на шаблона!", DBAPI_ERR_INVALID_PARAM);
			}

			$aData = array();
			$aData['obj'] = $nIDObject;
			$aData['name'] = $sSchemeName;
		
			$oMessage = new DBMessageSchemes();
			$oMessage->addScheme( $aData );
			
			$oResponse->printResponse();
		}		

		function delScheme( DBResponse $oResponse ) {
			$scheme = Params::get('scheme');
		
			$oMessage = new DBMessageSchemes();
			$oMessage->delScheme( $scheme );
			
			$oResponse->printResponse();
		}		

		function editScheme( DBResponse $oResponse ) {
			$nIDObject = Params::get('nID');
			$nIDScheme = Params::get('scheme');
			
			if ( empty($nIDObject) ) {
				throw new Exception("Проблем със шаблона!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($nIDScheme) ) {
				throw new Exception("Изберете шаблон за редакция!", DBAPI_ERR_INVALID_PARAM);
			}

			$aData = array();
			$aData['nIDObject'] = $nIDObject;
			$aData['nIDScheme'] = $nIDScheme;
		
			$oMessage = new DBMessageSchemes();
			$oMessage->editScheme( $aData );
			
			$oResponse->printResponse();
		}		

		function fromScheme( DBResponse $oResponse ) {
			$nID = Params::get('nID');
			$nIDScheme = Params::get('scheme');
			$user		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			if ( empty($nID) ) {
				throw new Exception("Проблем със шаблона!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($nIDScheme) ) {
				throw new Exception("Изберете шаблон!", DBAPI_ERR_INVALID_PARAM);
			}

			$aData 				= array();
			$aData['nID'] 		= $nID;
			$aData['nIDScheme'] = $nIDScheme;
			$aData['nIDPerson'] = $user;
			//APILog::log(0, $aData);
			$oMessage = new DBMessageSchemes();
			$oMessage->fromScheme( $oResponse, $aData );
			
			$oResponse->printResponse();
		}		

	}
?>