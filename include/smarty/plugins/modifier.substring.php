<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author Manuel Polacek / Hitflip
 */


/**
 * Smarty regex_replace modifier plugin
 *
 * Type:     modifier<br>
 * Name:     substring
 * Purpose:  substring like in php
 * @param string
 * @return string
 */
function smarty_modifier_substring($sString, $dFirst = 0, $dLast = 0) {
    mb_internal_encoding("UTF-8");
    if($dLast == 0) {
		return mb_substr($sString, $dFirst);
	} else {
		return mb_substr($sString, $dFirst, $dLast);
	}
}
?>