<?php

class ApiSetSetupObjectSector
{

    public function get( DBResponse $oResponse )
    {
        $nID    = Params::get("nID"     , 0);

        if( !empty( $nID ) )
        {
            $oSignalSector = new DBSignalSectors();

            $aSignalSector = $oSignalSector->getRecord( $nID );

            $oResponse->setFormElement( 'form1', 'nIDObject', array( 'value' => $aSignalSector['id_object'] ) );
            $oResponse->setFormElement( 'form1', 'sName'    , array( 'value' => $aSignalSector['name']      ) );
            $oResponse->setFormElement( 'form1', 'nSector'  , array( 'value' => $aSignalSector['sector']    ) );
        }

        $oResponse->printResponse();
    }



    public function save( DBResponse $oResponse )
    {
        $nIDObj = Params::get( "nIDObject" , 	0);
        $sName  = Params::get( "sName" );
        $nSector= Params::get( "nSector" );

        if( empty( $sName ) )
            throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );

        if( empty( $nSector ) )
            throw new Exception( "Въведете номер на зона!", DBAPI_ERR_INVALID_PARAM );

        $aData = array();
        $aData['id']   = Params::get( 'nID', 0 );
        $aData['id_object'] = $nIDObj;
        $aData['name']   = $sName;
        $aData['sector'] = $nSector;

        $oSignalSector = new DBSignalSectors();
        $oSignalSector->update( $aData );
    }
}

?>