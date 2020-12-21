<?php
if (!isset($_SESSION))
	session_start();

$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');

require_once ("../config/function.autoload.php");
require_once ("../include/adodb/adodb-exceptions.inc.php");
require_once ("../config/connect.inc.php");
require_once ("../include/general.inc.php");

header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past

$sResquestQueryType = !empty($_GET['query_type']) ? $_GET['query_type'] : '';
$sResquestValue		= !empty($_GET['val']) ? addslashes($_GET['val']) : '';

// бр. на редовете,които ще се показват в suggest-a
define ('NUM_ROWS', 20);

try {

	switch ($sResquestQueryType)
	{
		case 'firm':
			SuggestFirm();
			break;

		case 'region':
			SuggestRegion();
			break;

		case 'region_object':
			SuggestRegionObject();
			break;

		case 'acode':
			// Наработка към заплата
			SuggestAcode();
			break;

		case 'dcode':
			// Удръжка от заплата
			SuggestDcode();
			break;

		case 'document':
			SuggestDocument();
			break;

		case 'position':
			SuggestPosition();
			break;

		case 'position_nc':
			SuggestPositionNC();
			break;

		case 'person':
			SuggestPerson();
			break;
		case 'personContract':
			SuggestPersonContract();
			break;

		case 'personMultiLevel':
			SuggestPersonID();
			break;
		case 'personSecurityContract':
			SuggestPersonSecurity();
			break;
		case 'user':
			SuggestUser();
			break;

		case 'personByCode':
			SuggestPersonByCode();
			break;

		case 'personByName':
			SuggestPersonByName();
			break;

		case 'suggestObjectPerson':
			suggestObjectPerson();
			break;

		case 'suggestFreeObjectPerson':
			suggestFreeObjectPerson();
			break;

		case 'pppSourceName':
			pppSourceName();
			break;

		case 'pppDestName':
			pppDestName();
			break;

		case 'object':
			SuggestObject();
			break;

		case 'obj':
			SuggestObj();
			break;

		case 'objWithoutRegions':
			SuggestObjWithoutRegions();
			break;

		case 'objContractSecurity':
			objContractSecurity();
			break;
		case 'objRev':
			SuggestObjRev();
			break;

		case 'layer_object':
			SuggestLayerObject();
			break;
		case 'pobj':
			SuggestPLObj();
			break;

		case 'objByName':
			SuggestObjByName();
			break;

		case 'objByNum':
			SuggestObjByNum();
			break;

		case 'objByNumWithStatus':
			SuggestObjByNumWithStatus();
			break;

		case 'ActObjByNum':
			SuggestActObjByNum();
			break;

		case 'sCity':
			SuggestCity();
			break;

		case 'onSuggestCity':
			SuggestCityEx();
			break;

		case 'sStreet':
			SuggestStreet();
			break;

		case 'statesStorage':
			statesStorage();
			break;

		case 'objectTypes':
			objectTypes();
			break;

		case 'techPerson':
			SuggestTechPerson();
			break;

		case 'tech_operations':
			SuggestTechOperations();
			break;

		case 'client':
			SuggestClient();
			break;

		case 'ClientByNameOrEin' :
			suggestClientByNameOrEin();
			break;

		case 'clientContractSecurity':
			SuggestClientContractSecurity();
			break;

		case 'objectComplex':
			SuggestObjectComplex();
			break;

		case 'ClientName':
			SuggestClientName();
			break;

		case 'ClientEin':
			suggestClientEin();
			break;

		case 'ClientPhone':
			SuggestClientPhone();
			break;

		case 'deliverer':
			SuggestDeliverer();
			break;

		case 'MyTelepolClientName':
			SuggestMyTelepolClient();
			break;

		case 'MyTelepolObj':
			SuggestMyTelepolObj();
			break;

		case 'MyTelepolProfiles':
			SuggestMyTelepolProfiles();
			break;

		case 'ObjLayerSuggest':
			SuggestObjLayer();
			break;

		case 'nomenclatures':
			SuggestNomenclature();
			break;

		default:
			SuggestEmpty();
	}
}
catch( Exception $e )
{
	SuggestEmpty();
	return 0;
}

function toJS( $aKeys, $aHtmlValues, $aValues )
{
	printf("var key = new Array( %s ); var html_value = new Array( %s ); var value = new Array( %s );",
		implode(",",$aKeys), implode(",",$aHtmlValues), implode(",",$aValues) );
}

function SuggestEmpty()
{
	toJS( array(), array(), array() );
}

// Suggest по име на фирма
function SuggestFirm () {
	global $sResquestValue, $db_sod;

	$sQuery = "
					SELECT 
						distinct(code), name, id
					FROM
						firms
					WHERE
						to_arc = 0 AND UPPER(name) LIKE UPPER('%".$sResquestValue."%')	
					ORDER BY
						name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по име на офис
function SuggestRegion () {
	global $sResquestValue, $db_sod, $_GET;

	if( empty( $_GET['id_firm']) )
	{
		SuggestEmpty ();
		return 0;
	}

	$nIdFirm = (int) $_GET['id_firm'];

	$sQuery = "
					SELECT 
						distinct(r.code), r.name, r.id
					FROM
						offices r
					WHERE
						r.to_arc = 0 AND UPPER(r.name) LIKE UPPER('%{$sResquestValue}%') AND r.id_firm = {$nIdFirm}
					ORDER BY
						r.name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по име на офис
function SuggestRegionObject()
{
	global $sResquestValue, $db_sod;

	$sQuery = "
				SELECT
					distinct(o.num), o.name, o.id
				FROM objects o
					LEFT JOIN offices r ON r.id = o.id_office
				WHERE 1
		";

	//Conditions
	if( is_numeric( $sResquestValue ) )
	{
		$sQuery .= " AND o.num = {$sResquestValue} ";
	}
	else
	{
		$sQuery .= " AND UPPER( o.name ) LIKE UPPER( '%{$sResquestValue}%' ) ";
	}

	if( !empty( $_GET['firm'] ) )
	{
		$nIDFirm = (int) $_GET['firm'];
		$sQuery .= " AND r.id_firm = {$nIDFirm} ";
	}

	if( !empty( $_GET['region'] ) )
	{
		$nIDOffice = (int) $_GET['region'];
		$sQuery .= " AND o.id_office = {$nIDOffice} ";
	}
	else
	{
		$sIDOffices = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sQuery .= " AND o.id_office IN ({$sIDOffices}) \n";
	}
	//End Conditions

	$sQuery .= "
				ORDER BY
					r.name ASC
				LIMIT " . NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if( !$oRes )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( ( count( $aData ) == 1 ) && ( strtolower( $aData[0]['name'] ) == strtolower( $sResquestValue ) ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = javascriptescape_deep( $aData );

	foreach( $aData as $k => $v )
	{
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по наработки към заплата
function SuggestAcode () {
	global $sResquestValue, $db_personnel, $_GET;

	$sQuery = "
					SELECT 
						distinct(s.code), s.name, s.id
					FROM
						salary_earning_types s
					WHERE
						UPPER(s.code) LIKE UPPER('%{$sResquestValue}%')
						AND s.to_arc = 0
					ORDER BY
						s.name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['code']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по удръжки към заплата
function SuggestDcode () {
	global $sResquestValue, $db_personnel, $_GET;

	$sQuery = "
					SELECT 
						distinct(s.code), s.name, s.id
					FROM
						salary_expense_types s
					WHERE
						UPPER(s.code) LIKE UPPER('%{$sResquestValue}%')
						AND s.to_arc = 0
					ORDER BY
						s.name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['code']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по документи
function SuggestDocument () {
	global $sResquestValue, $db_personnel, $_GET;

	$sQuery = "
					SELECT 
						distinct(d.name), d.id
					FROM
						document_types d
					WHERE
						UPPER(d.name) LIKE UPPER('%{$sResquestValue}%')
					ORDER BY
						d.name ASC
					LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по длъжности
function SuggestPosition () {
	global $sResquestValue, $db_personnel, $_GET;

	$nIdFirm = isset($_GET['nIDFirm']) && is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;

	$sQuery = "
			SELECT 
				distinct(pp.code), 
				pp.name, 
				pp.id
			FROM positions pp
			WHERE
				pp.to_arc = 0 AND ( UPPER(pp.code) LIKE UPPER('%{$sResquestValue}%') OR UPPER(pp.name) LIKE UPPER('%{$sResquestValue}%') )
		";

	if ( !empty($nIdFirm) ) {
		$sQuery .= "	AND FIND_IN_SET({$nIdFirm}, pp.regions) ";
	}

	$sQuery .= "
			ORDER BY
				pp.name ASC
			LIMIT ".NUM_ROWS;

	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPositionNC () {
	global $sResquestValue, $db_personnel, $_GET;

	$nIdFirm = isset($_GET['nIDFirm']) && is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;

	$sQuery = "
                SELECT
                    distinct(pp.cipher),
                    pp.name,
                    pp.min_salary,
                    pp.id
                FROM positions_nc pp
                WHERE
                    pp.to_arc = 0 AND ( UPPER(pp.cipher) LIKE UPPER('%{$sResquestValue}%') OR UPPER(pp.name) LIKE UPPER('%{$sResquestValue}%') )
            ";

	if ( !empty($nIdFirm) ) {
		$sQuery .= "	AND FIND_IN_SET({$nIdFirm}, pp.regions) ";
	}

	$sQuery .= "
                ORDER BY
                    pp.name ASC
                LIMIT ".NUM_ROWS;

	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['cipher'].';'.$v['min_salary'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['cipher'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPerson() {
	global $sResquestValue, $db_personnel, $db_name_sod, $db_name_personnel, $_GET;

	$nIDFirm 	= 0;
	$nIDOffice 	= 0;

	$sQuery = "
			SELECT 
				distinct(p.code), 
				CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, 
				p.id
			FROM {$db_name_personnel}.personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
					OR UPPER(p.fname) LIKE UPPER('%{$sResquestValue}%') 
					OR UPPER(p.lname) LIKE UPPER('%{$sResquestValue}%') 
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$nIDOffice = is_numeric($_GET['nIDOffice']) ? $_GET['nIDOffice'] : 0;
		$sQuery .= " AND p.id_office = '{$nIDOffice}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$nIDFirm = is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;
		$sQuery .= " AND o.id_firm = '{$nIDFirm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['id'] ); // $v['code']
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPersonContract() {
	global $sResquestValue, $db_personnel, $db_name_sod, $db_name_personnel, $_GET;

	$nIDFirm 	= 0;
	$nIDOffice 	= 0;

	$sQuery = "
			SELECT 
				distinct(p.code), 
				CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name,
				o.name AS off_name,
				f.name as f_name, 
				p.id
			FROM {$db_name_personnel}.personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(CONCAT_WS(' ', p.fname, p.mname, p.lname)) LIKE UPPER('%{$sResquestValue}%')
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$nIDOffice = is_numeric($_GET['nIDOffice']) ? $_GET['nIDOffice'] : 0;
		$sQuery .= " AND p.id_office = '{$nIDOffice}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$nIDFirm = is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;
		$sQuery .= " AND o.id_firm = '{$nIDFirm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'],$v['off_name'].' '.$v['f_name'] ); // $v['code']
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPersonSecurity() {
	global $sResquestValue, $db_personnel, $db_name_sod, $db_name_personnel, $_GET;

	$nIDFirm 	= 0;
	$nIDOffice 	= 0;

	foreach($_GET as $sKey=>$Val) {
		$aTmp = explode('_',$sKey);

		if(isset($aTmp[0]) && $aTmp[0] == 'sPerson')
		{
			$nObjectRow = (int)$aTmp[1]; // обект към който е привързан suggest-a
			$nPersonRow = (int)$aTmp[2]; //slujitel kam obekt
		}
	}

	$sQuery = "
			SELECT
				distinct(p.code),
				CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name,
				o.name AS off_name,
				f.name as f_name,
				p.id
			FROM {$db_name_personnel}.personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active'
				AND (
					UPPER(CONCAT_WS(' ', p.fname, p.mname, p.lname)) LIKE UPPER('%{$sResquestValue}%')
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$nIDOffice = is_numeric($_GET['nIDOffice']) ? $_GET['nIDOffice'] : 0;
		$sQuery .= " AND p.id_office = '{$nIDOffice}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$nIDFirm = is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;
		$sQuery .= " AND o.id_firm = '{$nIDFirm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d@%d@%d'", $v['id'] , $nObjectRow , $nPersonRow );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'],$v['off_name'].' '.$v['f_name'] ); // $v['code']
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPersonID() {
	global $sResquestValue, $db_personnel, $db_name_sod, $db_name_personnel, $_GET;

	$nIDFirm 	= 0;
	$nIDOffice 	= 0;

	$sQuery = "
			SELECT 
				distinct(p.code), 
				CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name,
				o.name AS off_name,
				f.name as f_name, 
				p.id
			FROM {$db_name_personnel}.personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(CONCAT_WS(' ', p.fname, p.mname, p.lname)) LIKE UPPER('%{$sResquestValue}%')
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$nIDOffice = is_numeric($_GET['nIDOffice']) ? $_GET['nIDOffice'] : 0;
		$sQuery .= " AND p.id_office = '{$nIDOffice}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$nIDFirm = is_numeric($_GET['nIDFirm']) ? $_GET['nIDFirm'] : 0;
		$sQuery .= " AND o.id_firm = '{$nIDFirm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'],$v['off_name'].' '.$v['f_name'] ); // $v['code']
		$aValues[]		=	sprintf( "'%s [%d]'", $v['name'], $v['id']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestUser() {
	global $sResquestValue, $db_personnel, $db_name_sod, $db_name_personnel, $db_name_system, $_GET;

	$sQuery = "
			SELECT 
				distinct(p.code), CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, p.id
			FROM
				{$db_name_system}.access_account a
			LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = a.id_person AND p.to_arc = 0)
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			WHERE 1
				AND a.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
					OR UPPER(p.fname) LIKE UPPER('%{$sResquestValue}%') 
					OR UPPER(p.lname) LIKE UPPER('%{$sResquestValue}%') 
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$firm = (int) $_GET['nIDFirm'];
		$sQuery .= " AND o.id_firm = '{$firm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPersonByCode() {
	global $sResquestValue, $db_personnel, $db_name_sod, $_GET;
	//debug($_GET);
//		if( empty( $_GET['nIDFirm']) ) {
//			SuggestEmpty ();
//			return 0;
//		}


	$sQuery = "
			SELECT 
				distinct(p.code), CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, p.id
			FROM
				personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
				)
		";

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$firm = (int) $_GET['nIDFirm'];
		$sQuery .= " AND o.id_firm = '{$firm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['code'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestPersonByName() {
	global $sResquestValue, $db_personnel, $db_name_sod, $_GET;
	//debug($_GET);
//		if( empty( $_GET['nIDFirm']) ) {
//			SuggestEmpty ();
//			return 0;
//		}


	$sQuery = "
			SELECT 
				distinct(p.code), CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, p.id
			FROM
				personnel p
			LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
			WHERE 1
				AND p.to_arc = 0
				AND p.status = 'active' 
				AND ( 
					UPPER(CONCAT_WS(' ', p.fname, p.mname, p.lname)) LIKE UPPER('%{$sResquestValue}%')
				)
		";
//					UPPER(p.fname) LIKE UPPER('%{$sResquestValue}%') 
//					OR UPPER(p.lname) LIKE UPPER('%{$sResquestValue}%')

	if( isset($_GET['id_region']) && !empty($_GET['id_region']) ) {
		$reg = (int) $_GET['id_region'];
		$sQuery .= " AND p.id_office = '{$reg}' ";
	}

	if( isset($_GET['nIDFirm']) && !empty($_GET['nIDFirm']) ) {
		$firm = (int) $_GET['nIDFirm'];
		$sQuery .= " AND o.id_firm = '{$firm}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	//debug($sQuery);
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['code'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['name'], $v['code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function suggestObjectPerson()
{
	global $db_personnel, $db_name_sod, $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	$sResquestValue = addslashes( $sResquestValue );

	$sQuery = "
			SELECT 
				p.id,
				p.code,
				CONCAT_WS(' ', p.fname, p.mname, p.lname ) as name,
				CONCAT(                                           
					CONCAT_WS(' ', '[', p.code, '] ', p.fname, p.mname, p.lname ),   
			        IF(                                           
						ob.id,                                    
						CONCAT_WS(' ', ' (', ob.num, ob.name, ')'),
						''                                        
						)                                         
					) AS fullName                      
			FROM personnel p                                      
			LEFT JOIN {$db_name_sod}.offices of ON p.id_office = of.id       
			LEFT JOIN {$db_name_sod}.objects ob ON p.id_region_object = ob.id
			WHERE p.to_arc = 0 
				AND p.status = 'active'
				AND 
				( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
					OR 
					CONCAT_WS(' ', UPPER(p.fname), UPPER(p.mname), UPPER(p.lname )) LIKE UPPER('{$sResquestValue}%') 
				)
			ORDER BY name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes )
	{

		//print "alert('{$sQuery}')";
		die();
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) )
	{
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['code'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['fullName']);
		$aValues[]		=	sprintf( "'%s'", "");
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function suggestFreeObjectPerson()
{
	global $db_personnel, $db_name_sod, $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	$sResquestValue = addslashes( $sResquestValue );

	$sQuery = "
			SELECT 
				p.id,
				p.code,
				CONCAT_WS(' ', p.fname, p.mname, p.lname ) as name,
				CONCAT(                                           
					CONCAT_WS(' ', '[', p.code, '] ', p.fname, p.mname, p.lname ),   
			        IF(                                           
						ob.id,                                    
						CONCAT_WS(' ', ' (', ob.num, ob.name, ')'),
						''                                        
						)                                         
					) AS fullName                      
			FROM personnel p                                      
			LEFT JOIN {$db_name_sod}.offices of ON p.id_office = of.id       
			LEFT JOIN {$db_name_sod}.objects ob ON p.id_region_object = ob.id
			WHERE p.to_arc = 0 
				AND p.status = 'active'
				AND p.id_region_object = 0
				AND 
				( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
					OR 
					CONCAT_WS(' ', UPPER(p.fname), UPPER(p.mname), UPPER(p.lname )) LIKE UPPER('{$sResquestValue}%') 
				)
			ORDER BY name ASC
			LIMIT ".NUM_ROWS;

	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes )
	{

		//print "alert('{$sQuery}')";
		die();
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) )
	{
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['code'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['fullName']);
		$aValues[]		=	sprintf( "'%s'", "");
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}


function pppSourceName()
{
	global $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	if( empty( $_GET['sSendType'] ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = array();

	switch( $_GET['sSendType'] )
	{
		case 'object':
			$oObjects = new DBObjects();
			$aData = $oObjects->getObjectsNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'person':
			$oPersonnel = new DBPersonnel();
			$aData = $oPersonnel->getPersonNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'storagehouse':
			$oStoragehouses = new DBStoragehouses();
			$aData = $oStoragehouses->getStoragehouseNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'client':
			$oClients = new DBClients();
			$aData = $oClients->getClientNameByName( $sResquestValue, NUM_ROWS );
			break;
	}


	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep( $aData );

	foreach( $aData as $k => $v )
	{
		$aKeys[] = sprintf( "'%d'", $v['id'] );

		if( $_GET['sSendType'] == 'object' )
			$aHtmlValues[] = sprintf( "'%s [%d] (%s)'", $v['name'], $v['num'], $v['status'] );
		elseif( $_GET['sSendType'] == 'storagehouse' )
			$aHtmlValues[] = sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		else $aHtmlValues[] = sprintf( "'%s'", $v['name'] );

		if( $_GET['sSendType'] == 'object' )
			$aValues[] = sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		else $aValues[] = sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по обект
function SuggestObject() {
	global $sResquestValue, $db_sod;

	if( empty($_GET['bla']) ) {
		SuggestEmpty ();
		return 0;
	}

	if ( $_GET['bla'] == 'object' ) {
		$sQuery = "
				SELECT 
					distinct(num), name, id
				FROM
					objects
				WHERE
					num = '".$sResquestValue."'	
				ORDER BY
					name ASC
				LIMIT ".NUM_ROWS;
	} else {
		$sQuery = "
				SELECT 
					distinct(name), id AS num, id
				FROM
					patrul_parking
				WHERE
					name LIKE UPPER('%{$sResquestValue}%')
				ORDER BY
					name ASC
				LIMIT ".NUM_ROWS;
	}

	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		if ( $_GET['bla'] == 'object' ) {
			$aValues[]		=	sprintf( "'%s'", $v['num']);
		} else $aValues[]	=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestTechPerson() {
	global $db_personnel, $db_name_sod, $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	$sResquestValue = addslashes( $sResquestValue );

	$sQuery = "
			SELECT 
				p.id,
				p.code,
				CONCAT_WS(' ', p.fname, p.mname, p.lname ) as name,
				CONCAT_WS(' ', '[', p.code, '] ', p.fname, p.mname, p.lname ) AS fullName                   
			FROM personnel p                                      
			LEFT JOIN positions ps ON p.id_position = ps.id       
			WHERE p.to_arc = 0 
				AND ps.function = 'technic'
				AND 
				( 
					UPPER(p.code) LIKE UPPER('%{$sResquestValue}%') 
					OR 
					CONCAT_WS(' ', UPPER(p.fname), UPPER(p.mname), UPPER(p.lname )) LIKE UPPER('{$sResquestValue}%') 
				)
			ORDER BY name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_personnel->Execute( $sQuery );

	if ( !$oRes )
	{

		//print "alert('{$sQuery}')";
		die();
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) )
	{
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['name'] ); //sprintf( "'%s'", $v['code'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['fullName']);
		$aValues[]		=	sprintf( "'%s'", "ssss");
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function objContractSecurity() {
	global $sResquestValue, $db_sod;

	$oObjectServices = new DBObjectServices();

	$nRowObject = 0;

	foreach($_GET as $sKey=>$Val) {
		$aTmp = explode('_',$sKey);

		if(isset($aTmp[0]) && $aTmp[0] == 'sObject')
		{
			$nRowObject = (int)$aTmp[1];
		}
	}


	if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	{
		$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sCondition = " AND o.id_office IN ({$sAccessable}) \n";
	}
	else $sCondition = "";

	$sQuery = "
        SELECT
            distinct( o.num ),
            o.name,
            o.id,
            off.name AS off_name,
            s.name AS status
        FROM objects o
            LEFT JOIN statuses s ON s.id = o.id_status
            LEFT JOIN offices off ON o.id_office = off.id
        WHERE
            (o.num = '".$sResquestValue."' OR o.name like '%".$sResquestValue."%')
            {$sCondition}
    ";

	if ( isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$office = (int) $_GET['nIDOffice'];
		$sQuery .= " AND o.id_office = '{$office}' ";
	}

	if ( isset($_GET['sObjects']) && !empty($_GET['sObjects']) ) {
		$objects 	= $_GET['sObjects'];
		$sQuery 	.= " AND o.id IN ({$objects}) ";
	}

	$sQuery .= "
        ORDER BY
            name ASC
        LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$nTax = $oObjectServices->getSumPriceByObject($v['id']);
		$aKeys[]		=	sprintf( "'%d@%d@%f'", $v['id'] , $nRowObject , $nTax );
		$aHtmlValues[]	=	sprintf( "'%s [%d] %s  - %s'", $v['off_name'] , $v['num'] , $v['name'] , $v['status'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по обект
function SuggestObj() {
	global $sResquestValue, $db_sod;

//		if( empty($_GET['nObject']) ) {
//			SuggestEmpty ();
//			return 0;
//		}
	//ако не е подаден параметър nShowInactiveObj ги показва по подразбиране !
	$nShowInActive = isset($_GET['nShowInactiveObj'])? (int)$_GET['nShowInactiveObj'] : 1;

	if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	{
		$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sCondition = " AND o.id_office IN ({$sAccessable}) \n";
	}
	else $sCondition = "";

	$sQuery = "
			SELECT
				distinct( o.num ),
				o.name,
				o.id,
                off.name AS off_name,
                s.name AS status
            FROM objects o
                LEFT JOIN statuses s ON s.id = o.id_status
                LEFT JOIN offices off ON o.id_office = off.id
			WHERE
				(o.num = '".$sResquestValue."' OR o.name like '%".$sResquestValue."%')
				{$sCondition}
		";

	if ( isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$office = (int) $_GET['nIDOffice'];
		$sQuery .= " AND o.id_office = '{$office}' ";
	}

	if ( isset($_GET['sObjects']) && !empty($_GET['sObjects']) ) {
		$objects 	= $_GET['sObjects'];
		$sQuery 	.= " AND o.id IN ({$objects}) ";
	}


	if ( !$nShowInActive ) {
		$sQuery .= " AND o.id_status != 4 ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d] %s  - %s'", $v['off_name'] , $v['num'] , $v['name'] , $v['status'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestObjWithoutRegions() {
	global $sResquestValue, $db_sod;

//		if( empty($_GET['nObject']) ) {
//			SuggestEmpty ();
//			return 0;
//		}
	//ако не е подаден параметър nShowInactiveObj ги показва по подразбиране !
	$nShowInActive = isset($_GET['nShowInactiveObj'])? (int)$_GET['nShowInactiveObj'] : 1;

	/*if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	{
		$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sCondition = " AND o.id_office IN ({$sAccessable}) \n";
	}
	else $sCondition = "";*/

	$sQuery = "
			SELECT
				distinct( o.num ),
				o.name,
				o.id,
                off.name AS off_name,
                s.name AS status
            FROM objects o
                LEFT JOIN statuses s ON s.id = o.id_status
                LEFT JOIN offices off ON o.id_office = off.id
			WHERE
				(o.num = '".$sResquestValue."' OR o.name like '%".$sResquestValue."%')

		";

	if ( isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$office = (int) $_GET['nIDOffice'];
		$sQuery .= " AND o.id_office = '{$office}' ";
	}

	if ( isset($_GET['sObjects']) && !empty($_GET['sObjects']) ) {
		$objects 	= $_GET['sObjects'];
		$sQuery 	.= " AND o.id IN ({$objects}) ";
	}


	if ( !$nShowInActive ) {
		$sQuery .= " AND o.id_status != 4 ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d] %s  - %s'", $v['off_name'] , $v['num'] , $v['name'] , $v['status'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

// Suggest по обект
function SuggestLayerObject() {
	global $sResquestValue, $db_sod;

	if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	{
		$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sCondition = " AND lo.id_office IN ({$sAccessable}) \n";
	}
	else $sCondition = "";

	$sQuery = "
			SELECT 	
				lo.id,
				lo.name				
			FROM layers_objects lo
			WHERE
				( lo.name like '%".$sResquestValue."%')
				{$sCondition}
		";

	if ( isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$office = (int) $_GET['nIDOffice'];
		$sQuery .= " AND lo.id_office = '{$office}' ";
	}

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}


// Suggest по обект от базата на PowerLink
function SuggestPLObj() {
	global $sResquestValue, $db_telepol;

//		if( empty($_GET['nObject']) ) {
//			SuggestEmpty ();
//			return 0;
//		}

	$sResquestValue = iconv( 'utf-8', 'cp1251', $sResquestValue );
	$sResquestValue = mb_strtoupper( $sResquestValue );
	str_replace( 'Ч', '_', $sResquestValue );
	str_replace( 'Я', '_', $sResquestValue );

	//$value = strtoupper_utf8(  );



	$sQuery = "
			SELECT 
				distinct(num), 
				name, 
				id_obj as id
			FROM
				objects
			WHERE
				(IF ( '".$sResquestValue."' > 0, num = '".$sResquestValue."', num = -1) OR BINARY UPPER(name) like '%".$sResquestValue."%')
				AND id_status != 4
		";

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_telepol->Execute( $sQuery );

	if (!$oRes) {
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();
	//echo $aData;
	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if ( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$v['name']	  = iconv('cp1251', 'utf-8', $v['name']);

		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%d'", $v['id'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}


// Suggest по обект - ревизия
function SuggestObjRev() {
	global $sResquestValue, $db_sod;

	if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	{
		$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
		$sCondition = " AND o.id_office IN ({$sAccessable}) \n";
	}
	else $sCondition = "";

	$firm           = ( isset( $_GET['nIDFirm'] ) && is_numeric( $_GET['nIDFirm'] ) && !empty( $_GET['nIDFirm']) )    ? (int) $_GET['nIDFirm']    : '';
	$filter_status  = ( isset( $_GET['bFilterInactive'] ) && is_numeric( $_GET['nIDFirm'] ) && !empty( $_GET['bFilterInactive']) )  ? (int) $_GET['bFilterInactive']    : '';
	$rev_status     = ( isset( $_GET['bFilterRevision'] ) )    ? (int) $_GET['bFilterRevision']    : null;
	$year           = ( isset( $_GET['dateY'] ) && is_numeric( $_GET['dateY'] ) && ! empty( $_GET['dateY'] ) ) ? (int)$_GET['dateY'] : 0;

	$sQuery = "
                SELECT
                    distinct( o.num ),
                    o.name,
                    o.id,
                    s.name AS status
                FROM objects o
            ";

	if(! empty( $firm ) ){
		$sQuery .= "
                    LEFT JOIN offices off ON off.id = o.id_office
                    ";
	}

	$sQuery .= "
                LEFT JOIN statuses s ON s.id = o.id_status
                LEFT JOIN
                    (
                        SELECT id_object,
                            month
                        FROM objects_revision
                        WHERE to_arc = 0
                    ) ore ON ore.id_object = o.id
                WHERE
                    (o.num = '".$sResquestValue."' OR o.name like '%".$sResquestValue."%')
                    {$sCondition}
            ";

	if(! empty( $filter_status ) ) {
		$sQuery .= " AND o.id_status != 4";
	}

	if (! empty ( $firm ) ) {
		$sQuery .= " AND off.id_firm = '{$firm}' ";
	}

	if ( isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ) {
		$office = (int) $_GET['nIDOffice'];
		$sQuery .= " AND o.id_office = '{$office}' ";
	}

	if( ($rev_status) !== null) {
		if($rev_status == 1) {
			$sQuery .= " AND ore.id_object IS NULL";
		}
		elseif( $rev_status == 0) {
			$sQuery .= " AND ore.id_object IS NOT NULL";
		}
	}

	if(! empty($year)) {
		$yearFrom = (int)$year*100+01;
		$yearTo = (int)$year*100+12;

		$sQuery .= " AND ore.month BETWEEN {$yearFrom} AND {$yearTo}";
	}

	$sQuery .= "
                ORDER BY
                    name ASC
                LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
//            $aHtmlValues[]	=	sprintf( "'%s [%d] - %s'", $v['name'], $v['num'], $v['status'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}


function SuggestObjByName() {
	global $sResquestValue, $db_sod, $_GET;

	$sIDOffices = !empty($_GET['sIDOffices']) ? $_GET['sIDOffices'] : '';

	$nIDFirm 	= !empty( $_GET['nIDFirm'] ) 	? $_GET['nIDFirm']		: ( ( isset( $_GET['nFirm'] ) 	&& !empty( $_GET['nFirm'] ) ) 	? $_GET['nFirm'] 	: '' );
	$nIDOffice 	= !empty( $_GET['nIDOffice'] ) 	? $_GET['nIDOffice']	: ( ( isset( $_GET['nOffice'] ) && !empty( $_GET['nOffice'] ) ) ? $_GET['nOffice'] 	: '' );

	$sQuery = "
			SELECT 
				distinct(ob.num), ob.name, ob.id
			FROM
				objects ob
			LEFT JOIN offices off ON off.id = ob.id_office
			WHERE 1
				AND ( ob.name like '%".$sResquestValue."%' OR ob.num like '%".$sResquestValue."%' )
				AND id_status != 4
		";

	if ( empty($sIDOffices) ) {
		$sIDOffices = implode(",",$_SESSION['userdata']['access_right_regions']);
	}

	$sQuery .= " AND ob.id_office IN ({$sIDOffices})\n";

	if(!empty($nIDFirm)) {
		$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
	}

	if(!empty($nIDOffice)) {
		$sQuery .= " AND ob.id_office = {$nIDOffice}\n";
	}

	$sQuery .= "
			ORDER BY
				ob.name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['num'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestObjByNum() {
	global $sResquestValue, $db_sod, $_GET;

	$sIDOffices = !empty($_GET['sIDOffices']) ? $_GET['sIDOffices'] : '';

	$nIDFirm 	= !empty( $_GET['nIDFirm'] ) 	? $_GET['nIDFirm'] : 	( !empty( $_GET['id_firm'] ) 	? $_GET['id_firm'] 	: '' );
	$nIDOffice 	= !empty( $_GET['nIDOffice'] ) 	? $_GET['nIDOffice'] : 	( !empty( $_GET['id_reg'] ) 	? $_GET['id_reg'] 	: '' );

	$sQuery = "
			SELECT 
				distinct(ob.num), ob.name, ob.id
			FROM
				objects ob
			LEFT JOIN offices off ON off.id = ob.id_office
			WHERE
				ob.num like ".$sResquestValue."
				AND id_status != 4
		";

	if ( empty($sIDOffices) ) {
		$sIDOffices = implode(",",$_SESSION['userdata']['access_right_regions']);
	}

	$sQuery .= " AND ob.id_office IN ({$sIDOffices})\n";

	if(!empty($nIDFirm)) {
		$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
	}

	if(!empty($nIDOffice)) {
		$sQuery .= " AND ob.id_office = {$nIDOffice}\n";
	}

	$sQuery .= "
			ORDER BY
				ob.name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();


	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['num'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestObjByNumWithStatus()
{
	global $sResquestValue, $db_sod, $_GET;

	$sIDOffices = !empty($_GET['sIDOffices']) ? $_GET['sIDOffices'] : '';

	$nIDFirm 	= !empty( $_GET['nIDFirm'] ) 	? $_GET['nIDFirm'] : 	( !empty( $_GET['id_firm'] ) 	? $_GET['id_firm'] 	: '' );
	$nIDOffice 	= !empty( $_GET['nIDOffice'] ) 	? $_GET['nIDOffice'] : 	( !empty( $_GET['id_reg'] ) 	? $_GET['id_reg'] 	: '' );

	$sQuery = "
			SELECT 
				distinct( ob.num ), ob.name, ob.id, sta.name AS status
			FROM
				objects ob
			LEFT JOIN offices off ON off.id = ob.id_office
			LEFT JOIN
				statuses sta ON sta.id = ob.id_status
			WHERE
				ob.num like " . $sResquestValue . "
		";

	if( empty( $sIDOffices ) )
	{
		$sIDOffices = implode( ",", $_SESSION['userdata']['access_right_regions'] );
	}

	$sQuery .= " AND ob.id_office IN ( {$sIDOffices} )\n";

	if( !empty( $nIDFirm ) )
	{
		$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
	}

	if( !empty( $nIDOffice ) )
	{
		$sQuery .= " AND ob.id_office = {$nIDOffice}\n";
	}

	$sQuery .= "
			ORDER BY
				ob.name ASC
			LIMIT " . NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if( !$oRes )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( ( count( $aData ) == 1 ) && ( strtolower( $aData[0]['name'] ) == strtolower( $sResquestValue ) ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = javascriptescape_deep( $aData );

	foreach( $aData as $k => $v )
	{
		$aKeys[]		=	sprintf( "'%s'", $v['id'] . ';' . $v['num'] . ';' . $v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d] - %s'", $v['name'], $v['num'], $v['status'] );
		$aValues[]		=	sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestActObjByNum() {
	global $sResquestValue, $db_sod, $_GET;

	$sIDOffices = !empty($_GET['sIDOffices']) ? $_GET['sIDOffices'] : '';

	$nIDFirm 	= !empty( $_GET['nIDFirm'] ) 	? $_GET['nIDFirm']		: ( ( isset( $_GET['nFirm'] ) 	&& !empty( $_GET['nFirm'] ) ) 	? $_GET['nFirm'] 	: '' );
	$nIDOffice 	= !empty( $_GET['nIDOffice'] ) 	? $_GET['nIDOffice']	: ( ( isset( $_GET['nOffice'] ) && !empty( $_GET['nOffice'] ) ) ? $_GET['nOffice'] 	: '' );
	$nIDArea	= !empty( $_GET['nIDArea'] ) 	? $_GET['nIDArea']		: ( ( isset( $_GET['nAreas'] ) && !empty( $_GET['nAreas'] ) ) ? $_GET['nAreas'] 	: '' );


	$sQuery = "
			SELECT 
				distinct(ob.num), ob.name, ob.id
			FROM
				objects ob
			LEFT JOIN offices off ON off.id = ob.id_office
			LEFT JOIN areas_offices ao ON ao.id_offices = off.id
			WHERE 1
				AND ob.num like '".$sResquestValue."%'
				AND ob.id_status != 4
		";

	if ( empty($sIDOffices) ) {
		$sIDOffices = implode(",",$_SESSION['userdata']['access_right_regions']);
	}

	$sQuery .= " AND ob.id_office IN ({$sIDOffices})\n";

	if(!empty($nIDFirm)) {
		$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
	}

	if(!empty($nIDOffice)) {
		$sQuery .= " AND ob.id_office = {$nIDOffice}\n";
	}

	if(!empty($nIDArea)) {
		$sQuery .= " AND ao.id_areas = {$nIDArea}\n";
	}

	$sQuery .= "
			ORDER BY
				ob.num ASC
			LIMIT ".NUM_ROWS;
//		echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();


	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].';'.$v['num'].';'.$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestCity() {
	global $sResquestValue, $db_sod;

//		if( empty($_GET['nObject']) ) {
//			SuggestEmpty ();
//			return 0;
//		}


	$sQuery = "
			SELECT 
				distinct(post_code), name, id
			FROM
				cities
			WHERE
				(IF ( '".$sResquestValue."' > 0, post_code = '".$sResquestValue."', post_code = -1) OR name like '%".$sResquestValue."%')
		";

	/*if ( (int) $_GET['nIDOffice'] > 0 ) {
        $office = (int) $_GET['nIDOffice'];
        $sQuery .= " AND id_office = '{$office}' ";
    }*/

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['post_code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestCityEx() {
	global $sResquestValue, $db_sod;

	$sQuery = "
			SELECT 
				distinct(post_code), name, id
			FROM
				cities
			WHERE
				(IF ( '".$sResquestValue."' > 0, post_code = '".$sResquestValue."', post_code = -1) OR name like '%".$sResquestValue."%' OR name_en like '%".$sResquestValue."%')
		";

	/*if ( (int) $_GET['nIDOffice'] > 0 ) {
        $office = (int) $_GET['nIDOffice'];
        $sQuery .= " AND id_office = '{$office}' ";
    }*/

	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%s'", $v['id'].";".$v['name'] );
		$aHtmlValues[]	=	sprintf( "'%s [%d]'", $v['name'], $v['post_code'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestStreet() {
	global $sResquestValue, $db_sod;

//		if( empty($_GET['nObject']) ) {
//			SuggestEmpty ();
//			return 0;
//		}


	$sQuery = "
			SELECT 
				name, id
			FROM
				city_streets
			WHERE
				name like '%".$sResquestValue."%'
		";

	if ( (int) $_GET['nIDCity'] > 0 ) {
		$City = (int) $_GET['nIDCity'];
		$sQuery .= " AND id_city = '{$City}' ";
	}
	//echo $_GET['nIDCity'];
	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//echo $sQuery;
	$oRes = $db_sod->Execute( $sQuery );

	if (!$oRes)
	{
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if((count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue))){
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k => $v){
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function pppDestName()
{
	global $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	if( empty( $_GET['sReceiveType'] ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = array();

	switch( $_GET['sReceiveType'] )
	{
		case 'object':
			$oObjects = new DBObjects();
			$aData = $oObjects->getObjectsNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'person':
			$oPersonnel = new DBPersonnel();
			$aData = $oPersonnel->getPersonNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'storagehouse':
			$oStoragehouses = new DBStoragehouses();
			$aData = $oStoragehouses->getStoragehouseNameByName( $sResquestValue, NUM_ROWS );
			break;
		case 'client':
			$oClients = new DBClients();
			$aData = $oClients->getClientNameByName( $sResquestValue, NUM_ROWS );
			break;
	}


	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep( $aData );

	foreach( $aData as $k => $v )
	{
		$aKeys[] = sprintf( "'%d'", $v['id'] );

		if( $_GET['sReceiveType'] == 'object' )
			$aHtmlValues[] = sprintf( "'%s [%d] (%s)'", $v['name'], $v['num'], $v['status'] );
		elseif( $_GET['sReceiveType'] == 'storagehouse' )
			$aHtmlValues[] = sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		else $aHtmlValues[] = sprintf( "'%s'", $v['name'] );

		if( $_GET['sReceiveType'] == 'object' )
			$aValues[] = sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		else $aValues[] = sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function statesStorage()
{
	global $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	if( empty( $_GET['sStorageType'] ) )
	{
		SuggestEmpty ();
		return 0;
	}

	$nIDOffice = empty( $_GET['nIDOffice'] ) ? 0 : $_GET['nIDOffice'];
	$nIDFirm = empty( $_GET['nIDFirm'] ) ? 0 : $_GET['nIDFirm'];

	$aData = array();

	switch( $_GET['sStorageType'] )
	{
		case 'object':
			$oObjects = new DBObjects();
			$aData = $oObjects->getObjectsByOffice( $nIDFirm, $nIDOffice, $sResquestValue, NUM_ROWS );
			break;
		case 'person':
			$oPersonnel = new DBPersonnel();
			$aData = $oPersonnel->getPersonnelByOffice( $nIDFirm, $nIDOffice, $sResquestValue, NUM_ROWS );
			break;
		case 'storagehouse':
			$oStoragehouses = new DBStoragehouses();
			$aData = $oStoragehouses->getStoragehousesByOffice( $nIDFirm, $nIDOffice, $sResquestValue, NUM_ROWS );
			break;
	}


	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep($aData);

	foreach( $aData as $k => $v )
	{
		$aKeys[] = sprintf( "'%d'", $v['id'] );
		if( $_GET['sStorageType'] == 'object' || $_GET['sStorageType'] == 'storagehouse' )
			$aHtmlValues[] = sprintf( "'%s [%d]'", $v['name'], $v['num'] );
		else $aHtmlValues[] = sprintf( "'%s'", $v['name'] );
		$aValues[] 		= 	sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function objectTypes()
{
	global $sResquestValue;

	if( empty( $sResquestValue ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = array();

	$oObjectTypes = new DBObjectTypes();
	$aData = $oObjectTypes->getObjectTypes2( $sResquestValue, NUM_ROWS );


	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep($aData);

	foreach( $aData as $k => $v )
	{
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[] 	= 	sprintf( "'%s'", $v['name'] );
		$aValues[] 		= 	sprintf( "'%s'", $v['name'] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function SuggestTechOperations () {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
					SELECT 
						id,
						name
					FROM
						tech_operations 
					WHERE
						UPPER(name) LIKE UPPER('%{$sResquestValue}%')
						AND to_arc = 0
					ORDER BY
						name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id']);
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function suggestClient() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
			SELECT 
				id,
				name,
				invoice_address,
				invoice_ein,
				invoice_ein_dds,
				invoice_mol,
				phone
			FROM
				clients 
			WHERE
		";
//		var_dump($_GET);
	if(isset($_GET['client_name'])) {
		$sQuery .= "
				UPPER(name) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['client_address'])) {
		$sQuery .= "
				UPPER(invoice_address) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['client_ein'])) {
		$sQuery .= "
				UPPER(invoice_ein) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['client_ein_dds'])) {
		$sQuery .= "
				UPPER(invoice_ein_dds) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['client_mol'])) {
		$sQuery .= "
				UPPER(invoice_mol) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif (isset($_GET['client_phone'])) {
		$sQuery .= "
				UPPER(phone) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif (isset($_GET['client_eik'])) {
		$sQuery .= "
				UPPER(id) LIKE UPPER('%{$sResquestValue}%')
			";
	}

	$sQuery .= "
					ORDER BY
						name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){


		$sKey = implode(";;",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		if(isset($_GET['client_name'])) {
			$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		} elseif( isset($_GET['client_address'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_address'],$v['name']);
		} elseif( isset($_GET['client_ein'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_ein'],$v['name']);
		} elseif( isset($_GET['client_ein_dds'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_ein_dds'],$v['name']);
		} elseif( isset($_GET['client_mol'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_mol'],$v['name']);
		} elseif( isset($_GET['client_phone'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['phone'],$v['name']);
		} elseif( isset($_GET['client_eik'])) {
			$aHtmlValues[]	=	sprintf( "'%d [%s]'", $v['id'],$v['name']);
		}

		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function suggestClientByNameOrEin() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
                SELECT
                    id,
                    name,
                    invoice_address,
                    invoice_ein,
                    invoice_ein_dds,
                    invoice_mol,
                    phone,
                    email
                FROM
                    clients
                WHERE
            ";

	$sQuery .= "
                   name LIKE '%{$sResquestValue}%'
                ";

	$sQuery .= "
                   OR invoice_ein LIKE '%{$sResquestValue}%'
                ";

	$sQuery .= "
                        ORDER BY
                            name ASC
                        LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){

		$sKey = implode("{@}",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );

		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function SuggestClientContractSecurity() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
                SELECT
                    id,
                    invoice_ein,
                    name,
                    address,
                    invoice_mol,
                    invoice_address,
                    invoice_email,
                    phone
                FROM
                    clients
                WHERE UPPER(name) LIKE UPPER('%{$sResquestValue}%')
            ";

	if( isset($_GET['is_company']) ) {
		$sQuery.=' AND is_company = 1 ';
	}

	$sQuery .= "
                ORDER BY
                    name ASC
                LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['invoice_ein']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){

		$sKey = implode("@",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_ein'] , $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['invoice_ein']);

	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function suggestObjectComplex() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
			SELECT 
				o.id,
				o.num,
				o.name,
				o.address,
				c.invoice_mol as mol
			FROM objects o
			LEFT JOIN clients_objects co ON ( o.id = co.id_object AND co.to_arc = 0 )
			LEFT JOIN clients c ON c.id = co.id_client
			WHERE 1 
		";

	if(isset($_GET['object_num'])) {
		$sQuery .= "
				AND UPPER(o.num) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['object_name'])) {
		$sQuery .= "
				AND UPPER(o.name) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['object_address'])) {
		$sQuery .= "
				AND UPPER(o.address) LIKE UPPER('%{$sResquestValue}%')
			";
	} elseif(isset($_GET['object_mol'])) {
		$sQuery .= "
				AND UPPER(c.invoice_mol) LIKE UPPER('%{$sResquestValue}%')
			";
	}

	if ( isset($_GET['nIDStatus']) && !empty($_GET['nIDStatus']) ) {
		$status = $_GET['nIDStatus'];

		$sQuery .= " AND id_status = {$status} ";
	}

	$sQuery .= "
					ORDER BY
						name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){


		$sKey = implode(";;",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		if(isset($_GET['object_num'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['num'],$v['name'] );
		} elseif( isset($_GET['object_name'])) {
			$aHtmlValues[]	=	sprintf( "'[%s] %s'",$v['num'], $v['name']);
		} elseif( isset($_GET['object_address'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'",$v['address'],$v['name']);
		} elseif( isset($_GET['object_mol'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'",$v['mol'],$v['name']);
		}

		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );

}

function suggestClientName() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
			SELECT 
				id,
				name,
				phone
			FROM
				clients 
			WHERE UPPER(name) LIKE UPPER('%{$sResquestValue}%')
		";

	if( isset($_GET['is_company']) ) {
		$sQuery.=' AND is_company = 1 ';
	}


	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){

		//$aKeys[]		=	sprintf( "'%s;;%s'", $v['id'],$v['name'],$v[]);

		$sKey = implode(";;",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);

	}

	toJS( $aKeys, $aHtmlValues, $aValues );


}

//използва се в договорите
function suggestClientEin() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
                SELECT
                    id,
                    invoice_ein,
                    name,
                    address,
                    invoice_mol,
                    invoice_address,
                    invoice_email,
                    phone,
                    invoice_ein_dds
                FROM
                    clients
                WHERE UPPER(invoice_ein) LIKE UPPER('%{$sResquestValue}%')
            ";

	if( isset($_GET['is_company']) ) {
		$sQuery.=' AND is_company = 1 ';
	}

	$sQuery .= "
                ORDER BY
                    name ASC
                LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['invoice_ein']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){

		//$aKeys[]		=	sprintf( "'%s;;%s'", $v['id'],$v['name'],$v[]);

		$sKey = implode("@",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['invoice_ein'] , $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['invoice_ein']);

	}

	toJS( $aKeys, $aHtmlValues, $aValues );


}


function suggestClientPhone() {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
			SELECT 
				id,
				name,
				phone
			FROM
				clients 
			WHERE phone LIKE '%{$sResquestValue}%'
		";


	$sQuery .= "
			ORDER BY
				name ASC
			LIMIT ".NUM_ROWS;
	//debug($sQuery);
	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){

		//$aKeys[]		=	sprintf( "'%s;;%s'", $v['id'],$v['name'],$v[]);

		$sKey = implode(";;",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['phone']);

	}

	toJS( $aKeys, $aHtmlValues, $aValues );


}


function SuggestDeliverer () {
	global $sResquestValue, $db_sod, $_GET;

	$sQuery = "
					SELECT 
						id,
						jur_name,
						address,
						idn,
						idn_dds,
						jur_mol
					FROM
						firms 
					WHERE
		";

	if(isset($_GET['deliverer_name'])) {
		$sQuery .= " UPPER(jur_name) LIKE UPPER('%{$sResquestValue}%') ";
	} elseif (isset($_GET['deliverer_address'])) {
		$sQuery .= " UPPER(address) LIKE UPPER('%{$sResquestValue}%') ";
	} elseif (isset($_GET['deliverer_ein'])) {
		$sQuery .= " UPPER(idn) LIKE UPPER('%{$sResquestValue}%') ";
	} elseif (isset($_GET['deliverer_ein_dds'])) {
		$sQuery .= " UPPER(idn_dds) LIKE UPPER('%{$sResquestValue}%') ";
	} elseif (isset($_GET['deliverer_mol'])) {
		$sQuery .= " UPPER(jur_mol) LIKE UPPER('%{$sResquestValue}%') ";
	}


	$sQuery .= "
						AND to_arc = 0
					GROUP BY jur_name
					ORDER BY
						jur_name ASC
					LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['jur_name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		//$aKeys[]		=	sprintf( "'%s'", $v['id']);

		$sKey = implode(";;",$v);
		$aKeys[] = sprintf("'%s'",$sKey);

		if(isset($_GET['deliverer_name'])) {
			$aHtmlValues[]	=	sprintf( "'%s'", $v['jur_name'] );
			$aValues[]		=	sprintf( "'%s'", $v['jur_name']);
		} elseif (isset($_GET['deliverer_address'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['address'],$v['jur_name'] );
			$aValues[]		=	sprintf( "'%s'", $v['address']);
		} elseif (isset($_GET['deliverer_ein'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['idn'],$v['jur_name'] );
			$aValues[]		=	sprintf( "'%s'", $v['idn']);
		} elseif (isset($_GET['deliverer_ein_dds'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['idn_dds'],$v['jur_name'] );
			$aValues[]		=	sprintf( "'%s'", $v['idn_dds']);
		} elseif (isset($_GET['deliverer_mol'])) {
			$aHtmlValues[]	=	sprintf( "'%s [%s]'", $v['jur_mol'],$v['jur_name'] );
			$aValues[]		=	sprintf( "'%s'", $v['jur_mol']);
		}
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestMyTelepolClient()
{
	global $sResquestValue, $db_my_telepol;

	//    if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
	//    {
	//        $sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
	//        $sCondition = " AND o.id_office IN ({$sAccessable}) \n";
	//    }
	//    else $sCondition = "";

	$sStatus        = ( isset( $_GET['sStatus'] )       && ! empty( $_GET['sStatus']) )         ? $_GET['sStatus']      : null;
	$sSuggestName   = ( isset( $_GET['suggestName'] )   && ! empty( $_GET['suggestName']) )     ? $_GET['suggestName']  : null;
	if($sStatus == 'all') unset($sStatus);
	$field = 'username';
	switch ($sSuggestName)
	{
		case 'nClientName':
			$field = 'username';
			break;

		case 'iPhoneNum':
			$field = 'phone';
			break;
	}

	$sQuery = "
            SELECT
                id,
                {$field}
            FROM users_registrations
            WHERE 1
              AND {$field} LIKE '%{$sResquestValue}%'
            ";
	if(! empty ( $sStatus ) ) {
		$sQuery .= " AND status = '" . $sStatus ."' ";
	}

	$sQuery .= "
            ORDER BY
                {$field} ASC
            LIMIT " . NUM_ROWS;

	$oRes = $db_my_telepol->Execute( $sQuery );

	if( !$oRes )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( ( count( $aData ) == 1 ) && ( strtolower( $aData[0][$field] ) == strtolower( $sResquestValue ) ) )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = javascriptescape_deep( $aData );

	foreach( $aData as $k => $v )
	{
		$aKeys[]		=	sprintf( "'%d'", $v['id'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v[$field] );
		$aValues[]		=	sprintf( "'%s'", $v[$field] );
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

/**
 * Suggest за my_telepol меню, показва обекти за привързване към клиент на my_telepol по име на клиент, EIN, или телефон
 * @return int
 */
function SuggestMyTelepolObj(){
	global $sResquestValue, $db_sod, $db_my_telepol, $_GET;

	$nIDUser    = ( isset( $_GET['nIDUser'] ) && ! empty( $_GET['nIDUser']) ) ? $_GET['nIDUser'] : 0;
	$slave = false;

	$aIDObjects = array();      //Обекти, които вече са прикачени към потребителя

	if(!empty($nIDUser)){
		$oIdObjects = $db_my_telepol->Execute(" SELECT GROUP_CONCAT(id_object SEPARATOR ',') as id_obj FROM account2objects WHERE id_account = {$nIDUser} " );
		if(!$oIdObjects){
			$aIDObjects[0]['id_obj'] = 0;
		}
		else {
			$aIDObjects = $oIdObjects->GetArray();
		}
	}

	//Проверка, дали потребителя е slave. Ако да, трябва да се търси само в обектите на мастера
	$oUserType = $db_my_telepol->Execute("SELECT id_master, type FROM account_users WHERE id = {$nIDUser}");
	if($oUserType){
		$aUserType = $oUserType->GetArray();
		if($aUserType[0]['type'] == 'slave' && !empty($aUserType[0]['id_master'])){
			$slave = true;
			$oMasterObjects = $db_my_telepol->Execute("SELECT GROUP_CONCAT(id_object SEPARATOR ',') as id_obj FROM account2objects WHERE id_account = {$aUserType[0]['id_master']}");
			$aMasterObjects = $oMasterObjects->GetArray();
		}
	}

	$sQuery = "
            SELECT
	          o.id,
	          CONCAT('[', o.num, ']', '[', IF( c.name != ' ', c.name, 'N/A' ), '] ', o.name) as o_name,
	          c.invoice_ein as ein
            FROM
	          objects o
              LEFT JOIN clients_objects co ON co.id_object = o.id
              LEFT JOIN clients c ON	c.id = co.id_client
            WHERE 1
            AND o.id_status != 4
	        AND ( c.name LIKE '%{$sResquestValue}%'
              OR c.invoice_ein LIKE '{$sResquestValue}%'
		      OR c.phone LIKE '%{$sResquestValue}%'
		      OR o.num LIKE '{$sResquestValue}%'
		      OR o.name LIKE '%{$sResquestValue}%')
		";

	if(! empty($aIDObjects[0]['id_obj'])){
		$sQuery .= " AND o.id NOT IN({$aIDObjects[0]['id_obj']}) ";
	}
//        if( $slave && ! empty($aMasterObjects[0]['id_obj'])){
//            $sQuery .= " AND o.id IN({$aMasterObjects[0]['id_obj']}) ";
//        }

	$sQuery .= "  LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if( !$oRes )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['o_name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id']);
		$aHtmlValues[]	=	sprintf( "'%s'", $v['o_name'] );
		$aValues[]		=	sprintf( "'%s, %s'", $v['o_name'], $v['ein']);

	}
	toJS( $aKeys, $aHtmlValues, $aValues );
}

/**
 * Suggest за my_telepol меню, показва обекти за привързване към клиент на my_telepol по име на клиент, EIN, или телефон
 * @return int
 */
function SuggestMyTelepolProfiles(){
	global $sResquestValue, $db_my_telepol, $db_name_my_telepol;

	$sQuery = "
            SELECT
                au.id,
                CONCAT(au.name, ' [ ', au.username, ' ]') as name
            FROM
                {$db_name_my_telepol}.account_users au
            WHERE 1
            AND au.to_arc = 0
            AND ( au.name LIKE '%{$sResquestValue}%'
              OR au.username LIKE '%{$sResquestValue}%')
            LIMIT " . NUM_ROWS;

	$oRes = $db_my_telepol->Execute( $sQuery );
	if( !$oRes )
	{
		SuggestEmpty();
		return 0;
	}

	$aData = $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%s'", $v['id']);
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name'] );
		$aValues[]		=	sprintf( "'%s'", $v['name']);

	}
	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestObjLayer() {
	global $sResquestValue, $db_sod, $db_name_sod, $_GET;

	//от кой suggest е Задачата и дали е обект или слой
	$nSuggestNum = 0;
	$nIsObject = 0;
	foreach($_GET as $sKey=>$Val) {
		$aTmp = explode('_',$sKey);

		if(isset($aTmp[0]) && $aTmp[0] == 'nIsObj')
		{
			$nSuggestNum = $aTmp[1];
			$nIsObject = (int)$Val;
		}
	}

	if($nIsObject) {
		$sQuery = "
                SELECT
                  o.id,
                  CONCAT('[', o.num , '] ' , o.name ) as name
                FROM {$db_name_sod}.objects o
                WHERE 1
                    AND o.id_status <> 4
                    AND (
                        o.num = '".$sResquestValue."' OR o.name like '%".$sResquestValue."%'
                    )
            ";
	} else {
		$sQuery = "
                SELECT
                  lo.id,
                  lo.name
                FROM {$db_name_sod}.layers_objects lo
                WHERE 1
                    AND lo.to_arc = 0
                    AND (
                        UPPER(lo.name) LIKE UPPER('%{$sResquestValue}%')
                    )
            ";
	}

	$sQuery .= "
                ORDER BY
                    name ASC
                LIMIT ".NUM_ROWS;

	$oRes = $db_sod->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d@%d'", $v['id'] , $nSuggestNum );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name']);
		$aValues[]		=	sprintf( "'%s [%d]'", $v['name'], $v['id']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

function SuggestNomenclature() {
	global $sResquestValue, $db_storage, $db_name_storage, $_GET;


	$sQuery = "
            SELECT
            n.id,
            n.id_type,
            n.name
            FROM {$db_name_storage}.nomenclatures n
            WHERE 1
            AND n.to_arc = 0
            AND (
                UPPER(n.name) LIKE UPPER('%{$sResquestValue}%')
            )
        ";


	$sQuery .= "
                    ORDER BY
                        n.name ASC
                    LIMIT ".NUM_ROWS;

	$oRes = $db_storage->Execute( $sQuery );

	if ( !$oRes ) {
		SuggestEmpty();
		return 0;
	}

	$aData 		= $oRes->GetArray();

	$aKeys 			= array();
	$aHtmlValues 	= array();
	$aValues 		= array();

	if( (count($aData) == 1) &&  (strtolower($aData[0]['name']) == strtolower($sResquestValue)) ) {
		SuggestEmpty ();
		return 0;
	}

	$aData = javascriptescape_deep($aData);

	foreach($aData as $k=>$v){
		$aKeys[]		=	sprintf( "'%d@%d'", $v['id'] , $v['id_type'] );
		$aHtmlValues[]	=	sprintf( "'%s'", $v['name']);
		$aValues[]		=	sprintf( "'%s'", $v['name']);
	}

	toJS( $aKeys, $aHtmlValues, $aValues );
}

?>