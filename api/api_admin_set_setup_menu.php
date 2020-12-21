<?php
$oMenu = new DBMenu();

switch ($aParams['api_action']) {
    case "update":
        if( empty($aParams['title']) )
        {
            $oResponse->setError( DBAPI_ERR_INVALID_PARAM, 'Не е посочено име на менюто!' );
            print( $oResponse->toXML() );
            break;
        }

        $oResponse->setError( $oMenu->update($aParams)  );
        print( $oResponse->toXML() );
        break;

    case "after":
        {
            $id = Params::get('parent_id',0);

            $oResponse-> setFormElement('form1', 'menu_order', array(), '');
            $oResponse-> setFormElementChild('form1', 'menu_order', array('value' => '0'), '--- В началото ---');
            $aMenu=array();
            $oMenu->getMenu($aMenu, false);
            foreach ($aMenu as $key => $value)
                if( $value['id'] != $aParams['id']){
                    if ($id == $value['parent_id'])
                        $oResponse-> setFormElementChild('form1', 'menu_order', array_merge(array('value' => $value['menu_order'])), $value['tab_title']);
                }

            print( $oResponse->toXML() );
        }break;

    default :
        $aData = array();
        if( ($nResultOnce = $oMenu->getResultOnce($aParams['id'], $aData)) == DBAPI_ERR_SUCCESS ) {

            $oMenu->getBeforeElement( $nBeforeElement, $aData['menu_order'] );

            $oResponse->setFormElement('form1', 'title', array(), $aData['title']);
            $oResponse->setFormElement('form1', 'filename', array(), $aData['filename']);
            $aMenu=array();
            $oMenu->getMenu($aMenu, false);

            $oResponse-> setFormElement('form1', 'parent_id', array(), '');
            $oResponse-> setFormElementChild('form1', 'parent_id', array('value' => '0'), '--- Главна ---');

            $oResponse-> setFormElement('form1', 'menu_order', array(), '');
            $oResponse-> setFormElementChild('form1', 'menu_order', array('value' => '0'), '--- В началото ---');

            foreach ($aMenu as $key=>$value)
                if( $value['id'] != $aParams['id'])
                {
                    $parent_slected = $aData['parent_id'] == $value['id'] ? array('selected'=>'selected') : array();
                    $before_slected = $nBeforeElement == $value['menu_order'] ? array('selected'=>'selected') : array();

                    $oResponse-> setFormElementChild('form1', 'parent_id', array_merge(array('value' => $value['id']), $parent_slected), $value['tab_title']);

                    if ($aData['parent_id'] == $value['parent_id'])
                        $oResponse-> setFormElementChild('form1', 'menu_order', array_merge(array('value' => $value['menu_order']), $before_slected), $value['tab_title']);
                }

        }
        $oResponse->setError( $nResultOnce );
        print( $oResponse->toXML() );
        break;
}

?>