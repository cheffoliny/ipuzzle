<?php

define("CONTENT_TYPE_AMF", "application/x-amf");

require_once "Zend/Amf/Server.php";


class AmfServer extends Zend_Amf_Server
{
    const AMF_HEADER_REMOTE_LOGS = "remoteLog";
    
    /**
     * @name isAmfRequest
     * @return bool
     */
    public static function isAmfRequest() {
        
        return ( bool )( !empty( $_SERVER["CONTENT_TYPE"] ) && strtolower( $_SERVER["CONTENT_TYPE"] ) == strtolower( CONTENT_TYPE_AMF ) );
    }
    
    protected function _dispatch( $method, $params = null, $source = null ) {
        
        $mReturn = NULL;
        $oException = NULL;
        
        try {
            $source = str_replace("..", "", $source);
            
	        if( !file_exists("../api/{$source}.php") ) {
	            throw new Exception("Invalid 'source' of RemoteObject");
	        }
	        
	        require_once "api/{$source}.php";
	        
	        $sClassName = $source;
	        $sClassName = preg_replace('/.*?\//', "", $sClassName);
	        $sClassName = preg_replace('/\.php/', "", $sClassName);
	        $sClassName = preg_replace('/(\_)(.)/e', "strtoupper('\\2')", $sClassName);
	        $sClassName = preg_replace('/(^.)/e', "strtoupper('\\1')", $sClassName);
	        
            if( defined('EOL_DEBUG') && EOL_DEBUG ) {
                $sMsg = sprintf("PROTOCOL: AMF\nMETHOD: %s\nPARAMS: %s\n", $method, var_export( $params, true ));
                APILog::Log( DBAPI_ERR_SUCCESS, $sMsg );   
            }
            
	        $oResponse = NULL;
	        
	        $oClass = new ReflectionClass( $sClassName );
	        $oMethod = $oClass->getMethod( $method );
	        $aMethodParams = $oMethod->getParameters();
	        
	        if( !empty( $aMethodParams ) && count( $aMethodParams ) == 1 ) {
	           $oMethodParam = current( $aMethodParams );
	           $oMethodParamClass = $oMethodParam->getClass();
	           if( $oMethodParamClass != null ) {
	               $oMethodParamClassName = $oMethodParamClass->getName();
	               if( $oMethodParamClassName == "DBResponse") {
	                   $oResponse = new DBResponse();
	                   if( $params != NULL && count( $params ) == 1 ) {
	                       $aParams = current( $params );
	                       foreach( $aParams as $sKey => $mValue ) {
	                           Params::set( $sKey, $mValue );
	                       }
	                   }
	                   $params = array( $oResponse );
	                   $this->setClassMap("FlexResponse", "FlexResponse");
	               }
	           }
	        } 
	        
	        $this->setClass( $sClassName );
	            
	        $mReturn = parent::_dispatch( $method, $params, $sClassName );
	        
	        if( $oResponse != NULL ) {
	            $mReturn = $oResponse->toAMF();
	        }
	        
	        $sMsg = sprintf("RESPONSE: %s\n", var_export( $mReturn, true ));
	        APILog::Log( DBAPI_ERR_SUCCESS, $sMsg ); 
        }
        catch( Exception $e ) {
            APILog::Log( DBAPI_ERR_UNKNOWN, sprintf("%s: %s\n%s", get_class( $e ), $e->getMessage(), $e->getTraceAsString() ), $e->getFile(), $e->getLine() );
            $oException = $e;  
        }
        
	    if( defined('EOL_DEBUG') && EOL_DEBUG && !empty( APILog::$aLogs ) ) {
            $this->_response->addAmfHeader( new Zend_Amf_Value_MessageHeader( self::AMF_HEADER_REMOTE_LOGS, true, APILog::$aLogs ) );
        }
        
        if( $oException != NULL ) {
            throw $oException;
        }
        
        return $mReturn;
    }
}
?>