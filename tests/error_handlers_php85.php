<?php

$root = dirname(__DIR__);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . '/include');

require_once $root . '/include/Zend/Session/Exception.php';
require_once $root . '/include/Zend/Service/Audioscrobbler.php';

$sessionStart = new ReflectionMethod('Zend_Session_Exception', 'handleSessionStartError');
$sessionClose = new ReflectionMethod('Zend_Session_Exception', 'handleSilentWriteClose');
$audioHandler = new ReflectionMethod('Zend_Service_Audioscrobbler', '_errorHandler');

foreach (array($sessionStart, $sessionClose, $audioHandler) as $handler) {
    if ($handler->getNumberOfRequiredParameters() !== 4) {
        fwrite(
            STDERR,
            'ERROR_HANDLERS_PHP85=FAIL ' . $handler->getName() . " requires more than four parameters\n"
        );
        exit(1);
    }
}

Zend_Session_Exception::handleSessionStartError(E_WARNING, 'probe', __FILE__, __LINE__);
Zend_Session_Exception::handleSilentWriteClose(E_WARNING, 'probe', __FILE__, __LINE__);

$audio = (new ReflectionClass('Zend_Service_Audioscrobbler'))->newInstanceWithoutConstructor();
$audioHandler->invoke($audio, E_WARNING, 'probe', __FILE__, __LINE__);

$GLOBALS['amfphp']['errorLevel'] = E_ALL;
require_once $root . '/amfphp/core/shared/exception/php5Exception.php';

$amfHandler = new ReflectionFunction('amfErrorHandler');
if ($amfHandler->getNumberOfRequiredParameters() !== 4) {
    fwrite(STDERR, "ERROR_HANDLERS_PHP85=FAIL amfErrorHandler requires more than four parameters\n");
    exit(1);
}

$verbose = new VerboseException('probe', E_WARNING, 'legacy.php', 123);
if (
    $verbose->code !== 'AMFPHP_RUNTIME_ERROR' ||
    $verbose->file !== 'legacy.php' ||
    $verbose->line !== 123 ||
    $verbose->getMessage() !== 'probe'
) {
    fwrite(STDERR, "ERROR_HANDLERS_PHP85=FAIL AMF exception compatibility fields changed\n");
    exit(1);
}

restore_error_handler();

echo "ERROR_HANDLERS_PHP85=PASS\n";
