<?php

class ApiObjectGeo{

    public function save(DBResponse $oResponse)
    {
        $oDBObject = new DBObjects();

        $aParams = Params::getAll();

        if(empty($aParams['nID']))
        {
            throw new Exception("Няма привързан обект");
        }

        if( empty($aParams['new_lan']) || empty($aParams['new_lat']) )
        {
            throw new Exception("Въведете координати на обекта");
        }

        $aData = array();

        $aData['id'] 		= $aParams['nID'];
        $aData['geo_lan']	= $aParams['new_lan'];
        $aData['geo_lat']	= $aParams['new_lat'];
        // ПО искане на Денчев - Краси го
        $aData['confirmed']	= 1;

        $oDBObject->update($aData);

        $oDBObjectsHistory = new DBObjectsHistory();
        $oDBObjectsHistory->logCoordinatesHistory($aParams['nID'],['geo_lat'=>$aParams['new_lat'],'geo_lan'=>$aParams['new_lan']]);

        $oResponse->printResponse();
    }

    public function saveLastPov( DBResponse $oResponse ) {
        $newPov = Params::get("new_pov",    "");
        $nID    = Params::get("nID",        0);
        $isCoor = Params::get("saveCoords", 0);

APILog::Log(112121, $isCoor);
        if ( !empty($newPov) && !empty($nID) ) {
            $oObject            = new DBObjects();

            $aData              = array();
            $aData['id'] 		= $nID;
            $aData['geo_pov']   = $newPov;

            if ( $isCoor == 1 ) {
                $json = json_decode($newPov);

                $lan = isset($json->lng) ? $json->lng : 0;
                $lat = isset($json->lat) ? $json->lat : 0;
                APILog::Log(112121, $lan, $lat);
                if ( $lan > 0 && $lat > 0 ) {
                    $aData['geo_lan']   = $lan;
                    $aData['geo_lat']   = $lat;
                }

//                    $oDBObjectsHistory = new DBObjectsHistory();
//                    $oDBObjectsHistory->logCoordinatesHistory($nID,['geo_lat'=>$lat,'geo_lan'=>$lan]);
            }

            $oObject->update($aData);


        }

        $oResponse->printResponse();
    }

    public function clearPov( DBResponse $oResponse ) {
        $nID    = Params::get("nID",        0);

        if ( !empty($nID) ) {
            $oObject            = new DBObjects();

            $aData              = array();
            $aData['id'] 		= $nID;
            $aData['geo_pov']   = "";

            $oObject->update($aData);

            $oResponse->setFormElement( 'form1', 'ppov', array(), json_encode(array()) );
        }

        $oResponse->printResponse();
    }

    public function loadPov( DBResponse $oResponse ) {
        $nID    = Params::get("nID",        0);

        if ( !empty($nID) ) {
            $oObject    = new DBObjects();
            $aObject    = $oObject->getRecord($nID);

            $pov        = isset($aObject['geo_pov']) && !empty($aObject['geo_pov']) ? $aObject['geo_pov'] : json_encode(array());

            $oResponse->setFormElement( 'form1', 'ppov', array(), $pov );
        }

        $oResponse->printResponse();
    }
}
?>
