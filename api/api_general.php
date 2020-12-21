<?php

require_once ("../config/session.inc.php");  // установява includ_path = на директорията на проекта
require_once ('config/function.autoload.php');

require_once ("config/connect.inc.php");
require_once ("include/general.inc.php");
require_once ("include/validate.inc.php"); // валидации във формите



$oParams = Params::getInstance();
//debug($oParams);
$aParams = $oParams->toArray();

$nRpcVersion = $oParams->get("rpc_version", AmfServer::isAmfRequest() ? 3 : 0 );
//APILog::Log(0,ArrayToString($_SESSION));
switch( $nRpcVersion )
{
    case 2:
    case 3:
        require_once("../include/adodb/adodb-exceptions.inc.php");
        break;
}


$oAPILog = new APILog();	//catch php errors
$oEvents = new DBSystemEvents();
$oEvents->InsertSystemEvent( isset( $aParams['action_script'] ) ? $aParams['action_script'] : "none", true);

switch( $nRpcVersion )
{
    case 2:
        rpc2();
        break;
    case 3:
        rpc3();
        break;
    default:
        {
            $oResponse = new DBResponse();

            if(	isset( $aParams['action_script'] ) ) { //action_script -> параметъра който в който се съдържа името на скрипта за изпълнение

                require_once ($aParams['action_script']);
            }
        }
}

function rpc2()
{
    $oParams = Params::getInstance();
    $aParams = $oParams->toArray();

    if(	isset( $aParams['action_script'] ) )
    {
        $sFileName = $aParams['action_script'];

        require_once( $sFileName );

        $sClassName = $sFileName;
        $sClassName = preg_replace('/.*?\//', "", $sClassName);
        $sClassName = preg_replace('/\.php/', "", $sClassName);

        $sClassName = preg_replace_callback('/(\_)(.)/', function ($matches) {return strtoupper($matches[2]);}, $sClassName);
        $sClassName = preg_replace_callback('/(^.)/', function ($matches) {return strtoupper($matches[1]);}, $sClassName);
//			$sClassName = preg_replace('/(\_)(.)/e', "strtoupper('\\2')", $sClassName);
//			$sClassName = preg_replace('/(^.)/e', "strtoupper('\\1')", $sClassName);

        $oResponse = new DBResponse();

        try
        {
            $sApiAction = $oParams->api_action;

            if( in_array($sApiAction, array('result', 'export_to_xls', 'export_to_pdf')) )
                $sApiAction = 'result';

            if( empty( $sApiAction ) )
                throw new Exception("Remote method is empty!", DBAPI_ERR_INVALID_PARAM);

            $oClass = new ReflectionClass( $sClassName );

            if( !$oClass->hasMethod( $sApiAction ) )
                throw new Exception("Can not find remote method {$oParams->rpc_remote_method }!");

            if( empty( $oClass ) )
                throw new Exception("Can not find class {$sClassName}!");

            $oHandler = $oClass->newInstance();
            $oMethod = $oClass->getMethod( $sApiAction );
            $oMethod->invoke($oHandler, $oResponse);
        }
        catch( Exception $e )
        {
            $nCode = $e->getCode();
            $sMessage = $e->getMessage();

            if( $e instanceof ADODB_Exception )
            {
                $oResponse->setDebug( $e->getMessage() );
                $nCode = DBAPI_ERR_SQL_QUERY;
                $sMessage = "";
            }
            elseif( empty( $nCode ) )
            {
                $nCode = DBAPI_ERR_UNKNOWN;
            }

            $oResponse->setError($nCode, $sMessage, $e->getFile(), $e->getLine());
            $oResponse->setDebug( $e->getTraceAsString() );
            print $oResponse->toXML();
        }
    }
}

function rpc3()
{
    require_once 'Zend/Amf/Server.php';
    $oServer = new AmfServer();
    $oServer->setProduction( FALSE );
    print $oServer->handle();
}

//
//    require_once ("../config/session.inc.php");  // установява includ_path = на директорията на проекта
//	require_once ('config/function.autoload.php');
//
//    require_once ("config/connect.inc.php");
//    require_once ("include/general.inc.php");
//    require_once ("include/validate.inc.php"); // валидации във формите
//
//	$oParams = Params::getInstance();
//	$aParams = $oParams->toArray();
//
//	$nRpcVersion = $oParams->get("rpc_version", AmfServer::isAmfRequest() ? 3 : 0 );
//
//	switch( $nRpcVersion )
//	{
//		case 2:
//		case 3:
//			require_once("../include/adodb/adodb-exceptions.inc.php");
//			break;
//	}
//
//
//	$oAPILog = new APILog();	//catch php errors
//	$oEvents = new DBSystemEvents();
//	$oEvents->InsertSystemEvent( isset( $aParams['action_script'] ) ? $aParams['action_script'] : "none", true);
//
//	switch( $nRpcVersion )
//	{
//		case 2:
//			rpc2();
//			break;
//		case 3:
//		    rpc3();
//		    break;
//		default:
//			{
//				$oResponse = new DBResponse();
//
//				if(	isset( $aParams['action_script'] ) ) { //action_script -> параметъра който в който се съдържа името на скрипта за изпълнение
//					require_once ($aParams['action_script']);
//				}
//			}
//	}
//
//    function rpc2()
//    {
//        $oParams = Params::getInstance();
//        $aParams = $oParams->toArray();
//
//        if(	isset( $aParams['action_script'] ) )
//        {
//            $sFileName = $aParams['action_script'];
//
//            require_once( $sFileName );
//
//            $sClassName = $sFileName;
//            $sClassName = preg_replace('/.*?\//', "", $sClassName);
//            $sClassName = preg_replace('/\.php/', "", $sClassName);
//    //			$sClassName = preg_replace('/(\_)(.)/e', "strtoupper('\\2')", $sClassName);
//            $sClassName = preg_replace_callback('/(\_)(.)/', function ($matches) {
//                return strtoupper($matches[2]);
//            }, $sClassName);
//    //			$sClassName = preg_replace('/(^.)/e', "strtoupper('\\1')", $sClassName);
//            $sClassName = preg_replace_callback('/(^.)/', function ($matches) {
//                return strtoupper($matches[1]);
//            }, $sClassName);
//
//            $oResponse = new DBResponse();
//
//            try
//            {
//                $sApiAction = $oParams->api_action;
//
//                if( in_array($sApiAction, array('result', 'export_to_xls', 'export_to_pdf')) )
//                    $sApiAction = 'result';
//
//                if( empty( $sApiAction ) )
//                    throw new Exception("Remote method is empty!", DBAPI_ERR_INVALID_PARAM);
//
//                $oClass = new ReflectionClass( $sClassName );
//
//                if( !$oClass->hasMethod( $sApiAction ) )
//                    throw new Exception("Can not find remote method {$oParams->rpc_remote_method }!");
//
//                if( empty( $oClass ) )
//                    throw new Exception("Can not find class {$sClassName}!");
//
//                $oHandler = $oClass->newInstance();
//                $oMethod = $oClass->getMethod( $sApiAction );
//                $oMethod->invoke($oHandler, $oResponse);
//            }
//            catch( Exception $e )
//            {
//                $nCode = $e->getCode();
//                $sMessage = $e->getMessage();
//
//                if( $e instanceof ADODB_Exception )
//                {
//                    $oResponse->setDebug( $e->getMessage() );
//                    $nCode = DBAPI_ERR_SQL_QUERY;
//                    $sMessage = "";
//                }
//                elseif( empty( $nCode ) )
//                {
//                    $nCode = DBAPI_ERR_UNKNOWN;
//                }
//
//                $oResponse->setError($nCode, $sMessage, $e->getFile(), $e->getLine());
//                $oResponse->setDebug( $e->getTraceAsString() );
//                print $oResponse->toXML();
//            }
//        }
//    }
//
//    function rpc3()
//    {
//        require_once 'Zend/Amf/Server.php';
//        $oServer = new AmfServer();
//        $oServer->setProduction( FALSE );
//        print $oServer->handle();
//    }
?>