<?php

$root = dirname(__DIR__);
$directories = array('api', 'db_api', 'engine');
$violations = array();

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $source = file_get_contents($file->getPathname());
        if ($source === false) {
            fwrite(STDERR, "Unable to read {$file->getPathname()}.\n");
            exit(1);
        }

        $code = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if ($token[0] !== T_COMMENT && $token[0] !== T_DOC_COMMENT) {
                    $code .= $token[1];
                }
            } else {
                $code .= $token;
            }
        }

        if (preg_match('/\$[A-Za-z_][A-Za-z0-9_]*\s*->\s*debug\s*=\s*(?:true|1)\s*;/i', $code)) {
            $violations[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        }
    }
}

if (!empty($violations)) {
    fwrite(
        STDERR,
        "Forced ADOdb debug output can corrupt XML responses: " . implode(', ', $violations) . "\n"
    );
    exit(1);
}

echo "XML_OUTPUT_HYGIENE_CONTRACT=PASS\n";

