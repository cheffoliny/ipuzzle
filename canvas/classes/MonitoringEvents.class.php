<?php

class MonitoringEvents {   
            
    public static $aResponse = Array();    
    
    public static function addCmdWaitPoint($cmd) {
                    
    }
    
    public static function addCmdCar($oCar) {	    
	    foreach ($oCar as $k=>$v) {
		    self::$aResponse['sod'][$oCar['idRegion']]['cars'][$oCar['id']][$k] = $v;
	    }
    }
    
    public static function addCmdObject($oObject) { 	    
	    foreach ($oObject as $k=>$v) {
		    self::$aResponse['sod'][$oObject['idRegion']]['objects'][$oObject['id']][$k] = $v;
	    }
    }
    
    public function addCmdInit($sData) {
        global $db_sod;        
        $idOffices = Array();
        $aData = json_decode($sData,true);        
        $oInit = new DBBase2( $db_sod , 'layers_objects');
      
        //Regioni
        $sQuery  ="
            SELECT 
                o.id, 
                o.name, 
                o.geo_w, 
                o.geo_s, 
                o.geo_n, 
                o.geo_e, 
                o.geo_lat, 
                o.geo_lan
            FROM telenet_system.isu_settings AS sett
            LEFT JOIN telenet_system.isu_settings_params AS isp ON isp.id_filter=sett.id
            LEFT JOIN sod.offices AS o ON o.id=isp.value
            WHERE sett.id_person =".$aData['init']['id_person']." AND isp.name='account_regions'
        ";
       
        $aInit = $oInit->selectAssoc($sQuery);  
         
        foreach ($aInit as $ID=>$val) {                       
            self::$aResponse['regions'][$ID]['geo_w']    = $val['geo_w'];
            self::$aResponse['regions'][$ID]['geo_s']    = $val['geo_s'];
            self::$aResponse['regions'][$ID]['geo_n']    = $val['geo_n'];
            self::$aResponse['regions'][$ID]['geo_e']    = $val['geo_e'];
            self::$aResponse['regions'][$ID]['geo_lat']  = $val['geo_lat'];
            self::$aResponse['regions'][$ID]['geo_lan']  = $val['geo_lan'];
            self::$aResponse['regions'][$ID]['name']     = $val['name'];		  
            $idOffices[$ID] = $ID;
        }
         
        //Stoqnki
        if (!empty($idOffices)) {
            $sQuery = "
                SELECT lo.id_office, lo.geo_lat, lo.geo_lan, lo.name, lo.id
                FROM sod.layers AS l
                LEFT JOIN sod.layers_objects AS lo ON lo.id_layer=l.id
                WHERE l.to_arc=0 AND lo.to_arc=0 AND l.is_alpha=1 AND lo.id_office IN(".implode(",",$idOffices).")
            ";        
            $aInit = $oInit->select($sQuery); 
        
            foreach ($aInit as $row) {               
                self::$aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['name'] = $row['name'];
                self::$aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['geo_lat'] = $row['geo_lat'];
                self::$aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['geo_lan'] = $row['geo_lan'];
            }
             
            //Regioni pod alarma
            $sQuery = "
                SELECT id_office 
                FROM sod.alarm_register 
                WHERE status='active' AND id_office IN(".implode(",",$idOffices).")
                GROUP BY id_office
            ";
            $aInit = $oInit->select($sQuery);
            foreach ($aInit as $row) {
                self::$aResponse['regions'][$row['id_office']]['alarm']=1;
            }
		  self::$aResponse['type'] = 'session';
		  self::$aResponse['target_type'] = 'init';
        }
    }
    
	public static function addCmdReinit($aData) {		
         
    }


    public static function send() {
    	global $db_sod;
    	
        // Изпълнение на команди от таблицата monitoring_events
		$oEvents = new DBBase2( $db_sod, 'monitoring_events');             
		$sQuery = "SELECT e.id AS id_ , e.* FROM monitoring_events e";                
		$aEventsData = $oEvents->selectAssoc($sQuery);  		
       	foreach ($aEventsData as $aEvent) {			
       		switch ($aEvent['type']) {
				case "init":
					self::addCmdInit($aEvent['data']);
					$data = json_decode($aEvent['data'],true);
					self::$aResponse['session_id'] = $aEvent['session_id'];						
					self::sendSocket('init');	
					
				break;
				case "reinit":
					self::addCmdReinit($aEvent['dat']);
				break;
	       	}            
      	}        		
       	self::deleteEvents($aEventsData);       			
		//self::sendSocket();	
		
    }
    
	public static function sendAuth($aAuth) {    
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
		
		foreach ($aInit as $v) {    			
			self::$aResponse['auth']['regions'][$v['id']] = $v['id'];		  		 
		}
		self::$aResponse['auth']['session_id'] = $aAuth['session_id'];
		
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($sock === false)
			MonDebug::debug("socket_create() failed: reason: " . socket_strerror(socket_last_error()), 1, __FILE__, __LINE__);
                
                //socket change by network
		$rem_id = $_SERVER['REMOTE_ADDR'];
                if (!empty($rem_id) && (int)$rem_id[0]==10 && (int)$rem_ip[1] == 10) {
                    $result = socket_connect($sock, '10.10.1.2', 7001);
                } else {
                    $result = socket_connect($sock, '213.91.252.143', 7001);
                }
		
		if ($result === false)
			MonDebug::debug("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($sock)), 1, __FILE__, __LINE__);		
	
		$len = strlen(json_encode(self::$aResponse));
		
		$sent = socket_write($sock, json_encode(self::$aResponse),$len);    
		
		socket_close($sock);
		return $sent;
	}    
        
	private function createSocket() {
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($sock === false)
			MonDebug::debug("socket_create() failed: reason: " . socket_strerror(socket_last_error()), 1, __FILE__, __LINE__);
		
		$result = socket_connect($sock, MEDIATOR_URL, MEDIATOR_PORT);
		
		if ($result === false)
			MonDebug::debug("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($sock)), 1, __FILE__, __LINE__);
		
		return $sock;
    }
    
	private function sendSocket($type=FALSE) {
		$socket = self::createSocket();
		//MonDebug::debug(print_r(self::$aResponse, TRUE), 2, __FILE__, __LINE__);
		
		if ($type=="init") {
			
			$aPkg = array(
				's'=>array(
				    'target'=>self::$aResponse['session_id'],
				    'type'=>'session',
				    'data'=>self::$aResponse
				   )
			);
			$sPkg = json_encode($aPkg);			
		} else {
			$pkg = json_encode(self::$aResponse);			
			self::$aResponse = array();			
		}
		
		$len = strlen($sPkg);		
		$sent = socket_write($socket, $sPkg, $len); 		
		var_dump($sent);
		socket_close($socket);     				
	}
    
	private function deleteEvents($aData) {
		global $db_sod;    	                

		if( !empty($aData) ) 
			$db_sod->Execute("DELETE FROM monitoring_events WHERE id IN(".implode(",",array_keys($aData)).")");		   	   
		}
}

?>
