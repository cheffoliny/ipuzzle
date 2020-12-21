<?php
    require_once('engine/finance_operations_tabs.php');
    $oDBEasypayProvider = new DBEasypayProvider();

    $aProvider = $oDBEasypayProvider->getAll();

    $template->assign('aProvider',$aProvider);
    $template->assign("view", $view);