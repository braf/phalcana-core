<?php defined('SYSPATH') || die('No direct script access.');
/**
 * UTF8::substrReplace
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _substrReplace($str, $replacement, $offset, $length = null)
{
    if (\Phalcana\UTF8::isAscii($str)) {
        return ($length === null) ? substrReplace($str, $replacement, $offset) : substrReplace($str, $replacement, $offset, $length);
    }

    $length = ($length === null) ? \Phalcana\UTF8::strlen($str) : (int) $length;
    preg_match_all('/./us', $str, $str_array);
    preg_match_all('/./us', $replacement, $replacement_array);

    array_splice($str_array[0], $offset, $length, $replacement_array[0]);
    return implode('', $str_array[0]);
}
