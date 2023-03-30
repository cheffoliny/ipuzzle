<?php

class DBTechTiming extends DBBase2
{
    public function __construct()
    {
        global $db_sod;

        parent::__construct( $db_sod, 'tech_timing' );
    }

    public function getDescription($nID = NULL, $description = NULL) {
        $sQuery = "
						SELECT
							`description` 
						FROM tech_timing
						WHERE 1 " ;

        if(!empty($nID)) $sQuery .= "AND id = {$nID} ";
        if(!empty($description)) $sQuery .= "AND description = '{$description}' ";
        $sQuery .= "LIMIT 1 ";

        if(!empty($nID || $description)) return $this->selectOnce( $sQuery );

        return false;
    }

    public function getType( $nID, $bLatin )
    {
        if( !is_numeric( $nID ) )
        {
            return "";
        }

        if( $bLatin )
        {
            $sQuery = "
						SELECT
							name AS type
						FROM tech_timing
						WHERE id = {$nID}
						LIMIT 1
				";
        }
        else
        {
            $sQuery = "
						SELECT
							CASE name
								WHEN 'create' THEN 'Прием за сервиз'
                               WHEN 'destroy' THEN 'Спиране от сервиз'
                               WHEN 'arrange' THEN 'Ремонт'
                               WHEN 'holdup' THEN 'Авария'
                               WHEN 'plan' THEN 'Планово'
							END AS type
						FROM tech_timing
						WHERE id = {$nID}
						LIMIT 1
				";
        }

        $aContent = $this->selectOnce( $sQuery );

        if( !empty( $aContent ) && isset( $aContent['type'] ) )
        {
            return $aContent['type'];
        }
        else return "";
    }

    public function getReport( $aParams, DBResponse $oResponse )
    {
        $right_edit = false;
        if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
            if( in_array( 'tech_timing', $_SESSION['userdata']['access_right_levels'] ) )
            {
                $right_edit = true;
            }

        $sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						tt.id,
						CASE tt.name
						   WHEN 'create' THEN 'Прием за сервиз'
                           WHEN 'destroy' THEN 'Спиране от сервиз'
                           WHEN 'arrange' THEN 'Ремонт'
                           WHEN 'holdup' THEN 'Авария'
                           WHEN 'plan' THEN 'Планово'
						END AS name,
						tt.description as type,
						CONCAT( minute, ' мин.' ) AS minute,
						IF
						(
							tt.name = 'create',
							CONCAT( step_detector, ' мин.' ),
							''
						) AS step_detector,
						tt.color AS color,
						GROUP_CONCAT(DISTINCT f.name ) AS firm_name 
                    FROM tech_timing tt
                    LEFT JOIN tech_timing_firms ttf ON tt.id=ttf.id_tech_timing
                    LEFT JOIN firms f ON f.id = ttf.id_firm
                    WHERE tt.to_arc = 0
                    GROUP BY tt.id
			";



        $this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );

        $oResponse->setField( "type", 			"Тип обслужване", 					"Сортирай по тип обслужване" );
        $oResponse->setField( "minute", 		"Времетраене", 						"Сортирай по времетраене" );
        $oResponse->setField( "name", 	"Тип", 	"Сортирай по тип" );
        $oResponse->setField( "color", 	"Цвят", 	"Сортирай по цвят");
        $oResponse->setField( "firm_name", 	"ФИРМИ", 	"Сортирай по фирми" );

        $oResponse->setField('', '', '', 'images/cancel.gif', 'delTechTiming', '');


        foreach( $oResponse->oResult->aData as $key => $value )
        {
            if( $key != 0 )
            {
                $oResponse->setDataAttributes( $key, 'step_detector', array( "style" => "background: F0F0E6; text-align: center;" ) );
            }
            else
            {
                $oResponse->setDataAttributes( $key, 'step_detector', array( "style" => "text-align: center;" ) );
            }
            $oResponse->setDataAttributes( $key, 'color', array( "style" => "background: {$value['color']}; text-align: center;" ) );

        }

        if( $right_edit )
        {
            $oResponse->setFieldLink( "type", "editTiming" );
        }
    }

    public function getInfoByName($sName) {

        $sQuery = "
				SELECT 
					minute,
					step_detector
				FROM tech_timing
				WHERE name = '{$sName}'
			";

        return $this->selectOnce($sQuery);
    }

    public function getAllByIDFirm($nIDFirm, $techTimingName = false) {
        if(empty($nIDFirm))
            return array();

        $sQ = "
		        SELECT
		        *,
		        tt.id as tt_id
		        FROM
		        tech_timing tt
		        JOIN tech_timing_firms ttf ON ttf.id_tech_timing = tt.id
		        WHERE 1
		        AND tt.to_arc = 0
		        AND ttf.id_firm = {$nIDFirm}
		    ";

        if(!empty($techTimingName)) {
            $sQ .= " AND tt.name='plan' ";
        }
        return $this->select($sQ);
    }

    public function getAllByIDFirmAssoc($nIDFirm) {
        if(empty($nIDFirm))
            return array();

        $sQ = "
		        SELECT
		        tt.id AS _key,
		        tt.*
		        FROM
		        tech_timing tt
		        JOIN tech_timing_firms ttf ON ttf.id_tech_timing = tt.id
		        WHERE 1
		        AND tt.to_arc = 0
		        AND ttf.id_firm = {$nIDFirm}
		    ";

        return $this->selectAssoc($sQ);
    }

    public function getTechTimingByName($name) {
        $sQ = "
		        SELECT
		          *
		        FROM
		        tech_timing tt
		        WHERE 1
		        AND tt.name = '{$name}'
		        AND tt.to_arc = 0
		    ";

        return $this->select($sQ);
    }
}
?>