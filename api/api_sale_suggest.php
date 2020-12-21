<?php

/**
 * Created by PhpStorm.
 * User: adm
 * Date: 11.11.2019 г.
 * Time: 12:31
 */

if (!isset($_SESSION)) {
    session_start();
}

define ('NUM_ROWS', 10);

$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../' );

require_once("../config/function.autoload.php");
require_once("../include/adodb/adodb-exceptions.inc.php");
require_once("../config/connect.inc.php");
require_once("../include/general.inc.php");

class ApiSaleSuggest extends DBBase2 {
    function __construct() {
        global $db_sod;

        parent::__construct($db_sod, "clients");
    }

    public function suggestClient($value) {
        global $db_sod, $db_name_sod;

        $value = str_replace(' ','%',trim($value));

        $sQuery = sprintf("
	  			SELECT 
	  				*
	  			FROM {$db_name_sod}.clients
	  			WHERE UPPER(`name`) LIKE UPPER('%%%s%%')
	  				OR UPPER(`invoice_address`) LIKE UPPER('%%%s%%')
	  				OR `invoice_ein` LIKE '%s%%'
                    OR `phone` LIKE '%%%s%%'
                    OR `id_wf` LIKE '%s%%'
	  			LIMIT %d
	  		", $value, $value, $value, $value, $value, NUM_ROWS
        );

        return $db_sod->getArray($sQuery);
    }

    public function suggestObject($value) {
        global $db_sod, $db_name_sod;

        $value = str_replace(' ','%',trim($value));

        $sQuery = sprintf("
	  			SELECT 
	  				id,
	  				num,
	  				name,
	  				address
	  			FROM {$db_name_sod}.objects
	  			WHERE UPPER(`name`) LIKE UPPER('%%%s%%')
	  				OR `num` LIKE '%s%%'
	  			LIMIT %d
	  		", $value, $value, NUM_ROWS
        );

        return $db_sod->getArray($sQuery);
    }
}

if ( !isset($_SESSION['telenet_valid_session']) || $_SESSION['telenet_valid_session'] !== true ) {
    http_response_code(401);

    echo json_encode(["error" => "Not authorized"]);
    die();
}

$suggest = new ApiSaleSuggest();
$request = isset($_GET['action']) ? $_GET['action'] : "";
$flag = false;
$data = [];

switch ($request) {
    case "client":
        if ( isset($_GET['value']) && !empty($_GET['value']) ) {
            $value = $_GET['value'];
            $data = $suggest->suggestClient($value);
        }

        echo json_encode($data);
        $flag = true;

        break;

    case "object":
        if ( isset($_GET['value']) && !empty($_GET['value']) ) {
            $value = $_GET['value'];
            $data = $suggest->suggestObject($value);
        }

        echo json_encode($data);
        $flag = true;

        break;
}

if ( !$flag ) {
    http_response_code(400);

    echo json_encode(["error" => "Bad Request"]);
    die();
}