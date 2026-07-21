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
        'access_right_levels' => array()
    )
);

require_once $root . '/config/function.autoload.php';
require_once $root . '/config/config.inc.php';
require_once $root . '/config/connect.inc.php';
require_once $root . '/include/general.inc.php';
require_once $root . '/include/validate.inc.php';
require_once $root . '/api/api_person_schedule.php';

$objectId = (int) $db_sod->GetOne(
    "SELECT s.id_obj
       FROM object_shifts s
      WHERE s.to_arc = 0
        AND s.id_obj > 0
        AND EXISTS (
            SELECT 1
              FROM object_personnel op
             WHERE op.id_object = s.id_obj
               AND op.id_person > 0
        )
      ORDER BY s.id_obj
      LIMIT 1"
);

if ($objectId <= 0) {
    echo "PERSON_SCHEDULE_API_SMOKE=SKIP (no suitable local object)\n";
    exit(0);
}

Params::set('nIDObject', $objectId);
Params::set('sYearMonth', date('Ym'));
Params::set('api_action', 'result');

$response = new DBResponse();
$api = new ApiPersonSchedule();

ob_start();
try {
    $api->result($response);
    $xml = ob_get_clean();
} catch (Throwable $error) {
    ob_end_clean();
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n");
    exit(1);
}

if (
    strpos($xml, '<?xml') === false ||
    strpos($xml, 'id="object_shifts"') === false ||
    strpos($xml, 'id="nResultIDObject"') === false ||
    strpos($xml, '<result>') === false ||
    strpos($xml, '<php>') !== false ||
    preg_match('/Fatal error|Deprecated|Warning:/i', $xml)
) {
    fwrite(STDERR, "Unexpected person-schedule XML response for object {$objectId}.\n");
    exit(1);
}

echo "PERSON_SCHEDULE_API_SMOKE=PASS object={$objectId} rows=" . substr_count($xml, '<r ') . " bytes=" . strlen($xml) . "\n";
