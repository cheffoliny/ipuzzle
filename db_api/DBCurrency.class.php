<?php

class DBCurrency extends DBBase2 {
	
	public function __construct() {
		global $db_finance;
		//$db_sod->debug=true;			
		parent::__construct($db_finance, 'currency');
	}

    public function lvToEuro($nLv , $nPrecision = 2 , $Mode = PHP_ROUND_HALF_UP ) {
        $aEuro = $this->selectOnce("SELECT * FROM currency WHERE to_arc = 0 AND  sinature = 'EUR'");

        return round($nLv/$aEuro['rate'], 2 , $Mode);
    }

    public function euroFix() {
        return $this->selectOne("SELECT rate FROM currency WHERE to_arc = 0 AND  sinature = 'EUR'");
    }
}
?>