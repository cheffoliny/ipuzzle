<?php

class DBEasypayProvider extends DBBase2 {

    public function __construct() {
        global $db_finance;
        //$db_finance->debug=true;
        parent::__construct($db_finance, "easypay_provider");
    }
}
?>