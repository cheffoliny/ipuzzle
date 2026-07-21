<?php

function getRpcV1ReadonlyCases()
{
    $objectDefaults = array(
        'schemes' => 0,
        'id_firm' => 0,
        'id_reg' => 0,
        'nDefReg' => 66,
        'nOpenWin' => 0,
        'nIDFirm' => 0,
        'nIDOffice' => 0,
        'nIDReactionFirm' => 0,
        'nIDReactionOffice' => 0,
        'nIDTechFirm' => 0,
        'nIDTechOffice' => 0,
        'nIsPhysical' => 0,
        'nIsSOD' => 0,
        'nIsTech' => 0,
        'nIsServiceMode' => 0,
        'nIsWorkTime' => 0,
        'nDDS' => 0,
        'nNum' => '',
        'nNum1' => '',
        'sName' => '',
        'aStatus' => array(0),
        'nFunction' => 0,
        'nCity' => 0,
        'nType' => 0,
        'sAddress' => '',
        'sMol' => '',
        'sPhone' => '',
        'sIDN' => '',
        'sUnpaid' => '',
        'sPaidTo' => '',
        'current_page' => 1,
        'sfield' => 'num',
        'stype' => 0
    );

    $personnelDefaults = array(
        'nIDFirm' => 0,
        'nIDOffice' => 0,
        'nIDObject' => 0,
        'nPositions' => 0,
        'nMobile' => '',
        'sName' => '',
        'sStatus' => 'all',
        'tabs' => 0,
        'current_page' => 1,
        'sfield' => 'name',
        'stype' => 0
    );

    return array(
        'setup_firms_result' => array(
            'file' => 'api/api_setup_firms.php',
            'params' => array(
                'api_action' => 'result',
                'current_page' => 1,
                'sfield' => 'name',
                'stype' => 0
            )
        ),
        'setup_objects_generate' => array(
            'file' => 'api/api_setup_objects.php',
            'params' => array_replace($objectDefaults, array('api_action' => 'generate'))
        ),
        'setup_objects_result' => array(
            'file' => 'api/api_setup_objects.php',
            'params' => array_replace($objectDefaults, array('api_action' => 'result'))
        ),
        'admin_personnels_load' => array(
            'file' => 'api/api_admin_personnels.php',
            'params' => array_replace($personnelDefaults, array('api_action' => 'load'))
        ),
        'admin_personnels_result' => array(
            'file' => 'api/api_admin_personnels.php',
            'params' => array_replace($personnelDefaults, array('api_action' => 'result'))
        ),
        'person_salary_result' => array(
            'file' => 'api/api_person_salary.php',
            'params' => array(
                'api_action' => 'result',
                'id' => 1,
                'year' => (int) date('Y'),
                'month' => (int) date('m'),
                'sAct' => 1,
                'plus' => 1,
                'minus' => 1,
                'sName' => 'Salary',
                'current_page' => 1,
                'sfield' => 'code',
                'stype' => 0
            )
        ),
        'personal_card_salary_result' => array(
            'file' => 'api/api_personal_card_salary.php',
            'params' => array(
                'api_action' => 'result',
                'nID' => 1,
                'year' => (int) date('Y'),
                'month' => (int) date('m'),
                'sAct' => 1,
                'current_page' => 1,
                'sfield' => 'code',
                'stype' => 0
            )
        )
    );
}

function cleanPhpStartupWarning($output)
{
    return preg_replace('/^Failed loading .*php_xdebug-.*(?:\r?\n|$)/m', '', $output);
}

if (!isset($argv[1]) || $argv[1] !== '--worker') {
    $caseNames = array_keys(getRpcV1ReadonlyCases());
    $passed = array();

    foreach ($caseNames as $caseName) {
        $command = array(
            PHP_BINARY,
            '-d', 'short_open_tag=1',
            '-d', 'display_errors=0',
            '-d', 'log_errors=0',
            __FILE__,
            '--worker',
            $caseName
        );
        $pipes = array();
        $process = proc_open(
            $command,
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
            $pipes,
            dirname(__DIR__)
        );

        if (!is_resource($process)) {
            fwrite(STDERR, "Unable to start RPC v1 smoke worker for {$caseName}.\n");
            exit(1);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $stderr = trim(cleanPhpStartupWarning($stderr));

        if ($exitCode !== 0) {
            fwrite(STDERR, "PHP85_API_V1_READONLY_SMOKE=FAIL case={$caseName} " . trim($stderr . ' ' . $stdout) . "\n");
            exit(1);
        }

        $passed[] = trim($stdout);
    }

    echo 'PHP85_API_V1_READONLY_SMOKE=PASS ' . implode(' ', $passed) . "\n";
    exit(0);
}

$caseName = isset($argv[2]) ? $argv[2] : '';

// Constants are needed while building the case definitions.
$root = dirname(__DIR__);
chdir($root);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . PATH_SEPARATOR . $root . '/include');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET = array();
$_POST = array();
$_SESSION = array(
    'BASE_DIR' => $root,
    'userdata' => array(
        'id_person' => 1,
        'id_office' => 66,
        'row_limit' => 20,
        'access_right_all_regions' => 1,
        'access_right_regions' => array(66),
        'access_right_levels' => array()
    )
);

require_once $root . '/config/function.autoload.php';
require_once $root . '/config/config.inc.php';
require_once $root . '/config/connect.inc.php';
require_once $root . '/include/general.inc.php';
require_once $root . '/include/validate.inc.php';
require_once $root . '/db_api/include/db_include.inc.php';

$cases = getRpcV1ReadonlyCases();
if (!isset($cases[$caseName])) {
    fwrite(STDERR, "Unknown RPC v1 smoke case: {$caseName}\n");
    exit(1);
}

if ($caseName === 'person_salary_result' || $caseName === 'personal_card_salary_result') {
    $salaryRow = $db_personnel->GetRow(
        "SELECT id_person, month
           FROM salary
          WHERE to_arc = 0
            AND id_person > 0
            AND month > 0
          ORDER BY month DESC, id DESC
          LIMIT 1"
    );

    if (is_array($salaryRow) && !empty($salaryRow['id_person']) && !empty($salaryRow['month'])) {
        $salaryMonth = (int) $salaryRow['month'];
        $cases[$caseName]['params']['year'] = intdiv($salaryMonth, 100);
        $cases[$caseName]['params']['month'] = $salaryMonth % 100;

        if ($caseName === 'person_salary_result') {
            $cases[$caseName]['params']['id'] = (int) $salaryRow['id_person'];
        } else {
            $cases[$caseName]['params']['nID'] = (int) $salaryRow['id_person'];
        }
    }
}

new APILog();
APILog::$aLogs = array();

$aParams = $cases[$caseName]['params'];
$params =& Params::getAll();
$params = $aParams;
$_POST = $aParams;
$oResponse = new DBResponse();

ob_start();
try {
    require $root . '/' . $cases[$caseName]['file'];
    $xml = ob_get_clean();
} catch (Throwable $error) {
    ob_end_clean();
    fwrite(
        STDERR,
        get_class($error) . ': ' . $error->getMessage() . ' in ' .
        $error->getFile() . ':' . $error->getLine() . "\n" . $error->getTraceAsString() . "\n"
    );
    exit(1);
}

if (
    strpos($xml, '<?xml') !== 0 ||
    strpos($xml, '<response>') === false ||
    strpos($xml, '<php>') !== false ||
    preg_match('/(?:Fatal error|Parse error|Warning|Deprecated|Notice)\s*:/i', $xml)
) {
    fwrite(STDERR, "The RPC v1 case returned diagnostics or a non-XML response.\n");
    exit(1);
}

if (function_exists('simplexml_load_string')) {
    $previous = libxml_use_internal_errors(true);
    $document = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if ($document === false) {
        $message = isset($errors[0]) ? trim($errors[0]->message) : 'unknown XML parse error';
        fwrite(STDERR, "Invalid XML: {$message}\n");
        exit(1);
    }
}

echo $caseName . '(rows=' . substr_count($xml, '<r ') . ',bytes=' . strlen($xml) . ')';
