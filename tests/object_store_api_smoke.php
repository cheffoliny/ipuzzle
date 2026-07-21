<?php

$root = dirname(__DIR__);
chdir($root);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . PATH_SEPARATOR . $root . '/include');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = array(
    'BASE_DIR' => $root,
    'userdata' => array(
        'id_person' => 1,
        'id_office' => 0,
        'row_limit' => 37,
        'access_right_levels' => array()
    )
);

require_once $root . '/config/function.autoload.php';
require_once $root . '/config/config.inc.php';
require_once $root . '/config/connect.inc.php';
require_once $root . '/include/general.inc.php';
require_once $root . '/include/validate.inc.php';
require_once $root . '/api/api_object_store_state.php';
require_once $root . '/api/api_object_store_ppp.php';

$stateObjectId = (int) $db_storage->GetOne(
    "SELECT id_storage
       FROM states
      WHERE to_arc = 0
        AND storage_type = 'object'
        AND id_storage > 0
        AND count != 0
      ORDER BY id_storage
      LIMIT 1"
);
$pppObjectId = (int) $db_storage->GetOne(
    "SELECT CASE
              WHEN source_type = 'object' THEN id_source
              ELSE id_dest
            END
       FROM ppp
      WHERE to_arc = 0
        AND ((source_type = 'object' AND id_source > 0) OR (dest_type = 'object' AND id_dest > 0))
      ORDER BY id
      LIMIT 1"
);

if ($stateObjectId <= 0 && $pppObjectId <= 0) {
    echo "OBJECT_STORE_API_SMOKE=SKIP (no local object-store data)\n";
    exit(0);
}

if ($stateObjectId <= 0) {
    $stateObjectId = $pppObjectId;
}
if ($pppObjectId <= 0) {
    $pppObjectId = $stateObjectId;
}

function captureObjectStoreResponse($api, $objectId)
{
    Params::set('nID', $objectId);
    $response = new DBResponse();
    ob_start();
    try {
        $api->result($response);
        return ob_get_clean();
    } catch (Throwable $error) {
        ob_end_clean();
        throw $error;
    }
}

try {
    $stateXml = captureObjectStoreResponse(new ApiObjectStoreState(), $stateObjectId);
    $restoredRowLimit = $_SESSION['userdata']['row_limit'];
    unset($_SESSION['userdata']['row_limit']);
    $stateXmlWithoutRowLimit = captureObjectStoreResponse(new ApiObjectStoreState(), $stateObjectId);
    $missingRowLimitRestored = !array_key_exists('row_limit', $_SESSION['userdata']);
    $_SESSION['userdata']['row_limit'] = 37;
    $pppXml = captureObjectStoreResponse(new ApiObjectStorePPP(), $pppObjectId);
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n");
    exit(1);
}

foreach (array('state' => $stateXml, 'state_without_row_limit' => $stateXmlWithoutRowLimit, 'ppp' => $pppXml) as $name => $xml) {
    if (
        strpos($xml, '<?xml') === false ||
        strpos($xml, '<result>') === false ||
        strpos($xml, '<php>') !== false ||
        preg_match('/Fatal error|Deprecated|Warning:/i', $xml)
    ) {
        fwrite(STDERR, "Unexpected {$name} object-store XML response.\n");
        exit(1);
    }
}

if ($restoredRowLimit !== 37 || !$missingRowLimitRestored) {
    fwrite(STDERR, "The object-store API did not restore the session row limit.\n");
    exit(1);
}

if (
    (substr_count($stateXml, '<r ') > 0 && strpos($stateXml, '€') === false) ||
    (substr_count($pppXml, '<r ') > 0 && strpos($pppXml, '€') === false)
) {
    fwrite(STDERR, "The object-store XML response still contains a non-euro price format.\n");
    exit(1);
}

echo "OBJECT_STORE_API_SMOKE=PASS state_object={$stateObjectId} state_rows=" . substr_count($stateXml, '<r ')
    . " ppp_object={$pppObjectId} ppp_rows=" . substr_count($pppXml, '<r ') . "\n";
