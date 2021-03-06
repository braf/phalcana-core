<?php defined('SYSPATH') || die('No direct script access.');
/**
 * UTF8::strrev
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrev($str)
{
    if (\Phalcana\UTF8::isAscii($str)) {
        return strrev($str);
    }

    preg_match_all('/./us', $str, $matches);
    return implode('', array_reverse($matches[0]));
}
