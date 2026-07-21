<?php
require_once dirname(__DIR__) . '/include/php2excel/class.writeexcel_formula.inc.php';

$parser = new writeexcel_formula(0);
$encoded = $parser->parse_formula('1+2');

if (!is_string($encoded) || strlen($encoded) === 0) {
    fwrite(STDERR, "Formula parser returned no data.\n");
    exit(1);
}

echo 'FORMULA_BYTES=' . strlen($encoded) . PHP_EOL;
