<?php
require_once( 'include/parse_excel/reader.php' );
require_once( 'include/import.inc.php' );

require_once("include/fpdi/fpdi.php");

$oSalary = new DBBase( $db_personnel, 'salary' );


if ( $aParams['api_action'] == 'delete' ) {
    /*if ($aParams['idc'] > 0) {
        if ( ($nResult = $oSalary->toARC( (int) $aParams['idc']) ) != DBAPI_ERR_SUCCESS ) {
            $oResponse->setError( $nResult );
            print( $oResponse->toXML() );
        }
    }*/
    $chk = $aParams['chk'];
    $tmp = array();

    foreach( $chk as $k => $v ) {
        if ( !empty($v) ) {
            array_push($tmp, $k);
        }
    }

    if ( !empty($tmp) ) {
        global $db_personnel;
        $nIDs = implode( ",", $tmp );
        $sQuery = "UPDATE salary SET to_arc = 1, updated_time = NOW() , updated_user = {$_SESSION['userdata']['id_person']}  WHERE id IN ({$nIDs})";
        $db_personnel->Execute($sQuery);
    }

} elseif($aParams['api_action'] == 'openTicket') {
    global $oResponse;

    $oDBPersonnel = new DBPersonnel();

    $nID = (int) !empty( $aParams['id'] ) ? $aParams['id'] : 0;
    $nYear = (int) !empty($aParams['year']) ? $aParams['year'] : 0;
    $nMonth = (int) !empty($aParams['month']) ? $aParams['month'] : 0;

    if ( ( !empty($nMonth) && (($nMonth < 1) || ($nMonth >12))) ) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за месец!");
        print( $oResponse->toXML() );
        return DBAPI_ERR_INVALID_PARAM;
    }

    if ( ( !empty($nYear) && (($nYear < 2000) || ($nYear >2050))) ) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за година!");
        print( $oResponse->toXML() );
        return DBAPI_ERR_INVALID_PARAM;
    }

    $nMonth = zero_padding($nMonth,2);

    $aPerson = $oDBPersonnel->getRecord($nID);
    $nCode = $aPerson['code'];
    $nPage = 0;

    $aData = array();
    $sTmpDir = $_SESSION['BASE_DIR'].'/tmp/';
    $sFileNameExcel = $_SESSION['BASE_DIR'].'/storage/salary_ticket_'.$nYear.$nMonth.'.xls';
    $sFileNamePDF = $_SESSION['BASE_DIR'].'/storage/salary_ticket_'.$nYear.$nMonth.'.pdf';


    if(!file_exists($sFileNamePDF)) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е намерен PDF файл за конкретния месец!");
        print( $oResponse->toXML() );
        return DBAPI_ERR_INVALID_PARAM;
    }

    if(!file_exists($sFileNameExcel)) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е намерен EXEL-ски файл за конкретния месец!");
        print( $oResponse->toXML() );
        return DBAPI_ERR_INVALID_PARAM;
    }

    $sError = DataFromXLS(false,$sFileNameExcel,$aData);

    foreach($aData['cells'] as $values) {
        if(!empty($values[2])) {

            if($values[2] == $nCode) {
                $nPage = $values[1];
                break;
            }
        }
    }


    if(empty($nPage)) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Няма въведен фиш в системата");
        print( $oResponse->toXML() );
        return DBAPI_ERR_INVALID_PARAM;
    } else {

        $oFPDI = new FPDI();
        $nPages = $oFPDI->setSourceFile($sFileNamePDF);
        $tplidx = $oFPDI->importPage($nPage,'/MediaBox');


        $oFPDI->AddPage();
        $oFPDI->useTemplate($tplidx);
        $oFPDI->Output('sal.pdf','D');

    }



}

class MyHandler
    extends APIHandler {

    function setFields($aParams) {
        global $oResponse;

        $act 	= isset($aParams['sAct']) 	&& is_numeric($aParams['sAct'])  	? $aParams['sAct'] : 0;
        $nYear 	= isset($aParams['year']) 	&& is_numeric($aParams['year']) 	? $aParams['year'] : 0;
        $nMonth = isset($aParams['month']) 	&& is_numeric($aParams['month']) 	? $aParams['month'] : 0;

        $nD		 = mktime(0, 0, 0, $nMonth, 1, $nYear);
        $nN		 = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));

        //debug($oResponse->oResult->aData);
        if ( in_array('person_salary_edit', $_SESSION['userdata']['access_right_levels']) ) {
            if ( $act == 1 ) {
                if ( $nD >= $nN ) {
                    $oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
                    $oResponse->setFieldData('chk', 'input', array('type' => 'checkbox', 'exception' => 'false'));

                    $oResponse -> setFormElement('form1', 'sel', array(), '');
                    $oResponse -> setFormElementChild('form1', 'sel', array('value' => '0'), "------");
                    $oResponse -> setFormElementChild('form1', 'sel', array('value' => '1'), "--- Маркирай всички ---");
                    $oResponse -> setFormElementChild('form1', 'sel', array('value' => '2'), "--- Отмаркирай всички ---");
                    $oResponse -> setFormElementChild('form1', 'sel', array('value' => '3'), "--- Изтрий ---");
                }
            }
        }

        $oResponse->setField( 'code', 			'код', 			'Сортирай по код', NULL, NULL, NULL, array('DATA_TOTAL' => 1) );
        $oResponse->setField( 'name', 			'описание',		'Сортирай по описание' );
        $oResponse->setField( 'firm', 			'към фирма', 	'Сортирай по фирма'  );
        $oResponse->setField( 'region', 		'регион', 		'Сортирай по регион'  );
        $oResponse->setField( 'objectsa', 		'обект', 		'Сортирай по обект'  );
        if ( $act == 1 ) {
            $oResponse->setField( 'sum', 		'стойност', 	'Сортирай по стойност', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY) );
        }
        $oResponse->setField( 'count', 			'кол', 			'Сортирай по количество', NULL, NULL, NULL, array('DATA_FORMAT' => DF_DIGIT, 'DATA_TOTAL' => 1) );
        $oResponse->setField( 'total_sum', 		'общо', 		'Сортирай по стойност', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1) );
        $oResponse->setField( 'paid', 			'платено', 		'Сортирай по стойност', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1) );
        $oResponse->setField( 'paid_date', 		'дата', 		'Сортирай по дата'  );
        $oResponse->setField( 'updated_user', 	'...', 			'Сортиране по последно редактирал', 'images/dots.gif' );

        if ( in_array('person_salary_edit', $_SESSION['userdata']['access_right_levels']) ) {
//					if ( $act == 1 ) {
//						$oResponse->setField( '', '', 'Изтрий', "images/cancel.gif", "delSalary", '');
//					}

            if ( $nD >= $nN ) {
                $oResponse->setFIeldLink( 'code',		'editSalaryORRoadList' );
                $oResponse->setFIeldLink( 'name',		'editSalary' );
                $oResponse->setFIeldLink( 'objectsa',	'openObject' );
            }
        }
    }

    function getReport( $aParams ) {
        global $oResponse, $db_name_sod, $db_name_personnel;
        $aWhere = array();

        $nID 	= isset($aParams['id']) 	&& is_numeric($aParams['id']) 		? $aParams['id'] 	: 0;
        $nYear 	= isset($aParams['year']) 	&& is_numeric($aParams['year']) 	? $aParams['year'] 	: 0;
        $nMonth = isset($aParams['month']) 	&& is_numeric($aParams['month']) 	? $aParams['month'] : 0;
        $act 	= isset($aParams['sAct']) 	&& is_numeric($aParams['sAct'])  	? $aParams['sAct'] 	: 1;

        $sfield = $aParams['sfield'];


        if( $aParams['api_action'] == "export_to_xls" || $aParams['api_action'] == "export_to_pdf" )
        {
            //ако ще експортва в pdf и xls параметрите plus и минус се сетват true заради заявката
            //иначе заявката се чупи
            $plus = true;
            $minus = true;
        }
        else
        {
            $plus = isset($aParams['plus']) ? (int) $aParams['plus'] : 0;
            $minus = isset($aParams['minus']) ? (int) $aParams['minus'] : 0;
        }
        //$aParams['sfield'] = "t.is_earning DESC,".$aParams['sfield'];


        if ( (!empty($nYear) && ( ($nYear < 2000) || ($nYear > 2050) ))  || ( !empty($nMonth) && (($nMonth < 1) || ($nMonth >12))) )
        {
            $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за месец, година!");
            return DBAPI_ERR_INVALID_PARAM;
        }

        if ( $act == 1 ) {
            $sum = "
						os.stake_duty as sum, 
						t.count as count,
						t.total_sum as total_sum,
						SUM(count) as quantity,
					";
        }

        if ( $act == 2 ) {
            //SUM(t.sum) / COUNT(t.sum) as sum,
            //SUM(t.total_sum) / SUM(t.count) as sum,
            $sum = "
						1 as sum,
						SUM(t.count) as count,
						SUM(t.total_sum) as total_sum,
						SUM(count) as quantity,
					";
        }

        if ( $act == 3 ) {
            //SUM(t.sum) / COUNT(t.sum) as sum,
            //SUM(t.total_sum) / SUM(t.count) as sum,
            $sum = "
						1 as sum,
						SUM(t.count) as count,
						SUM(t.total_sum) as total_sum,
						SUM(count) as quantity,
					";
        }

        $aWhere[] = "( IF (pos.position_function IN ('patrul', 'dispatcher'), 1, 0) = 1 AND ( t.code != '+ДЕЖ_Д' OR f.id != 2)  OR IF (pos.position_function NOT IN ('patrul', 'dispatcher'), 1, 0) = 1 )";
        $aWhere[] = sprintf(" t.id_person = %d", $nID);
        $aWhere[] = sprintf(" t.month = %d ",  $nYear * 100 + $nMonth );
        $aWhere[] = " t.to_arc=0 ";

        if ( $plus && !$minus ) {
            $aWhere[] = " t.is_earning = 1 ";
        } elseif ( !$plus && $minus ) {
            $aWhere[] = " t.is_earning = 0 ";
        } elseif ( !$plus && !$minus ) {
            $aWhere[] = " t.is_earning > 1 ";
        }

        $sQuery = sprintf(" 
					SELECT 
						SQL_CALC_FOUND_ROWS 
						t.id as _id, 
						t.id as chk,
						CONCAT_WS(',' ,IF ( r.id_firm, CONCAT(t.id, ',', r.id_firm), CONCAT(t.id, ',0') ) , t.id_road_list , t.id_object , IF(  tr.id_contract IS NOT NULL , tr.id_contract , 0 ) ) as id,
						t.code as code, 
						t.month,
						r.name as region,
						f.name as firm,
						o.name as object,
						obj.name as objectsa,
						is_earning as type,
						{$sum}
						t.is_earning,
						CONCAT( f.name , ' (',r.name, ')' ) as region_name,
						CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user,
						t.description as name,
						t.paid_sum as paid,
						IF ( UNIX_TIMESTAMP(t.last_paid_date) > 0, DATE_FORMAT(t.last_paid_date,'%%d.%%m.%%Y'), '' ) as paid_date,
						h.id as h1,
						h_y.id as h2,
						t.auto
					FROM 
						%s t 
						LEFT JOIN {$db_name_sod}.offices r ON r.id = t.id_office
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = t.id_person
						LEFT JOIN {$db_name_personnel}.positions pos ON pos.id = p.id_position
						LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
						LEFT JOIN {$db_name_sod}.region_objects o ON o.id = t.id_object
						LEFT JOIN {$db_name_sod}.objects obj ON obj.id = t.id_object
						LEFT JOIN {$db_name_personnel}.personnel as up on up.id = t. updated_user
						LEFT JOIN {$db_name_personnel}.salary_earning_types ear ON (t.code = ear.code AND t.is_earning = 1 AND ear.to_arc = 0)
						LEFT JOIN {$db_name_personnel}.salary_expense_types expe ON (t.code = expe.code AND t.is_earning = 0 AND expe.to_arc = 0)
						LEFT JOIN {$db_name_sod}.object_duty objd ON objd.id = t.id_object_duty
						LEFT JOIN {$db_name_sod}.object_shifts os ON os.id = objd.id_shift
						LEFT JOIN {$db_name_sod}.holidays h ON ( DAY( objd.startShift ) = h.day AND MONTH( objd.startShift ) = h.month )
						LEFT JOIN {$db_name_sod}.holidays h_y ON ( DAY( objd.endShift ) = h_y.day AND MONTH( objd.endShift ) = h_y.month )
						LEFT JOIN {$db_name_sod}.tech_limit_cards tlc ON tlc.id = t.id_limit_card
						LEFT JOIN {$db_name_sod}.tech_requests tr ON tlc.id = tr.id_limit_card

					",
            $this->_oBase->_sTableName
        );

        if ( $act == 1 ) {
            $aGroup = " t.id ";
        }

        if ( $act == 2 ) {
            $aGroup = " t.code ";
        }

        if ( $act == 3 ) {
            $aGroup = " t.id_object, t.code ";
        }

        //if ( t.is_earning, ear.name, expe.name) as name
        //	SUM( IF(is_earning = 1, total_sum, (total_sum * -1)) ) AS total,
        //echo $sQuery;

        APILog::Log(0, $sQuery);

        $nRowCount = $_SESSION['userdata']['row_limit'];
        $_SESSION['userdata']['row_limit'] = 10000;

        $this->_oBase->getReport( $aParams, $sQuery, $aWhere, $aGroup );
        //return $aWhere;
        $_SESSION['userdata']['row_limit'] = $nRowCount;
        //$aParams['sfield'] = $sfield;

        $total 		= 0;
        $total2 	= 0;
        $quantity 	= 0;
        $rows 		= 0;
        $earning	= 0;
        $expense	= 0;

        foreach ( $oResponse->oResult->aData as $key => &$val ) {
            if ( $val['is_earning'] == 0 ) {
                $expense += $val['total_sum'];
                $val['total_sum'] = $val['total_sum'] * -1;
                $val['sum'] = $val['sum'] * -1;
            } else {
                $earning += $val['total_sum'];
            }

            $total += $val['total_sum'];
            $total2 += $val['paid'];
            $quantity += $val['quantity'];
            $rows++;


            //if ( !empty($val['h1']) || !empty($val['h2']) ) {
            if ( !empty($val['is_earning']) ) {
                $oResponse->setDataAttributes( $key, 'code', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'name', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'firm', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'region', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'objectsa', array('class' => 'bg-dark'));
                if ( $act == 1 ) {
                    $oResponse->setDataAttributes( $key, 'sum', array('class' => 'bg-dark'));
                }
                $oResponse->setDataAttributes( $key, 'count', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'total_sum', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'paid', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'paid_date', array('class' => 'bg-dark'));
                $oResponse->setDataAttributes( $key, 'updated_user', array('class' => 'bg-dark'));
            } else {
                $oResponse->setDataAttributes( $key, 'code', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'name', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'firm', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'region', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'objectsa', array('class' => 'bg-warning'));
                if ( $act == 1 ) {
                    $oResponse->setDataAttributes( $key, 'sum', array('class' => 'bg-danger'));
                }
                $oResponse->setDataAttributes( $key, 'count', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'total_sum', array('class' => 'bg-danger'));
                $oResponse->setDataAttributes( $key, 'paid', array('class' => 'bg-danger'));
                $oResponse->setDataAttributes( $key, 'paid_date', array('class' => 'bg-warning'));
                $oResponse->setDataAttributes( $key, 'updated_user', array('class' => 'bg-warning'));
            }

            if (!empty($val['auto'])) {
                $oResponse->setDataAttributes( $key, 'code', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'name', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'firm', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'region', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'objectsa', array('style' => 'font-weight: bold;'));
                if ( $act == 1 ) {
                    $oResponse->setDataAttributes( $key, 'sum', array('style' => 'font-weight: bold;'));
                }
                $oResponse->setDataAttributes( $key, 'count', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'total_sum', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'paid', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'paid_date', array('style' => 'font-weight: bold;'));
                $oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'font-weight: bold;'));
            }
        }

        if ( $rows == 1 ) {
            $rows = $rows." ред";
        } else $rows = $rows." реда";

        $oResponse->addTotal('total_sum', $total);
        $oResponse->addTotal('paid', $total2);
        $oResponse->addTotal('count', $quantity);
        $oResponse->addTotal('code', $rows);

        $oResponse->setFormElement( 'form1', 'plus_price', array(), sprintf("%01.2f лв.", $earning) );
        $oResponse->setFormElement( 'form1', 'minus_price', array(), sprintf("%01.2f лв.", $expense) );
        //debug($oResponse->oResult->aData);
    }

}

$oHandler = new MyHandler( $oSalary, 'is_earning DESC, code', 'salary', $aParams['sName'] );

$oHandler->Handler( $aParams );
//debug($oResponse);
?>