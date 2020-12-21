<?php
class MonDebug {    
    
    public static function debug($msg,$level,$file, $line) {              
        if (MONITORING_DEBUG && $level <= MONITORING_DEBUG) print(sprintf("[%s]\t%s\t%s\t%s\n",date("d.m.Y H:i:s",mktime()),$file,$line,$msg));                
    }        
}

?>
