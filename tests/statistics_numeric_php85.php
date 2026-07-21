<?php

$root = dirname(__DIR__);
$sourceFile = $root . '/db_api/DBStatistics2.class.php';
$source = file_get_contents($sourceFile);

if ($source === false) {
    fwrite(STDERR, "STATISTICS_NUMERIC_PHP85=FAIL unable to read DBStatistics2.class.php\n");
    exit(1);
}

$forbiddenPatterns = array(
    '/objects_price_sum_change[^;\r\n]*\/[^;\r\n]*objects_count_change/',
    '/\$nPreviousObjectPriceSum\s*=\s*\$aFinalData[^;\r\n]*objects_price_sum/',
    '/ лв\./u'
);

foreach ($forbiddenPatterns as $pattern) {
    if (preg_match($pattern, $source)) {
        fwrite(
            STDERR,
            'STATISTICS_NUMERIC_PHP85=FAIL formatted currency is reused as a number: ' . $pattern . "\n"
        );
        exit(1);
    }
}

$requiredPatterns = array(
    '/\$nObjectPriceSumChange\s*\/\s*\$nObjectCountChange/',
    '/\$nPreviousObjectPriceSum\s*=\s*\$nCurrentObjectPriceSum/',
    '/" €"/'
);

foreach ($requiredPatterns as $pattern) {
    if (!preg_match($pattern, $source)) {
        fwrite(
            STDERR,
            'STATISTICS_NUMERIC_PHP85=FAIL missing raw numeric calculation: ' . $pattern . "\n"
        );
        exit(1);
    }
}

echo "STATISTICS_NUMERIC_PHP85=PASS\n";
