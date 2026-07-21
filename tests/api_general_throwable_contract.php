<?php

$source = file_get_contents(dirname(__DIR__) . '/api/api_general.php');

if ($source === false) {
    fwrite(STDERR, "Unable to read api/api_general.php.\n");
    exit(1);
}

if (preg_match_all('/catch\s*\(\s*Throwable\s+\$e\s*\)/', $source) < 2) {
    fwrite(STDERR, "RPC v1 and RPC v2 must both catch PHP Error/TypeError through Throwable.\n");
    exit(1);
}

if (!preg_match('/function\s+printRpcThrowableResponse.*?print\s+\$oResponse->toXML\(\)/s', $source)) {
    fwrite(STDERR, "Caught PHP throwables are not serialized through DBResponse XML.\n");
    exit(1);
}

if (substr_count($source, 'printRpcThrowableResponse($oResponse, $e);') < 2) {
    fwrite(STDERR, "RPC v1 and RPC v2 do not share the XML throwable serializer.\n");
    exit(1);
}

echo "API_GENERAL_THROWABLE_CONTRACT=PASS\n";
