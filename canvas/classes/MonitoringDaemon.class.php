<?php

class MonitoringDaemon {

    const REASON_ALARM_AUTO_RESTORE = 21; // ID на причината за автоматично прекратяване на алармата, ако на обекта не е реагирано до N сек
    const REASON_OPEN_AFTER_ALARM = 20; // ID на причината за отваряне след аларма
    const REASON_SYSTEM_RESET = 23;
    const MAX_GPS_REPORT = 1800; // Максимално време за което GPS-а трябва да рапортува, ако се надвиши това време колата е в статус, без координати 
    const MAX_DISLPAY_REPORT = 60; // Максимално време за което дисплея трябва да рапортува, ако се надвиши това време колата е в статус, без връзка с дисплея
    const PANIC_ALARM_ID = 3; //  id na signala panic button
    const CAR_MIN_MOVE_DISTANCE = 15; //минимално разстояние в м за което се смята че колата се движи
    const GC_INTERVAL = 30; //през колко време се чистят излишните данни от паметта (сек)
    const GC_ALARM_AFTER_CLOSE = 600; //време след което приключените аларми се изтриват от паметта (сек)
    const OBJECTS_MAX_ID = 100000000;

    protected $nIDStatusActive = 1;
    protected $nIDStatusMonitoring = 14;
    protected $nIDReasonNotify = 22;

    public function debug($var) {
//		ob_start();
//		var_dump($var);
//		$dump = ob_get_clean();
//		file_put_contents("D:/tmp/daemon_debug.txt", $dump, FILE_APPEND);
    }

    /**
     *  Функцията връща дистанцията м/у две точки в геостационарна координатна система в километри
     *
     * @author	Павел Петров
     * 
     * @param double $x1 координатите на първата точка
     * @param double $y1 координатите на първата точка
     * @param double $x2 координатите на втората точка
     * @param double $y2 координатите на втората точка
     * @return double Дистанция в километри
     */
    public static function getDistanceByGeo($x1, $y1, $x2, $y2) {
        $x1 = deg2rad($x1);
        $x2 = deg2rad($x2);
        $y1 = deg2rad($y1);
        $y2 = deg2rad($y2);

        $R = 6371;

        $lat = $x2 - $x1;
        $long = $y1 - $y2;

        $arc = ( sin($lat / 2) * sin($lat / 2) ) + cos($x1) * cos($x2) * ( sin($long / 2) * sin($long / 2) );
        $cer = 2 * atan2(sqrt($arc), sqrt(1 - $arc));

        return $R * $cer;
    }

    /**
     * @param double $nPointLat
     * @param double $nPointLng
     * @param array $aPolygon - masiv ot vurhovete, vseki vruh e ot vida array('lat' => 4.5, 'lng' => 1.7)
     * @return boolean
     */
    public static function isPointInPolygon($nPointLat, $nPointLng, $aPolygon) {
        if (!is_array($aPolygon))
            return false;
        $nPointLat = (double) $nPointLat;
        $nPointLng = (double) $nPointLng;
        foreach ($aPolygon as $k => $aPoint) {
            $aPolygon[$k]['lat'] = (double) $aPoint['lat'];
            $aPolygon[$k]['lng'] = (double) $aPoint['lng'];
        }

        //ako horizontalnata liniq prez tochkata minava prez nqkoi ot vurhovete q premestvame malko
        while (true) {
            foreach ($aPolygon as $aVertex)
                if ($aVertex['lng'] == $nPointLng) {
                    $nPointLng += 0.000000001;
                    continue 2;
                }
            break;
        }

        //zatvarqme poligona ako ne e zatvoren
        $aFirstPoint = reset($aPolygon);
        $aLastPoint = end($aPolygon);
        if ($aFirstPoint['lat'] != $aLastPoint['lat'] || $aFirstPoint['lng'] != $aLastPoint['lng'])
            $aPolygon[] = $aFirstPoint;

        if (count($aPolygon) <= 3)
            return false; //zatvoren poligon s po malko ot 4 vyrha nqma tochki vutre

        $nIntersections = 0;
        reset($aPolygon);
        while ($aVertex1 = current($aPolygon)) {
            $aVertex2 = next($aPolygon);
            if (!$aVertex2)
                break;

            if (
                    ($aVertex1['lng'] > $aVertex2['lng'] && ($aVertex1['lng'] < $nPointLng || $aVertex2['lng'] > $nPointLng) ) ||
                    ($aVertex1['lng'] <= $aVertex2['lng'] && ($aVertex1['lng'] > $nPointLng || $aVertex2['lng'] < $nPointLng) ) ||
                    $aVertex1['lng'] == $aVertex2['lng']
            )
                continue; //tochkata e pod,nad stranata ili stranata e horizontalna

            if ($aVertex1['lat'] == $aVertex2['lat']) {//stranata e vertikalna
                if ($nPointLat < $aVertex1['lat'])
                    $nIntersections++;
                continue;
            }

            if ($nPointLat < $aVertex1['lat'] + ($nPointLng - $aVertex1['lng']) * ($aVertex2['lat'] - $aVertex1['lat']) / ($aVertex2['lng'] - $aVertex1['lng']))
                $nIntersections++;
        }
        return $nIntersections % 2;
    }

    public function getObject($nIDObject) {
        $nIDObject = (int) $nIDObject;
        if ($nIDObject < self::OBJECTS_MAX_ID) {
            //kato se smenq query-to trqbva da se smeni i v loadAlarms
            $aObject = $this->oDBObjects->select("
				SELECT
					o.*,
					s.is_sod as status_is_sod
				FROM objects o
				JOIN statuses s on s.id = o.id_status
				WHERE o.id = $nIDObject
			");

            ($aObject[0]['id_reaction_office'] == 0 || $aObject[0]['geo_lan'] == 0 || $aObject[0]['geo_lat'] == 0) &&
                    $aObject[0]['id_status'] = $this->nIDStatusMonitoring;
            return reset($aObject);
        } else {
            $aObject = $this->oDBObjects->select("
				SELECT 
					o.*,
					o.id as num,
					o.id_office as id_reaction_office
				FROM layers_objects o WHERE id = $nIDObject
			");
            return reset($aObject);
        }
    }

    public function updateObject($aObject) {
        if (empty($aObject['id']))
            return;
        $aObjectUpdate = array(
            'id' => $aObject['id'],
            'reaction_status' => $aObject['reaction_status'],
            'reaction_car' => $aObject['reaction_car']
        );
        if ($aObject['id'] < self::OBJECTS_MAX_ID)
            return $this->oDBObjects->update($aObjectUpdate);
    }

    /**
     * @var DBCars
     */
    protected $oDBCars = null;

    /**
     * @var DBObjects
     */
    protected $oDBObjects = null;

    /**
     * @var DBWaypoints
     */
    protected $oDBWaypoints = null;

    /**
     * @var DBSODSignals
     */
    protected $oDBSODSignals = null;

    /**
     * @var DBStory
     */
    protected $oDBAlarmHistory = null;

    /**
     * @var DBAlarmRegister
     */
    protected $oDBAlarmRegister = null;

    /**
     * @var DBSODPatruls
     */
    protected $oDBSODPatruls = null;

    /**
     * @var DBAlarmPatruls
     */
    protected $oDBAlarmPatruls = null;

    /**
     * @var DBObjectsServicesSignals
     */
    protected $oDBObjectsServicesSignals = null;

    /**
     * @var DBNotifications
     */
    protected $oDBNotifications = null;
    
    /**
     * @var DBNotifications
     */
    protected $oDBNotificationsEvents = null;    

    /**
     * @var DBFiltersEvents
     */
    protected $oDBFiltersEvents = null;

    /**
     * @var DBSettingsStat
     */
    protected $oDBSettingsStat = null;

    /**
     * @var WSSEvents
     */
    protected $oWSSEvents = null;

    /**
     * @var DBMonthTable
     */
    protected $oDBAlarmArchive = null;

    /**
     * @var DBAlarmPanics
     */
    protected $oDBAlarmPanics = null;

    /**
     * @var DBBase2
     */
    protected $oDBMonitoringEvents = null;

    /**
     * @var DBBase2
     */
    protected $oDBAlertnessChecks = null;

    /**
     * @var DBBase2
     */
    protected $oDBBypass = null;
    protected $sDBSOD = null;
    protected $sDBAuto = null;
    protected $sDBPersonnel = null;

    public function __construct() {
        global $db_name_sod, $db_sod, $db_name_personnel, $db_name_auto_trans, $db_auto_trans;
        $this->sDBSOD = $db_name_sod;
        $this->sDBAuto = $db_name_auto_trans;
        $this->sDBPersonnel = $db_name_personnel;

        $this->oDBCars = new DBBase2($db_auto_trans, 'auto');
        $this->oDBObjects = new DBObjects();
        $this->oDBWaypoints = new DBBase2($db_sod, 'layers_stand');
        $this->oDBSODSignals = new DBSODSignals();
        $this->oDBAlarmHistory = new DBStory();
        $this->oDBAlarmRegister = new DBAlarmRegister();
        $this->oDBSODPatruls = new DBSODPatruls();
        $this->oDBAlarmPatruls = new DBAlarmPatruls();
        $this->oDBFiltersEvents = new DBFiltersEvents();
        $this->oDBSettingsStat = new DBSettingsStat();
        $this->oDBAlarmPanics = new DBAlarmPanicsCanvas();
        $this->oDBObjectsServicesSignals = new DBObjectsServicesSignalsCanvas();
       // $this->oDBNotificationsEvents = new DBNotificationsEvents();
        $this->oDBNotifications = new DBNotificationsCanvas();
        $this->oDBAlarmArchive = new DBMonthTable($db_name_sod, 'archiv_', $db_sod);
        $this->oWSSEvents = new WSSEvents();
        $this->oDBMonitoringCommands = new DBBase2($db_sod, 'monitoring_commands');
        $this->oDBBypass = new DBBase2($db_sod, 'object_bypass');
        $this->oDBAlertnessChecks = new DBBase2($db_sod, 'alertness_checks');
        $this->nLastAlertnessCheckGenerateTime = time();
    }

    /**
     * dobavq se kum vsichki subitiq v DB i se smenq predi i sled obrabotkata na vsqka komanda
     *
     * @var boolean
     */
    protected $sEventSource = 'system';

    /**
     * signali koito sa alarmirashti
     *
     * @var array
     */
    protected $aAlarmSignalTypes = array();

    /**
     * signali za filtyr snemane sled alarma
     *
     * @var array
     */
    protected $aOpenCloseSignals = array();

    /**
     * vreme za filtyr snemane sled alarma
     *
     * @var integer
     */
    protected $nOpenCloseMaxTime = 0;

    /**
     * vreme za filtyr alarma sled predavane
     *
     * @var integer
     */
    protected $nMaxAlarmAfterClose = 0;

    /**
     * max vreme za obekt pod alarma
     *
     * @var integer
     */
    protected $nObjectAlarmMaxTime = 0;

    /**
     * vreme za avtomatichno otkazvane na anons
     *
     * @var integer
     */
    protected $nCarAutoRejectTime = 0;

    /**
     * max razstoqnie za koeto se broi che kolata e pristignala (m)
     *
     * @var double
     */
    protected $nDistanceToArrival = 0;

    /**
     * vreme sled otkaz na anons prez koeto ne se izprashta avtomatichen anons kum sushtiq obekt
     *
     * @var integer
     */
    protected $nMinAnnounceTimeAfterRejection = 20;

    /**
     * vreme zsa bypass na alarma po podrazbirane
     *
     * @var integer
     */
    protected $nDefaultBypassTime = 300;

    /**
     * 
     *
     * @var array
     */
    protected $aStaticSettings = array();
    protected $aCancelReasons = array();
    protected $aAlarmReasons = array();
    protected $nLastLoadSettings = 0;
    protected $nLastAlertnessCheckGenerateTime;
    protected $nMinAlertnessCheckTimeInterval;
    protected $nAlertnessCheckPeriod;
    protected $nAlertnessCheckProbability;
    protected $nMaxObjectServiceTime;

    protected function loadSettings() {
        if ($this->nLastLoadSettings + 60 < time()) {
            $this->nLastLoadSettings = time();
            $oDBSettings = new DBFiltersEvents();
            $aSettings = $oDBSettings->getSettings();

            $this->aAlarmSignalTypes = empty($aSettings[0]['reactions']) ? array() : explode(',', $aSettings[0]['reactions']);
            $this->aOpenCloseSignals = empty($aSettings[0]['reactions_down']) ? array() : explode(',', $aSettings[0]['reactions_down']);
            $this->nOpenCloseMaxTime = (int) $aSettings[0]['down_after_alarm'];
            $this->nMaxAlarmAfterClose = (int) $aSettings[0]['alarm_after'];
            $this->nObjectAlarmMaxTime = (int) $aSettings[0]['max_time_react'];
            $this->nCarAutoRejectTime = (int) $aSettings[0]['wait_time'];
            $this->nDistanceToArrival = (int) $aSettings[0]['distance_to_arrival'];
            if (!$this->nDistanceToArrival)
                $this->nDistanceToArrival = 200;

            $this->nMaxObjectServiceTime = ((int) $aSettings[0]['nServTimePat']) * 60;
            if (empty($this->nMaxObjectServiceTime))
                $this->nMaxObjectServiceTime = 300;

            $this->nDefaultBypassTime = (int) $aSettings[0]['nServTimeObj'] * 60;
            if (empty($this->nDefaultBypassTime))
                $this->nDefaultBypassTime = 300;

            $this->nMinAnnounceTimeAfterRejection = (int) $this->nCarAutoRejectTime / 4;
            if (!$this->nMinAnnounceTimeAfterRejection)
                $this->nMinAnnounceTimeAfterRejection = 2;

            $this->nMinAlertnessCheckTimeInterval = (int) $aSettings[0]['min_alertness_check_time_interval'];
            if (empty($this->nMinAlertnessCheckTimeInterval))
                $this->nMinAlertnessCheckTimeInterval = 300;
            $this->nAlertnessCheckPeriod = (int) $aSettings[0]['alertness_check_period'];
            if (empty($this->nAlertnessCheckPeriod))
                $this->nAlertnessCheckPeriod = 300;
            $this->nAlertnessCheckProbability = (double) $aSettings[0]['alertness_check_probability'];

            /*
              $oDBSettingsStatic = new DBSettingsStat();
              $aStaticSettings = $oDBSettingsStatic->select("SELECT * FROM isu_settings_static WHERE to_arc = 0");
              $this->aStaticSettings = array();
              foreach ($aStaticSettings as $aRow) {
              if(!array_key_exists($aRow['type'],$this->aStaticSettings)) $this->aStaticSettings[$aRow['type']] = array();
              $this->aStaticSettings[$aRow['type']][$aRow['id_region']] = $aRow['value'];
              }

             * 
             */
            $this->aAlarmReasons = $this->oDBObjects->selectAssoc("SELECT id as __key, r.* FROM alarm_reasons r WHERE to_arc = 0");
            $this->aCancelReasons = $this->oDBObjects->selectAssoc("SELECT id as __key, r.* FROM alarm_reasons_cancel r WHERE to_arc = 0");
            $this->loadRegionBounds();
            $this->loadReactionZones();
        }
    }

    protected function getStaticSetting($sType, $nIDRegion = 0) {
        if (empty($this->aStaticSettings[$sType]))
            return null;
        if (array_key_exists($nIDRegion, $this->aStaticSettings[$sType]))
            return $this->aStaticSettings[$sType][$nIDRegion];
        elseif (array_key_exists(0, $this->aStaticSettings[$sType]))
            return $this->aStaticSettings[$sType][0];
        else
            return null;
    }

    /**
     * очаквано време за пристигане 
     *
     * @param double $nDistance - разстояние в м
     * @param integer $nIDRegion
     * @return integer - време в сек
     */
    protected function estimateArrivalTime($nDistance, $nIDRegion = 0) {
        $oDBIsuSettingsStatisctics = new DBIsuSettingsStatistics();
        return $oDBIsuSettingsStatisctics->estimateArrivalTime($nDistance, $nIDRegion);
    }

    protected $nLastGCTime = 0;

    protected function gc() {
        $nNow = time();
        if ($this->nLastGCTime + self::GC_INTERVAL > $nNow)
            return;
        $this->nLastGCTime = $nNow;

        //closed alarms
        foreach ($this->aAlarmRegister as $nIDObject => $aAlarm) {
            if ($aAlarm['status'] == 'closed' && mysqlDateToTimestamp($aAlarm['reaction_time']) + self::GC_ALARM_AFTER_CLOSE < $nNow) {
                unset($this->aAlarmPatrols[$nIDObject]);
                unset($this->aAlarmRegister[$nIDObject]);
                unset($this->aAlarmObjects[$nIDObject]);
                unset($this->aAnnounceRejections[$nIDObject]);
            }
        }

        //bypassed alarms
        foreach ($this->aBypassedMessages as $nIDObject => $aMessages) {
            foreach ($this->aBypassedMessages[$nIDObject] as $nIDMessage => $nTimeTo) {
                if ($nTimeTo < time())
                    unset($this->aBypassedMessages[$nIDObject][$nIDMessage]);
            }
            if (empty($this->aBypassedMessages[$nIDObject])) {
                unset($this->aBypassedMessages[$nIDObject]);
            }
        }
    }

    /**
     * обекти с активни аларми key => id_object
     *
     * @var array
     */
    protected $aAlarmObjects = array();

    /**
     * активни аларми key => id_object
     *
     * @var array
     */
    protected $aAlarmRegister = array();

    /**
     * колите които за заети по аларми key => id_object, id_car
     *
     * @var array
     */
    protected $aAlarmPatrols = array();

    protected function loadAlarms() {
        $this->aAlarmObjects = $this->oDBObjects->selectAssoc("
			SELECT
				o.id as __key, 
				o.*,
				s.is_sod as status_is_sod
			FROM objects o
			JOIN statuses s ON s.id = o.id_status
			WHERE 
			reaction_status != 0
		");
        foreach ($this->aAlarmObjects as &$aObject)
            ($aObject['id_reaction_office'] == 0 || $aObject['geo_lan'] == 0 || $aObject['geo_lat'] == 0) && $aObject['id_status'] = $this->nIDStatusMonitoring;
        $this->aAlarmRegister = $this->oDBAlarmRegister->selectAssoc("
			SELECT 
				ar.id_object as __key,
				ar.*
			FROM alarm_register ar
			WHERE 1
			AND status = 'active'
			AND id_object != 0
		");
        $this->aAlarmPatrols = array();
        if (!empty($this->aAlarmRegister)) {
            $aCarsByRoadList = array();
            foreach ($this->aPatrolCars as $aCar)
                $aCarsByRoadList[$aCar['id_road_list']] = $aCar;

            $aTmpAlarmsObjects = array();
            foreach ($this->aAlarmRegister as $aAlarm)
                $aTmpAlarmsObjects[$aAlarm['id']] = $aAlarm['id_object'];
            $aAlarmPatrols = $this->oDBAlarmPatruls->select(sprintf("
				SELECT 
					ap.* 
				FROM alarm_patruls ap
				WHERE 1
				AND id_alarm_register IN (%s)
				"
                            , implode(',', array_keys($aTmpAlarmsObjects))
                    ));
            foreach ($aAlarmPatrols as $aAlarmPatrol) {
                if (empty($aCarsByRoadList[$aAlarmPatrol['id_road_list']]))
                    continue;
                if (!array_key_exists($aTmpAlarmsObjects[$aAlarmPatrol['id_alarm_register']], $this->aAlarmPatrols))
                    $this->aAlarmPatrols[$aTmpAlarmsObjects[$aAlarmPatrol['id_alarm_register']]] = array();
                $this->aAlarmPatrols[$aTmpAlarmsObjects[$aAlarmPatrol['id_alarm_register']]][$aCarsByRoadList[$aAlarmPatrol['id_road_list']]['id']] = $aAlarmPatrol;
            }
        }
        //vodi me ..
        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            if (!empty($aCar['reaction_object']) && empty($aCar['reaction_status'])) {
                if (empty($this->aAlarmObjects[$aCar['reaction_object']]))
                    $this->aAlarmObjects[$aCar['reaction_object']] = $this->getObject($aCar['reaction_object']);
                if (empty($this->aAlarmPatrols[$aCar['reaction_object']][$nIDCar]))
                    $this->aAlarmPatrols[$aCar['reaction_object']][$nIDCar] = $this->oDBAlarmPatruls->selectOnce("
					SELECT * FROM alarm_patruls
					WHERE id_object = {$aCar['reaction_object']} AND id_alarm_register = 0
					ORDER BY id DESC
					LIMIT 1
				");
            }
        }
        //bypass alarms
        $sQuery = "SELECT * FROM object_bypass WHERE time_to > '" . date('Y-m-d H:i:s') . "'";
        $aObjectBypass = $this->oDBBypass->selectAssoc($sQuery);
        foreach ($aObjectBypass as $row) {
            $this->aBypassedMessages[$row['id_object']][$row['id_message']] = mysqlDateToTimestamp($row['time_to']);
        }
    }

    protected $nLastSignalID = null;

    protected function getNewSignals() {
        $aSignals = array();
        if (empty($this->nLastSignalID)) {
            $aLastID = array();
            $this->oDBAlarmArchive->select("SELECT MAX(id) FROM <table>", $aLastID, time(), time());
            if (!empty($aLastID))
                $aLastID = reset($aLastID);
            if (!empty($aLastID))
                $this->nLastSignalID = reset($aLastID);
        } else {
            $nTimeFrom = mktime(1, 1, 1, $this->oDBAlarmArchive->monthFromID($this->nLastSignalID), 1, $this->oDBAlarmArchive->yearFromID($this->nLastSignalID));
            $nMinMsgTime = date("Y-m-d H:i:s", time() - 60);
            if ($this->oDBAlarmArchive->select("
				SELECT
					a.id as id_archiv,
					m.id as id_msg,
					o.id as id_object,
					s.id as id_sig,
					a.msg as message,
					s.msg_al,
					a.alarm as alarm,
					a.msg_time as alarm_time,
					CONCAT(DATE_FORMAT(a.msg_time, '%h:%n:%s'), ' ', a.msg) as alarm_message,
					IF ( a.alarm = 1, m.msg_al, m.msg_rest ) as signal_message,
					s.play_alarm as play_alarm,
					o.name as object_name,
					o.num as object_num,
					o.address as object_address,
					o.id_reaction_office as id_office,
					o.reaction_status,
					o.geo_lan,
					o.geo_lat,
					o.remoteness,
					(o.reaction_time_normal * 60) as time_limit,
					a.channel,
					o.service_status
				FROM <table> a
				JOIN $this->sDBSOD.messages m ON m.id = a.id_msg
				JOIN $this->sDBSOD.objects o ON ( o.id = m.id_obj AND m.id_obj > 0 )
				LEFT JOIN $this->sDBSOD.signals s ON ( s.id = m.id_sig AND m.id_sig > 0 )
				LEFT JOIN $this->sDBSOD.offices of ON of.id = o.id_office
				WHERE 1
				AND a.id > {$this->nLastSignalID}
#				AND o.confirmed = 1
#				AND o.geo_lan > 0
#				AND o.id_status IN ($this->nIDStatusActive,$this->nIDStatusMonitoring)
				AND ( 
					o.service_status = 0 OR 
					( o.service_status = 1 AND DATE(o.service_status_time) != DATE(NOW())) 
				)
				
			", $aSignals, $nTimeFrom, time()) != DBAPI_ERR_SUCCESS) {
                throw new Exception();
            }
            foreach ($aSignals as $aSignal)
                if ($aSignal['id_archiv'] > $this->nLastSignalID)
                    $this->nLastSignalID = $aSignal['id_archiv'];
        }
        return $aSignals;
    }

    protected function processSignals() {
        $aNewSignals = $this->getNewSignals();
        $aSignalsForNotifications 		= $this->oDBObjectsServicesSignals->getSignalsForNotifications();
        $oNotificationsEvents 			= new DBNotificationsEvents();
        $aNotificationsEvents			= $oNotificationsEvents->getByCode("alarm_event");
        
        $aSignalsForSMSNotifications 	= $aSignalsForNotifications['SMS'];
        $aSignalsForTelNotifications 	= $aSignalsForNotifications['TEL'];
        

        foreach ($aNewSignals as $aSignal) {

            //bypass ?
            if (
                    !empty($this->aBypassedMessages[$aSignal['id_object']][$aSignal['id_msg']]) &&
                    $this->aBypassedMessages[$aSignal['id_object']][$aSignal['id_msg']] > time()
            ) {
                continue;
            }

            if (empty($aSignal['id_object']))
                continue;
            /*  CLIENTS NOTIFICATIONS */
            if ( $aNotificationsEvents['sms'] == 1 && empty($aSignal['service_status']) && ($aSignal['play_alarm'] == 1 || ($aSignal['play_alarm'] == 2 && $aSignal['alarm'] == 1)) ) {
	            if ( !empty($aSignalsForSMSNotifications[$aSignal['id_object']][$aSignal['id_sig']]) ) {
	                $this->oDBNotifications->addSignalNotifications($aSignalsForSMSNotifications[$aSignal['id_object']][$aSignal['id_sig']], $aSignal);
	            }
            }
            if ($aNotificationsEvents['system'] == 1 && !empty($aSignalsForTelNotifications[$aSignal['id_object']][$aSignal['id_sig']])) {
                $this->oDBNotifications->addSignalNotifications($aSignalsForTelNotifications[$aSignal['id_object']][$aSignal['id_sig']], $aSignal);
            }
            /* END OF CLIENTS NOTIFICATIONS */


            if ($aSignal['id_sig'] == self::PANIC_ALARM_ID) {

                $aAlarmPanic = array();
                $aAlarmPanic['id_object'] = $aSignal['id_object'];
                $aAlarmPanic['id_archiv'] = $aSignal['id_archiv'];
                $aAlarmPanic['alarm_time'] = $aSignal['alarm_time'];
                $aAlarmPanic['status'] = 'new';

                $this->oDBAlarmPanics->update($aAlarmPanic);
            }

            $sOldEventSource = $this->sEventSource;
            if ($aSignal['channel'] == 99)
                $this->sEventSource = 'dispatcher';
            if ($aSignal['play_alarm'] == 2 && $aSignal['alarm'] == 1) {
                //Сигнала е алармиращ

                if (empty($this->aAlarmRegister[$aSignal['id_object']]) || $this->aAlarmRegister[$aSignal['id_object']]['status'] == 'closed') {
                    // Сигнали без регистър (нова аларма)
                    $this->createAlarm(
                            $aSignal['id_object'], array(
                        'id_sig' => $aSignal['id_sig'],
                        'id_msg' => $aSignal['id_msg'],
                        'id_archiv' => $aSignal['id_archiv'],
                        'alarm_time' => $aSignal['alarm_time'],
                        'alarm_name' => $aSignal['message']
                            )
                    );
                } else {
                    //нов статус на вече добавена аларма
                    $this->addEvent(array(
                        'target_type' => 'object',
                        'target' => $aSignal['id_object'],
                        'event_type' => 'update',
                        'id_msg' => $aSignal['id_msg'],
                        'id_sig' => $aSignal['id_sig'],
                        'alarm_time' => $aSignal['alarm_time'],
                        'alarm_name' => $aSignal['message'],
                        'id_archiv' => $aSignal['id_archiv'],
                    ));
                }
            } elseif (!empty($this->aAlarmRegister[$aSignal['id_object']])) {
                // Сигнала НЕ е алармиращ но има аларма от обекта

                $nAlarmTime = mysqlDateToTimestamp($this->aAlarmRegister[$aSignal['id_object']]['alarm_time']);
                $nSigTime = mysqlDateToTimestamp($aSignal['alarm_time']);
                if (empty($nSigTime))
                    $nSigTime = time();
                if (!empty($nAlarmTime) && in_array($aSignal['id_sig'], $this->aOpenCloseSignals) && (($nSigTime - $nAlarmTime) <= $this->nOpenCloseMaxTime)) {
                    // Имаме снемане, прекратяваме действията
                    $this->closeAlarm($aSignal['id_object'], self::REASON_OPEN_AFTER_ALARM);
                }
            }
            $this->sEventSource = $sOldEventSource;
        }
    }

    /**
     * колите патрули key => id на кола (auto_trans.auto.id)
     *
     * @var array
     */
    protected $aPatrolCars = array();

    protected function loadPatrolCars() {
        $aYearMonths = array();
        $nYearFrom = date('Y', time() - 60 * 60 * 24);
        $nMonthFrom = date('m', time() - 60 * 60 * 24);
        $aRealTables = $this->oDBCars->select("SHOW TABLES LIKE 'road\_lists\_____'");
        foreach ($aRealTables as $k => $aRealTableRow)
            $aRealTables[$k] = reset($aRealTableRow);
        rsort($aRealTables);

        foreach ($aRealTables as $sTable) {
            $sYearMonth = preg_replace("/[^\d]/", '', $sTable);
            $nYear = '20' . substr($sYearMonth, 0, 2);
            $nMonth = substr($sYearMonth, 2, 2);
            $aYearMonths[] = substr($sYearMonth, 0, 2) . $nMonth;
            if ($nYearFrom > $nYear || $nMonthFrom > $nMonth)
                break;
        }

        //tozi masiv se izpolzva za update na tablica auto - poletata koito ne trqbva da se updatevat se select-vat s drugi imena
        $sQuery = "
			SELECT
				a.id AS __key,
				a.id,
				a.reaction_status,
				a.reaction_object,
				a.reaction_time,
				a.service_status,
				arc.name as service_reason,
				a.service_status_reason,
				a.end_service_status_time,
				
				rl.id 					AS id_road_list,
				rl.id_function			AS car_function,
				
				pat.id_office			AS id_main_office,
				pat.id_office			AS office_ids,
				
				a.phone 				AS car_phone,
				a.reg_num				AS car_reg_num,
				
				
				a.geo_lat 				AS current_geo_lat,
				a.geo_lan 				AS current_geo_lan,
				a.geo_real_time			AS geo_last_update,
				a.gps_fix				AS has_gps_fix,
				a.ignition				AS has_ignition,
				a.avg_speeg             AS avg_speed,

				a.client_geo_lan		AS current_client_geo_lan,
				a.client_geo_lat		AS current_client_geo_lat,
				a.client_geo_accuracy	AS current_client_geo_accuracy,
				a.client_geo_time		AS current_client_geo_time,

				a.last_info_request		AS last_display_request,
				
				pat.id 					AS id_patrul,
				pat.num_patrul 			AS num_patrul,
				rl.persons				AS patrol_persons,
				a.last_user_action		AS last_user_action_time,
				UNIX_TIMESTAMP(a.last_info_request)		AS last_user_time,
				GROUP_CONCAT(DISTINCT ac.id)		AS alertness_checks,
				ac.created_time			AS alertness_check_time,

				GROUP_CONCAT(DISTINCT IF(rp.target_type = 'zone',rp.id_target,null)) AS reaction_zones,
				GROUP_CONCAT(DISTINCT IF(rp.target_type = 'region',rp.id_target,null)) AS reaction_regions,

				'' as _blank
			FROM $this->sDBAuto.road_lists_<yearmonth>	rl 
			
			LEFT JOIN $this->sDBAuto.auto				a ON a.id = rl.id_auto
			LEFT JOIN $this->sDBAuto.auto_models		m ON a.id_model = m.id
			LEFT JOIN $this->sDBAuto.functions			f ON f.id = rl.id_function
			LEFT JOIN $this->sDBSOD.patruls				pat ON pat.id = rl.id_patrul
			LEFT JOIN $this->sDBSOD.alertness_checks	ac ON ac.id_road_list = rl.id AND ac.confirm_time = '0000-00-00 00:00:00'
			LEFT JOIN $this->sDBSOD.reaction_patrols	rp ON rp.id_patrol = pat.id
			LEFT JOIN $this->sDBSOD.alarm_reasons_cancel arc ON arc.id = a.service_status_reason
			WHERE 1
				
				AND f.id = 2
				AND rl.end_time = 0
				AND a.geo_lat > 0
				
				AND pat.id_office != 0
			GROUP BY rl.id
		";
//AND a.id_gps != 0
        $aQueries = array();
        foreach ($aYearMonths as $sYearMonths)
            $aQueries[] = str_replace('<yearmonth>', $sYearMonths, $sQuery);
        $sQuery = implode("\nUNION\n", $aQueries);

        if (!empty($sQuery))
            $this->aPatrolCars = $this->oDBCars->selectAssoc(implode("\nUNION\n", $aQueries));
        $aObjectIDs = array();
        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            $this->aPatrolCars[$nIDCar]['offices'] = explode(',', $aCar['office_ids']);
            $this->aPatrolCars[$nIDCar]['status_connection'] = time() - mysqlDateToTimestamp($aCar['last_display_request']) < self::MAX_DISLPAY_REPORT;
            $this->aPatrolCars[$nIDCar]['status_geo'] = !empty($aCar['has_gps_fix']) || (time() - mysqlDateToTimestamp($aCar['geo_last_update']) < self::MAX_GPS_REPORT);
            $this->aPatrolCars[$nIDCar]['patrol_persons'] = !empty($aCar['patrol_persons']) ? explode(',', $aCar['patrol_persons']) : array();
            $this->aPatrolCars[$nIDCar]['car_function'] = $aCar['car_function'];
            $this->aPatrolCars[$nIDCar]['geo_regions'] = array();
            $this->aPatrolCars[$nIDCar]['id_main_office'] = $aCar['id_main_office'];
            $this->aPatrolCars[$nIDCar]['service_status_reason'] = empty($aCar['service_status_reason']) ? 0 : $aCar['service_status_reason'];
            $this->aPatrolCars[$nIDCar]['service_reason'] = empty($aCar['service_reason']) ? "" : $aCar['service_reason'];
            $this->aPatrolCars[$nIDCar]['alertness_checks'] = !empty($aCar['alertness_checks']) ? explode(',', $aCar['alertness_checks']) : array();
            $this->aPatrolCars[$nIDCar]['reaction_zones'] = !empty($aCar['reaction_zones']) ? explode(',', $aCar['reaction_zones']) : array();
            $this->aPatrolCars[$nIDCar]['reaction_regions'] = !empty($aCar['reaction_regions']) ? explode(',', $aCar['reaction_regions']) : array();
        }
    }

    protected function processCars() {
        $aOldCars = $this->aPatrolCars;

        $this->loadPatrolCars();


        foreach ($aOldCars as $aCar) {
            if (empty($this->aPatrolCars[$aCar['id']])) {
                //kolata se maha samo ako nqma reakciq ot neq
                $this->aPatrolCars[$aCar['id']] = $aCar;
                if (empty($aCar['reaction_status'])) {
                    $this->addEvent(array(
                        'target_type' => 'car',
                        'target' => $aCar['id'],
                        'event_type' => 'car_remove',
                    ));
                    unset($this->aPatrolCars[$aCar['id']]);
                }
            }
        }


        if (time() - $this->nLastAlertnessCheckGenerateTime > $this->nAlertnessCheckPeriod) {
            $this->nLastAlertnessCheckGenerateTime = time();
            $bGenerateAlertnessChecks = true;
        } else {
            $bGenerateAlertnessChecks = false;
        }
        $sNow = date('Y-m-d H:i:s');
        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            if (!empty($aCar['reaction_object']) && empty($this->aAlarmObjects[$aCar['reaction_object']]))
                $this->aAlarmObjects[$aCar['reaction_object']] = $this->getObject($aCar['reaction_object']);
            if (empty($aOldCars[$aCar['id']])) {
                $this->aPatrolCars[$nIDCar]['is_new'] = true;
            } else {
                $this->aPatrolCars[$nIDCar]['old_geo_lan'] = $aOldCars[$aCar['id']]['current_geo_lan'];
                $this->aPatrolCars[$nIDCar]['old_geo_lat'] = $aOldCars[$aCar['id']]['current_geo_lat'];
                $this->aPatrolCars[$nIDCar]['old_avg_speed'] = $aOldCars[$aCar['id']]['avg_speed'];
            }

            if (!empty($aCar['service_status']) && mysqlDateToTimestamp($aCar['end_service_status_time']) < time()) {
                $this->carCancelService($nIDCar);
            }

            $bHasMoved = !empty($this->aPatrolCars[$nIDCar]['is_new']) || self::CAR_MIN_MOVE_DISTANCE < (self::getDistanceByGeo(
                            $this->aPatrolCars[$nIDCar]['old_geo_lat'], $this->aPatrolCars[$nIDCar]['old_geo_lan'], $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']
                    ) * 1000);

            if ($bHasMoved) {
                $this->aPatrolCars[$nIDCar]['last_move_time'] = date('Y-m-d H:i:s');
            }


            //profilaktichen tormoz sus buton bodrost
            if ($bGenerateAlertnessChecks &&
                    $this->aPatrolCars[$nIDCar]['status_connection'] &&
                    empty($this->aPatrolCars[$nIDCar]['service_status']) &&
                    time() - mysqlDateToTimestamp($this->aPatrolCars[$nIDCar]['last_move_time']) > $this->nMinAlertnessCheckTimeInterval &&
                    time() - mysqlDateToTimestamp($this->aPatrolCars[$nIDCar]['last_user_action_time']) > $this->nMinAlertnessCheckTimeInterval &&
                    empty($this->aPatrolCars[$nIDCar]['alertness_checks']) &&
                    empty($this->aPatrolCars[$nIDCar]['reaction_status']) &&
                    rand() / getrandmax() < $this->nAlertnessCheckProbability
            ) {
                foreach ($this->aPatrolCars[$nIDCar]['patrol_persons'] as $nIDPerson) {
                    $aNewCheck = array(
                        'id_road_list' => $this->aPatrolCars[$nIDCar]['id_road_list'],
                        'id_auto' => $nIDCar,
                        'confirm_time' => '0000-00-00 00:00:00',
                        'id_person' => $nIDPerson,
                        'created_time' => $sNow
                    );
                    $this->oDBAlertnessChecks->update($aNewCheck);
                    $this->aPatrolCars[$nIDCar]['alertness_checks'][] = $aNewCheck['id'];
                    $this->aPatrolCars[$nIDCar]['alertness_check_time'] = $sNow;
                }
                $bGeneratedAlertCheck = true;
            } else {
                $bGeneratedAlertCheck = false;
            }

            //ako kolata se dviji ili e nova ili se e smenil nqkoi status
            if (
                    $bGeneratedAlertCheck ||
                    $bHasMoved ||
                    $this->aPatrolCars[$nIDCar]['status_geo'] != $aOldCars[$nIDCar]['status_geo'] ||
                    $this->aPatrolCars[$nIDCar]['status_connection'] != $aOldCars[$nIDCar]['status_connection']
            ) {
                $this->aPatrolCars[$nIDCar]['geo_regions'] = $this->getRegionsByGeo($this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']);
                $this->addEvent(array(
                    'target_type' => 'car',
                    'target' => $nIDCar,
                    'event_type' => 'position',
                ));
            }
        }
    }

    protected function getRegionsByGeo($geo_lat, $geo_lan) {
        $aRegions = array();

        foreach ($this->aRegionBounds as $region => $bounds) {
            if ($geo_lan >= $bounds['west'] &&
                    $geo_lan <= $bounds['east'] &&
                    $geo_lat <= $bounds['north'] &&
                    $geo_lat >= $bounds['south']
            ) {
                $aRegions[] = $region;
            }
        }
        return $aRegions;
    }

    protected function getZoneIDsByGeo($nLat, $nLan, $aRegions = null) {
        if (!is_array($aRegions))
            $aRegions = array_keys($this->aReactionZones);
        $aZoneIDs = array();
        foreach ($aRegions as $nIDRegion) {
            if (empty($this->aReactionZones[$nIDRegion]))
                continue;
            foreach ($this->aReactionZones[$nIDRegion] as $aZone) {
                foreach ($aZone['polygons'] as $aPolygon) {
                    if ($this->isPointInPolygon($nLat, $nLan, $aPolygon)) {
                        $aZoneIDs[] = $aZone['id'];
                        break;
                    }
                }
            }
        }
        return $aZoneIDs;
    }

    protected $aOtherCars = array();

    protected function loadOtherCars() {
        $sQuery = "
			SELECT 
				a.id					AS __key,
				a.id,
				a.phone					AS car_phone,
				a.reg_num				AS car_reg_num,								
				a.geo_lat				AS current_geo_lat,
				a.geo_lan				AS current_geo_lan,
				ro.id_telenet_office	AS offices,
				a.id_function			AS car_function,
				f.name					AS car_function_name
			FROM $this->sDBAuto.auto AS a
			LEFT JOIN $this->sDBAuto.functions AS f ON f.id = a.id_function
			LEFT JOIN $this->sDBAuto.relation_offices AS ro ON a.id_division = ro.id_division AND a.id_region = ro.id_region AND a.id_function=ro.id_function
			WHERE 1
				
				AND a.geo_lat > 0
				AND a.id_function <> 2
		";
        //AND a.id_gps <> 0
        $this->aOtherCars = $this->oDBCars->selectAssoc($sQuery);
        foreach ($this->aOtherCars as $nIDCar => $aCar) {
            if (!empty($this->aPatrolCars[$nIDCar])) {
                unset($this->aOtherCars[$nIDCar]);
                continue;
            }
            $this->aOtherCars[$nIDCar]['offices'] = $this->getRegionsByGeo($aCar['current_geo_lat'], $aCar['current_geo_lan']);
            $this->aOtherCars[$nIDCar]['current_geo_lan'] = $aCar['current_geo_lan'];
            $this->aOtherCars[$nIDCar]['current_geo_lat'] = $aCar['current_geo_lat'];
            $this->aOtherCars[$nIDCar]['car_reg_num'] = $aCar['car_reg_num'];
            $this->aOtherCars[$nIDCar]['car_phone'] = $aCar['car_phone'];
            $this->aOtherCars[$nIDCar]['car_function'] = $aCar['car_function'];
            $this->aOtherCars[$nIDCar]['car_function_name'] = $aCar['car_function_name'];
            $this->aOtherCars[$nIDCar]['id_main_office'] = $aCar['offices'];
        }
    }

    protected function processOtherCars() {
        $aOldCars = $this->aOtherCars;

        $this->loadOtherCars();

        foreach ($this->aOtherCars as $nIDCar => $aCar) {
            if (self::CAR_MIN_MOVE_DISTANCE < (self::getDistanceByGeo(
                            $aOldCars[$nIDCar]['current_geo_lat'], $aOldCars[$nIDCar]['current_geo_lan'], $this->aOtherCars[$nIDCar]['current_geo_lat'], $this->aOtherCars[$nIDCar]['current_geo_lan']))
                    * 1000) {
                $this->addEvent(array(
                    'target_type' => 'car',
                    'target' => $nIDCar,
                    'event_type' => 'position',
                    'nopatrol' => true
                ));
            }
        }
    }

    protected $aReactionZones = array();

    protected function loadReactionZones() {
        $this->aReactionZones = array();
        $sDateTime = date('Y-m-d H:i:s');
        $nMonth = date('m');
        $nDay = date('d');
        $nWeekDay = date('w');
        $nWeekDay = $nWeekDay ? $nWeekDay : 7;
        $sTime = date('H:i:s');
        $aZones = $this->oDBAlarmPatruls->select("
			SELECT
			DISTINCT rz.*
			FROM reaction_zones rz
			JOIN reaction_zones_periods rzp ON rzp.id_zone = rz.id AND rzp.to_arc = 0
			WHERE 1
			AND rz.zone_type = 'reaction_zone'
			AND '$sDateTime' BETWEEN rzp.valid_from AND rzp.valid_to
			AND '$sTime' BETWEEN rzp.time_from AND rzp.time_to
			AND FIND_IN_SET($nMonth,rzp.months)
			AND FIND_IN_SET($nDay,rzp.days)
			AND FIND_IN_SET($nWeekDay,rzp.weekdays)
			AND rz.to_arc = 0
		");
        foreach ($aZones as $aZone) {
            if (!array_key_exists($aZone['id_region'], $this->aReactionZones))
                $this->aReactionZones[$aZone['id_region']] = array();
            $aZone['polygons'] = json_decode($aZone['polygons'], true);
            if (!is_array($aZone['polygons']))
                continue;
            $this->aReactionZones[$aZone['id_region']][$aZone['id']] = $aZone;
        }
    }
    
    protected $aServiceReasons = array();

    protected function loadServiceReasons() {
        $this->aServiceReasons = array();

        $aZones = $this->oDBAlarmPatruls->select("
			SELECT
				id,
				name
			FROM alarm_reasons_cancel
			WHERE to-arc = 0
		");
        
        foreach ($aZones as $aReason) {
            if ( !array_key_exists($aReason['id'], $this->aReactionZones) ) {
                $this->aServiceReasons[$aReason['id']] = array();
            }

            $this->aServiceReasons[$aReason['id']] = $aReason;
        }
    }

    protected $aRegionBounds = array();

    protected function loadRegionBounds() {
        global $db_sod;
        $oRegionBounds = new DBBase2($db_sod, 'layers');
        $sQuery = "
			SELECT 
				lo.id_office, 
				MIN(lo.geo_lan) AS west, 
				MAX(lo.geo_lan) AS east, 
				MIN(lo.geo_lat) AS south,
				MAX(lo.geo_lat) AS north
			FROM $this->sDBSOD.layers AS l
			LEFT JOIN $this->sDBSOD.layers_objects AS lo ON lo.id_layer=l.id
			WHERE lo.to_arc=0 AND l.is_alpha=1
			GROUP BY lo.id_office
		";
        $this->aRegionBounds = $oRegionBounds->selectAssoc($sQuery);
    }

    protected $aWaypoints = array();
    protected $nWaypointsUpdateLast;

    protected function loadWaypoints() {
        $this->nWaypointsUpdateLast = time();
        $past = mktime() - 604800;
        $dateBegin = date("Y-m-d", $past) . " " . (date("i") < 30 ? date("H") - 1 . ":30:00" : date("H") . ":30:00");
        $dateEnd = date("Y-m-d", $past) . " " . (date("i") < 30 ? date("H") - 1 . ":40:00" : date("H") . ":40:00");
        $sQuery = "
			SELECT *
			FROM $this->sDBSOD.layers_stand
			WHERE to_arc=0 AND time_stat >= '$dateBegin' AND time_stat <= '$dateEnd'
		";
        $aWP = $this->oDBWaypoints->select($sQuery);

        foreach ($aWP as $row) {
            $this->aWaypoints[$row['id_office']][$row['patrul_count']] = $row['stands'];
        }
    }

    protected function processWaypoints($bInit = FALSE) {
        if ($bInit || empty($this->nWaypointsUpdateLast) || time() - $this->nWaypointsUpdateLast > 600) {
            $this->nWaypointsUpdateLast = time();
        } else {
            return;
        }
        $nWaypointsUpdateLast = time();
        $this->loadWaypoints();
        $regions = array();
        foreach ($this->aWaypoints as $k => $v) {
            array_push($regions, $k);
        }
        $this->addEvent(array(
            'target_type' => 'waypoint',
            'idRegions' => $regions,
            'data' => $this->aWaypoints,
            'region_bounds' => $this->aRegionBounds
        ));
    }

    protected $aAnnounceRejections = array();

    protected function processAlarms() {
    	//ob_toFile(ArrayToString($this->aPatrolCars), "asdf.txt");
        $aUnReactedObjects = array();
        foreach ($this->aAlarmRegister as $nIDObject => $aAlarm) {
            //Затваряне на всички аларами, за които не е регирано през последните $this->nObjectAlarmMaxTime сек
            if ($aAlarm['status'] == 'active' && mysqlDateToTimestamp($aAlarm['alarm_time']) + $this->nObjectAlarmMaxTime < time())
                $this->closeAlarm($nIDObject, self::REASON_ALARM_AUTO_RESTORE, 'system');

            if ($aAlarm['status'] != 'active' || !empty($this->aAlarmObjects[$nIDObject]['reaction_car']))
                continue;

            if ($this->aAlarmObjects[$nIDObject]['id_status'] == $this->nIDStatusMonitoring)
                continue;

            $aUnReactedObjects[$nIDObject] = &$this->aAlarmObjects[$nIDObject];
        }

        $aObjectsWithAnnouncedCars = array();
        $aCarsBy = array('region' => array(), 'zone' => array());
        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            if ($aCar['reaction_status'] == 1) {
                //avtomatichno otkazvane na anons sled $this->nCarAutoRejectTime sec
                if (mysqlDateToTimestamp($aCar['reaction_time']) + $this->nCarAutoRejectTime < time()) {
                    $this->freeCar($nIDCar);
                } else {
                    $aObjectsWithAnnouncedCars[$aCar['reaction_object']] = $nIDCar;
                }
            }

            foreach ($aCarsBy as $type => $a) {
                foreach ($aCar['reaction_' . $type . 's'] as $nID) {
                    if (!array_key_exists($nID, $aCarsBy[$type]))
                        $aCarsBy[$type][$nID] = array();
                    $aCarsBy[$type][$nID][$nIDCar] = $this->aPatrolCars[$nIDCar];
                }
            }
        }

        foreach ($aUnReactedObjects as $nIDObject => $aObject) {
            if (!empty($aObjectsWithAnnouncedCars[$nIDObject]))
                continue;
            $nIDClosestCar = 0;
            $nClosestCarDistance = INF;

            foreach ($aCarsBy as $type => $aCarsByType) {
                if ($type == 'zone')
                    $aIDs = $this->getZoneIDsByGeo($aObject['geo_lat'], $aObject['geo_lan'], array($aObject['id_reaction_office']));
                else
                    $aIDs = array($aObject['id_reaction_office']);
                foreach ($aIDs as $nID) {
            //echo "nID";
            //print_r($aCarsByType);                	
                    if (!empty($aCarsByType[$nID]))
                        foreach ($aCarsByType[$nID] as $nIDCar => $aCar) {
                            //ako kolata moje da reagira i ne e otkazala anons kum tozi obekt prez poslednite $this->nMinAnnounceTimeAfterRejection sec
                            if (
                            	(time() - $aCar['last_user_time'] <= 80) &&
                                    $aCar['reaction_status'] == 0 &&
                                    $aCar['service_status'] == 0 &&
                                    (empty($this->aAnnounceRejections[$nIDObject][$nIDCar]) || $this->aAnnounceRejections[$nIDObject][$nIDCar] + $this->nMinAnnounceTimeAfterRejection < time())
                            ) {
                                $nDistance = self::getDistanceByGeo(
                                                $aCar['current_geo_lat'], $aCar['current_geo_lan'], $aObject['geo_lat'], $aObject['geo_lan']
                                );
                                if ($nClosestCarDistance > $nDistance) {
                                    $nClosestCarDistance = $nDistance;
                                    $nIDClosestCar = $aCar['id'];
                                }
                            }
                        }
                }
            }

            if (!empty($nIDClosestCar)) {
                $this->announceCar($nIDClosestCar, $aObject['id']);
            }
        }
    }

    protected function createAlarm($nIDObject, $aParams) {
        $aObject = $this->getObject(intval($nIDObject));
        if (empty($aObject))
            return;
        //? eventualna proverka za lipsvashti parametri
        //ako nqkoi e v rejim "vodi me" kum toq obekt
        if (!empty($this->aAlarmPatrols[$nIDObject])) {
            foreach ($this->aAlarmPatrols[$nIDObject] as $nIDCar => $aAlarmPatrol) {
                if (empty($this->aPatrolCars[$nIDCar]['reaction_status']))
                    $this->freeCar($nIDCar);
            }
            $this->aAlarmPatrols[$nIDObject] = array();
        }

        $this->aAlarmRegister[$aObject['id']] = array(
            'id_object' => $aObject['id'],
            'id_office' => empty($aObject['id_reaction_office']) ? $aObject['id_office'] : $aObject['id_reaction_office'],
            'obj_num' => $aObject['num'],
            'obj_name' => $aObject['name'],
            'obj_address' => $aObject['address'],
            'obj_geo_lan' => $aObject['geo_lan'],
            'obj_geo_lat' => $aObject['geo_lat'],
            'id_sig' => empty($aParams['id_sig']) ? 0 : $aParams['id_sig'],
            'obj_time_alarm_reaction' => $aObject['reaction_time_normal'] * 60,
            'status' => "active",
        );
        unset($this->aAlarmPatrols[$nIDObject]);
        unset($this->aAnnounceRejections[$nIDObject]);

        foreach ($aParams as $k => $v) {
            if (!isset($this->aAlarmRegister[$aObject['id']][$k]))
                $this->aAlarmRegister[$aObject['id']][$k] = $v;
        }

        if (empty($this->aAlarmRegister[$aObject['id']]['alarm_time']))
            $this->aAlarmRegister[$aObject['id']]['alarm_time'] = date('Y-m-d H:i:s');

        $this->oDBAlarmRegister->update($this->aAlarmRegister[$aObject['id']]);

        $this->aAlarmObjects[$aObject['id']] = $aObject;
        $this->aAlarmObjects[$aObject['id']]['reaction_status'] = 1;
        $this->aAlarmObjects[$aObject['id']]['reaction_car'] = 0;
        $this->updateObject($this->aAlarmObjects[$aObject['id']]);

        $this->addEvent(array(
            'target_type' => 'object',
            'target' => $aObject['id'],
            'event_type' => 'alarm',
            'id_msg' => empty($aParams['id_msg']) ? 0 : $aParams['id_msg'],
            'id_sig' => empty($aParams['id_sig']) ? 0 : $aParams['id_sig'],
            'alarm_time' => $this->aAlarmRegister[$aObject['id']]['alarm_time'],
            'alarm_name' => $this->aAlarmRegister[$aObject['id']]['alarm_name'],
            'id_archiv' => empty($aParams['id_archiv']) ? 0 : $aParams['id_archiv']
        ));
    }

    protected function closeAlarm($nIDObject, $nIDReason, $reason_from = '') {
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;
        if ($reason_from != 'system') {
            if (empty($this->aAlarmObjects[$nIDObject]) || empty($this->aAlarmObjects[$nIDObject]['reaction_status']))
                return;
        }

        $aPatrolNums = array();
        $nFirstArriveTime = 0x7FFFFFFF;
        //освобождаване на колите, 
        if (!empty($this->aAlarmPatrols[$nIDObject])) {
            foreach ($this->aAlarmPatrols[$nIDObject] as $nIDCar => $aAlarmPatrol) {
                $aPatrolNums[] = $this->aPatrolCars[$nIDCar]['num_patrul'];

                $nEndTime = 0;
                if (isset($aAlarmPatrol['end_time']) && mysqlDateToTimestamp($aAlarmPatrol['end_time']) > 7200 ) {
                    $nEndTime = mysqlDateToTimestamp($aAlarmPatrol['end_time']);
                } else {
                	$nEndTime = time();
                }
                
                if (!empty($nEndTime)) {
                    /*
                    $real_distance  = 0;

                    if ( !empty($nIDObject) && $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'] > 0 && $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'] > 0 && mysqlDateToTimestamp($this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']) <= 7200) {
                        echo "ID2: ".$this->aAlarmPatrols[$nIDObject][$nIDCar]['id']."\n";

                        $end_geo_lat = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'];
                        $end_geo_lan = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lan'];

                        $start_geo_lat  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'];
                        $start_geo_lan  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lan'];

                        try {
                            $real_distance  = $this->getRealDistance($start_geo_lat, $start_geo_lan, $end_geo_lat, $end_geo_lan);
                            echo "distanciata2: ".$start_geo_lat.":".$start_geo_lan." => ".$end_geo_lat.":".$end_geo_lan." ==> ".$real_distance."\n";
                        } catch (Exception $ex) {
                            echo "failed!\n";
                            print_r($ex);
                        }

                        $this->aAlarmPatrols[$nIDObject][$nIDCar]['route_distance'] = $real_distance;
                    }
                    */
                    if ($nEndTime < $nFirstArriveTime)
                        $nFirstArriveTime = $nEndTime;

                    $this->aAlarmPatrols[$nIDObject][$nIDCar]['reaction_time'] 	= $nEndTime - mysqlDateToTimestamp($aAlarmPatrol['start_time']);
					$this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time'] 		= $nEndTime;
					
                    //koeficient vreme na reakciq (ili neshto ot toq rod)
                    $this->aAlarmPatrols[$nIDObject][$nIDCar]['k_vr'] =
                            $this->aAlarmPatrols[$nIDObject][$nIDCar]['reaction_time'] /
                            $this->estimateArrivalTime($aAlarmPatrol['start_distance'], $this->aAlarmObjects[$nIDObject]['id_reaction_office']);

                    $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);
                }

                $this->freeCar($nIDCar);
            }
        }

        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            if ($aCar['reaction_object'] == $nIDObject)
                $this->freeCar($nIDCar);
        }

        //zatvarqne na alarmata
        $this->aAlarmRegister[$nIDObject]['status'] = 'closed';
        $this->aAlarmRegister[$nIDObject]['arrival_time'] = $nFirstArriveTime != 0x7FFFFFFF ? date('Y-m-d H:i:s', $nFirstArriveTime) : '0000-00-00 00:00:00';
        $this->aAlarmRegister[$nIDObject]['reaction_time'] = date('Y-m-d H:i:s');
        $this->aAlarmRegister[$nIDObject]['id_reason'] = $nIDReason;
        $this->aAlarmRegister[$nIDObject]['reason_from'] = $reason_from;
        $this->aAlarmRegister[$nIDObject]['patruls'] = implode(',', $aPatrolNums);
        
        $this->oDBAlarmRegister->update($this->aAlarmRegister[$nIDObject]);

        
        
        $this->aAlarmObjects[$nIDObject]['reaction_status'] = 0;
        $this->aAlarmObjects[$nIDObject]['reaction_car'] = 0;
        $this->updateObject($this->aAlarmObjects[$nIDObject]);

        $this->addEvent(array(
            'target_type' => 'object',
            'target' => $nIDObject,
            'event_type' => 'cancel',
            'id_reason' => $nIDReason,
        ));
    }

    protected function alarmReason($nIDObject, $nIDReason, $nIDCar = 0) {
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;
        if (empty($nIDReason) || empty($this->aAlarmReasons[$nIDReason]))
            return;
        if (empty($this->aPatrolCars[$nIDCar]))
            $nIDCar = 0;

        $aEvent = array(
            'event_type' => 'reason',
            'id_reason' => $nIDReason
        );

        $reason_from = '';
        if (empty($nIDCar)) {
            $aEvent['target_type'] = 'object';
            $aEvent['target'] = $nIDObject;
            $reason_from = 'dispatcher_object';
            $nDistance = 0;
        } else {
            $this->carArrive($nIDCar);
            $aEvent['target_type'] = 'car';
            if ($this->sEventSource == 'patrol') {
                $reason_from = 'patrol';
            } else if ($this->sEventSource == 'dispatcher') {
                $reason_from = 'dispatcher_for_patrol';
            }
            $aEvent['target'] = $nIDCar;
            $nDistance = self::getDistanceByGeo(
                            $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan'], $this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan']
                    ) * 1000;
        }

        $aEvent['id_reason_object'] = $nIDObject;

        if ($this->aAlarmRegister[$nIDObject]['status'] == 'active')
            $this->closeAlarm($nIDObject, $nIDReason, $reason_from);

        if (!empty($nIDReason)) {
            //eventa se dobavq ot tuka poneje alarmata moje da ne e active
            $aAlarmHistory = array(
                'id_alarm_register' => $this->aAlarmRegister[$nIDObject]['id'],
                'alarm_status' => 'reason',
                'id_patrul' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['id_patrul'],
                'patrul_num' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['num_patrul'],
                'id_auto' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['id'],
                'status_geo' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['status_geo'],
                'status_service' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['service_status'],
                'status_connection' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['status_connection'],
                'patrul_trace' => 0,
                'patrul_cascade_sum' => 0,
                'id_reason' => $nIDReason,
                'distance' => $nDistance,
                'alarm_time' => date('Y-m-d H:i:s'),
                'patrul_geo_lat' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['current_geo_lat'],
                'patrul_geo_lan' => empty($nIDCar) ? 0 : $this->aPatrolCars[$nIDCar]['current_geo_lan'],
                'source_type' => $this->sEventSource
            );
            $this->oDBAlarmHistory->update($aAlarmHistory);
        }
        $this->addEvent($aEvent);
    }

    protected function alarmNotify($nIDObject, $nIDReason) {
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;
        $aAlarmHistory = array(
            'id_alarm_register' => $this->aAlarmRegister[$nIDObject]['id'],
            'alarm_status' => 'notify',
            'alarm_time' => date('Y-m-d H:i:s'),
            'source_type' => $this->sEventSource
        );
        $this->oDBAlarmHistory->update($aAlarmHistory);
        if (
                $this->aAlarmRegister[$nIDObject]['status'] == 'active' &&
                $this->aAlarmObjects[$nIDObject]['id_status'] == $this->nIDStatusMonitoring
        )
            $this->closeAlarm($nIDObject, $nIDReason, 'dispatcher_monitoring');
    }

    protected $aBypassedMessages = array();

    protected function bypassAlarm($nIDObject, $nBypassTime, $nIDReason) {
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;
        $nBypassTime = (int) $nBypassTime;
        if (empty($nBypassTime))
            return;
        $aFirstHistoryItem = $this->oDBAlarmHistory->select("
			SELECT * FROM alarm_history 
			WHERE 1
			AND alarm_status = 'alarm'
			AND id_alarm_register = {$this->aAlarmRegister[$nIDObject]['id']}
			ORDER BY id ASC
			LIMIT 1
		");
        $aFirstHistoryItem = reset($aFirstHistoryItem);
        if (empty($aFirstHistoryItem['id_msg']))
            return;
        $aBypass = array(
            'id_object' => $nIDObject,
            'id_message' => $aFirstHistoryItem['id_msg'],
            'time_to' => date('Y-m-d H:i:s', time() + $nBypassTime),
            'time_from' => date('Y-m-d H:i:s'),
            'id_reason' => (int) $nIDReason
        );
        $this->oDBBypass->update($aBypass);
        $this->aBypassedMessages[$nIDObject][$aFirstHistoryItem['id_msg']] = time() + $nBypassTime;

        //if ($this->aAlarmRegister[$nIDObject]['status'] == 'active')
            $this->closeAlarm($nIDObject, (int) $nIDReason, 'bypass');
    }

    protected function onRemoveBypassAlarm($aData) {
        $this->oDBBypass->delete_permanently($aData['id_bypass']);
        unset($this->aBypassedMessages[$aData['id_object']]);
    }

    protected function carArrive($nIDCar) {
    	//ob_toFile( ArrayToString($this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar]), "asdf.txt");
        if (
                empty($this->aPatrolCars[$nIDCar]) ||
                empty($this->aPatrolCars[$nIDCar]['reaction_object']) ||
                empty($this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar])
        )
            return;
            
        $nIDObject = $this->aPatrolCars[$nIDCar]['reaction_object'];

        //ako veche e pristignala nqma da prisgiga pak
        if (!empty($this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']) && mysqlDateToTimestamp($this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']))
            return;

        //poseteni obekti
        $aVisitedObjectsRows = array();
        $sVisitedType = empty($this->aPatrolCars[$nIDCar]['reaction_status']) ? 'visited' : 'reacted';
        foreach ($this->aPatrolCars[$nIDCar]['patrol_persons'] as $nIDPerson) {
            $aVisitedObjectsRows[] = "($nIDPerson, $nIDObject, '$sVisitedType')";
        }
        if (!empty($aVisitedObjectsRows))
            $this->oDBAlarmArchive->_oDB->Execute("REPLACE INTO visited_objects (id_person,id_object,type) VALUES " . implode(',', $aVisitedObjectsRows));
/*
        $real_distance  = 0;

        if ( !empty($nIDObject) && $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'] > 0 && $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'] > 0) {
            echo "ID3: ".$this->aAlarmPatrols[$nIDObject][$nIDCar]['id']."\n";

            $end_geo_lat = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'];
            $end_geo_lan = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lan'];

            $start_geo_lat  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'];
            $start_geo_lan  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lan'];

            try {
                $real_distance  = $this->getRealDistance($start_geo_lat, $start_geo_lan, $end_geo_lat, $end_geo_lan);
                echo "distanciata3: ".$start_geo_lat.":".$start_geo_lan." => ".$end_geo_lat.":".$end_geo_lan." ==> ".$real_distance."\n";
            } catch (Exception $ex) {
                echo "failed!\n";
                print_r($ex);
            }
        }

        $this->aAlarmPatrols[$nIDObject][$nIDCar]['route_distance'] = $real_distance;
*/
        $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']       = date('Y-m-d H:i:s');
        $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);
        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'arrival',
        ));
        //ob_toFile( ArrayToString($nIDObject));
        if ( $nIDObject > 100000000 ) {
            $this->addEvent(array(
                'target_type' => 'car',
                'target' => $nIDCar,
                'event_type' => 'car_free'
            ));
            
            $this->addEvent(array(
                'target_type' => 'object',
                'target' => $nIDObject,
                'event_type' => 'cancel',
            ));
            
            unset($this->aAlarmObjects[$nIDObject]);
        }
        
        // rychna alarma - zarvarqme q!
        if ( isset($this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar]['id_alarm_register']) && empty($this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar]['id_alarm_register']) ) {
        	//$this->freeCar($nIDCar);      	
        	
            $aData3 = array();
	        $aData3['id'] 				= $nIDCar;
	        $aData3['reaction_object'] 	= 0;
	        
	        $this->oDBCars->update($aData3);    
	        
	        $aData3 = array();
            $aData3['id'] 				= 0;
            $aData3['id_alarm_patrul'] 	= $this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar]['id'];
            $aData3['alarm_time'] 		= time();
            $aData3['alarm_status'] 	= "cancel";
            $aData3['id_patrul'] 		= 0;
            $aData3['patrul_num'] 		= $this->aAlarmPatrols[$this->aPatrolCars[$nIDCar]['reaction_object']][$nIDCar]['patrul_num'];
            $aData3['id_auto'] 			= $nIDCar;
            
            $this->oDBAlarmHistory->update($aData3);  
        }
    }

    protected function getRealDistance($olat, $olan, $dlat, $dlan) {
        $origin_lat = str_replace(',', '.', $olat);
        $origin_lan = str_replace(',', '.', $olan);
        $dest_lat 	= str_replace(',', '.', $dlat);
        $dest_lan 	= str_replace(',', '.', $dlan);

        $origin_lat = floatval($origin_lat);
        $origin_lan = floatval($origin_lan);
        $dest_lat	= floatval($dest_lat);
        $dest_lan 	= floatval($dest_lan);

        $url 		= "http://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin_lat},{$origin_lan}&destinations={$dest_lat},{$dest_lan}&mode=driving&sensor=false";
        $json 		= file_get_contents($url);
        $distance	= 0;
        //$status		= "FAIL";

        if (false !== $json) {
            $result = json_decode($json, true);

            if ( isset($result['status']) && $result['status'] == "OK" ) {
                //$status		= "OK";
                $distance 	= intval($result['rows'][0]['elements'][0]['distance']['value']);

                /*
                foreach($result->rows[0]->elements as $road) {
                    $time 		+= $road->duration->value;
                    $distance 	+= $road->distance->value;
                }
                */
            }
            //var_dump($result);
        }

        return $distance;
    }

    protected function freeCar($nIDCar, $nIDReason = 0) {
        $this->carConfirmAlertness($nIDCar, 0);
        $nIDObject      = intval($this->aPatrolCars[$nIDCar]['reaction_object']);
        /*
        $real_distance  = 0;

        if ( !empty($nIDObject) && $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'] > 0 && $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'] > 0 && empty($this->aPatrolCars[$nIDCar]['reaction_status']) ) {
            echo "ID: ".$this->aAlarmPatrols[$nIDObject][$nIDCar]['id']."\n";

            $end_geo_lat = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'];
            $end_geo_lan = $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lan'];

            $start_geo_lat  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lat'];
            $start_geo_lan  = $this->aAlarmPatrols[$nIDObject][$nIDCar]['start_geo_lan'];

            try {
                $real_distance  = $this->getRealDistance($start_geo_lat, $start_geo_lan, $end_geo_lat, $end_geo_lan);
                echo "distanciata: ".$start_geo_lat.":".$start_geo_lan." => ".$end_geo_lat.":".$end_geo_lan." ==> ".$real_distance."\n";
            } catch (Exception $ex) {
                echo "failed!\n";
                print_r($ex);
            }
        }
        */
        //ob_toFile( ArrayToString($this->aAlarmPatrols[$nIDObject][$nIDCar]), "asdf.txt");
        if (empty($this->aPatrolCars[$nIDCar]['reaction_status'])) {
            if (empty($nIDObject))
                return;

            //prekratqvane na "vodi me"
            if (!empty($this->aAlarmPatrols[$nIDObject]) && !empty($this->aAlarmPatrols[$nIDObject][$nIDCar]['id']) ) {

                //$this->aAlarmPatrols[$nIDObject][$nIDCar]['route_distance'] = $real_distance;
                $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']       = time();
                $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);

                $aData = array();
                $aData['id'] = 0;
                $aData['id_alarm_patrul'] = $this->aAlarmPatrols[$nIDObject][$nIDCar]['id'];
                $aData['alarm_time'] = time();
                $aData['alarm_status'] = "cancel";
                $aData['id_patrul'] = $this->aAlarmPatrols[$nIDObject][$nIDCar]['id_patrul'];
                $aData['patrul_num'] = $this->aAlarmPatrols[$nIDObject][$nIDCar]['patrul_num'];
                $aData['id_auto'] = $nIDCar;
                
                $this->oDBAlarmHistory->update($aData);
                
	            $this->addEvent(array(
	                'target_type' => 'car',
	                'target' => $nIDCar,
	                'event_type' => 'car_free',
	                'id_reason' => $nIDReason
	            ));
                       
	            if ( $nIDObject > 100000000 ) {     
	                $this->addEvent(array(
	                    'target_type' => 'object',
	                    'target' => $nIDObject,
	                    'event_type' => 'cancel',
	                ));
	                
	                unset($this->aAlarmObjects[$nIDObject]); 
	            }
                 
                //ob_toFile( ArrayToString($this->aAlarmPatrols[$nIDObject][$nIDCar]), "asdf.txt");
                unset($this->aAlarmPatrols[$nIDObject][$nIDCar]);
            }
            $this->aPatrolCars[$nIDCar]['reaction_object'] 	= 0;
            $this->aPatrolCars[$nIDCar]['end_time'] 		= time();
            $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

            if ( !empty($this->aAlarmPatrols[$nIDObject][$nIDCar]['id']) ) {
                //$this->aAlarmPatrols[$nIDObject][$nIDCar]['route_distance'] = $real_distance;
                $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time']       = time();
                $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);
                //echo "Route distance: ".$real_distance."\n";
            }

            $this->addEvent(array(
                'target_type' => 'car',
                'target' => $nIDCar,
                'event_type' => 'car_free',
            ));
            
            return;
        }
        
        $nIDObject = $this->aPatrolCars[$nIDCar]['reaction_object'];

    	//$this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time'] = time();
        //$this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]); 
                    
//		if($this->aPatrolCars[$nIDCar]['reaction_status'] == 1) {
        $this->aAnnounceRejections[$nIDObject][$nIDCar] = time();
//		}

        $this->aPatrolCars[$nIDCar]['reaction_status'] 	= 0;
        $this->aPatrolCars[$nIDCar]['reaction_object'] 	= 0;
        $this->aPatrolCars[$nIDCar]['reaction_time'] 	= 0;
        $this->aPatrolCars[$nIDCar]['end_time'] 		= time();
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        //състоянието на обекта се обновява
        if ($this->aAlarmObjects[$nIDObject]['reaction_car'] == $nIDCar && !empty($this->aAlarmObjects[$nIDObject]['reaction_status'])) {
            $this->aAlarmObjects[$nIDObject]['reaction_car'] = 0;
            //ако има друга кола към този обект тя се отбелязва като реагища
            foreach ($this->aAlarmPatrols[$nIDObject] as $nIDOtherCar => $aAlarmPatrol) {
                if (
                        $nIDOtherCar != $nIDCar &&
                        $this->aPatrolCars[$nIDOtherCar]['reaction_status'] == 2 &&
                        $this->aPatrolCars[$nIDOtherCar]['reaction_object'] == $nIDObject
                ) {
                    $this->aAlarmObjects[$nIDObject]['reaction_car'] = $nIDOtherCar;
                    break;
                }
            }
            $this->updateObject($this->aAlarmObjects[$nIDObject]);
        }



        $nIDReason = (int) $nIDReason;

        if (!empty($this->aAlarmRegister[$nIDObject])) {
            //eventa se dobavq ot tuka poneje kolata veche e svobodna
            $aAlarmHistory = array(
                'id_alarm_register' => $this->aAlarmRegister[$nIDObject]['id'],
                'alarm_status' => $nIDReason ? 'refusal_car' : 'car_free',
                'id_patrul' => $this->aPatrolCars[$nIDCar]['id_patrul'],
                'patrul_num' => $this->aPatrolCars[$nIDCar]['num_patrul'],
                'id_auto' => $this->aPatrolCars[$nIDCar]['id'],
                'status_geo' => $this->aPatrolCars[$nIDCar]['status_geo'],
                'status_service' => $this->aPatrolCars[$nIDCar]['service_status'],
                'status_connection' => $this->aPatrolCars[$nIDCar]['status_connection'],
                'patrul_trace' => 0,
                'patrul_cascade_sum' => 0,
                'id_reason' => $nIDReason,
                'distance' => 0,
                'alarm_time' => date('Y-m-d H:i:s'),
                'patrul_geo_lat' => $this->aPatrolCars[$nIDCar]['current_geo_lat'],
                'patrul_geo_lan' => $this->aPatrolCars[$nIDCar]['current_geo_lan'],
                'source_type' => $this->sEventSource
            );
            $this->oDBAlarmHistory->update($aAlarmHistory);
            
            $aData = array();
	        $aData['id'] 				= $this->aPatrolCars[$nIDCar]['id'];
	        $aData['reaction_object'] 	= 0;
	        
	        $this->oDBCars->update($aData);
        }

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => $nIDReason ? 'refusal_car' : 'car_free',
            'id_reason' => $nIDReason
        ));
    }

    protected function startCarDirection($nIDCar, $nIDObject) {
        if (empty($this->aPatrolCars[$nIDCar]) && empty($nIDObject))
            return;
        if ($this->aPatrolCars[$nIDCar]['reaction_object'] == $nIDObject && $this->aPatrolCars[$nIDCar]['reaction_status'] == 0)
            return;

        if (empty($this->aAlarmObjects[$nIDObject])) {
            $aObject = $this->getObject($nIDObject);
            if (empty($aObject))
                return;
            $this->aAlarmObjects[$nIDObject] = $aObject;
        }

        if (!empty($this->aAlarmRegister[$nIDObject]) && $this->aAlarmRegister[$nIDObject]['status'] != 'closed')
            return;

        $this->freeCar($nIDCar);

        $this->aPatrolCars[$nIDCar]['reaction_object'] = $nIDObject;

        $real_distance  = 0;

        if ( !empty($nIDObject) && $this->aPatrolCars[$nIDCar]['current_geo_lan'] > 0 && $this->aPatrolCars[$nIDCar]['current_geo_lat'] > 0) {
            echo "ID1: ".$nIDObject."\n";

            $end_geo_lat = $this->aAlarmObjects[$nIDObject]['geo_lat'];
            $end_geo_lan = $this->aAlarmObjects[$nIDObject]['geo_lan'];

            $start_geo_lat  = $this->aPatrolCars[$nIDCar]['current_geo_lat'];
            $start_geo_lan  = $this->aPatrolCars[$nIDCar]['current_geo_lan'];

            try {
                $real_distance  = $this->getRealDistance($start_geo_lat, $start_geo_lan, $end_geo_lat, $end_geo_lan);
                echo "distanciata1: ".$start_geo_lat.":".$start_geo_lan." => ".$end_geo_lat.":".$end_geo_lan." ==> ".$real_distance."\n";
            } catch (Exception $ex) {
                echo "failed!\n";
                print_r($ex);
            }
        }

        if ( $real_distance < 50 ) {
            $real_distance = self::getDistanceByGeo($this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan'], $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']) * 1000;
        }

        $this->aAlarmPatrols[$nIDObject][$nIDCar] = array(
            'id_alarm_register' => 0,
            'id_object' => $this->aPatrolCars[$nIDCar]['reaction_object'],
            'id_road_list' => $this->aPatrolCars[$nIDCar]['id_road_list'],
            'patrul_num' => $this->aPatrolCars[$nIDCar]['num_patrul'],
            'start_geo_lan' => $this->aPatrolCars[$nIDCar]['current_geo_lan'],
            'start_geo_lat' => $this->aPatrolCars[$nIDCar]['current_geo_lat'],
            'start_time' => date('Y-m-d H:i:s'),
            'route_distance' => $real_distance,
            'start_distance' => self::getDistanceByGeo(
                $this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan'], $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']
            ) * 1000
        );

        //init na vremennite danni za izminato razstoqnie
        $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'] = $this->aPatrolCars[$nIDCar]['current_geo_lat'];
        $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lan'] = $this->aPatrolCars[$nIDCar]['current_geo_lan'];
        $this->aAlarmPatrols[$nIDObject][$nIDCar]['reaction_distance'] = 0;
        $this->aAlarmPatrols[$nIDObject][$nIDCar]['total_distance'] = 0;

        $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        $this->addEvent(array(
            'target_type' => 'object',
            'target' => $nIDObject,
            'event_type' => 'update',
        ));

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'position',
        ));
    }

    protected function startCar($nIDCar, $nIDObject) {
        if (empty($this->aPatrolCars[$nIDCar]))
            return;
        if (empty($this->aAlarmObjects[$nIDObject]) || empty($this->aAlarmObjects[$nIDObject]['reaction_status']))
            return;
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;

        $sReactionStartTime = $this->aPatrolCars[$nIDCar]['reaction_status'] == 1 ? $this->aPatrolCars[$nIDCar]['reaction_time'] : date('Y-m-d H:i:s');

        //Освобождаваме колата ако не е в режим анонс
        if ($this->aPatrolCars[$nIDCar]['reaction_status'] != 1) {
            $this->freeCar($nIDCar);
        } else {
            $this->carConfirmAlertness($nIDCar, 0);
        }

        //Маркираме колата като заета
        $this->aPatrolCars[$nIDCar]['reaction_status'] = 2;
        $this->aPatrolCars[$nIDCar]['reaction_object'] = $nIDObject;
        $this->aPatrolCars[$nIDCar]['reaction_time'] = date('Y-m-d H:i:s');
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        //Маркираме обекта че има кола към него ако е първата
        if (empty($this->aAlarmObjects[$nIDObject]['reaction_car'])) {
            $this->aAlarmObjects[$nIDObject]['reaction_car'] = $nIDCar;
            $this->updateObject($this->aAlarmObjects[$nIDObject]);
        }

        if (empty($this->aAlarmPatrols[$nIDObject]))
            $this->aAlarmPatrols[$nIDObject] = array();
        if (empty($this->aAlarmPatrols[$nIDObject][$nIDCar])) {

            $real_distance  = 0;

            if ( !empty($nIDObject) && $this->aPatrolCars[$nIDCar]['current_geo_lan'] > 0 && $this->aPatrolCars[$nIDCar]['current_geo_lat'] > 0) {
                echo "ID1: ".$nIDObject."\n";

                $end_geo_lat = $this->aAlarmObjects[$nIDObject]['geo_lat'];
                $end_geo_lan = $this->aAlarmObjects[$nIDObject]['geo_lan'];

                $start_geo_lat  = $this->aPatrolCars[$nIDCar]['current_geo_lat'];
                $start_geo_lan  = $this->aPatrolCars[$nIDCar]['current_geo_lan'];

                try {
                    $real_distance  = $this->getRealDistance($start_geo_lat, $start_geo_lan, $end_geo_lat, $end_geo_lan);
                    echo "distanciata1: ".$start_geo_lat.":".$start_geo_lan." => ".$end_geo_lat.":".$end_geo_lan." ==> ".$real_distance."\n";
                } catch (Exception $ex) {
                    echo "failed!\n";
                    print_r($ex);
                }
            }

            if ( $real_distance < 50 ) {
                $real_distance = self::getDistanceByGeo($this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan'], $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']) * 1000;
            }

            $this->aAlarmPatrols[$nIDObject][$nIDCar] = array(
                'id_alarm_register' => $this->aAlarmRegister[$nIDObject]['id'],
                'id_object' => $nIDObject,
                'id_road_list' => $this->aPatrolCars[$nIDCar]['id_road_list'],
                'patrul_num' => $this->aPatrolCars[$nIDCar]['num_patrul'],
                'start_geo_lan' => $this->aPatrolCars[$nIDCar]['current_geo_lan'],
                'start_geo_lat' => $this->aPatrolCars[$nIDCar]['current_geo_lat'],
                'start_time' => $sReactionStartTime,
                'route_distance' => $real_distance,
                'start_distance' => self::getDistanceByGeo(
                        $this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan'], $this->aPatrolCars[$nIDCar]['current_geo_lat'], $this->aPatrolCars[$nIDCar]['current_geo_lan']
                ) * 1000
            );
            //init na vremennite danni za izminato razstoqnie
            $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lat'] = $this->aPatrolCars[$nIDCar]['current_geo_lat'];
            $this->aAlarmPatrols[$nIDObject][$nIDCar]['end_geo_lan'] = $this->aPatrolCars[$nIDCar]['current_geo_lan'];
            $this->aAlarmPatrols[$nIDObject][$nIDCar]['reaction_distance'] = 0;
            $this->aAlarmPatrols[$nIDObject][$nIDCar]['total_distance'] = 0;
//			$this->aAlarmPatrols[$nIDObject][$nIDCar]['end_time'] = date('Y-m-d H:i:s');
        }

        $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$nIDCar]);

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'start',
        ));
    }

    protected function announceCar($nIDCar, $nIDObject) {

        if (empty($this->aPatrolCars[$nIDCar]))
            return;
        if (empty($this->aAlarmObjects[$nIDObject]) || empty($this->aAlarmObjects[$nIDObject]['reaction_status']))
            return;
        if (empty($this->aAlarmRegister[$nIDObject]))
            return;

        //Освобождаваме колата
        //if (!empty($this->aPatrolCars[$nIDCar]['reaction_status']))
            $this->freeCar($nIDCar);

        //Маркираме колата като анонсирана
        $this->aPatrolCars[$nIDCar]['reaction_status'] = 1;
        $this->aPatrolCars[$nIDCar]['reaction_object'] = $nIDObject;
        $this->aPatrolCars[$nIDCar]['reaction_time'] = date('Y-m-d H:i:s');
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        if (empty($this->aAlarmRegister[$nIDObject]['announce_time'])) {
            $this->aAlarmRegister[$nIDObject]['announce_time'] = date('Y-m-d H:i:s');
            $this->oDBAlarmRegister->update($this->aAlarmRegister[$nIDObject]);
        }

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'announce',
        ));
    }

            //$aEvent['statusService'] = !empty($this->aPatrolCars[$aEvent['target']]['service_status']);
            //$aEvent['statusServiceReason'] = $this->aPatrolCars[$aEvent['target']]['service_status_reason'];
			//$aEvent['serReason'] = $this->aPatrolCars[$aEvent['target']]['service_reason'];
			
    protected function carService($nIDCar, $nIDReason, $nServiceTime) {
        if (empty($this->aPatrolCars[$nIDCar]))
            return;
        $nServiceTime = (int) $nServiceTime;
        if (empty($nServiceTime))
            return;
        $nIDReason = (int) $nIDReason;
        $this->freeCar($nIDCar);
        $this->aPatrolCars[$nIDCar]['service_status'] = 1;
        $this->aPatrolCars[$nIDCar]['service_status_reason'] = $nIDReason;
        $this->aPatrolCars[$nIDCar]['end_service_status_time'] = date('Y-m-d H:i:s', time() + $nServiceTime);
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'position',
            'id_service_reason' => $nIDReason
        ));
    }

    protected function carCancelService($nIDCar) {
        if (empty($this->aPatrolCars[$nIDCar]) || empty($this->aPatrolCars[$nIDCar]['service_status']))
            return;
        $this->aPatrolCars[$nIDCar]['service_status'] 			= 0;
        $this->aPatrolCars[$nIDCar]['service_status_reason'] 	= 0;
        $this->aPatrolCars[$nIDCar]['end_time'] 				= time();
        $this->oDBCars->update($this->aPatrolCars[$nIDCar]);

        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'position',
        ));
    }

    protected function carConfirmAlertness($nIDCar, $nIDPerson) {
        global $db_sod;
        if (empty($this->aPatrolCars[$nIDCar]['alertness_checks']))
            return;
        $nIDPerson = (int) $nIDPerson;
        $db_sod->Execute(sprintf("
			UPDATE alertness_checks	SET
			confirm_time = NOW(),
			id_confirm_person = %s
			WHERE
			id IN (%s)
			"
                        , (int) $nIDPerson
                        , implode(',', $this->aPatrolCars[$nIDCar]['alertness_checks'])
                ));
        $this->aPatrolCars[$nIDCar]['alertness_checks'] = array();
        $this->aPatrolCars[$nIDCar]['alertness_check_time'] = null;
        $this->addEvent(array(
            'target_type' => 'car',
            'target' => $nIDCar,
            'event_type' => 'position',
        ));
    }

    protected $aEvents = array();

    protected function addEvent($aEvent) {
    	//ob_toFile(ArrayToString($this->aPatrolCars[$aEvent['target']]));
        if ($aEvent['target_type'] == 'car' && empty($aEvent['nopatrol'])) {
            //dobavqne na parametrite na kolata
            if (empty($this->aPatrolCars[$aEvent['target']]))
                return;
            $aEvent['id'] = $aEvent['target'];
            $aEvent['idRegions'] = array_merge($this->aPatrolCars[$aEvent['target']]['offices'], $this->aPatrolCars[$aEvent['target']]['geo_regions']);
            $aEvent['callsign'] = $this->aPatrolCars[$aEvent['target']]['num_patrul'];
            $aEvent['regnum'] = $this->aPatrolCars[$aEvent['target']]['car_reg_num'];
            $aEvent['phoneNum'] = $this->aPatrolCars[$aEvent['target']]['car_phone'];
            $aEvent['car_function'] = $this->aPatrolCars[$aEvent['target']]['car_function'];
            $aEvent['mainOffice'] = $this->aPatrolCars[$aEvent['target']]['id_main_office'];

            $aEvent['statusService'] = !empty($this->aPatrolCars[$aEvent['target']]['service_status']);
            $aEvent['statusServiceReason'] = $this->aPatrolCars[$aEvent['target']]['service_status_reason'];
			//$aEvent['serReason'] = $this->aPatrolCars[$aEvent['target']]['service_status_reason'];
			$aEvent['serReason'] = isset($this->aCancelReasons[$this->aPatrolCars[$aEvent['target']]['service_status_reason']]['name']) ? $this->aCancelReasons[$this->aPatrolCars[$aEvent['target']]['service_status_reason']]['name'] : "";
            $aEvent['statusConnection'] = $this->aPatrolCars[$aEvent['target']]['status_connection'];

            //gps state
            $aEvent['statusGeo'] = $this->aPatrolCars[$aEvent['target']]['status_geo'];
            $aEvent['geo_lat'] = $this->aPatrolCars[$aEvent['target']]['current_geo_lat'];
            $aEvent['geo_lan'] = $this->aPatrolCars[$aEvent['target']]['current_geo_lan'];
            $aEvent['avg_speed'] = $this->aPatrolCars[$aEvent['target']]['avg_speed'];

            $aEvent['client_geo_lan'] = $this->aPatrolCars[$aEvent['target']]['current_client_geo_lan'];
            $aEvent['client_geo_lat'] = $this->aPatrolCars[$aEvent['target']]['current_client_geo_lat'];
            $aEvent['client_geo_accuracy'] = $this->aPatrolCars[$aEvent['target']]['current_client_geo_accuracy'];
            $aEvent['client_geo_time'] = $this->aPatrolCars[$aEvent['target']]['current_client_geo_time'];

            //alertness check status
            $aEvent['alertnessCheckTime'] = !empty($this->aPatrolCars[$aEvent['target']]['alertness_checks']) ? mysqlDateToTimestamp($this->aPatrolCars[$aEvent['target']]['alertness_check_time']) : false;


            //reason text
            if (!empty($aEvent['id_reason']) && $aEvent['event_type'] == 'reason') {
                $aEvent['reason'] = $this->aAlarmReasons[$aEvent['id_reason']];
            }

            $aEvent['statusReaction'] = 'free';

            if ($nIDObject = $this->aPatrolCars[$aEvent['target']]['reaction_object']) {
                $aEvent['idObject'] = $nIDObject;

                //razstoqnie do obekta
                $aEvent['distance'] = self::getDistanceByGeo(
                                $aEvent['geo_lat'], $aEvent['geo_lan'], $this->aAlarmObjects[$nIDObject]['geo_lat'], $this->aAlarmObjects[$nIDObject]['geo_lan']
                        ) * 1000;

                if ($this->aPatrolCars[$aEvent['target']]['reaction_status'] == 1)
                    $aEvent['statusReaction'] = 'announce';
                elseif ($this->aPatrolCars[$aEvent['target']]['reaction_status'] > 1)
                    $aEvent['statusReaction'] = 'reaction';


                $nPatrolTotalDistance = 0;
                $nPatrolMoveDistance = 0;

                //sybitie za pristigane
                if ($aEvent['distance'] < $this->nDistanceToArrival) {
                    $sOldEventSource = $this->sEventSource;
                    $this->sEventSource = 'system';
                    $this->carArrive($aEvent['target']);
                    $this->sEventSource = $sOldEventSource;
                }

                if (!empty($this->aAlarmPatrols[$nIDObject][$aEvent['target']])) {
                    $nIDAlarmRegister = $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['id_alarm_register'];
                    $nIDAlarmPatrol = $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['id'];

                    //reaction time
                    $aEvent['reactionElapsedTime'] = time() - mysqlDateToTimestamp($this->aAlarmPatrols[$nIDObject][$aEvent['target']]['start_time']);
                    $aEvent['reactionTimeNormal'] = $this->estimateArrivalTime(
                            $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['start_distance'], $this->aAlarmObjects[$nIDObject]['id_reaction_office']
                    );

                    if ($aEvent['event_type'] == 'position') {
                        $nPatrolMoveDistance = self::getDistanceByGeo(
                                        $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_geo_lat'], $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_geo_lan'], $aEvent['geo_lat'], $aEvent['geo_lan']
                                ) * 1000;

                        $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['total_distance'] += $nPatrolMoveDistance;
                        $nPatrolTotalDistance = $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['total_distance'];

                        // reaction_distance e dokato pristigne
                        if (empty($this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_time']) || $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_time'] == '0000-00-00 00:00:00') {
                            $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['reaction_distance'] = $nPatrolTotalDistance;
                            $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_geo_lat'] = $aEvent['geo_lat'];
                            $this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_geo_lan'] = $aEvent['geo_lan'];
                        } else {
                            $aEvent['arrivalTime'] = mysqlDateToTimestamp($this->aAlarmPatrols[$nIDObject][$aEvent['target']]['end_time']) - mysqlDateToTimestamp($this->aAlarmPatrols[$nIDObject][$aEvent['target']]['start_time']);
                        }

                        $this->oDBAlarmPatruls->update($this->aAlarmPatrols[$nIDObject][$aEvent['target']]);
                    }
                } else {
                    $nIDAlarmRegister = !empty($this->aAlarmRegister[$nIDObject]) ? $this->aAlarmRegister[$nIDObject]['id'] : 0;
                    $nIDAlarmPatrol = 0;
                }

                //dobavqne v alarm_history
                //ako ima session id subitieto e za obnovqvane pri konkreten potrebitel i ne se dobavq v DB
                if (empty($aEvent['session_id']) && (!empty($nIDAlarmRegister) || !empty($nIDAlarmPatrol)) && in_array($aEvent['event_type'], array('position', 'announce', 'start', 'arrival', 'reason', 'car_free', 'refusal_car', 'gps_failure'))) {

                    $aAlarmHistory = array(
                        'id_alarm_register' => $nIDAlarmRegister,
                        'id_alarm_patrul' => $nIDAlarmPatrol,
                        'alarm_status' => $aEvent['event_type'],
                        'id_patrul' => $this->aPatrolCars[$aEvent['target']]['id_patrul'],
                        'patrul_num' => $this->aPatrolCars[$aEvent['target']]['num_patrul'],
                        'id_auto' => $this->aPatrolCars[$aEvent['target']]['id'],
                        'status_geo' => $this->aPatrolCars[$aEvent['target']]['status_geo'],
                        'status_service' => $this->aPatrolCars[$aEvent['target']]['service_status'],
                        'status_connection' => $this->aPatrolCars[$aEvent['target']]['status_connection'],
                        'patrul_trace' => $nPatrolMoveDistance,
                        'patrul_trace_cascade_sum' => $nPatrolTotalDistance,
                        'avg_speed' => $aEvent['avg_speed'],
                        'id_reason' => empty($aEvent['id_reason']) ? 0 : $aEvent['id_reason'],
                        'distance' => $aEvent['distance'],
                        'patrul_geo_lat' => $aEvent['geo_lat'],
                        'patrul_geo_lan' => $aEvent['geo_lan'],
                        'alarm_time' => date('Y-m-d H:i:s'),
                        'source_type' => $this->sEventSource
                    );
                    $this->oDBAlarmHistory->update($aAlarmHistory);
                }
            }
        } elseif ($aEvent['target_type'] == 'car' && !empty($aEvent['nopatrol'])) {
            if (empty($this->aOtherCars[$aEvent['target']]))
                return;
            $aEvent['id'] = $aEvent['target'];
            $aEvent['idRegions'] = $this->aOtherCars[$aEvent['target']]['offices'];
            $aEvent['regnum'] = $this->aOtherCars[$aEvent['target']]['car_reg_num'];
            $aEvent['phoneNum'] = $this->aOtherCars[$aEvent['target']]['car_phone'];
            $aEvent['car_function'] = $this->aOtherCars[$aEvent['target']]['car_function'];
            $aEvent['car_function_name'] = $this->aOtherCars[$aEvent['target']]['car_function_name'];
            $aEvent['geo_lat'] = $this->aOtherCars[$aEvent['target']]['current_geo_lat'];
            $aEvent['geo_lan'] = $this->aOtherCars[$aEvent['target']]['current_geo_lan'];
            $aEvent['mainOffice'] = $this->aOtherCars[$aEvent['target']]['id_main_office'];
        } elseif ($aEvent['target_type'] == 'object') {
            //dobavqne na parametri za obekta			
            $aObjectGeo = $this->getObject($aEvent['target']);
            $geo_lat = $aObjectGeo['geo_lat'];
            $geo_lan = $aObjectGeo['geo_lan'];
            $id_region = empty($this->aAlarmObjects[$aEvent['target']]['id_reaction_office']) ? $this->aAlarmRegister[$aEvent['target']]['id_office'] : $this->aAlarmObjects[$aEvent['target']]['id_reaction_office'];
            if (empty($this->aAlarmObjects[$aEvent['target']]))
                return;
            $aEvent['id'] = $aEvent['target'];
            $aEvent['idRegions'] = array($id_region);
            $aEvent['num'] = $this->aAlarmObjects[$aEvent['target']]['num'];
            $aEvent['name'] = $this->aAlarmObjects[$aEvent['target']]['name'];
            $aEvent['geo_lat'] = $geo_lat;
            $aEvent['geo_lan'] = $geo_lan;
            $aEvent['statusAlarm'] = !empty($this->aAlarmObjects[$aEvent['target']]['reaction_status']);
            $aEvent['isSod'] = !empty($this->aAlarmObjects[$aEvent['target']]['status_is_sod']);
            $aEvent['isMonitoring'] = $this->aAlarmObjects[$aEvent['target']]['id_status'] == $this->nIDStatusMonitoring;

            if (!empty($this->aAlarmRegister[$aEvent['target']]['id']) && $this->aAlarmRegister[$aEvent['target']]['status'] != 'closed') {
                $aEvent['alarmName'] = empty($aEvent['alarm_name']) ? $this->aAlarmRegister[$aEvent['target']]['last_alarm_name'] : $aEvent['alarm_name'];
                $aEvent['statusService'] = !empty($this->aAlarmObjects[$aEvent['target']]['service_status']);
                $aEvent['alarmElapsedTime'] = time() - mysqlDateToTimestamp($this->aAlarmRegister[$aEvent['target']]['alarm_time']);
                $aEvent['alarmTime'] = mysqlDateToTimestamp($this->aAlarmRegister[$aEvent['target']]['alarm_time']);
                $aEvent['reactionTimeNormal'] = $this->aAlarmObjects[$aEvent['target']]['reaction_time_normal'] * 60;
                $aEvent['reactionTimeDifficult'] = $this->aAlarmObjects[$aEvent['target']]['reaction_time_difficult'] * 60;
                $aEvent['idAlarmRegister'] = $this->aAlarmRegister[$aEvent['target']]['id'];
            }

            if (!empty($aEvent['id_reason']) && $aEvent['event_type'] == 'cancel') {
                $aEvent['reason'] = $this->aAlarmReasons[$aEvent['id_reason']];
            }

            //dobavqne na zapis v history-to
            if (!empty($this->aAlarmObjects[$aEvent['target']]) && in_array($aEvent['event_type'], array('alarm', 'cancel', 'update')) && !empty($this->aAlarmRegister[$aEvent['target']])) {
                //ako ima session id subitieto e za obnovqvane pri konkreten potrebitel i ne se dobavq v DB
                if (empty($aEvent['session_id'])) {
                    $this->aAlarmRegister[$aEvent['target']]['last_alarm_name'] = empty($aEvent['alarm_name']) ? '' : $aEvent['alarm_name'];
                    $aAlarmHistory = array(
                        'id_alarm_register' => $this->aAlarmRegister[$aEvent['target']]['id'],
                        'id_sig' => empty($aEvent['id_sig']) ? 0 : $aEvent['id_sig'],
                        'id_msg' => empty($aEvent['id_msg']) ? 0 : $aEvent['id_msg'],
                        'id_archiv' => empty($aEvent['id_archiv']) ? 0 : $aEvent['id_archiv'],
                        'alarm_time' => empty($aEvent['alarm_time']) ? date('Y-m-d H:i:s') : $aEvent['alarm_time'],
                        'alarm_name' => empty($aEvent['alarm_name']) ? '' : $aEvent['alarm_name'],
                        'alarm_status' => $aEvent['event_type'],
                        'id_reason' => empty($aEvent['id_reason']) ? 0 : $aEvent['id_reason'],
                        'source_type' => $this->sEventSource
                    );
                    $this->oDBAlarmHistory->update($aAlarmHistory);
                }
            }
        } elseif ($aEvent['target_type'] == 'safd') {
            return;
        }

        $aEvent['time'] = date('Y-m-d H:i:s');
        //file_put_contents("D:/tmp/monitoring_events.txt",print_r($aEvent,true),FILE_APPEND);

        $this->oWSSEvents->addEvent($aEvent);
    }

    public function run() {
        $this->loadPatrolCars();
        $this->loadOtherCars();
        $this->loadAlarms();
        $this->loadRegionBounds();
        while (2) {
            try {
                $this->loadSettings();

                $this->processSignals();

                $this->processCars();

                $this->processOtherCars();

                $this->processAlarms();

                $this->processCommands();

                $this->processWaypoints();

                $this->oWSSEvents->sendEvents();

                $this->gc();
            } catch (Exception $e) {
                var_dump($e);
            }
            sleep(1);
        }
    }

    /*     * **************************************************************
     * 
     * komandi ot platno ili display na kola 
     *
     * ************************************************************** */

    protected function sendErrorEvent($sSessionID, $sMessage) {
        if (empty($sSessionID) || empty($sMessage))
            return;
        $this->oWSSEvents->addEvent(array('session_id' => $sSessionID, 'message' => $sMessage));
    }

    /**
     * типове команди
     *
     * @var array
     */
    protected static $aCommandTypes = array(
        'bypass_alarm' => array('fn' => 'onBypassAlarm'),
        'remove_bypass_alarm' => array('fn' => 'onRemoveBypassAlarm'),
        'car_direction' => array('fn' => 'onCarDirection'),
        'car_cancel_direction' => array('fn' => 'onCarCancelDirection'),
        'car_react' => array('fn' => 'onCarReact'),
        'car_arrive' => array('fn' => 'onCarArrive'),
        'car_announce' => array('fn' => 'onCarAnnounce'),
        'car_reject' => array('fn' => 'onReject'),
        'alarm_reason' => array('fn' => 'onAlarmReason'),
        'alarm_notify' => array('fn' => 'onAlarmNotify'),
        'car_service' => array('fn' => 'onCarService'),
        'car_cancel_service' => array('fn' => 'onCarCancelService'),
        'car_confirm_alertness' => array('fn' => 'onCarConfirmAlertness'),
        'init' => array('fn' => 'onCanvasInit'),
        'display_init' => array('fn' => 'onDisplayInit'),
        'reset_alarms' => array('fn' => 'onResetAlarms'),
        'stop' => array(),
    );

    protected function processCommands() {
        global $db_sod;
        $aQuotedCommandTypes = array();
        foreach (self::$aCommandTypes as $sCommandType => $aCommand) {
            $aQuotedCommandTypes[] = $db_sod->Quote($sCommandType);
        }
        $sCommandTypes = implode(',', $aQuotedCommandTypes);
        $aCommands = $this->oDBMonitoringCommands->selectAssoc("
			SELECT me.id as __key,me.*
			FROM monitoring_commands me
			WHERE type IN ($sCommandTypes)
		");
        if (empty($aCommands))
            return;
        $db_sod->Execute(sprintf("
			DELETE FROM monitoring_commands
			WHERE id IN (%s)
			"
                        , implode(',', array_keys($aCommands))
                ));
        //file_put_contents('log_mon.log', ArrayToString($aCommands)."\n", FILE_APPEND);
        foreach ($aCommands as $aCommand) {
            $aData = json_decode($aCommand['data'], true);
            
            if ($aCommand['type'] == 'stop')
                die("Stop command at " . date("Y-m-d H:i:s"));
            if (is_callable(array($this, self::$aCommandTypes[$aCommand['type']]['fn']))) {
                try {
                    if (!empty($aData['is_dispatcher'])) {
                        $this->sEventSource = 'dispatcher';
                    } elseif ($aData['is_patrol']) {
                        $this->sEventSource = 'patrol';
                    } else {
                        throw new Exception('Invalid source type.');
                    }
                    call_user_func_array(array($this, self::$aCommandTypes[$aCommand['type']]['fn']), array($aData, $aCommand['session_id']));
                    $this->sEventSource = 'system';
                } catch (Exception $e) {
                    $this->sEventSource = 'system';
                    $sMsg = $e->getMessage();
                    if (empty($sMsg))
                        $sMsg = "Грешка при изпълнение на операцията.";
                    $this->sendErrorEvent($aCommand['session_id'], $sMsg);
                }
            }
        }
    }

    protected function onBypassAlarm($aData) {
        $nIDObject = (int) $aData['id_object'];
        if (empty($nIDObject) || empty($this->aAlarmRegister[$nIDObject])) {
            throw new Exception("Обекта не е под аларма.");
        }

        if (empty($aData['bypass_time']) || ((int) $aData['bypass_time']) <= 0)
            $aData['bypass_time'] = $this->nDefaultBypassTime;
        $this->bypassAlarm($nIDObject, $aData['bypass_time'], $aData['id_reason']);
    }

    protected function onCarDirection($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        if (empty($aData['id_object']) || !($nIDObject = intval($aData['id_object'])))
            throw new Exception("Невалиден обект.");

        $this->startCarDirection($nIDCar, $nIDObject);
    }

    protected function onCarCancelDirection($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        if (!empty($this->aPatrolCars[$nIDCar]['reaction_status']) || empty($this->aPatrolCars[$nIDCar]['reaction_object']))
            throw new Exception("Патрула не е в режим 'води ме'.");
        $this->freeCar($nIDCar);
    }

    protected function onCarReact($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        if (!empty($aData['id_object'])) {
            $nIDObject = (int) $aData['id_object'];
        } else {
            if ($this->aPatrolCars[$nIDCar]['reaction_status'] != 1)
                throw new Exception("Патрула не е анонсиран");
            $nIDObject = $this->aPatrolCars[$nIDCar]['reaction_object'];
        }

        if (empty($nIDObject) || empty($this->aAlarmRegister[$nIDObject]) || $this->aAlarmRegister[$nIDObject]['status'] != 'active') {
            throw new Exception("Обекта не е под аларма.");
        }

        $this->startCar($nIDCar, $nIDObject);
    }

    protected function onCarArrive($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $this->carArrive($nIDCar);
    }

    protected function onCarAnnounce($aData) {
    	echo "oCarAnnounce";
    	print_r($aData);
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $nIDObject = (int) $aData['id_object'];
        if (empty($nIDObject) || empty($this->aAlarmRegister[$nIDObject]) || $this->aAlarmRegister[$nIDObject]['status'] != 'active') {
            throw new Exception("Обекта не е под аларма.");
        }
        $this->announceCar($nIDCar, $nIDObject);
    }

    protected function onReject($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $nIDReason = (int) $aData['id_reason'];
        $this->freeCar($nIDCar, $nIDReason);
    }

    protected function onAlarmReason($aData) {
        if (!empty($aData['id_car'])) {
            $nIDCar = (int) $aData['id_car'];
            if (empty($this->aPatrolCars[$nIDCar]))
                throw new Exception("Патрула не е намерен.");
        } else {
            $nIDCar = 0;
        }

        $nIDObject = (int) $aData['id_object'];
        if (empty($nIDObject) || empty($this->aAlarmRegister[$nIDObject])) {
            throw new Exception("Обекта не е под аларма.");
        }
        $nIDReason = (int) $aData['id_reason'];
        if (empty($nIDReason) || empty($this->aAlarmReasons[$nIDReason]))
            throw new Exception("Невалидна причина за аларма.");

        $this->alarmReason($nIDObject, $nIDReason, $nIDCar);
    }

    protected function onAlarmNotify($aData) {
        $nIDObject = (int) $aData['id_object'];
        $nIDReason = (int) $aData['id_reason'];
        if (empty($nIDObject) || empty($this->aAlarmRegister[$nIDObject])) {
            throw new Exception("Обекта не е под аларма.");
        }
        $this->alarmNotify($nIDObject, $nIDReason);
    }

    protected function onCarService($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $nIDReason = (int) $aData['id_reason'];
        if (empty($nIDReason) || empty($this->aCancelReasons[$nIDReason]))
            throw new Exception("Невалидна причина.");

        $nServiceTime = 0;
        if (isset($aData['service_time'])) {
            $nServiceTime = (int) $aData['service_time'];
        }
        if ($nServiceTime <= 0 || $nServiceTime > $this->nMaxObjectServiceTime)
            $nServiceTime = $this->nMaxObjectServiceTime;

        $this->carService($nIDCar, $nIDReason, $nServiceTime);
    }

    protected function onCarCancelService($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $this->carCancelService($nIDCar);
    }

    protected function onCarConfirmAlertness($aData) {
        $nIDCar = (int) $aData['id_car'];
        if (empty($nIDCar) || empty($this->aPatrolCars[$nIDCar]))
            throw new Exception("Патрула не е намерен.");
        $nIDPerson = (int) $aData['id_person'];
        $this->carConfirmAlertness($nIDCar, $nIDPerson);
    }

    protected function onCanvasInit($aData, $sSessionID) {
        global $db_sod;
        $nIDPerson = (int) $aData['id_person'];
        if (empty($nIDPerson))
            throw new Exception;
        $aResponse = array();
        $idOffices = Array();
        $oInit = new DBBase2($db_sod, 'layers_objects');
        //Regioni
        $sQuery = "
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
            WHERE sett.id_person = $nIDPerson AND isp.name='account_regions'
        ";

        $aInit = $oInit->selectAssoc($sQuery);

        foreach ($aInit as $ID => $val) {
            $aResponse['regions'][$ID]['geo_w'] = $val['geo_w'];
            $aResponse['regions'][$ID]['geo_s'] = $val['geo_s'];
            $aResponse['regions'][$ID]['geo_n'] = $val['geo_n'];
            $aResponse['regions'][$ID]['geo_e'] = $val['geo_e'];
            $aResponse['regions'][$ID]['geo_lat'] = $val['geo_lat'];
            $aResponse['regions'][$ID]['geo_lan'] = $val['geo_lan'];
            $aResponse['regions'][$ID]['name'] = $val['name'];
            $idOffices[$ID] = $ID;
        }

        //Stoqnki
        if (!empty($idOffices)) {
            $sQuery = "
                SELECT lo.id_office, lo.geo_lat, lo.geo_lan, lo.name, lo.description, lo.id
                FROM sod.layers AS l
                LEFT JOIN sod.layers_objects AS lo ON lo.id_layer=l.id
                WHERE lo.to_arc=0 AND l.is_alpha=1 AND lo.id_office IN(" . implode(",", $idOffices) . ")
            ";
            $aInit = $oInit->select($sQuery);

            foreach ($aInit as $row) {
                $aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['name'] = $row['name'];
                $aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['description'] = $row['description'];
                $aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['geo_lat'] = $row['geo_lat'];
                $aResponse['regions'][$row['id_office']]['waitpoints'][$row['id']]['geo_lan'] = $row['geo_lan'];
            }
        }

        foreach ($this->aAlarmReasons as $id => $row) {
            if ($row['from_patrul_display'] == 1)
                $aResponse['alarmReasonsPatrol'][$id] = $row['name'];
            if ($row['from_dispatcher_canvas'] == 1)
                $aResponse['alarmReasonsObject'][$id] = $row['name'];
            if ($row['from_dispatcher_monitoring'] == 1)
                $aResponse['alarmReasonsObjectMonitoring'][$id] = $row['name'];
            if ($row['from_bypass_canvas'] == 1)
                $aResponse['alarmReasonsBypassObject'][$id] = $row['name'];
            $aResponse['alarmReasonsAll'][$id] = $row['name'];
        }
        foreach ($this->aCancelReasons as $id => $row) {
            $aResponse['alarmReasonsCancelPatrol'][$id] = $row['name'];
        }
        $aResponse['alarmReasonNotify'] = $this->nIDReasonNotify;
        $aResponse['regionBounds'] = $this->aRegionBounds;
        $aResponse['target_type'] = 'init';
        $aResponse['session_id'] = $sSessionID;
        //send response
        $this->oWSSEvents->addEvent($aResponse);
        if (!empty($idOffices)) {
            //objects			 			
            foreach ($this->aAlarmObjects as $nIDObject => $aObject) {
                if ($nIDObject > self::OBJECTS_MAX_ID)
                    continue;
                //ako obekta e pod alarma ili ima kola koqto otiva natam
                $bAdd = false;
                if (!empty($idOffices[$aObject['id_reaction_office']])) {
                    if (!empty($aObject['reaction_status']))
                        $bAdd = true;
                    elseif (!empty($this->aAlarmPatrols[$nIDObject])) {
                        foreach ($this->aAlarmPatrols[$nIDObject] as $nIDCar => $aAlarmPatrol)
                            if ($this->aPatrolCars[$nIDCar]['reaction_object'] == $nIDObject) {
                                $bAdd = true;
                                break;
                            }
                    }
                }

                if ($bAdd)
                    $this->addEvent(array(
                        'session_id' => $sSessionID,
                        'target_type' => 'object',
                        'target' => $nIDObject,
                        'event_type' => 'update'
                    ));
            }

            //cars			
            foreach ($this->aPatrolCars as $nIDCar => $aCar) {
                $bIsInRegion = false;
                foreach ($aCar['offices'] as $nIDOffice)
                    if (!empty($idOffices[$nIDOffice])) {
                        $bIsInRegion = true;
                        break;
                    }
                if ($bIsInRegion) {
                    $this->addEvent(array(
                        'session_id' => $sSessionID,
                        'target_type' => 'car',
                        'target' => $nIDCar,
                        'event_type' => 'position'
                    ));
                }
            }

            foreach ($this->aOtherCars as $nIDCar => $aCar) {
                foreach ($aCar['offices'] as $nIDOffice)
                    if (!empty($idOffices[$nIDOffice])) {
                        $this->addEvent(array(
                            'session_id' => $sSessionID,
                            'target_type' => 'car',
                            'target' => $nIDCar,
                            'event_type' => 'position',
                            'nopatrol' => true
                        ));
                        break;
                    }
            }
        }
        $this->processWaypoints(true);
    }

    protected function onDisplayInit($aData, $sSessionID) {
        $aOffices = array();
        if (!is_array($aData['offices']) || empty($sSessionID))
            throw new Exception();
        foreach ($aData['offices'] as $nIDOffice)
            $aOffices[$nIDOffice] = $nIDOffice;
        //objects
        foreach ($this->aAlarmObjects as $nIDObject => $aObject) {
            //ako obekta e pod alarma ili ima kola koqto otiva natam
            $bAdd = false;
            if (!empty($aOffices[$aObject['id_reaction_office']])) {
                if (!empty($aObject['reaction_status']))
                    $bAdd = true;
                elseif (!empty($this->aAlarmPatrols[$nIDObject])) {
                    foreach ($this->aAlarmPatrols[$nIDObject] as $nIDCar => $aAlarmPatrol)
                        if ($this->aPatrolCars[$nIDCar]['reaction_object'] == $nIDObject) {
                            $bAdd = true;
                            break;
                        }
                }
            }
            if ($bAdd)
                $this->addEvent(array(
                    'session_id' => $sSessionID,
                    'target_type' => 'object',
                    'target' => $nIDObject,
                    'event_type' => 'update'
                ));
        }

        //cars
        foreach ($this->aPatrolCars as $nIDCar => $aCar) {
            foreach ($aCar['offices'] as $nIDCarOffice)
                if (!empty($aOffices[$nIDCarOffice])) {
                    $this->addEvent(array(
                        'session_id' => $sSessionID,
                        'target_type' => 'car',
                        'target' => $nIDCar,
                        'event_type' => 'position'
                    ));
                    break;
                }
        }
    }

    protected function onResetAlarms($aData, $sSessionID) {
        foreach ($this->aAlarmRegister as $nID => $aAlarm) {
        	if ( isset($aData['id_office']) ) {
        		if ( $aAlarm['id_office'] == $aData['id_office'] ) {
        			$this->closeAlarm($nID, self::REASON_SYSTEM_RESET);
        		}
        	} else {
        		$this->closeAlarm($nID, self::REASON_SYSTEM_RESET);
        	}
        }
    }

}