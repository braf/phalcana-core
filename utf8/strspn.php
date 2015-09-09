<?php defined('SYSPATH') || die('No direct script access.');
/**
 * UTF8::strspn
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strspn($str, $mask, $offset = null, $length = null)
{
    if ($str == '' || $mask == '') {
        return 0;
    }

    if (\Phalcana\UTF8::isAscii($str) && \Phalcana\UTF8::isAscii($mask)) {
        return ($offset === null) ? strspn($str, $mask) : (($length === null) ? strspn($str, $mask, $offset) : strspn($str, $mask, $offset, $length));
    }

    if ($offset !== null || $length !== null) {
        $str = \Phalcana\UTF8::substr($str, $offset, $length);
    }

    // Escape these characters:  - [ ] . : \ ^ /
    // The . and : are escaped to prevent possible warnings about POSIX regex elements
    $mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
    preg_match('/^[^'.$mask.']+/u', $str, $matches);

    return isset($matches[0]) ? \Phalcana\UTF8::strlen($matches[0]) : 0;
}
