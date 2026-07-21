<?php

$root = dirname(__DIR__);
chdir($root);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . PATH_SEPARATOR . $root . '/include');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET = array();
$_POST = array();
$_SESSION = array(
    'BASE_DIR' => $root,
    'sales_rows' => array(),
    'buy_rows' => array(),
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
require_once $root . '/include/adodb/adodb-exceptions.inc.php';

new APILog();

$clientId = (int) $db_sod->GetOne(
    "SELECT id_client
       FROM clients_objects
      WHERE to_arc = 0
        AND id_client > 0
      ORDER BY id_client
      LIMIT 1"
);
$assetId = (int) $db_storage->GetOne(
    "SELECT id
       FROM assets
      WHERE to_arc = 0
      ORDER BY id
      LIMIT 1"
);
$salaryRow = $db_personnel->GetRow(
    "SELECT id_person, month
       FROM salary
      WHERE to_arc = 0
        AND id_person > 0
        AND month > 0
      ORDER BY month DESC, id DESC
      LIMIT 1"
);
$salaryPeriod = is_array($salaryRow) && !empty($salaryRow['month'])
    ? (int) $salaryRow['month']
    : ((int) date('Y') * 100 + (int) date('m'));
$salaryYear = intdiv($salaryPeriod, 100);
$salaryMonth = $salaryPeriod % 100;
$firmIds = $db_sod->GetCol(
    "SELECT id
       FROM firms
      WHERE to_arc = 0
      ORDER BY id
      LIMIT 2"
);
$salaryFirmFrom = isset($firmIds[0]) ? (int) $firmIds[0] : 0;
$salaryFirmTo = isset($firmIds[1]) ? (int) $firmIds[1] : $salaryFirmFrom;
$salesRowTables = SQL_get_tables($db_finance, 'sales_docs_rows_', '______', 'DESC');
$orderTables = SQL_get_tables($db_finance, 'orders_', '______', 'DESC');

function extractFinanceMonths($tables, $prefix)
{
    $months = array();
    foreach ($tables as $table) {
        if (preg_match('/' . preg_quote($prefix, '/') . '(\d{4})(\d{2})$/', $table, $matches)) {
            $months[] = $matches[1] . '-' . $matches[2];
        }
    }

    rsort($months, SORT_STRING);
    return array_values(array_unique($months));
}

$salesMonths = extractFinanceMonths($salesRowTables, 'sales_docs_rows_');
$orderMonths = extractFinanceMonths($orderTables, 'orders_');
$commonFinanceMonths = array_values(array_intersect($salesMonths, $orderMonths));
rsort($commonFinanceMonths, SORT_STRING);
$latestSalesMonth = isset($salesMonths[0]) ? $salesMonths[0] : date('Y-m');
$latestOrderMonth = isset($orderMonths[0]) ? $orderMonths[0] : date('Y-m');
$latestBudgetMonth = isset($commonFinanceMonths[0]) ? $commonFinanceMonths[0] : $latestOrderMonth;
$previousSalesMonth = isset($salesMonths[1]) ? $salesMonths[1] : $latestSalesMonth;
$previousOrderMonth = isset($orderMonths[1]) ? $orderMonths[1] : $latestOrderMonth;
$previousBudgetMonth = isset($commonFinanceMonths[1]) ? $commonFinanceMonths[1] : $latestBudgetMonth;

$salesParams = array(
    'subm' => 'no',
    'nIDClient' => 0,
    'nIDFirm' => '',
    'sFromDate' => date('01.m.Y'),
    'sToDate' => date('d.m.Y'),
    'sClientName' => '',
    'nNum' => '',
    'schemes' => '',
    'current_page' => 1,
    'api_action' => 'result'
);

$buyParams = array(
    'subm' => 'no',
    'nIDClient' => 0,
    'nIDFirm' => '',
    'sFromDate' => date('01.m.Y'),
    'sToDate' => date('d.m.Y'),
    'sClientName' => '',
    'nNum' => '',
    'schemes' => '',
    'current_page' => 1,
    'api_action' => 'result'
);

$cases = array(
    'setup_clients_load' => array(
        'file' => 'api/api_setup_clients.php',
        'class' => 'ApiSetupClients',
        'method' => 'load',
        'params' => array('schemes' => '', 'api_action' => 'load')
    ),
    'sales_docs_load' => array(
        'file' => 'api/api_sales_docs.php',
        'class' => 'ApiSalesDocs',
        'method' => 'load',
        'params' => array_replace($salesParams, array('api_action' => 'load'))
    ),
    'sales_docs_result' => array(
        'file' => 'api/api_sales_docs.php',
        'class' => 'ApiSalesDocs',
        'method' => 'result',
        'params' => $salesParams
    ),
    'tech_planning_requests_load' => array(
        'file' => 'api/api_tech_planning_requests.php',
        'class' => 'ApiTechPlanningRequests',
        'method' => 'load',
        'params' => array(
            'id_request' => 0,
            'id_office' => 66,
            'id_contract' => 0,
            'nIDFirm' => '',
            'nIDOffice' => '',
            'nIDTechTiming' => 0,
            'sObjectName' => '',
            'api_action' => 'load'
        )
    ),
    'buy_docs_load' => array(
        'file' => 'api/api_buy_docs.php',
        'class' => 'ApiBuyDocs',
        'method' => 'load',
        'params' => array_replace($buyParams, array('api_action' => 'load'))
    ),
    'buy_docs_result' => array(
        'file' => 'api/api_buy_docs.php',
        'class' => 'ApiBuyDocs',
        'method' => 'result',
        'params' => $buyParams
    ),
    'working_cards_result' => array(
        'file' => 'api/api_working_cards.php',
        'class' => 'ApiWorkingCards',
        'method' => 'result',
        'params' => array(
            'nNum' => 0,
            'sStatus' => '',
            'sFrom' => date('d.m.Y', strtotime('-7 days')),
            'sTo' => date('d.m.Y'),
            'sAct' => 'search',
            'nIDDispatcher' => 0,
            'current_page' => 1,
            'api_action' => 'result'
        )
    ),
    'incomings_init' => array(
        'file' => 'api/api_incomings.php',
        'class' => 'ApiIncomings',
        'method' => 'init',
        'params' => array('api_action' => 'init')
    ),
    'assets_settings_result' => array(
        'file' => 'api/api_assets_settings.php',
        'class' => 'ApiAssetsSettings',
        'method' => 'result',
        'params' => array('api_action' => 'result')
    ),
    'assets_nomenclatures_result' => array(
        'file' => 'api/api_assets_nomenclatures.php',
        'class' => 'ApiAssetsNomenclatures',
        'method' => 'result',
        'params' => array('api_action' => 'result')
    ),
    'asset_groups_result' => array(
        'file' => 'api/api_asset_groups.php',
        'class' => 'ApiAssetGroups',
        'method' => 'result',
        'params' => array('api_action' => 'result')
    ),
    'attributes_result' => array(
        'file' => 'api/api_attributes.php',
        'class' => 'ApiAttributes',
        'method' => 'result',
        'params' => array('api_action' => 'result')
    ),
    'assets_storagehouses_result' => array(
        'file' => 'api/api_assets_storagehouses.php',
        'class' => 'ApiAssetsStorageHouses',
        'method' => 'result',
        'params' => array('api_action' => 'result')
    ),
    'assets_totals_load' => array(
        'file' => 'api/api_assets_totals.php',
        'class' => 'ApiAssetsTotals',
        'method' => 'load',
        'params' => array('nIDFirm' => 0, 'api_action' => 'load')
    ),
    'assets_totals_result' => array(
        'file' => 'api/api_assets_totals.php',
        'class' => 'ApiAssetsTotals',
        'method' => 'result',
        'params' => array('nIDFirm' => 0, 'api_action' => 'result')
    ),
    'admin_salary_total_load' => array(
        'file' => 'api/api_admin_salary_total.php',
        'class' => 'ApiAdminSalaryTotal',
        'method' => 'load',
        'params' => array('api_action' => 'load')
    ),
    'admin_salary_total_result' => array(
        'file' => 'api/api_admin_salary_total.php',
        'class' => 'ApiAdminSalaryTotal',
        'method' => 'result',
        'params' => array(
            'sAct' => 1,
            'account_firms' => array(),
            'account_regions' => array(),
            'account_objects' => array(),
            'nIDObject' => 0,
            'type' => 1,
            'nRadio' => 1,
            'year' => $salaryYear,
            'month' => $salaryMonth,
            'schemes' => 0,
            'positions' => 0,
            'active' => 0,
            'current_page' => 1,
            'api_action' => 'result'
        )
    ),
    'admin_salary_total_regions_empty_result' => array(
        'file' => 'api/api_admin_salary_total.php',
        'class' => 'ApiAdminSalaryTotal',
        'method' => 'result',
        'params' => array(
            'show_filters' => 'hide',
            'sIDOffices' => '',
            'nIDObject' => 0,
            'nRadio' => 1,
            'sAct' => 2,
            'active' => 0,
            'positions' => 0,
            'type' => 1,
            'schemes' => 0,
            'month' => $salaryMonth,
            'year' => $salaryYear,
            'types' => 'rFirms',
            'sNum' => '',
            'sName' => '',
            'sfield' => '',
            'stype' => 0,
            'current_page' => 1,
            'api_action' => 'result'
        )
    ),
    'salary_firms_load' => array(
        'file' => 'api/api_salary_firms.php',
        'class' => 'ApiSalaryFirms',
        'method' => 'load',
        'params' => array('nIDSelectFirmFrom' => 0, 'nIDSelectFirmTo' => 0, 'api_action' => 'load')
    ),
    'salary_firms_result' => array(
        'file' => 'api/api_salary_firms.php',
        'class' => 'ApiSalaryFirms',
        'method' => 'result',
        'params' => array(
            'sAct' => 1,
            'nIDFirmFrom' => $salaryFirmFrom,
            'nIDFirmTo' => $salaryFirmTo,
            'year' => $salaryYear,
            'month' => $salaryMonth,
            'api_action' => 'result'
        )
    ),
    'salary_firms_total_result' => array(
        'file' => 'api/api_salary_firms_total.php',
        'class' => 'ApiSalaryFirmsTotal',
        'method' => 'result',
        'params' => array('year' => $salaryYear, 'month' => $salaryMonth, 'api_action' => 'result')
    ),
    'budget_init' => array(
        'file' => 'api/api_budget.php',
        'class' => 'ApiBudget',
        'method' => 'init',
        'params' => array('month' => date('Y-m'), 'api_action' => 'init')
    ),
    'collections_init' => array(
        'file' => 'api/api_collections.php',
        'class' => 'ApiCollections',
        'method' => 'init',
        'params' => array('month' => date('Y-m'), 'api_action' => 'init')
    ),
    'budget_search' => array(
        'file' => 'api/api_budget.php',
        'class' => 'ApiBudget',
        'method' => 'search',
        'flex_vars' => array('arr_earnings', 'arr_expenses'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 0,
            'month_from' => $latestBudgetMonth,
            'month_to' => $latestBudgetMonth,
            'api_action' => 'search'
        )
    ),
    'budget_two_month_search' => array(
        'file' => 'api/api_budget.php',
        'class' => 'ApiBudget',
        'method' => 'search',
        'flex_vars' => array('arr_earnings', 'arr_expenses'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 0,
            'month_from' => $previousBudgetMonth,
            'month_to' => $latestBudgetMonth,
            'api_action' => 'search'
        )
    ),
    'budget_regions_search' => array(
        'file' => 'api/api_budget.php',
        'class' => 'ApiBudget',
        'method' => 'search',
        'flex_vars' => array('arr_earnings', 'arr_expenses'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 1,
            'month_from' => $latestBudgetMonth,
            'month_to' => $latestBudgetMonth,
            'api_action' => 'search'
        )
    ),
    'collections_search' => array(
        'file' => 'api/api_collections.php',
        'class' => 'ApiCollections',
        'method' => 'search',
        'flex_vars' => array('arr_earnings'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 0,
            'month_from' => $latestSalesMonth,
            'month_to' => $latestSalesMonth,
            'api_action' => 'search'
        )
    ),
    'collections_two_month_search' => array(
        'file' => 'api/api_collections.php',
        'class' => 'ApiCollections',
        'method' => 'search',
        'flex_vars' => array('arr_earnings'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 0,
            'month_from' => $previousSalesMonth,
            'month_to' => $latestSalesMonth,
            'api_action' => 'search'
        )
    ),
    'collections_regions_search' => array(
        'file' => 'api/api_collections.php',
        'class' => 'ApiCollections',
        'method' => 'search',
        'flex_vars' => array('arr_earnings'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'regions_view' => 1,
            'month_from' => $latestSalesMonth,
            'month_to' => $latestSalesMonth,
            'api_action' => 'search'
        )
    ),
    'incomings_search' => array(
        'file' => 'api/api_incomings.php',
        'class' => 'ApiIncomings',
        'method' => 'search',
        'flex_vars' => array('arr_earnings', 'arr_expenses'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'month_from' => $latestOrderMonth,
            'month_to' => $latestOrderMonth,
            'api_action' => 'search'
        )
    ),
    'incomings_two_month_search' => array(
        'file' => 'api/api_incomings.php',
        'class' => 'ApiIncomings',
        'method' => 'search',
        'flex_vars' => array('arr_earnings', 'arr_expenses'),
        'params' => array(
            'id_firm' => 0,
            'id_office' => 0,
            'id_filter' => 0,
            'month_from' => $previousOrderMonth,
            'month_to' => $latestOrderMonth,
            'api_action' => 'search'
        )
    ),
    'limit_card_info_empty_selection' => array(
        'file' => 'api/api_limit_card_info.php',
        'class' => 'ApiLimitCardInfo',
        'method' => 'limit',
        'params' => array('chk' => '', 'api_action' => 'limit')
    ),
    'limit_card_persons_empty_selection' => array(
        'file' => 'api/api_limit_card_persons.php',
        'class' => 'ApiLimitCardPersons',
        'method' => 'limit',
        'params' => array('chk' => '', 'api_action' => 'limit')
    ),
    'tech_limit_cards_delete_empty_selection' => array(
        'file' => 'api/api_tech_limit_cards.php',
        'class' => 'ApiTechLimitCards',
        'method' => 'delete',
        'params' => array('chk' => '', 'api_action' => 'delete')
    ),
    'tech_limit_cards_limit_empty_selection' => array(
        'file' => 'api/api_tech_limit_cards.php',
        'class' => 'ApiTechLimitCards',
        'method' => 'limit',
        'params' => array('chk' => '', 'api_action' => 'limit')
    ),
    'tech_support_requests_delete_empty_selection' => array(
        'file' => 'api/api_tech_support_requests.php',
        'class' => 'ApiTechSupportRequests',
        'method' => 'delete',
        'params' => array('chk' => '', 'api_action' => 'delete')
    ),
    'tech_support_requests_limit_empty_selection' => array(
        'file' => 'api/api_tech_support_requests.php',
        'class' => 'ApiTechSupportRequests',
        'method' => 'limit',
        'params' => array('chk' => '', 'api_action' => 'limit')
    ),
    'personal_card_operations_empty_selection' => array(
        'file' => 'api/api_personal_card_operations.php',
        'class' => 'ApiPersonalCardOperations',
        'method' => 'save',
        'params' => array('chk' => '', 'api_action' => 'save')
    ),
    'ppp_delete_all_empty_selection' => array(
        'file' => 'api/api_ppp.php',
        'class' => 'ApiPPP',
        'method' => 'deleteAll',
        'params' => array('chk' => '', 'api_action' => 'deleteAll')
    ),
    'missing_documents_scalar_selection' => array(
        'file' => 'api/api_missing_documents.php',
        'class' => 'ApiMissingDocuments',
        'method' => 'result',
        'params' => array(
            'nIDFirm' => $salaryFirmFrom,
            'nIDOffice' => 0,
            'account_documents' => '1',
            'api_action' => 'result'
        )
    )
);

if ($clientId > 0) {
    $cases['client_objects_result'] = array(
        'file' => 'api/api_client_objects.php',
        'class' => 'ApiClientObjects',
        'method' => 'result',
        'params' => array('nID' => $clientId, 'api_action' => 'result')
    );
}

if ($assetId > 0) {
    $cases['asset_info_sub_assets_result'] = array(
        'file' => 'api/api_asset_info_sub_assets.php',
        'class' => 'APIAssetInfoSubAssets',
        'method' => 'result',
        'params' => array('nID' => $assetId, 'api_action' => 'result')
    );
}

function invokeReadonlyApiCase($root, $case)
{
    $params =& Params::getAll();
    $params = $case['params'];
    $_GET = $case['params'];
    $_POST = array();
    APILog::$aLogs = array();

    $response = new DBResponse();
    ob_start();

    try {
        require_once $root . '/' . $case['file'];
        $handler = new $case['class']();
        $method = $case['method'];
        $handler->$method($response);
        return array(
            'xml' => ob_get_clean(),
            'response' => $response
        );
    } catch (Throwable $error) {
        ob_end_clean();
        throw $error;
    }
}

function validateFlexVariables($name, DBResponse $response, $expectedNames)
{
    if (empty($expectedNames)) {
        return '';
    }

    $variables = array();
    foreach ($response->toAMF()->variables as $variable) {
        $variables[$variable->name] = $variable->value;
    }

    $summary = array();
    foreach ($expectedNames as $expectedName) {
        if (!array_key_exists($expectedName, $variables)) {
            throw new RuntimeException("{$name} did not set Flex variable {$expectedName}");
        }

        $value = $variables[$expectedName];
        if (!is_array($value) || empty($value)) {
            throw new RuntimeException("{$name} returned an empty Flex variable {$expectedName}");
        }

        validateCurrencyLabels($name, $expectedName, $value);

        $summary[] = $expectedName . ':' . count($value);
    }

    return implode(',', $summary);
}

function validateCurrencyLabels($caseName, $variableName, $value)
{
    if (is_array($value)) {
        foreach ($value as $child) {
            validateCurrencyLabels($caseName, $variableName, $child);
        }
        return;
    }

    if (!is_string($value)) {
        return;
    }

    if (strpos($value, 'лв.') !== false || strpos($value, '€ €') !== false) {
        throw new RuntimeException(
            "{$caseName} returned an invalid currency label in Flex variable {$variableName}: {$value}"
        );
    }
}

function validateXmlResponse($name, $xml)
{
    if (
        strpos($xml, '<?xml') !== 0 ||
        !preg_match('/<response(?:\s[^>]*)?\s*\/?\>/', $xml) ||
        strpos($xml, '<php>') !== false ||
        preg_match('/(?:Fatal error|Parse error|Warning|Deprecated|Notice)\s*:/i', $xml)
    ) {
        $summary = trim(preg_replace('/\s+/', ' ', substr($xml, 0, 1000)));
        throw new RuntimeException(
            "{$name} returned PHP diagnostics or a non-XML response: " . substr($summary, 0, 500)
        );
    }

    if (function_exists('simplexml_load_string')) {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($document === false) {
            $message = isset($errors[0]) ? trim($errors[0]->message) : 'unknown XML parse error';
            throw new RuntimeException("{$name} returned invalid XML: {$message}");
        }
    }
}

$passed = array();

foreach ($cases as $name => $case) {
    try {
        $result = invokeReadonlyApiCase($root, $case);
        $xml = $result['xml'];
        validateXmlResponse($name, $xml);
        $flexSummary = validateFlexVariables(
            $name,
            $result['response'],
            isset($case['flex_vars']) ? $case['flex_vars'] : array()
        );
        $passed[] = $name . '(rows=' . substr_count($xml, '<r ') . ',bytes=' . strlen($xml) .
            ($flexSummary !== '' ? ',flex=' . $flexSummary : '') . ')';
    } catch (Throwable $error) {
        fwrite(
            STDERR,
            "PHP85_API_READONLY_SMOKE=FAIL case={$name} " . get_class($error) . ': ' .
            $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine() . "\n" .
            $error->getTraceAsString() . "\n"
        );
        exit(1);
    }
}

echo 'PHP85_API_READONLY_SMOKE=PASS ' . implode(' ', $passed) . "\n";
