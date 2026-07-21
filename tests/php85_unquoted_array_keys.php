<?php

chdir(dirname(__DIR__));

$aRoots = array('api', 'db_api', 'engine', 'pdf', 'classes', 'config');
$aViolations = array();
$nFileCount = 0;

function nextSignificantToken(array $aTokens, $nIndex)
{
    $nCount = count($aTokens);
    while ($nIndex < $nCount) {
        $mToken = $aTokens[$nIndex];
        if (
            is_array($mToken) &&
            in_array($mToken[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)
        ) {
            $nIndex++;
            continue;
        }

        return $nIndex;
    }

    return NULL;
}

foreach ($aRoots as $sRoot) {
    $oIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($oIterator as $oFile) {
        if (!$oFile->isFile() || strtolower($oFile->getExtension()) !== 'php') {
            continue;
        }

        $nFileCount++;
        $sPath = $oFile->getPathname();
        $sSource = file_get_contents($sPath);
        if ($sSource === false) {
            $aViolations[] = "{$sPath}: unable to read file";
            continue;
        }

        $aTokens = token_get_all($sSource);
        $nTokenCount = count($aTokens);

        for ($nIndex = 0; $nIndex < $nTokenCount; $nIndex++) {
            $mToken = $aTokens[$nIndex];
            if (!is_array($mToken) || $mToken[0] !== T_VARIABLE) {
                continue;
            }

            $nOpenIndex = nextSignificantToken($aTokens, $nIndex + 1);
            if ($nOpenIndex === NULL || $aTokens[$nOpenIndex] !== '[') {
                continue;
            }

            $nKeyIndex = nextSignificantToken($aTokens, $nOpenIndex + 1);
            if (
                $nKeyIndex === NULL ||
                !is_array($aTokens[$nKeyIndex]) ||
                $aTokens[$nKeyIndex][0] !== T_STRING
            ) {
                continue;
            }

            $nCloseIndex = nextSignificantToken($aTokens, $nKeyIndex + 1);
            if ($nCloseIndex !== NULL && $aTokens[$nCloseIndex] === ']') {
                $aViolations[] = sprintf(
                    '%s:%d unquoted array key %s[%s]',
                    $sPath,
                    $aTokens[$nKeyIndex][2],
                    $mToken[1],
                    $aTokens[$nKeyIndex][1]
                );
            }
        }
    }
}

if (!empty($aViolations)) {
    fwrite(STDERR, implode("\n", $aViolations) . "\n");
    exit(1);
}

echo "PHP85_UNQUOTED_ARRAY_KEYS=PASS ({$nFileCount} files)\n";

