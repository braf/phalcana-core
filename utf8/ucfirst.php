<?php defined('SYSPATH') || die('No direct script access.');
/**
 * \Phalcana\UTF8::ucfirst
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ucfirst($str)
{
    if (\Phalcana\UTF8::isAscii($str)) {
        return ucfirst($str);
    }

    preg_match('/^(.?)(.*)$/us', $str, $matches);
    return \Phalcana\UTF8::strtoupper($matches[1]).$matches[2];
}
