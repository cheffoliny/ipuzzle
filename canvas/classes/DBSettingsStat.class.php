<?php
	
	class DBSettingsStat extends DBBase2 {
		
		public function __construct() {
			global $db_system;
			
			parent::__construct($db_system, "isu_settings_static");
		}
		

		public function Signals( $id )
		{
			global $db_system,$db_name_system,$db_name_sod,$db_sod;
			
			//ВР
			$sQuery="
			SELECT
				IF(end_time != '0000-00-00 00:00:00',
				(unix_timestamp(end_time)-unix_timestamp(start_time)),
				(unix_timestamp(NOW())-unix_timestamp(start_time))
				)
				as VR
			FROM {$db_name_sod}.alarm_patruls
			WHERE id_alarm_register = {$id}
			";
			
			$VR = $this->selectOne($sQuery);
			$VR = $VR;

			//Овр
			$sQuery="
				SELECT
					ap.start_distance,
					ap.start_time,
					obj.id_office
				FROM {$db_name_sod}.alarm_patruls ap
				LEFT JOIN {$db_name_sod}.alarm_register ar ON ar.id = ap.id_alarm_register
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = ap.id_object
				WHERE id_alarm_register = {$id}
				";
				
			$Res = $this->select($sQuery);
//			print_r($Res);
			foreach ($Res as $r )
			{
				if($r['start_distance'] > 0)
					$distance=$r['start_distance']/1000;
				
				$start_time = $r['start_time'];
				$id_office = $r['id_office'];
				
				/*
				if($r['type']=='MGP')
					$MGP = $r['value'];
					
				if($r['type']=='SSigp')
					$SSigp = $r['value'];
					
				if($r['type']=='SSgrp')
					$SSgrp = $r['value'];
				if($r['type'] == 'KR')
					$KR = $r['value']; */
			}
			
			$oDBIsuSettingsStatistics = new DBIsuSettingsStatistics();
			$aSettings = $oDBIsuSettingsStatistics->getRegionOptionsFullQueries($id_office, $start_time);
			
			
			$KR = $oDBIsuSettingsStatistics->getStraightLineCorrection();
			$MGP = $aSettings['incity_km'];
			$SSgrp = $aSettings['incity_avg_speed'];
			$SSigp = $aSettings['outcity_avg_speed'];						
			
			//Квр
			if(($distance*$KR)>$MGP)
			{
				$OVR1 = ($MGP/$SSgrp)*3600;
				$OVR2 = ((($distance*$KR)-$MGP)/$SSigp)*3600;
				$OVR = ($OVR1+$OVR2);
			}
			else $OVR = ($distance*$KR/$SSgrp)*3600;

//			echo "ovr".$ovr1 = gmdate("H:i:s", ($OVR)); 	
			
			$KVR =$VR/$OVR;
			
//			echo "\n{$id} : {$VR} / {$OVR} = {$KVR}\n";
			
			return $KVR;	

		}
		
	}