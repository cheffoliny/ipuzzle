<?php

class ApiSetSetupObjectUser
{

    public function get( DBResponse $oResponse )
    {
        $nID    = Params::get("nID"     , 0);

        if( !empty( $nID ) )
        {
            $oSignalUser = new DBSignalUsers();

            $aSignalUser = $oSignalUser->getRecord( $nID );

            $oResponse->setFormElement( 'form1', 'nIDObject', array( 'value' => $aSignalUser['id_object'] ) );
            $oResponse->setFormElement( 'form1', 'sName'    , array( 'value' => $aSignalUser['name']      ) );
            $oResponse->setFormElement( 'form1', 'nUser'  , array( 'value' => $aSignalUser['user']    ) );
        }

        $oResponse->printResponse();
    }



    public function save( DBResponse $oResponse )
    {
        $nIDObj = Params::get( "nIDObject" , 	0);
        $sName  = Params::get( "sName" );
        $nUser  = Params::get( "nUser" );

        if( empty( $sName ) )
            throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );

        if( empty( $nUser ) )
            throw new Exception( "Въведете номер на зона!", DBAPI_ERR_INVALID_PARAM );

        $aData = array();
        $aData['id']   = Params::get( 'nID', 0 );
        $aData['id_object'] = $nIDObj;
        $aData['name']   = $sName;
        $aData['user'] = $nUser;

        $oSignalUser = new DBSignalUsers();
        $oSignalUser->update( $aData );
    }
}

?>