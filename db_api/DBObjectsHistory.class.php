<?php

class DBObjectsHistory extends DBBase2
{

    public function __construct()
    {
        global $db_sod;
        parent::__construct($db_sod, 'objects_history');
    }

    public function getByID($nID)
    {
        global $db_name_personnel;

        $sQuery = "
            SELECT
                o.name as object_name,
                oh.data,
                CONCAT_WS(' ',p.fname,p.lname) as person_name,
                DATE_FORMAT(oh.created_time,'%d/%m/%Y %H:%i:%S') as time
            FROM objects_history oh
            JOIN {$db_name_personnel}.personnel p ON p.id = oh.created_user
            JOIN objects o ON o.id = oh.id_object
            WHERE oh.id = {$nID}
        ";

        return $this->selectOnce($sQuery);

    }

    public function logCoordinatesHistory($nIDObject, $coordinates, $confirm = false)
    {

        if (!is_array($coordinates) || !isset($coordinates['geo_lat']) || !isset($coordinates['geo_lan'])) {
            throw new Exception('Координатите на обекта не са зададени, или са в некоректен формат!');
        }

        $aLastInfo = $this->getLastFullInfo($nIDObject);
        if (!empty($aLastInfo)) {
            $aLastInfoData = json_decode($aLastInfo['data']);

            foreach ($aLastInfoData as &$data) {

                if (isset($data->changed)) {
                    $data->originalValue = $data->value;
                    unset($data->changed);
                }

                if ($data->name == 'geo_lat') {
                    $data->value = $coordinates['geo_lat'];
                    $data->changed = true;
                }

                if ($data->name == 'geo_lan') {
                    $data->value = $coordinates['geo_lan'];
                    $data->changed = true;
                }

            }
        } else {
            $aLastInfoData[] = (object)['name' => 'geo_lat', 'value' => $coordinates['geo_lat'], 'originalValue' => $coordinates['geo_lat']];
            $aLastInfoData[] = (object)['name' => 'geo_lan', 'value' => $coordinates['geo_lan'], 'originalValue' => $coordinates['geo_lan']];
        }


        $aData['id'] = 0;
        $aData['id_object'] = $nIDObject;
        $aData['data'] = json_encode($aLastInfoData);
        $aData['type'] = $confirm ? 'confirmed' : 'coordinates';

        $this->update($aData);

    }

    private function getLastFullInfo($nIDObject)
    {

        $sQuery = "

            SELECT
                id,
                id_object,
                `data`,
                `type`
            FROM objects_history oh
            WHERE oh.type = 'info'
            AND oh.id_object = {$nIDObject}
            ORDER BY id DESC
            LIMIT 1
        ";

        return $this->selectOnce($sQuery);

    }

    public function getMerged($nIDObject, $aData)
    {
        $aLastLoggedData = $this->getLastFullInfo($nIDObject);
//        throw new ExException('test1');


        if (!empty($aLastLoggedData)) {
            $newKeys = [];

            foreach ($aData as $item) {
                $newKeys[$item->name] = $item->name;
            }

            $aNewData = json_decode($aLastLoggedData['data']);
            foreach ($aNewData as $key => $val) {
                if (!in_array($val->name, $newKeys)) {
                    $aData[] = $val;
                }
            }

        }

        return $aData;

    }

    public function getReport($nIDObject, DBResponse $oResponse)
    {
        global $db_name_personnel;

        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS
                CONCAT_WS('@',oh.id,o.id) as id,
                oh.id as _id,
                CONCAT_WS(' ',p.fname,p.lname) as person_name,
                o.name as object_name,
                DATE_FORMAT(oh.created_time,'%d/%m/%Y %k:%i:%s') as crated_time,
                CASE oh.`type`
                  WHEN 'info' THEN 'Информация'
                  WHEN 'coordinates' THEN 'Координати'
                  WHEN 'confirmed' THEN 'Потвърден'
                END as `type`,
                oh.created_time as crated_time_raw
            FROM objects_history oh
            JOIN {$db_name_personnel}.personnel p ON p.id = oh.created_user
            JOIN objects o ON o.id = oh.id_object
            WHERE oh.id_object = {$nIDObject}

        ";

        $this->getResult($sQuery, 'crated_time_raw', DBAPI_SORT_DESC, $oResponse);

        $oResponse->setField('person_name', 'име', 'сортирай по име', null, 'openHistory');
        $oResponse->setField('type', 'тип', 'сортирай по тип');
        $oResponse->setField('object_name', 'обект', 'сортирай по обект');
        $oResponse->setField('crated_time', 'дата', 'сортирай по дата');


    }


}