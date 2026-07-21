<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @link http://smarty.php.net/manual/en/language.modifier.date.format.php
 *          date_format (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_date_format_php_pattern($format)
{
    $tokens = array(
        '%' => '%',
        'a' => 'D',
        'A' => 'l',
        'b' => 'M',
        'B' => 'F',
        'd' => 'd',
        'D' => 'm/d/y',
        'e' => 'j',
        'F' => 'Y-m-d',
        'h' => 'M',
        'H' => 'H',
        'I' => 'h',
        'm' => 'm',
        'M' => 'i',
        'p' => 'A',
        'P' => 'a',
        'r' => 'h:i:s A',
        'R' => 'H:i',
        's' => 'U',
        'S' => 's',
        'T' => 'H:i:s',
        'u' => 'N',
        'V' => 'W',
        'w' => 'w',
        'x' => 'm/d/y',
        'X' => 'H:i:s',
        'y' => 'y',
        'Y' => 'Y',
        'z' => 'O',
        'Z' => 'T'
    );
    $phpFormat = '';
    $length = strlen($format);

    for ($index = 0; $index < $length; $index++) {
        $character = $format[$index];

        if ($character === '%' && $index + 1 < $length) {
            $token = $format[++$index];
            $phpFormat .= isset($tokens[$token]) ? $tokens[$token] : '\\%' . $token;
        } else {
            $phpFormat .= ctype_alpha($character) || $character === '\\' ? '\\' . $character : $character;
        }
    }

    return $phpFormat;
}

function smarty_modifier_date_format($string, $format="%b %e, %Y", $default_date=null)
{
    $phpFormat = smarty_date_format_php_pattern($format);

    if($string != '') {
        return date($phpFormat, smarty_make_timestamp($string));
    } elseif (isset($default_date) && $default_date != '') {
        return date($phpFormat, smarty_make_timestamp($default_date));
    } else {
        return;
    }
}

/* vim: set expandtab: */

?>
