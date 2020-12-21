<?php

class ApiObjectZones {

    public function result( DBResponse $oResponse ) {
        global $db_sod;

        $nID = Params::get("nID", 0);

        if( !empty( $nID ) ) {
            $oZones = new DBSignalZones();
            $aZone = $oZones->getReport( $nID, $oResponse );
        }

        $oResponse->printResponse("Зони на обект", "objects_zones");
    }

    function delete( DBResponse $oResponse ) {
        global $db_sod;
        $nID = Params::get('nIDZone');

        $oZones = new DBSignalZones();

        $validate = $oZones->zoneInUse( $nID );

        if ( $validate['br'] > 0 ) {
            throw new Exception("Тази зона се използва в сигнал!", DBAPI_ERR_INVALID_PARAM);
        } else {
            $aData = array();
            $aData['id'] = $nID;
            $aData['to_arc'] = 1;

            $oZones->update( $aData );
        }

        $oResponse->printResponse();
    }

    public function setServiceStatus(DBResponse $oResponse){
        $nIDObject = Params::get('nID', 0);
        $oDBObjects = new DBObjects();

        if ((int)$nIDObject > 0) {
            $oDBObjects->setServiceStatus($nIDObject);
        }
        $oResponse->printResponse();
    }

}
?>