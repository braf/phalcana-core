<?php

namespace Phalcana;

use Phalcana\Phalcana;

/**
 * A port of [phputf8](http://phputf8.sourceforge.net/) to a unified set
 * of files. Provides multi-byte aware replacement string functions.
 *
 * For UTF-8 support to work correctly, the following requirements must be met:
 *
 * - PCRE needs to be compiled with UTF-8 support (--enable-utf8)
 * - Support for [Unicode properties](http://php.net/manual/reference.pcre.pattern.modifiers.php)
 *   is highly recommended (--enable-unicode-properties)
 * - The [mbstring extension](http://php.net/mbstring) is highly recommended,
 *   but must not be overloading string functions
 *
 * [!!] This file is licensed differently from the rest of Kohana. As a port of
 * [phputf8](http://phputf8.sourceforge.net/), this file is released under the LGPL.
 *
 * @package    Phalcana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class UTF8
{

    /**
     * @var  boolean  Does the server support UTF-8 natively?
     */
    public static $server_utf8 = null;

    /**
     * @var  array  List of called methods that have had their required file included.
     */
    public static $called = array();

    /**
     * Recursively cleans arrays, objects, and strings. Removes ASCII control
     * codes and converts to the requested charset while silently discarding
     * incompatible characters.
     *
     *     UTF8::clean($_GET); // Clean GET data
     *
     * @param   mixed   $var        variable to clean
     * @param   string  $charset    character set, defaults to Phalcana\Phalcana::$charset
     * @return  mixed
     * @uses    UTF8::clean
     * @uses    UTF8::stripAsciiCtrl
     * @uses    UTF8::isAscii
     */
    public static function clean($var, $charset = null)
    {
        if (! $charset) {
            // Use the application character set
            $charset = Phalcana::$charset;
        }

        if (is_array($var) || is_object($var)) {
            foreach ($var as $key => $val) {
                // Recursion!
                $var[UTF8::clean($key)] = UTF8::clean($val);
            }
        } elseif (is_string($var) && $var !== '') {
            // Remove control characters
            $var = UTF8::stripAsciiCtrl($var);

            if (! UTF8::isAscii($var)) {
                // Temporarily save the mb_substitute_character() value into a variable
                $mb_substitute_character = mb_substitute_character();

                // Disable substituting illegal characters with the default '?' character
                mb_substitute_character('none');

                // convert encoding, this is expensive, used when $var is not ASCII
                $var = mb_convert_encoding($var, $charset, $charset);

                // Reset mb_substitute_character() value back to the original setting
                mb_substitute_character($mb_substitute_character);
            }
        }

        return $var;
    }

    /**
     * Tests whether a string contains only 7-bit ASCII bytes. This is used to
     * determine when to use native functions or UTF-8 functions.
     *
     *     $ascii = UTF8::isAscii($str);
     *
     * @param   mixed   $str    string or array of strings to check
     * @return  boolean
     */
    public static function isAscii($str)
    {
        if (is_array($str)) {
            $str = implode($str);
        }

        return ! preg_match('/[^\x00-\x7F]/S', $str);
    }

    /**
     * Strips out device control codes in the ASCII range.
     *
     *     $str = UTF8::stripAsciiCtrl($str);
     *
     * @param   string  $str    string to clean
     * @return  string
     */
    public static function stripAsciiCtrl($str)
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
    }

    /**
     * Strips out all non-7bit ASCII bytes.
     *
     *     $str = UTF8::stripNonAscii($str);
     *
     * @param   string  $str    string to clean
     * @return  string
     */
    public static function stripNonAscii($str)
    {
        return preg_replace('/[^\x00-\x7F]+/S', '', $str);
    }

    /**
     * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents".
     *
     *     $ascii = UTF8::transliterateToAscii($utf8);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str    string to transliterate
     * @param   integer $case   -1 lowercase only, +1 uppercase only, 0 both cases
     * @return  string
     */
    public static function transliterateToAscii($str, $case = 0)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _transliterateToAscii($str, $case);
    }

    /**
     * Returns the length of the given string. This is a UTF8-aware version
     * of [strlen](http://php.net/strlen).
     *
     *     $length = UTF8::strlen($str);
     *
     * @param   string  $str    string being measured for length
     * @return  integer
     * @uses    UTF8::$server_utf8
     * @uses    Phalcana\Phalcana::$charset
     */
    public static function strlen($str)
    {
        if (UTF8::$server_utf8) {
            return mb_strlen($str, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strlen($str);
    }

    /**
     * Finds position of first occurrence of a UTF-8 string. This is a
     * UTF8-aware version of [strpos](http://php.net/strpos).
     *
     *     $position = UTF8::strpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    haystack
     * @param   string  $search needle
     * @param   integer $offset offset from which character in haystack to start searching
     * @return  integer position of needle
     * @return  boolean false if the needle is not found
     * @uses    UTF8::$server_utf8
     * @uses    Phalcana\Phalcana::$charset
     */
    public static function strpos($str, $search, $offset = 0)
    {
        if (UTF8::$server_utf8) {
            return mb_strpos($str, $search, $offset, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strpos($str, $search, $offset);
    }

    /**
     * Finds position of last occurrence of a char in a UTF-8 string. This is
     * a UTF8-aware version of [strrpos](http://php.net/strrpos).
     *
     *     $position = UTF8::strrpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    haystack
     * @param   string  $search needle
     * @param   integer $offset offset from which character in haystack to start searching
     * @return  integer position of needle
     * @return  boolean false if the needle is not found
     * @uses    UTF8::$server_utf8
     */
    public static function strrpos($str, $search, $offset = 0)
    {
        if (UTF8::$server_utf8) {
            return mb_strrpos($str, $search, $offset, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strrpos($str, $search, $offset);
    }

    /**
     * Returns part of a UTF-8 string. This is a UTF8-aware version
     * of [substr](http://php.net/substr).
     *
     *     $sub = UTF8::substr($str, $offset);
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string  $str    input string
     * @param   integer $offset offset
     * @param   integer $length length limit
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Phalcana\Phalcana::$charset
     */
    public static function substr($str, $offset, $length = null)
    {
        if (UTF8::$server_utf8) {
            return ($length === null)
                ? mb_substr($str, $offset, mb_strlen($str), Phalcana::$charset)
                : mb_substr($str, $offset, $length, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _substr($str, $offset, $length);
    }

    /**
     * Replaces text within a portion of a UTF-8 string. This is a UTF8-aware
     * version of [substrReplace](http://php.net/substr_replace).
     *
     *     $str = UTF8::substrReplace($str, $replacement, $offset);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str            input string
     * @param   string  $replacement    replacement string
     * @param   integer $offset         offset
     * @return  string
     */
    public static function substrReplace($str, $replacement, $offset, $length = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _substrReplace($str, $replacement, $offset, $length);
    }

    /**
     * Makes a UTF-8 string lowercase. This is a UTF8-aware version
     * of [strtolower](http://php.net/strtolower).
     *
     *     $str = UTF8::strtolower($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Phalcana\Phalcana::$charset
     */
    public static function strtolower($str)
    {
        if (UTF8::$server_utf8) {
            return mb_strtolower($str, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strtolower($str);
    }

    /**
     * Makes a UTF-8 string uppercase. This is a UTF8-aware version
     * of [strtoupper](http://php.net/strtoupper).
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Phalcana\Phalcana::$charset
     */
    public static function strtoupper($str)
    {
        if (UTF8::$server_utf8) {
            return mb_strtoupper($str, Phalcana::$charset);
        }

        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strtoupper($str);
    }

    /**
     * Makes a UTF-8 string's first character uppercase. This is a UTF8-aware
     * version of [ucfirst](http://php.net/ucfirst).
     *
     *     $str = UTF8::ucfirst($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str mixed case string
     * @return  string
     */
    public static function ucfirst($str)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ucfirst($str);
    }

    /**
     * Makes the first character of every word in a UTF-8 string uppercase.
     * This is a UTF8-aware version of [ucwords](http://php.net/ucwords).
     *
     *     $str = UTF8::ucwords($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str mixed case string
     * @return  string
     */
    public static function ucwords($str)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ucwords($str);
    }

    /**
     * Case-insensitive UTF-8 string comparison. This is a UTF8-aware version
     * of [strcasecmp](http://php.net/strcasecmp).
     *
     *     $compare = UTF8::strcasecmp($str1, $str2);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str1   string to compare
     * @param   string  $str2   string to compare
     * @return  integer less than 0 if str1 is less than str2
     * @return  integer greater than 0 if str1 is greater than str2
     * @return  integer 0 if they are equal
     */
    public static function strcasecmp($str1, $str2)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strcasecmp($str1, $str2);
    }

    /**
     * Returns a string or an array with all occurrences of search in subject
     * (ignoring case) and replaced with the given replace value. This is a
     * UTF8-aware version of [strIreplace](http://php.net/str_ireplace).
     *
     * [!!] This function is very slow compared to the native version. Avoid
     * using it when possible.
     *
     * @author  Harry Fuecks <hfuecks@gmail.com
     * @param   string|array    $search     text to replace
     * @param   string|array    $replace    replacement text
     * @param   string|array    $str        subject text
     * @param   integer         $count      number of matched and replaced needles will be returned via this parameter which is passed by reference
     * @return  string  if the input was a string
     * @return  array   if the input was an array
     */
    public static function strIreplace($search, $replace, $str, & $count = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strIreplace($search, $replace, $str, $count);
    }

    /**
     * Case-insensitive UTF-8 version of strstr. Returns all of input string
     * from the first occurrence of needle to the end. This is a UTF8-aware
     * version of [stristr](http://php.net/stristr).
     *
     *     $found = UTF8::stristr($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $search needle
     * @return  string  matched substring if found
     * @return  false   if the substring was not found
     */
    public static function stristr($str, $search)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _stristr($str, $search);
    }

    /**
     * Finds the length of the initial segment matching mask. This is a
     * UTF8-aware version of [strspn](http://php.net/strspn).
     *
     *     $found = UTF8::strspn($str, $mask);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $mask   mask for search
     * @param   integer $offset start position of the string to examine
     * @param   integer $length length of the string to examine
     * @return  integer length of the initial segment that contains characters in the mask
     */
    public static function strspn($str, $mask, $offset = null, $length = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strspn($str, $mask, $offset, $length);
    }

    /**
     * Finds the length of the initial segment not matching mask. This is a
     * UTF8-aware version of [strcspn](http://php.net/strcspn).
     *
     *     $found = UTF8::strcspn($str, $mask);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $mask   mask for search
     * @param   integer $offset start position of the string to examine
     * @param   integer $length length of the string to examine
     * @return  integer length of the initial segment that contains characters not in the mask
     */
    public static function strcspn($str, $mask, $offset = null, $length = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strcspn($str, $mask, $offset, $length);
    }

    /**
     * Pads a UTF-8 string to a certain length with another string. This is a
     * UTF8-aware version of [str_pad](http://php.net/str_pad).
     *
     *     $str = UTF8::strPad($str, $length);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str                input string
     * @param   integer $final_str_length   desired string length after padding
     * @param   string  $pad_str            string to use as padding
     * @param   string  $pad_type           padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
     * @return  string
     */
    public static function strPad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strPad($str, $final_str_length, $pad_str, $pad_type);
    }

    /**
     * Converts a UTF-8 string to an array. This is a UTF8-aware version of
     * [str_split](http://php.net/str_plit).
     *
     *     $array = UTF8::strSplit($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str            input string
     * @param   integer $split_length   maximum length of each chunk
     * @return  array
     */
    public static function strSplit($str, $split_length = 1)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strSplit($str, $split_length);
    }

    /**
     * Reverses a UTF-8 string. This is a UTF8-aware version of [strrev](http://php.net/strrev).
     *
     *     $str = UTF8::strrev($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str string to be reversed
     * @return  string
     */
    public static function strrev($str)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strrev($str);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the beginning and
     * end of a string. This is a UTF8-aware version of [trim](http://php.net/trim).
     *
     *     $str = UTF8::trim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function trim($str, $charlist = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _trim($str, $charlist);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the beginning of
     * a string. This is a UTF8-aware version of [ltrim](http://php.net/ltrim).
     *
     *     $str = UTF8::ltrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function ltrim($str, $charlist = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ltrim($str, $charlist);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the end of a string.
     * This is a UTF8-aware version of [rtrim](http://php.net/rtrim).
     *
     *     $str = UTF8::rtrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function rtrim($str, $charlist = null)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _rtrim($str, $charlist);
    }

    /**
     * Returns the unicode ordinal for a character. This is a UTF8-aware
     * version of [ord](http://php.net/ord).
     *
     *     $digit = UTF8::ord($character);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $chr    UTF-8 encoded character
     * @return  integer
     */
    public static function ord($chr)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ord($chr);
    }

    /**
     * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
     * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
     * Occurrences of the BOM are ignored. Surrogates are not allowed.
     *
     *     $array = UTF8::toUnicode($str);
     *
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
     *
     * @param   string  $str    UTF-8 encoded string
     * @return  array   unicode code points
     * @return  false   if the string is invalid
     */
    public static function toUnicode($str)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _toUnicode($str);
    }

    /**
     * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
     * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
     * Occurrences of the BOM are ignored. Surrogates are not allowed.
     *
     *     $str = UTF8::toUnicode($array);
     *
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
     *
     * @param   array   $str    unicode code points representing a string
     * @return  string  utf8 string of characters
     * @return  boolean false if a code point cannot be found
     */
    public static function fromUnicode($arr)
    {
        if (! isset(UTF8::$called[__FUNCTION__])) {
            require Phalcana::$di->get('fs')->findFile('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _fromUnicode($arr);
    }
}


if (UTF8::$server_utf8 === null) {
    // Determine if this server supports UTF-8 natively
    UTF8::$server_utf8 = extension_loaded('mbstring');
}
