<?php
class WSSEvents {
	protected $aEvents = array();
	
	protected $socket = null;
	
	protected $sAddr = 'localhost';
	
	protected $nPort = 7000;
	
	protected $nClientPort = 7001;

	protected $oDBMonitoringEvents = null;
	
	public function __construct($sAddr = null, $nPort = null) {		
		global $db_sod,$aHosts;
		
		if(!empty($aHosts)) $this->sAddr = '213.91.252.143';
		
		$this->oDBMonitoringEvents = new DBBase2($db_sod,'monitoring_events');
	}
	
	public function sendAuth($aAuth) {    
		global $db_system;  			
		$oInit = new DBBase2( $db_system , 'isu_settings');
		
		//Regioni
		$sQuery  ="
			SELECT o.id			 
			FROM telenet_system.isu_settings AS sett
			LEFT JOIN telenet_system.isu_settings_params AS isp ON isp.id_filter=sett.id
			LEFT JOIN sod.offices AS o ON o.id=isp.value
			WHERE sett.id_person =".$aAuth['id_person']." AND isp.name='account_regions'
		";

		$aInit = $oInit->select($sQuery);  
		$aTmpAuth = array('type'=>'auth','target'=>$aAuth['session_id']);
		foreach ($aInit as $v) { 
			$aTmpAuth['data'][$v['id']] = $v['id'];		  		 
			//$this->aEvents['auth']['regions'][$v['id']] = $v['id'];		  		 
		}
		//$this->aEvents['auth']['session_id'] = $aAuth['session_id'];
		$this->aEvents[] = $aTmpAuth;
		$this->sendEvents($this->nClientPort);
	}    

	public function addEvent($aEvent) {		
		$aTmpEvent = array();
		$aTmpEvent['type']   = !empty($aEvent['session_id']) ? 'session' : 'region';
		(!empty($aEvent['type'])) && $aTmpEvent['type'] = $aEvent['type'];
		$aTmpEvent['target'] = !empty($aEvent['session_id']) ? $aEvent['session_id'] : implode(',',$aEvent['idRegions']);		
		if(empty($aTmpEvent['target'])) return;
		$aTmpEvent['data'] = $aEvent;		
		$this->aEvents[] = $aTmpEvent;				
	}
	
	private function createSocket($port) {			
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($this->socket === false) return false;
		
		$result = socket_connect($this->socket, $this->sAddr, $port);
		
		if ($result === false) return false;
					
		return true;
	}
	
	public function sendEvents($port=FALSE) {		
		if(empty($port)) $port = $this->nPort;
		if(empty($this->aEvents)) return true;				
		//file_put_contents(dirname(__FILE__)."/../monitoring_events.txt",print_r($this->aEvents,true),FILE_APPEND);
		if($this->createSocket($port)) {			
			$aEvents = $this->aEvents;
			//$aEvents[-1] = $aEvents[0];
			$sPkg='';
			foreach ($aEvents as $evt) {
				$aPkg = array(
					'type'		=> $evt['type'],
					'target'	=> $evt['target'],
					'data'		=> json_encode($evt['data'])
				);	
				$sPkg.=implode("\t",$aPkg);
				$sPkg.="\n";
			}			 			
//			unset($aEvents[0]);
//			$sPkg = json_encode($aEvents);
			$nLen = strlen($sPkg);												
			
			if(socket_write($this->socket, $sPkg, $nLen) == $nLen) {
				//socket_shutdown($this->socket,2);
//				usleep(500);
//				socket_shutdown($this->socket,0);	
				socket_close($this->socket);	
				
			} else {		
				socket_close($this->socket);						
			}
		}
		try {
			$this->addDBEvents($this->aEvents);
		} catch (Exception $e) {			
			var_dump($e);
		}
		
		$this->aEvents = array();
		
		return true;
	}
	
	public function addDBEvents($aEvents) {
		global $db_sod;
		
		$aRows = array();
		foreach($aEvents as $aEvent) {
			if(empty($aEvent['type'])) continue;
			$aRow = array(
				$db_sod->Quote($aEvent['type']),
				$db_sod->Quote($aEvent['target']),
				$db_sod->Quote(serialize($aEvent['data'])),
			);
			$aRows[] = '('.implode(',',$aRow).')';
		}
		if(empty($aRows)) return;
		$db_sod->Execute("INSERT INTO monitoring_events (type,target,data) VALUES \n".implode(",\n",$aRows));
		if(rand()%100 == 0) {
			$db_sod->Execute(sprintf("DELETE FROM monitoring_events WHERE created_time < '%s'",date('Y-m-d H:i:s',time() - 300)));
		}
	}
}
