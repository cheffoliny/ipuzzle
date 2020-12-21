<?php

class DBOffices
    extends DBBase2
{
    public function __construct()
    {
        global $db_sod;
        //$db_sod->debug=true;

        parent::__construct( $db_sod, 'offices');
    }

    public function getFirmOfficesAssoc( $nIDFirm )
    {
        if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )return array();
        //throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
        {
            $off = implode( ",", $_SESSION['userdata']['access_right_regions'] );
            $wh = " AND id IN({$off}) \n";
        }
        else $wh = "";

        $sQuery = "
				SELECT
					t.id as _id,
					t.*
				FROM offices t
				WHERE to_arc = 0
					AND id_firm = {$nIDFirm}
					{$wh}
				ORDER BY t.name
			";

        return $this->selectAssoc( $sQuery );
    }

    /**
     * Офисите за фирма без да се вземат под внимание правата на потребителя
     * @param $nIDFirm
     * @return array
     */
    public function getFirmOfficesAssocWithoutRights( $nIDFirm )
    {
        if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) ) {return array();}

        $sQuery = "
				SELECT
					t.id as _id,
					t.*
				FROM offices t
				WHERE to_arc = 0
					AND id_firm = {$nIDFirm}
				ORDER BY t.name
			";

        return $this->selectAssoc( $sQuery );
    }

    public function getFirmOfficesRightAssoc( $nIDFirm ) {
        if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
            $off = implode(",", $_SESSION['userdata']['access_right_regions']);
            $wh = " AND id IN({$off}) \n";
        } else $wh = "";

        $sQuery = "
				SELECT
					t.id as _id,
					t.*
				FROM offices t
				WHERE to_arc = 0
					{$wh}
				AND id_firm = {$nIDFirm}
				ORDER BY t.name
			";

        return $this->selectAssoc( $sQuery );
    }

    public function getInfoByID( $nID ) {
        global $db_name_personnel;

        if( empty( $nID ) || !is_numeric( $nID ) )
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        $sQuery = "
				SELECT 
					p.*,
					o.id_firm,
					ob.name as obj_name
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN offices o ON o.id = p.id_office
				LEFT JOIN objects ob ON ob.id = p.id_region_object
				WHERE p.to_arc = 0
					AND p.id = {$nID} 
				LIMIT 1
			";

        return $this->selectOnce( $sQuery );
    }

    public function getIDOfficesByJurName($sJurName) {

        $sQuery = "
				SELECT
					off.id as id_office
				FROM offices off
				LEFT JOIN firms f ON f.id = off.id_firm
				WHERE f.jur_name = {$this->oDB->Quote($sJurName)}
			";

        return $this->select($sQuery);
    }

    public function getOfficesByFirmForFlexCombo( $nIDFirm )
    {
        if( !is_numeric( $nIDFirm ) )$nIDFirm = 0;

        if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
        {
            $sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
            $sCondition = " AND id IN ({$sAccessable}) \n";
        }
        else $sCondition = "";

        $sQuery = "
					SELECT
						id AS id,
						name AS label
					FROM offices 
					WHERE to_arc = 0
			";

        $sQuery .= $sCondition;

        if( !empty( $nIDFirm ) )$sQuery .= " AND id_firm = {$nIDFirm} ";

        $sQuery .= " ORDER BY label ";

        return $this->select( $sQuery );
    }

    public function getOfficesByFirm( $nIDFirm )
    {
        if( !is_numeric( $nIDFirm ) )$nIDFirm = 0;

        if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
        {
            $sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
            $sCondition = " AND id IN ({$sAccessable}) \n";
        }
        else $sCondition = "";

        $sQuery = "
					SELECT * 
					FROM offices 
					WHERE to_arc = 0
			";

        $sQuery .= $sCondition;

        if( !empty( $nIDFirm ) )$sQuery .= " AND id_firm = {$nIDFirm} ";

        $sQuery .= " ORDER BY name ";

        return $this->select( $sQuery );
    }

    public function getOffices2()
    {

        $sQuery = "
				SELECT
					id, 
					code,
					name
				FROM offices
				WHERE to_arc = 0
				ORDER BY name
			";

        return $this->select( $sQuery );
    }

    public function getOffices( ) {

        $sQuery = "
				SELECT
					of.id as _id,
					of.id, 
					of.code,
					of.name
				FROM offices of
				WHERE 1
					AND of.to_arc = 0
					AND of.id_firm = 1
				ORDER BY of.name
			";

        return $this->selectAssoc( $sQuery );
    }

    public function getOffices3()
    {

        $sQuery = "
				SELECT
					id, 
					name
				FROM offices
				WHERE to_arc = 0
				ORDER BY name
			";

        return $this->selectAssoc( $sQuery );
    }

    public function getOffices4()
    {
        $sQuery = "
				SELECT
					o.id, 
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM offices o
				LEFT JOIN firms f ON o.id_firm = f.id AND f.to_arc = 0
				WHERE o.to_arc = 0
				ORDER BY f.name
			";

        return $this->selectAssoc( $sQuery );
    }


    // ако $is_tech = true взема само офисите които имам техническа поддръжка:
    public function getOfficesRight($is_tech = false ) {
        global $db_name_sod;

        if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
            $sAccessable 	= implode( ",", $_SESSION['userdata']['access_right_regions'] );
            $sCondition 	= " AND o.id IN ({$sAccessable}) \n";
        } else {
            $sCondition = "";
        }

        if($is_tech){
            $sCondition.=" AND  o.is_tech = 1 ";
        }

        $sQuery = "
				SELECT
					o.id, 
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( o.id_firm = f.id AND f.to_arc = 0 )
				WHERE o.to_arc = 0
					AND LENGTH(f.name) > 2
			";

        $sQuery .= $sCondition;

        $sQuery .= "
				ORDER BY f.name, o.name
			";

        return $this->selectAssoc( $sQuery );
    }


    /**
     * Връща неасоциативен масив за да е сортиран по имена
     * @param bool $is_tech
     * @return array
     */
    public function getOfficesRight1($is_tech = false ) {
        global $db_name_sod;

        if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
            $sAccessable 	= implode( ",", $_SESSION['userdata']['access_right_regions'] );
            $sCondition 	= " AND o.id IN ({$sAccessable}) \n";
        } else {
            $sCondition = "";
        }

        if($is_tech){
            $sCondition.=" AND  o.is_tech = 1 ";
        }

        $sQuery = "
				SELECT
					o.id,
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( o.id_firm = f.id AND f.to_arc = 0 )
				WHERE o.to_arc = 0
					AND LENGTH(f.name) > 2
			";

        $sQuery .= $sCondition;

        $sQuery .= "
				ORDER BY f.name, o.name
			";

        return $this->select( $sQuery );
    }

    public function getOfficesByOffices( $sIDOffices)
    {
        $sQuery = "
				SELECT
					o.id, 
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM offices o
				LEFT JOIN firms f ON o.id_firm = f.id AND f.to_arc = 0
				WHERE 1
					AND o.to_arc = 0
					AND o.id IN ($sIDOffices)
				ORDER BY f.name
			";

        return $this->selectAssoc( $sQuery );
    }

    public function getFirmByIDOffice($nIDOffice)
    {
        $sQuery = "
				SELECT
					id_firm
				FROM
					offices
				WHERE id = {$nIDOffice}
			";

        return $this->selectOne($sQuery);
    }

    public function getFirmNameByIDOffice($nIDOffice) {
        global $db_name_sod;

        $sQuery = "
				SELECT
					f.name
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				WHERE o.id = {$nIDOffice}
			";

        return $this->selectOne($sQuery);
    }

    public function getGeoByIDOffice($nIDOffice) {
        global $db_name_sod;

        $sQuery = "
				SELECT
				  CONCAT(geo_lat, ', ', geo_lan) as geo
				FROM {$db_name_sod}.offices
				WHERE id = {$nIDOffice}
			";

        return $this->selectOne($sQuery);
    }

    /**
     * Връща името на фирмата и името на офиса по номер на офис
     * @author Румен Пенчев
     *
     * @param int $nIDOffice
     * @return array
     */
    public function getFirmNameOfficeNameByIDOffice($nIDOffice) {
        global $db_name_sod;

        $sQuery = "
				SELECT
					f.name as fname,
					o.name as oname,
					o.work_flow_acc
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				WHERE o.id = {$nIDOffice}
			";

        return $this->select($sQuery);
    }

    public function getOfficesByIDFirm( $nIDFirm=0 ) {

        $sQuery = "
				SELECT 
					id,
					name 
				FROM offices 
				WHERE 1
					AND to_arc = 0 
					AND id_firm = {$nIDFirm}
			";



        if( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
            $sIDOffices = implode( ",", $_SESSION['userdata']['access_right_regions'] );
            if(!empty($sIDOffices))  {
                $sQuery .= " AND id IN ({$sIDOffices}) \n";
            }
        }

        $sQuery.=" ORDER BY name\n";

        return $this->selectAssoc( $sQuery );
    }

    /**
     * Връща всички офиси към дадената фирма, независимо от правата на достъп.
     *
     * @param int $nIDFirm
     * @return array ( assoc )
     * @author Mihail Dimitrov
     */
    public function getAllOfficesByIDFirm( $nIDFirm )
    {
        if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
        {
            return array();
        }

        $sQuery = "
				SELECT
					id,
					name
				FROM
					offices
				WHERE
					to_arc = 0
					AND id_firm = {$nIDFirm}
				 ORDER BY
				 	name
			";

        return $this->selectAssoc( $sQuery );
    }

    /**
     * Връща всички ID-тата на офисите по дадена фирма.
     *
     * @param int $nIDFirm
     * @return string ( id1,id2,id3 )
     * @author Павел Петров
     */
    public function getIdsByFirm( $nIDFirm ) {
        global $db_name_sod;

        if ( empty($nIDFirm) || !is_numeric($nIDFirm) ) {
            return "-1";
        }

        $sQuery = "
				SELECT 
					GROUP_CONCAT(id) as id
				FROM {$db_name_sod}.offices
				WHERE id_firm = {$nIDFirm}
			";

        return $this->selectOne( $sQuery );
    }

    public function getReactionOfficesByIDFirm( $nIDFirm ) {
        $sQuery = "
				SELECT 
				id,
				name 
				FROM offices 
				WHERE 1
					AND id_firm = {$nIDFirm}
					AND is_reaction = 1 
					AND to_arc = 0 
				ORDER BY name
			 ";
        return $this->selectAssoc( $sQuery );
    }

    public function getReactionOffices() {
        global $db_name_sod;

        $sQuery = "
				SELECT 
					o.id,
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( o.id_firm = f.id AND f.to_arc = 0 )
				WHERE o.is_reaction = 1
					AND o.to_arc = 0 
					AND LENGTH(f.name) > 2	
				ORDER BY name
			 ";

        return $this->selectAssoc( $sQuery );
    }

    public function getAdminOffices() {
        global $db_name_sod;

        $sQuery = "
				SELECT 
					o.id,
					CONCAT('[',f.name,'] ',o.name) AS name
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON ( o.id_firm = f.id AND f.to_arc = 0 )
				WHERE o.is_admin = 1 
					AND o.to_arc = 0 
					AND LENGTH(f.name) > 2	
				ORDER BY name
			 ";

        return $this->selectAssoc( $sQuery );
    }

    public function getAdminOfficesByCity($nIDCity) {
        global $db_name_sod;

        $sQuery = "
				SELECT
					c.id,
					o.id AS id_office,
					CONCAT('[',f.name,'] ',o.name) AS name,
					o.geo_lat,
					o.geo_lan,
					o.factor_office_admin,
					o.factor_office_tech,
					o.factor_office_reaction
				FROM {$db_name_sod}.cities AS c
				LEFT JOIN {$db_name_sod}.offices AS o ON o.id = c.id_office AND o.to_arc = 0
				LEFT JOIN {$db_name_sod}.firms AS f ON  o.id_firm = f.id AND f.to_arc = 0 AND LENGTH(f.name) > 2					
				WHERE c.id = $nIDCity					
			 ";

        return $this->selectAssoc( $sQuery );
    }

    public function getReactionOfficesByCity($nIDCity) {
        global $db_name_sod;

        $sQuery = "
				SELECT
					c.id,
					o.id AS id_office,
                                     c.id_reaction_office,
					CONCAT('[',f.name,'] ',o.name) AS name,
					o.geo_lat,
					o.geo_lan
				FROM {$db_name_sod}.cities AS c
				LEFT JOIN {$db_name_sod}.offices AS o ON o.id = c.id_reaction_office AND o.to_arc = 0
				LEFT JOIN {$db_name_sod}.firms AS f ON  o.id_firm = f.id AND f.to_arc = 0 AND LENGTH(f.name) > 2					
				WHERE c.id = $nIDCity					
			 ";

        return $this->selectAssoc( $sQuery );
    }

    public function getTechOfficesByIDFirm( $nIDFirm ) {
        $sQuery = "
				SELECT 
				id,
				name 
				FROM offices 
				WHERE 1
					AND id_firm = {$nIDFirm}
					AND is_tech = 1 
					AND to_arc = 0 
				ORDER BY name
			 ";
        return $this->selectAssoc( $sQuery );
    }

    public function getPatrulOfficesByIDFirm( $nIDFirm )
    {
        $sQuery = "
				SELECT 
				id,
				name 
				FROM offices 
				WHERE is_reaction = 1 AND to_arc = 0 AND id_firm = {$nIDFirm}
				ORDER BY name
			 ";
        return $this->selectAssoc( $sQuery );
    }

    public function getPatrulOffices()
    {
        $sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);

        $sQuery = "
				SELECT 
				o.id,
				CONCAT('[',f.name,'] ',o.name) AS name 
				FROM offices o
				LEFT JOIN firms f ON f.id = o.id_firm AND f.to_arc = 0 
				WHERE	1
					AND o.is_reaction = 1 
					AND o.to_arc = 0 
					AND o.id IN ({$sAccessRegions})
				ORDER BY name
			 ";
        return $this->selectAssoc( $sQuery );
    }

    public function retrieveLoggedUserOffice( $sFieldFirm, $sFieldOffice, DBResponse $oResponse, $nAddCodeToOffice = 1, $nRefreshOffices = 1, $sCommonOffice = "Всички Региони" )
    {
        $nIDUserOffice = $_SESSION['userdata']['id_office'];

        if( !empty( $nIDUserOffice ) )
        {
            $nIDUserFirm = $this->getFirmByIDOffice( $nIDUserOffice );

            if( !empty( $nIDUserFirm ) )
            {
                $oResponse->setFormElementAttribute( 'form1', $sFieldFirm, 'value', $nIDUserFirm );

                if( $nRefreshOffices )
                {
                    //Set Regions
                    $aOffices = $this->getOfficesByFirm( $nIDUserFirm );

                    $oResponse->setFormElement( 'form1', $sFieldOffice );
                    $oResponse->setFormElementChild( 'form1', $sFieldOffice, array( 'value' => 0 ), $sCommonOffice );
                    foreach( $aOffices as $aOffice )
                    {
                        if( $nAddCodeToOffice )
                        {
                            $oResponse->setFormElementChild( 'form1', $sFieldOffice, array( 'value' => $aOffice['id'] ), sprintf( "%s [%s]", $aOffice['name'], $aOffice['code'] ) );
                        }
                        else
                        {
                            $oResponse->setFormElementChild( 'form1', $sFieldOffice, array( 'value' => $aOffice['id'] ), sprintf( "%s", $aOffice['name'] ) );
                        }
                    }
                    //End Set Regions
                }

                $oResponse->setFormElementAttribute( 'form1', $sFieldOffice, 'value', $nIDUserOffice );
            }
        }
    }

    /**
     * Връща офиса за реакция по зададен обект
     * @author Pavel Petrov
     *
     * @param int $nIDFirm
     * @return int
     */
    public function getOfficesIDByObject( $nIDObject ) {

        $nIDObject = is_numeric( $nIDObject ) ? $nIDObject : -1;

        $sQuery = "
				SELECT
					o.id_office
				FROM objects o
				WHERE o.id = {$nIDObject}
			";

        return $this->selectOne( $sQuery );
    }


    public function getOfficesIDByFirm( $nIDFirm ) {

        $nIDFirm = !empty($nIDFirm) && is_numeric($nIDFirm) ? $nIDFirm : -1;

        $sQuery = "
				SELECT 
					group_concat(id) as id
				FROM offices 
				WHERE 1
					AND id_firm = {$nIDFirm}
					AND to_arc = 0 
				group by id_firm
			 ";

        return $this->selectOne( $sQuery );
    }

    /**
     * Връща името на офиса по зададено ID
     *
     * @param (int) $nIDOffice - ID на офиса
     * @author Павел Петров
     *
     * @return (string) - името на търсения офис
     */
    public function getNameByID( $nIDOffice ) {
        global $db_name_sod;

        if ( !is_numeric($nIDOffice) || empty($nIDOffice) ) {
            return "";
        }

        $sQuery = "
				SELECT 
					name
				FROM {$db_name_sod}.offices 
				WHERE id = {$nIDOffice}
			";

        return $this->selectOne( $sQuery );
    }

    public function getOffices5() {
        $sQ = "
		        SELECT 
                    o.id,
                    f.idn
                from offices o
                LEFT JOIN firms f ON o.id_firm = f.id AND f.to_arc = 0
                WHERE o.to_arc = 0  
		    ";

        return $this->selectAssoc( $sQ );
    }
}
?>