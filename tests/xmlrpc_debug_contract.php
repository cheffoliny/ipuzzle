<?php

$source = file_get_contents(dirname(__DIR__) . '/js/xmlrpc.js');

if ($source === false) {
    fwrite(STDERR, "Unable to read js/xmlrpc.js.\n");
    exit(1);
}

$requestLog = strpos($source, 'Request to:');
$responseLog = strpos($source, 'XML Response:');
$processing = strpos($source, 'FormProcessing(xml);');
$renderer = strpos($source, 'RpcResultRenderer.isSupportedProfile');
$domOnly = strpos($source, '// DOM-only runtime.');
$domError = strpos($source, 'DOM renderer error:');
$loaderAfterDomError = strpos($source, 'DisableLoader();', $domError === false ? 0 : $domError);
$forbiddenXsltRuntime = array(
    'XSLTProcessor',
    'transformToFragment',
    'transformNode(xsl)',
    "xslhttp.open('GET'",
    'rpc_xsl',
    'XSL renderer error:',
    'ecxecuteXML'
);

if (
    $requestLog === false ||
    $responseLog === false ||
    $processing === false ||
    $renderer === false ||
    $domOnly === false ||
    $domError === false ||
    $loaderAfterDomError === false
) {
    fwrite(STDERR, "The XML debug or result-rendering contract is incomplete.\n");
    exit(1);
}

if ($responseLog > $processing || $responseLog > $renderer) {
    fwrite(STDERR, "The raw XML response must be logged before processing and rendering.\n");
    exit(1);
}

foreach ($forbiddenXsltRuntime as $forbiddenToken) {
    if (strpos($source, $forbiddenToken) !== false) {
        fwrite(STDERR, "Browser XSLT runtime code is still present: {$forbiddenToken}.\n");
        exit(1);
    }
}

echo "XMLRPC_DEBUG_CONTRACT=PASS\n";
