<?php

    $nID = isset($_GET['nID'])? (int)$_GET['nID'] : 0;

    $template->assign('nID',$nID);