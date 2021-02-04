<?php
$oLeave = New DBBase( $db_personnel, 'person_leaves' );

switch( $aParams['api_action']) {
    case "save" :
        saveLeave( $aParams );
        break;
    default :
        loadLeave( $aParams['id'] );
        break;
}

function saveLeave( $aParams ) {
    global $oLeave, $oResponse;

    if( empty( $aParams['id_person'] ) ) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела не е вкаран в системата!", __FILE__, __LINE__ );
        return DBAPI_ERR_INVALID_PARAM;
    }

    !empty($aParams['year']) ? $year = (int) $aParams['year'] : $year = 0;
    if( $year < 0 )
    {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето година!", __FILE__, __LINE__ );
        return DBAPI_ERR_INVALID_PARAM;
    }

    $aDuplicate = array();
    $aWhere = array();
    $aWhere[] = " id != {$aParams['id']} ";
    $aWhere[] = " id_person = {$aParams['id_person']} ";
    $aWhere[] = " year = '{$year}' ";
    $aWhere[] = " to_arc = 0 ";
    $aWhere[] = " type = 'leave' ";
    $aWhere[] = " leave_types = 'due' ";

    if( $nResult = $oLeave->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
        $oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
        return DBAPI_ERR_SQL_QUERY;
    }

    if( !empty( $aDuplicate ) ) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Съществува запис за избраната година!", __FILE__, __LINE__ );
        return DBAPI_ERR_INVALID_PARAM;
    }

    //-- Obsolete : MANTIS ID : 0001502
    /*if( empty( $aParams['due_days'] ) ) {
        $oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето дни!", __FILE__, __LINE__ );
        return DBAPI_ERR_INVALID_PARAM;
    }*/

    $aLeave = array();
    $aLeave['id'] 			= $aParams['id'];
    $aLeave['id_person'] 	= $aParams['id_person'];
    $aLeave['year'] 		= $aParams['year'];
    $aLeave['due_days'] 	= ( int ) $aParams['due_days'];
    $aLeave['remaining_days'] 	= ( int ) $aParams['due_days'];
    $aLeave['type'] 		= "leave";

    if( $nResult = $oLeave->update( $aLeave ) != DBAPI_ERR_SUCCESS ) {
        $oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
        return $nResult;
    }

    return DBAPI_ERR_SUCCESS;
}

function loadLeave( $nID ) {
    global $oLeave, $oResponse;

    $id = (int) $nID;

    if ( $id > 0 ) {
        // Редакция
        $aData = array();

        if( $nResult = $oLeave->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS ) {
            $oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
            return $nResult;
        }

        $oResponse->setFormElement('form1', 'year', array(),		$aData['year']);
        $oResponse->setFormElement('form1', 'due_days', array(),	$aData['due_days']);

        //debug($aData);
    }

    return DBAPI_ERR_SUCCESS;
}

print( $oResponse->toXML() );
?>