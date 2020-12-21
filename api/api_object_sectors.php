<?php

class ApiObjectSectors {

    public function result( DBResponse $oResponse ) {
        global $db_sod;

        $nID = Params::get("nID", 0);

        if( !empty( $nID ) ) {
            $oSectors = new DBSignalSectors();
            $aSector = $oSectors->getReport( $nID, $oResponse );
        }

        $oResponse->printResponse("Сектори на обект", "objects_sectors");
    }

    function delete( DBResponse $oResponse ) {
        global $db_sod;
        $nID = Params::get('nIDSector');

        $oSectors = new DBSignalSectors();

        $validate = $oSectors->sectorInUse( $nID );

        if ( $validate['br'] > 0 ) {
            throw new Exception("Този сектор се използва в сигнал!", DBAPI_ERR_INVALID_PARAM);
        } else {
            $aData = array();
            $aData['id'] = $nID;
            $aData['to_arc'] = 1;

            $oSectors->update( $aData );
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