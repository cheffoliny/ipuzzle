<?php

class ApiSetSetupObjectZone
{

    public function get( DBResponse $oResponse )
    {
        $nID    = Params::get("nID"     , 0);

        if( !empty( $nID ) )
        {
            $oSignalZone = new DBSignalZones();

            $aSignalZone = $oSignalZone->getRecord( $nID );

            $oResponse->setFormElement( 'form1', 'nIDObject', array( 'value' => $aSignalZone['id_object'] ) );
            $oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aSignalZone['name'] ) );
            $oResponse->setFormElement( 'form1', 'nZone', array( 'value' => $aSignalZone['zone'] ) );
        }

        $oResponse->printResponse();
    }



    public function save( DBResponse $oResponse )
    {
        $nIDObj = Params::get( "nIDObject" , 	0);
        $sName  = Params::get( "sName" );
        $nZone  = Params::get( "nZone" );

        if( empty( $sName ) )
            throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );

        if( empty( $nZone ) )
            throw new Exception( "Въведете номер на зона!", DBAPI_ERR_INVALID_PARAM );

        $aData = array();
        $aData['id']   = Params::get( 'nID', 0 );
        $aData['id_object'] = $nIDObj;
        $aData['name'] = $sName;
        $aData['zone'] = $nZone;

        $oSignalZone = new DBSignalZones();
        $oSignalZone->update( $aData );
    }
}

?>