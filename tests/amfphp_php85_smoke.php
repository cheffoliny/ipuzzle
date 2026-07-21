<?php

$root = dirname(__DIR__);
$amfRoot = $root . '/amfphp';
$sessionDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ipuzzle-amfphp-' . getmypid();
$sessionId = 'ipuzzle-amfphp-' . getmypid();
$sessionFile = $sessionDirectory . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;

function amfFail($message)
{
    throw new RuntimeException($message);
}

if (!mkdir($sessionDirectory, 0700) && !is_dir($sessionDirectory)) {
    fwrite(STDERR, "AMFPHP_PHP85_SMOKE=FAIL Unable to create the session directory.\n");
    exit(1);
}

$failure = null;

try {
    if (!ini_set('session.save_path', $sessionDirectory)) {
        amfFail('Unable to configure the temporary session directory.');
    }

    chdir($amfRoot);
    session_id($sessionId);

    include $amfRoot . '/globals.php';
    require_once $amfRoot . '/core/amf/app/Gateway.php';
    require_once $amfRoot . '/core/amf/io/AMFSerializer.php';
    require_once $amfRoot . '/core/amf/io/AMFDeserializer.php';

    CharsetHandler::setMethod('none');
    CharsetHandler::setPhpCharset('UTF-8');
    CharsetHandler::setSqlCharset('UTF-8');

    $gateway = new Gateway();
    if (!($gateway->exec instanceof Executive) || count($gateway->filters) !== 5 || count($gateway->actions) !== 4) {
        amfFail('Gateway constructor did not initialize the processing chains.');
    }

    $errorBody = new MessageBody('/test', '/error');
    $typeCheckedService = new class {
        public function requiresInteger(int $value)
        {
            return $value;
        }
    };
    $errorResult = Executive::doMethodCall(
        $errorBody,
        $typeCheckedService,
        'requiresInteger',
        array('not-an-integer')
    );
    $serializedError = $errorBody->getResults();
    if ($errorResult !== '__amfphp_error'
        || $errorBody->responseURI !== '/error/onStatus'
        || !is_array($serializedError)
        || !isset($serializedError['code'])
    ) {
        amfFail('Executive did not convert a PHP TypeError to an AMF error response.');
    }

    $roundTripSource = new AMFObject('');
    $roundTripBody = new MessageBody('/test', '/1');
    $roundTripBody->setResults(array('hello' => 'world', 'number' => 85));
    $roundTripSource->addBody($roundTripBody);

    $roundTripBinary = (new AMFSerializer())->serialize($roundTripSource);
    $roundTripTarget = new AMFObject($roundTripBinary);
    (new AMFDeserializer($roundTripBinary))->deserialize($roundTripTarget);
    $roundTripValue = $roundTripTarget->getBodyAt(0)->getValue();
    if (!is_array($roundTripValue)
        || !isset($roundTripValue['hello'], $roundTripValue['number'])
        || $roundTripValue['hello'] !== 'world'
        || (float) $roundTripValue['number'] !== 85.0
    ) {
        amfFail('AMF serializer/deserializer round trip changed the payload.');
    }

    $requestObject = new AMFObject('');
    $requestBody = new MessageBody();
    $requestBody->responseURI = 'amfphp.DiscoveryService.getServices';
    $requestBody->responseTarget = '/1';
    $requestBody->setResults(array());
    $requestObject->addBody($requestBody);
    $request = (new AMFSerializer())->serialize($requestObject);

    $gateway->setClassPath($amfRoot . '/services/');
    $gateway->setClassMappingsPath($amfRoot . '/services/vo/');
    $gateway->setCharsetHandler('none', 'UTF-8', 'UTF-8');
    $gateway->disableDebug();
    $GLOBALS['amfphp']['disableTrace'] = true;
    $GLOBALS['HTTP_RAW_POST_DATA'] = $request;
    $_SERVER['HTTP_ACCEPT_ENCODING'] = '';
    $_SERVER['QUERY_STRING'] = '';

    ob_start();
    $gateway->service();
    $response = ob_get_clean();

    $decodedResponse = new AMFObject($response);
    (new AMFDeserializer($response))->deserialize($decodedResponse);
    $responseBody = $decodedResponse->getBodyAt(0);
    $services = $responseBody->getValue();

    if ($responseBody->targetURI !== '/1/onResult' || !is_array($services) || count($services) < 1) {
        amfFail('Gateway did not return a successful DiscoveryService response.');
    }

    $serviceLabels = array();
    foreach ($services as $service) {
        if (isset($service['label'])) {
            $serviceLabels[] = $service['label'];
        }
    }
    if (!in_array('buy', $serviceLabels, true) || !in_array('setup_code_leave', $serviceLabels, true)) {
        amfFail('DiscoveryService response is missing the application services.');
    }

    $_SESSION = array(
        'BASE_DIR' => $root,
        'userdata' => array(
            'id_person' => 1,
            'access_right_levels' => array(),
        ),
    );
    $_SERVER['SCRIPT_FILENAME'] = $amfRoot . '/gateway.php';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    $applicationRequestObject = new AMFObject('');
    $applicationRequestBody = new MessageBody();
    $applicationRequestBody->responseURI = 'setup_code_leave.init';
    $applicationRequestBody->responseTarget = '/2';
    $applicationRequestBody->setResults(array());
    $applicationRequestObject->addBody($applicationRequestBody);
    $applicationRequest = (new AMFSerializer())->serialize($applicationRequestObject);
    $GLOBALS['HTTP_RAW_POST_DATA'] = $applicationRequest;

    ob_start();
    $gateway->service();
    $applicationResponse = ob_get_clean();

    $decodedApplicationResponse = new AMFObject($applicationResponse);
    (new AMFDeserializer($applicationResponse))->deserialize($decodedApplicationResponse);
    $applicationBody = $decodedApplicationResponse->getBodyAt(0);
    $applicationValue = $applicationBody->getValue();

    if ($applicationBody->targetURI !== '/2/onResult'
        || !is_array($applicationValue)
        || !array_key_exists('variables', $applicationValue)
        || !array_key_exists('error', $applicationValue)
    ) {
        amfFail('The read-only setup_code_leave.init AMF service call failed: target='
            . $applicationBody->targetURI . ' value=' . json_encode($applicationValue));
    }

    echo 'AMFPHP_PHP85_SMOKE=PASS request_bytes=' . strlen($request)
        . ' response_bytes=' . strlen($response)
        . ' services=' . count($services)
        . ' application_bytes=' . strlen($applicationResponse) . "\n";
} catch (Throwable $exception) {
	$failure = $exception;
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    if (is_file($sessionFile)) {
        unlink($sessionFile);
    }
    if (is_dir($sessionDirectory)) {
        rmdir($sessionDirectory);
    }
}

if ($failure !== null) {
    fwrite(STDERR, "AMFPHP_PHP85_SMOKE=FAIL {$failure->getMessage()}\n");
    exit(1);
}
