<?php
class DBException extends Exception {
	public function __construct($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection){
		parent::__construct('sql err');
	}
}