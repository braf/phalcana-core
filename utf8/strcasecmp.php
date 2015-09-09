<?php defined('SYSPATH') || die('No direct script access.');
/**
 * UTF8::strcasecmp
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strcasecmp($str1, $str2)
{
    if (\Phalcana\UTF8::isAscii($str1) && \Phalcana\UTF8::isAscii($str2)) {
        return strcasecmp($str1, $str2);
    }

    $str1 = \Phalcana\UTF8::strtolower($str1);
    $str2 = \Phalcana\UTF8::strtolower($str2);
    return strcmp($str1, $str2);
}
