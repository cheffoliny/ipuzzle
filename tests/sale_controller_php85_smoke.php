<?php

$root = dirname(__DIR__);
chdir($root . '/api');

$_SERVER['SCRIPT_FILENAME'] = $root . '/api/api_sale_controller.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = array(
    'BASE_DIR' => $root,
    'telenet_valid_session' => true,
    'userdata' => array(
        'id_person' => 1,
        'id_office' => 66,
        'access_right_regions' => array(66),
        'access_right_levels' => array()
    )
);
$_REQUEST = array('action' => 'init');
$_GET = array('id' => 0);
$_POST = array();

ob_start();
require $root . '/api/api_sale_controller.php';
$json = ob_get_clean();
trigger_error('sale controller PHP 8 error-handler probe', E_USER_DEPRECATED);
$data = json_decode($json, true);

if (!is_array($data) || isset($data['error']) || json_last_error() !== JSON_ERROR_NONE) {
    fwrite(
        STDERR,
        'SALE_CONTROLLER_PHP85_SMOKE=FAIL ' . substr(trim($json), 0, 1000) . "\n"
    );
    exit(1);
}

foreach (array('bank_orders', 'bank_accounts', 'services', 'document_data') as $requiredKey) {
    if (!array_key_exists($requiredKey, $data)) {
        fwrite(STDERR, "SALE_CONTROLLER_PHP85_SMOKE=FAIL missing {$requiredKey}\n");
        exit(1);
    }
}

foreach ($data['bank_accounts'] as $account) {
    if (!isset($account['id']) || !is_int($account['id'])) {
        fwrite(STDERR, "SALE_CONTROLLER_PHP85_SMOKE=FAIL bank account ID was not cast to int\n");
        exit(1);
    }
}

echo 'SALE_CONTROLLER_PHP85_SMOKE=PASS bank_orders=' . count($data['bank_orders']) .
    ' bank_accounts=' . count($data['bank_accounts']) .
    ' services=' . count($data['services']) .
    ' bytes=' . strlen($json) . "\n";
