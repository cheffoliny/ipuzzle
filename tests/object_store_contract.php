<?php

$root = dirname(__DIR__);
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');
$stateTemplate = file_get_contents($root . '/templates/object_store_state.tpl');
$pppTemplate = file_get_contents($root . '/templates/object_store_ppp.tpl');
$mainTemplate = file_get_contents($root . '/templates/object_store.tpl');
$stateApi = file_get_contents($root . '/api/api_object_store_state.php');
$database = file_get_contents($root . '/db_api/DBPPP.class.php');
$activeObjectStorageReferences = array();

foreach (array('api', 'classes', 'db_api', 'engine', 'js', 'templates') as $sourceDirectory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $sourceDirectory));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), array('js', 'php', 'tpl'), true)) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if ($contents !== false && strpos($contents, 'object_storage.xsl') !== false) {
            $activeObjectStorageReferences[] = $file->getPathname();
        }
    }
}

if (in_array(false, array($renderer, $xmlrpc, $stateTemplate, $pppTemplate, $mainTemplate, $stateApi, $database), true)) {
    fwrite(STDERR, "Unable to read object-store migration files.\n");
    exit(1);
}

$checks = array(
    strpos($xmlrpc, 'var rpc_renderer_profile = "general"') !== false,
    strpos($renderer, 'general: true') !== false,
    strpos($renderer, 'copyAttributes(cells[cellIndex], cell)') !== false,
    strpos($stateTemplate, 'rpc_excel_panel="off" rpc_paging="off"') !== false,
    strpos($pppTemplate, 'rpc_excel_panel="off" rpc_paging="off"') !== false,
    strpos($stateTemplate, "var oParentID = parent.document.getElementById( 'nID' )") !== false,
    strpos($pppTemplate, "var oParentID = parent.document.getElementById( 'nID' )") !== false,
    strpos($pppTemplate, 'value="6005553"') === false,
    strpos($pppTemplate, 'ui-object-store-frame-form') !== false,
    strpos($stateTemplate, 'ui-object-store-result') !== false,
    strpos($pppTemplate, 'ui-object-store-result') !== false,
    strpos($mainTemplate, 'class="ui-object-core ui-object-store"') !== false,
    strpos($mainTemplate, 'src="page.php?page=object_store_ppp"') !== false,
    strpos($mainTemplate, 'ui-object-store-frame') !== false,
    strpos($mainTemplate, '<i class=') === false,
    strpos($mainTemplate, 'ui-icon-expand') !== false,
    strpos($stateApi, "array_key_exists( 'row_limit', \$_SESSION['userdata'] )") !== false,
    strpos($stateApi, 'finally') !== false,
    strpos($stateApi, "unset( \$_SESSION['userdata']['row_limit'] )") !== false,
    substr_count($database, "' €' ) AS") >= 3,
    strpos($renderer, 'object_storage\\.xsl') === false,
    count($activeObjectStorageReferences) === 0
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "The object-store DOM/PHP 8.5 contract is incomplete.\n");
    exit(1);
}

echo "OBJECT_STORE_CONTRACT=PASS\n";
