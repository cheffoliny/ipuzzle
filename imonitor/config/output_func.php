<?php	

	//require_once( "./config/dictionar.inc.php" );

    function time_elapsed($secs){
        $bit = array(
            'y ' => $secs / 31556926 % 12,
            'w ' => $secs / 604800 % 52,
            'д ' => $secs / 86400 % 7,
            'ч' => $secs / 3600 % 24,
            'м' => $secs / 60 % 60,
            'с' => $secs % 60
        );

        foreach($bit as $k => $v)
            if($v > 0)$ret[] = $v . $k;

        return join(' ', $ret);
    }

    function dotted_date_to_datetime( $date, $time ) {
        if( $time == 0 ) {
            $tm = '00:00:00';
        } else {
            $tm = '23:59:59';
        }
        $date_time = substr( $date,   6, 4 )."-".substr( $date,   3, 2 )."-".substr( $date,   0, 2 )." ".$tm;

        return $date_time;
    }


    function get_receivers() {

        global  $db_sod;
        $return_result = '';

        $aQuery = "SELECT id, name FROM receivers WHERE to_arc = 0";
        $aRes   = mysqli_query( $db_sod, $aQuery ) or die( "Error: ".$aQuery );

        while ( $aRow = mysqli_fetch_assoc( $aRes ) ) {
            $return_result .= '<li><a href="#" onclick="changeReceiver('.$aRow["id"].'); return false;">'.$aRow['name'].'</a></li><li class="divider"></li>';
        }
        echo $return_result;
    }

    function getOffices() {

        global  $db_sod;
        $return_result = '';

        $aQuery = "SELECT id, name FROM offices WHERE to_arc = 0";
        $aRes   = mysqli_query( $db_sod, $aQuery ) or die( "Error: ".$aQuery );

        while ( $aRow = mysqli_fetch_assoc( $aRes ) ) {
            $return_result .= '<li><a href="#" onclick="changeOffice('.$aRow["id"].'); alarms_by_object(); return false;">'.$aRow['name'].'</a></li><li class="divider"></li>';
        }
        echo $return_result;
    }

    function getOffices_d() {

        global  $db_sod;
        $return_result = '';

        $aQuery = "SELECT id, name FROM offices WHERE to_arc = 0";
        $aRes   = mysqli_query( $db_sod, $aQuery ) or die( "Error: ".$aQuery );

        while ( $aRow = mysqli_fetch_assoc( $aRes ) ) {
            $return_result .= '<li><a href="#" onclick="changeOffice_d('.$aRow["id"].'); alarms_by_object_detailed(); return false;">'.$aRow['name'].'</a></li><li class="divider"></li>';
        }
        echo $return_result;
    }


    function getOfficesByIDFirm( $idFirm ) {

        global  $db_sod;
        $returnResult    = 0;

        $aQuery = "SELECT
                        off.id    AS 'id'   ,
                        off.name  AS 'name'
                   FROM offices off
                   JOIN firms f ON f.id = off.id_firm AND f.to_arc = 0
                   WHERE
                        off.to_arc = 0 AND off.id_firm = ".$idFirm." ";
        $aRes   = mysqli_query( $db_sod, $aQuery ) or die( "Error: ".$aQuery );

        while ( $aRow = mysqli_fetch_assoc( $aRes ) ) {
            $returnResult = $aRow['id'].",".$returnResult;
        }

        echo $returnResult;
    }


    function strTimeToTimeSeconds( $str_time ) {
        $start_hour   = ( mb_substr($str_time,0,2,'UTF-8') * 3600);
        $start_minute = ( mb_substr($str_time,3,2,'UTF-8') * 60);
        $start_second = ( mb_substr($str_time,6,2,'UTF-8') );

        $return_seconds   = $start_hour + $start_minute + $start_second;

        if( $start_hour == 0 ) {
            $return_time = mb_substr( $str_time,3,5,'UTF-8' );
        }

        return array( $return_time, $return_seconds );
    }


     function setDistance( $lan, $lat, $distance ) {
           $radius   = 6378.1;

             $due_north  = 0;
             $due_south  = 180;
             $due_east = 90;
             $due_west = 270;

             $lat_r    = deg2rad($lat);
             $lon_r    = deg2rad($lan);

             $northmost  = asin(sin($lat_r) * cos($distance/$radius) + cos($lat_r) * sin ($distance/$radius) * cos($due_north));
             $southmost  = asin(sin($lat_r) * cos($distance/$radius) + cos($lat_r) * sin ($distance/$radius) * cos($due_south));

             $eastmost = $lon_r + atan2(sin($due_east)*sin($distance/$radius)*cos($lat_r),cos($distance/$radius)-sin($lat_r)*sin($lat_r));
             $westmost = $lon_r + atan2(sin($due_west)*sin($distance/$radius)*cos($lat_r),cos($distance/$radius)-sin($lat_r)*sin($lat_r));


             $northmost  = rad2deg($northmost);
             $southmost  = rad2deg($southmost);
             $eastmost = rad2deg($eastmost);
             $westmost = rad2deg($westmost);

             if ( $northmost > $southmost ) {
                  $lat1 = $southmost;
                  $lat2 = $northmost;

             } else {
                  $lat1 = $northmost;
                  $lat2 = $southmost;
             }

             if ( $eastmost > $westmost ) {
                  $lon1 = $westmost;
                  $lon2 = $eastmost;
             } else {
                  $lon1 = $eastmost;
                  $lon2 = $westmost;
             }

             return array( $lat1, $lat2, $lon1, $lon2 );
     }

/*
      function getObjectsInRadius($id_office, $lan, $lat, $ts, $distance) {
             global $db1;

             $aResult  = array();
             $table    = "archiv_".date("Ym");

             $ts     = !empty($ts) ? $ts : time();
             $time1    = $ts - 600;
             $time2    = $ts + 600;

             $t1     = date("Y-m-d H:i:s", $time1);
             $t2     = date("Y-m-d H:i:s", $time2);

             $sQuery = "
                SELECT
                            o.id,
                            o.num,
                            o.name,
                    o.address,
                        o.geo_lan,
                            o.geo_lat
                FROM messages m
                LEFT JOIN objects o ON o.id = m.id_obj
                WHERE o.id_office = {$id_office}
                        AND o.confirmed = 1
                        AND o.id_status != 4
                                AND o.is_sod = 1
                        AND distanceByGeo(o.geo_lan, o.geo_lat, {$lan}, {$lat}) <= {$distance}
                ";
        $oRs = mysql_query( $sQuery, $db_sod ) or die( mysql_error() );
    //echo $sQuery;
        while ( $oRow = mysql_fetch_assoc( $oRs ) ) {
                      $aResult[] = $oRow;
        }

        return $aResult;
     }*/



     function get_object_by_num_rec( $id_obj, $num, $rec ) {

         global $db_sod;
        $aResult    = array();
          $aResult[0  ] = '';
          $aResult[1  ] = '';
          $aResult[2  ] = '';
          $aResult[3  ] = '';
          $aResult[4  ] = '';
          $aResult[5  ] = '';
          $aResult[6  ] = '';
          $aResult[7  ] = '';
          $aResult[8  ] = '';
          $aResult[9  ] = '';

          $oQuery = "
                      SELECT
                        o.id              AS 'oID'    ,
                        o.num             AS 'oNUM'   ,
                        o.name            AS 'oNAME'  ,
                        o.address         AS 'oADDR'  ,
                        o.place           AS 'oADDR2' ,
                        o.phone           AS 'oPHONE' ,
                        o.operativ_info   AS 'oINFO'  ,
                        o.id_status       AS 'oSTAT'  ,
                        o.geo_lan		  AS 'oGeoLan',
                        o.geo_lat		  AS 'oGeoLat',
                        o.id_office	      AS 'offID'  ,
                        r.name            AS 'rNAME'  ,
                        ao.id_areas       AS 'rec'
                   FROM objects o
                   LEFT JOIN offices  r ON o.id_office= r.id
                         LEFT JOIN areas_offices ao ON r.id = ao.id_offices
                   WHERE ";
         if( isset( $id_obj ) && $id_obj != 0 ) {
      $oQuery .= " o.id = $id_obj ";
         }
         else {
      $oQuery .= " o.id_status != 4 AND o.num = $num AND ao.id_areas = $rec ";
         }
      $oQuery .= " ORDER BY o.id
                   LIMIT 1 ";

       $oRs = mysqli_query( $db_sod, $oQuery ) or die( "Error: ".$oQuery );
       while ( $oRow = mysqli_fetch_assoc( $oRs ) ) {

         $aResult[0  ] = $oRow['oID'   ];
         $aResult[1  ] = $oRow['oNUM'  ];
         $aResult[2  ] = $oRow['oNAME' ];
         $aResult[3  ] = $oRow['oADDR' ];
         $aResult[4  ] = $oRow['oADDR2'  ];
         $aResult[5  ] = $oRow['oPHONE'  ];
         $aResult[6  ] = $oRow['oINFO' ];
         $aResult[7  ] = $oRow['oSTAT' ];
         $aResult[8  ] = $oRow['rNAME' ];
         $aResult[9  ] = $oRow['rec'   ];
         $aResult[10 ] = $oRow['offID' ];
         $aResult[11 ] = $oRow['oGeoLan'];
         $aResult[12 ] = $oRow['oGeoLat'];
         }

         return $aResult;

     }

    /*
     * $type = 0 - checkboxes;
     * $type = 1 - radio
     * $list = 1 - get play_alarms IN(1,2) and remove <li> and counter
     * */
    function get_signals( $type, $list ) {

        global  $db_sod;
        $c = 0;
        $strChecked = "";
        $strType = "";
        $sRs = "";
        $sRs_h = "";
        if( $list == 1 ) {
            $strWhere = " AND play_alarm IN(1,2) ";
        }


        $sQuery = "SELECT id, COALESCE( msg_al, msg_rest ) AS msg, play_alarm, pic, ico, ico_al FROM signals WHERE to_arc = 0 ".$strWhere." ORDER BY play_alarm DESC";
        $sResult = mysqli_query( $db_sod, $sQuery ) or die( print "ВЪЗНИКНА ГРЕШКА! ОПИТАЙТЕ ПО–КЪСНО!" );
        $sNumRes = mysqli_num_rows( $sResult );

        if( $type == 0 ) {
            if( $list == 0 ) {
                $sRs_h = "<ol><li class='alert alert-danger' style='margin-left: 0px; font-size: 10px;'>
                                <input type='checkbox' id='flag' name='flag' checked='checked' />
                                &nbsp;<i class='fa fa-ban fa-2x'></i>
                                &nbsp; НЕВЪЗСТАНОВЕНИ
                            </li>";
            } else {
                $sRs_h = "<ol class='control-sidebar-menu'>";
            }
        } else {
            $sRs_h = "<ol><li class='alert alert-danger' style='margin-left: 0px; font-size: 10px;'>
                            <input type='checkbox' id='rflag' name='rflag' />
                            &nbsp;<i class='fa fa-calendar fa-2x'></i>
                            &nbsp;&nbsp; ПО ДНИ
                        </li>";
        }

        $sRs_f = "</ol>";

        while ( $oRow = mysqli_fetch_assoc( $sResult ) ) {

            $c = $c + 1;
            $sID    = $oRow['id'        ];
            $sName  = $oRow['msg'       ];
            $sPlay  = $oRow['play_alarm'];
            $strIcon= $oRow['ico'       ];

            if( $type == 0 ) {

                if( $sID == 1 || $sID == 2 || $sID == 3 || $sID == 4 || $sID == 13 ) {
                    $strChecked = "checked = 'checked' ";
                    $strType = "checkbox";
                } else {
                    $strChecked = "";
                }
            } else {
                    $strType = "radio";
            }


            if( $list == 1 ) {

                if( $sPlay == 2 ) {
                    $strBG = "bg-red";
                    $strChecked = "checked = 'checked' ";
                } else {
                    $strBG = "bg-yellow";
                    $strChecked = "";
                }

//                $sRs .= "<div class='form-group'>
//                            <label class='control-sidebar-subheading'>
//                            &nbsp; <i class='".$strIcon." fa-md'></i> &nbsp; ".$sName."
//                            <input type='checkbox' class='pull-right' value='".$sID."' ".$strChecked." />
//                            </label>
//                          </div>";
                $sRs .= "<li>
                            <a href='#'>
                                <i class='menu-icon ".$strIcon." ".$strBG."'></i>
                                <div class='menu-info'>
                                    <p class='control-sidebar-subheading'>".$sName."
                                    <input type='checkbox' class='pull-right' value='".$sID."' ".$strChecked."  onclick='set_signal_type(".$sID."); refreshDiv(control-sidebar-home-tab);'/>
                                    </p>
                                </div>
                            </a>
                        </li>";
            } else {

                $sRs .= "
                     <li class='btn-sm btn-default' style='margin: 4px;' >
                        <input type='".$strType."' id='".$sID."' name='signals' value='".$sID."' ".$strChecked." />
                        &nbsp; <i class='".$strIcon." fa-lg'></i> &nbsp; ".$sName."
                    </li>
                     ";
            }
            
        }

        echo $sRs_h.$sRs.$sRs_f;

    }


function get_birthdays() {

    global  $db_personnel;
    $sRs = "";
    $sRs_h = "";



    $sQuery = "
            SELECT
                CONCAT( fname,' ', lname) AS pName,
                CONCAT(substr(egn, 5, 2),'.', substr(egn, 3, 2),'.', year(NOW()) ) AS bDay
            FROM personnel
            WHERE
                CONCAT( year(NOW()), substr(egn, 3, 2), substr(egn, 5, 2) ) < DATE_FORMAT( DATE_ADD(NOW(), INTERVAL +1 month ), '%Y%m%d' )
                AND
                CONCAT( year(NOW()), substr(egn, 3, 2), substr(egn, 5, 2) ) >= DATE_FORMAT( NOW(), '%Y%m%d' )
                AND
	            vacate_date != 0
	        ORDER BY bDay ASC";
    $sResult = mysqli_query( $db_personnel, $sQuery ) or die( print "ВЪЗНИКНА ГРЕШКА! ОПИТАЙТЕ ПО–КЪСНО!" );


    $sRs_h = "<h3 class='control-sidebar-heading'>Рожденници</h3>
              <ul class='control-sidebar-menu'>";
    $sRs_f = "</ul>";

    while ( $oRow = mysqli_fetch_assoc( $sResult ) ) {

        $pName = $oRow['pName'];
        $bDay  = $oRow['bDay' ];

        $sRs .= "<li>
                    <a href='#'>
                      <i class='menu-icon fa fa-birthday-cake bg-red'></i>
                      <div class='menu-info'>
                        <h4 class='control-sidebar-subheading'>".$pName."</h4>
                        <p>".$bDay."</p>
                      </div>
                    </a>
                  </li>";
    }

    echo $sRs_h.$sRs.$sRs_f;

}




    function get_Prepaid_Objects()
    {

        global $db_sod, $strCurrentOffice;
        $i = 0; /* var for months */

        $cQuery = "
                    SELECT
                        TIMESTAMPDIFF( month, MIN(os.real_paid), MAX(os.real_paid) ) AS 'cR',
                        MAX(os.real_paid) AS 'maxMonth'
                    FROM objects o
                    LEFT JOIN objects_services os ON. o.id = os.id_object AND os.to_arc = 0
                    WHERE
                        o.id_status IN( 1, 14 ) AND os.real_paid > NOW()
                    LIMIT 1";

        $cResult = mysqli_query($db_sod, $cQuery) or die("Error: " . $cQuery);
        $cRows = mysqli_num_rows($cResult);

        if (!$cRows) {
            echo "<li class='callout callout-success'><h5><i class='fa fa-smile-o'></i> Няма предплатени такси! </h5></li>";
        }

        while ($cRow = mysqli_fetch_assoc($cResult)) {
            $cR = isset($cRow['cR']) ? $cRow['cR'] : 0;
            $maxMonth = isset($cRow['maxMonth']) ? $cRow['maxMonth'] : 0;
        }

        $sub_total = 0;
        $strBank = 0;
        $strReturn = 0;
        $strTotal = 0;
        $strMonth = '';
        $cMonth = '';
        $maxValue = 0;
        $bankLineColor = 'red';

        for($i = 0; $i <= $cR; $i++ ) {

            $cMonth = date( 'Y-m-d', strtotime(date($maxMonth)." -$i month"));

            $aQuery = "
                    SELECT
                        ROUND( SUM( os.total_sum + 0 )/1.2, 0     ) AS 'tSum'   ,
                        DATE_FORMAT( '".$cMonth."' , '%Y.%m'  ) AS 'pMonth' ,
                        ROUND( (SELECT sum(current_sum) FROM finance.account_states WHERE id_bank_account IN(30,42)), 0 ) AS 'iBank'
                    FROM objects o
                    LEFT JOIN objects_services os ON. o.id = os.id_object AND os.to_arc = 0 AND os.real_paid = '".$cMonth."'
                    WHERE
                        o.id_status IN( 1, 14 ) AND o.id_office IN(".$strCurrentOffice.")
                    GROUP BY pMonth ";

            $aResult = mysqli_query($db_sod, $aQuery) or die("Error: " . $aQuery);
            $aRows = mysqli_num_rows($aResult);

            if (!$aRows) {
                echo "<li class='callout callout-success'><h5><i class='fa fa-smile-o'></i> Няма обекти с предплатени такси! </h5></li>";
            }

            while ($aRow = mysqli_fetch_assoc($aResult)) {

                $iBank  = isset($aRow['iBank'   ]) ? $aRow['iBank'  ] : 0;
                $tSum   = isset($aRow['tSum'    ]) ? $aRow['tSum'   ] : 0;

            }

            $strBank = $iBank . "," . $strBank;

            $sub_total += $tSum;                        // month on month with add previous to increase current

            if( $i > 0 ) {
                $strReturn = $tSum . "," . $strReturn;  // string for bar
                $strTotal  = $sub_total . "," . $strTotal;
            }
            else {
                $strReturn = $tSum;
                $strTotal  = $sub_total;                // last value for increase sum bye month - line
            }

            if( $sub_total > $iBank ) { $maxValue = $sub_total;  }
            else { $maxValue = $iBank;  $bankLineColor = 'gray'; }

            $strMonth = "&nbsp;".substr( $cMonth, 5, 2 ).".".substr( $cMonth, 0, 4 ) ."&nbsp;&nbsp;&nbsp;". $strMonth; // string for month X

        } // end for $i;

        $strBank    = $iBank. "," . $iBank. "," . $iBank. "," . $iBank. "," . $iBank. "," . $iBank. "," . $iBank. "," . $strBank;

        return array($strReturn, $strBank, $strTotal, $strMonth, $maxValue, $bankLineColor);
    } // end for function;
?> 
