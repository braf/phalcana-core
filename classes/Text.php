<?php

namespace Phalcana;

use Phalcana\UTF8;

/**
 * Text helper class. Provides simple methods for working with text.
 *
 * @package    Phalcana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Text extends \Phalcon\Di\Injectable
{

    /**
     * @var  array   number units and text equivalents
     */
    public static $units = array(
        1000000000 => 'billion',
        1000000    => 'million',
        1000       => 'thousand',
        100        => 'hundred',
        90 => 'ninety',
        80 => 'eighty',
        70 => 'seventy',
        60 => 'sixty',
        50 => 'fifty',
        40 => 'fourty',
        30 => 'thirty',
        20 => 'twenty',
        19 => 'nineteen',
        18 => 'eighteen',
        17 => 'seventeen',
        16 => 'sixteen',
        15 => 'fifteen',
        14 => 'fourteen',
        13 => 'thirteen',
        12 => 'twelve',
        11 => 'eleven',
        10 => 'ten',
        9  => 'nine',
        8  => 'eight',
        7  => 'seven',
        6  => 'six',
        5  => 'five',
        4  => 'four',
        3  => 'three',
        2  => 'two',
        1  => 'one',
    );


    /**
     * Convert a phrase to a URL-safe title.
     *
     *     echo URL::title('My Blog Post'); // "my-blog-post"
     *
     * @param   string  $title       Phrase to convert
     * @param   string  $separators  Word separator (any single character)
     * @param   bool    $ascii_only  Transliterate to ASCII?
     * @return  string
     */
    public static function title($title, $separator = '-', $ascii_only = FALSE)
    {
        if ($ascii_only === TRUE)
        {
            // Transliterate non-ASCII characters
            $title = UTF8::transliterate_to_ascii($title);

            // Remove all characters that are not the separator, a-z, 0-9, or whitespace
            $title = preg_replace('![^'.preg_quote($separator).'a-z0-9\s]+!', '', strtolower($title));
        }
        else
        {
            // Remove all characters that are not the separator, letters, numbers, or whitespace
            $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', UTF8::strtolower($title));
        }

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        // Trim separators from the beginning and end
        return trim($title, $separator);
    }

    /**
     * Limits a phrase to a given number of words.
     *
     *     $text = Text::limitWords($text);
     *
     * @param   string  $str        phrase to limit words of
     * @param   integer $limit      number of words to limit to
     * @param   string  $end_char   end character or entity
     * @return  string
     */
    public static function limitWords($str, $limit = 100, $end_char = null)
    {
        $limit = (int) $limit;
        $end_char = ($end_char === null) ? '…' : $end_char;

        if (trim($str) === '') {
            return $str;
        }

        if ($limit <= 0) {
            return $end_char;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

        // Only attach the end character if the matched string is shorter
        // than the starting string.
        return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
    }

    /**
     * Limits a phrase to a given number of characters.
     *
     *     $text = Text::limitChars($text);
     *
     * @param   string  $str            phrase to limit characters of
     * @param   integer $limit          number of characters to limit to
     * @param   string  $end_char       end character or entity
     * @param   boolean $preserve_words enable or disable the preservation of words while limiting
     * @return  string
     * @uses    UTF8::strlen
     */
    public static function limitChars($str, $limit = 100, $end_char = null, $preserve_words = false)
    {
        $end_char = ($end_char === null) ? '…' : $end_char;

        $limit = (int) $limit;

        if (trim($str) === '' || UTF8::strlen($str) <= $limit) {
            return $str;
        }

        if ($limit <= 0) {
            return $end_char;
        }

        if ($preserve_words === false) {
            return rtrim(UTF8::substr($str, 0, $limit)).$end_char;
        }

        // Don't preserve words. The limit is considered the top limit.
        // No strings with a length longer than $limit should be returned.
        if (! preg_match('/^.{0,'.$limit.'}\s/us', $str, $matches)) {
            return $end_char;
        }

        return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
    }

    /**
     * Alternates between two or more strings.
     *
     *     echo Text::alternate('one', 'two'); // "one"
     *     echo Text::alternate('one', 'two'); // "two"
     *     echo Text::alternate('one', 'two'); // "one"
     *
     * Note that using multiple iterations of different strings may produce
     * unexpected results.
     *
     * @param   string  $str,...    strings to alternate between
     * @return  string
     */
    public static function alternate()
    {
        static $i;

        if (func_num_args() === 0) {
            $i = 0;
            return '';
        }

        $args = func_get_args();
        return $args[($i++ % count($args))];
    }

    /**
     * Generates a random string of a given type and length.
     *
     *
     *     $str = Text::random(); // 8 character random string
     *
     * The following types are supported:
     *
     * alnum
     * :  Upper and lower case a-z, 0-9 (default)
     *
     * alpha
     * :  Upper and lower case a-z
     *
     * hexdec
     * :  Hexadecimal characters a-f, 0-9
     *
     * distinct
     * :  Uppercase characters and numbers that cannot be confused
     *
     * You can also create a custom type by providing the "pool" of characters
     * as the type.
     *
     * @param   string  $type   a type of pool, or a string of characters to use as the pool
     * @param   integer $length length of string to return
     * @return  string
     * @uses    UTF8::split
     */
    public static function random($type = null, $length = 8)
    {
        if ($type === null) {
            // Default is to generate an alphanumeric string
            $type = 'alnum';
        }

        $utf8 = false;

        switch ($type)
        {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string) $type;
                $utf8 = ! UTF8::isAscii($pool);
                break;
        }

        // Split the pool into an array of characters
        $pool = ($utf8 === true) ? UTF8::strSplit($pool, 1) : str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' && $length > 1) {
            if (ctype_alpha($str)) {
                // Add a random digit
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            } elseif (ctype_digit($str)) {
                // Add a random letter
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    /**
     * Uppercase words that are not separated by spaces, using a custom
     * delimiter or the default.
     *
     *      $str = Text::ucfirst('content-type'); // returns "Content-Type"
     *
     * @param   string  $string     string to transform
     * @param   string  $delimiter  delimiter to use
     * @uses    UTF8::ucfirst
     * @return  string
     */
    public static function ucfirst($string, $delimiter = '-')
    {
        // Put the keys back the Case-Convention expected
        return implode($delimiter, array_map('Phalcana\UTF8::ucfirst', explode($delimiter, $string)));
    }

    /**
     * Reduces multiple slashes in a string to single slashes.
     *
     *     $str = Text::reduceSlashes('foo//bar/baz'); // "foo/bar/baz"
     *
     * @param   string  $str    string to reduce slashes of
     * @return  string
     */
    public static function reduceSlashes($str)
    {
        return preg_replace('#(?<!:)//+#', '/', $str);
    }

    /**
     * Replaces the given words with a string.
     *
     *     // Displays "What the #####, man!"
     *     echo Text::censor('What the frick, man!', array(
     *         'frick' => '#####',
     *     ));
     *
     * @param   string  $str                    phrase to replace words in
     * @param   array   $badwords               words to replace
     * @param   string  $replacement            replacement string
     * @param   boolean $replace_partial_words  replace words across word boundaries (space, period, etc)
     * @return  string
     * @uses    UTF8::strlen
     */
    public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = true)
    {
        foreach ((array) $badwords as $key => $badword) {
            $badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
        }

        $regex = '('.implode('|', $badwords).')';

        if ($replace_partial_words === false) {
            // Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
            $regex = '(?<=\b|\s|^)'.$regex.'(?=\b|\s|$)';
        }

        $regex = '!'.$regex.'!ui';

        // if $replacement is a single character: replace each of the characters of the badword with $replacement
        if (UTF8::strlen($replacement) == 1) {
            return preg_replace_callback($regex, function ($matches) use ($replacement) {
                return str_repeat($replacement, UTF8::strlen($matches[1]));
            }, $str);
        }

        // if $replacement is not a single character, fully replace the badword with $replacement
        return preg_replace($regex, $replacement, $str);
    }

    /**
     * Finds the text that is similar between a set of words.
     *
     *     $match = Text::similar(array('fred', 'fran', 'free'); // "fr"
     *
     * @param   array   $words  words to find similar text of
     * @return  string
     */
    public static function similar(array $words)
    {
        // First word is the word to match against
        $word = current($words);

        for ($i = 0, $max = strlen($word); $i < $max; ++$i) {
            foreach ($words as $w) {
                // Once a difference is found, break out of the loops
                if (! isset($w[$i]) || $w[$i] !== $word[$i]) {
                    break 2;
                }
            }
        }

        // Return the similar text
        return substr($word, 0, $i);
    }

    /**
     * Converts text email addresses and anchors into links. Existing links
     * will not be altered.
     *
     *     echo Text::autoLink($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string  $text   text to auto link
     * @return  string
     * @uses    Text::autoLinkUrls
     * @uses    Text::autoLinkEmails
     */
    public static function autoLink($text)
    {
        // Auto link emails first to prevent problems with "www.domain.com@example.com"
        return Text::autoLinkUrls(Text::autoLinkEmails($text));
    }

    /**
     * Converts text anchors into links. Existing links will not be altered.
     *
     *     echo Text::autoLinkUrls($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string  $text   text to auto link
     * @return  string
     * @uses    Phalcon\Tag::linkTo
     */
    public static function autoLinkUrls($text)
    {
        // Find and replace all http/https/ftp/ftps links that are not part of an existing html anchor
        $text = preg_replace_callback('~\b(?<!href="|">)(?:ht|f)tps?://[^<\s]+(?:/|\b)~i', 'Phalcana\Text::autoLinkUrlsCallback1', $text);

        // Find and replace all naked www.links.com (without http://)
        return preg_replace_callback('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}[^<\s]*\b~i', 'Phalcana\Text::autoLinkUrlsCallback2', $text);
    }

    /**
     * Callback to link urls that have scheme
     *
     * @return  string
     **/
    protected static function autoLinkUrlsCallback1($matches)
    {
        return \Phalcon\Tag::linkTo($matches[0], $matches[0], false);
    }

    /**
     * Callback to link without scheme
     *
     * @return  string
     **/
    protected static function autoLinkUrlsCallback2($matches)
    {
        return \Phalcon\Tag::linkTo('http://'.$matches[0], $matches[0], false);
    }

    /**
     * Converts text email addresses into links. Existing links will not
     * be altered.
     *
     *     echo Text::autoLinkEmails($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string  $text   text to auto link
     * @return  string
     */
    public static function autoLinkEmails($text)
    {
        // Find and replace all email addresses that are not part of an existing html mailto anchor
        // Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
        //       The html entity for a colon (:) is &#58; or &#058; or &#0058; etc.
        return preg_replace_callback('~\b(?<!href="mailto:|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b(?!</a>)~i', 'Phalcana\Text::autoLinkEmailsCallback', $text);
    }

    /**
     * Callback to add mailto link
     *
     * @return  string
     **/
    protected static function autoLinkEmailsCallback($matches)
    {
        return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$matches[0].'">'.$matches[0].'</a>';
    }

    /**
     * Automatically applies "p" and "br" markup to text.
     * Basically [nl2br](http://php.net/nl2br) on steroids.
     *
     *     echo Text::autoP($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string  $str    subject
     * @param   boolean $br     convert single linebreaks to <br />
     * @return  string
     */
    public static function autoP($str, $br = true)
    {
        // Trim whitespace
        if (($str = trim($str)) === '') {
            return '';
        }

        // Standardize newlines
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // Trim whitespace on each line
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found = (strpos($str, '<') !== false)) {
            // Elements that should not be surrounded by p tags
            $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

            // Put at least two linebreaks before and after $no_p elements
            $str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
            $str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
        }

        // Do the <p> magic!
        $str = '<p>'.trim($str).'</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found !== false) {
            // Remove p tags around $no_p elements
            $str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
        }

        // Convert single linebreaks to <br />
        if ($br === true) {
            $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
        }

        return $str;
    }

    /**
     * Returns human readable sizes. Based on original functions written by
     * [Aidan Lister](http://aidanlister.com/repos/v/function.size_readable.php)
     * and [Quentin Zervaas](http://www.phpriot.com/d/code/strings/filesize-format/).
     *
     *     echo Text::bytes(filesize($file));
     *
     * @param   integer $bytes      size in bytes
     * @param   string  $force_unit a definitive unit
     * @param   string  $format     the return string format
     * @param   boolean $si         whether to use SI prefixes or IEC
     * @return  string
     */
    public static function bytes($bytes, $force_unit = null, $format = null, $si = true)
    {
        // Format string
        $format = ($format === null) ? '%01.2f %s' : (string) $format;

        // IEC prefixes (binary)
        if ($si == false || strpos($force_unit, 'i') !== false) {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        } else {
        // SI prefixes (decimal)
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }

        // Determine unit to use
        if (($power = array_search((string) $force_unit, $units)) === false) {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

    /**
     * Format a number to human-readable text.
     *
     *     // Display: one thousand and twenty-four
     *     echo Text::number(1024);
     *
     *     // Display: five million, six hundred and thirty-two
     *     echo Text::number(5000632);
     *
     * @param   integer $number number to format
     * @return  string
     * @since   3.0.8
     */
    public static function number($number)
    {
        // The number must always be an integer
        $number = (int) $number;

        // Uncompiled text version
        $text = array();

        // Last matched unit within the loop
        $last_unit = null;

        // The last matched item within the loop
        $last_item = '';

        foreach (Text::$units as $unit => $name) {
            if ($number / $unit >= 1) {
                // $value = the number of times the number is divisible by unit
                $number -= $unit * ($value = (int) floor($number / $unit));
                // Temporary var for textifying the current unit
                $item = '';

                if ($unit < 100) {
                    if ($last_unit < 100 && $last_unit >= 20) {
                        $last_item .= '-'.$name;
                    } else {
                        $item = $name;
                    }
                } else {
                    $item = Text::number($value).' '.$name;
                }

                // In the situation that we need to make a composite number (i.e. twenty-three)
                // then we need to modify the previous entry
                if (empty($item)) {
                    array_pop($text);

                    $item = $last_item;
                }

                $last_item = $text[] = $item;
                $last_unit = $unit;
            }
        }

        if (count($text) > 1) {
            $and = array_pop($text);
        }

        $text = implode(', ', $text);

        if (isset($and)) {
            $text .= ' and '.$and;
        }

        return $text;
    }

    /**
     * Prevents [widow words](http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin)
     * by inserting a non-breaking space between the last two words.
     *
     *     echo Text::widont($text);
     *
     * regex courtesy of the Typogrify project
     * @link http://code.google.com/p/typogrify/
     *
     * @param   string  $str    text to remove widows from
     * @return  string
     */
    public static function widont($str)
    {
        // use '%' as delimiter and 'x' as modifier
        $widont_regex = "%
            ((?:</?(?:a|em|span|strong|i|b)[^>]*>)|[^<>\s]) # must be proceeded by an approved inline opening or closing tag or a nontag/nonspace
            \s+                                             # the space to replace
            ([^<>\s]+                                       # must be flollowed by non-tag non-space characters
            \s*                                             # optional white space!
            (</(a|em|span|strong|i|b)>\s*)*                 # optional closing inline tags with optional white space after each
            ((</(p|h[1-6]|li|dt|dd)>)|$))                   # end with a closing p, h1-6, li or the end of the string
        %x";
        return preg_replace($widont_regex, '$1&nbsp;$2', $str);
    }

    /**
     * Returns information about the client user agent.
     *
     *     // Returns "Chrome" when using Google Chrome
     *     $browser = Text::userAgent($agent, 'browser');
     *
     * Multiple values can be returned at once by using an array:
     *
     *     // Get the browser and platform with a single call
     *     $info = Text::userAgent($agent, array('browser', 'platform'));
     *
     * When using an array for the value, an associative array will be returned.
     *
     * @param   string  $agent  userAgent
     * @param   mixed   $value  array or string to return: browser, version, robot, mobile, platform
     * @return  mixed   requested information, false if nothing is found
     * @uses    Kohana::$config
     */
    public static function userAgent($agent, $value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $part) {
                // Add each part to the set
                $data[$part] = Text::userAgent($agent, $part);
            }

            return $data;
        }

        if ($value === 'browser' || $value == 'version') {
            // Extra data will be captured
            $info = array();

            // Load browsers
            $browsers = \Phalcana\Phalcana::$di->get('config')->load('user_agents')->browser;

            foreach ($browsers as $search => $name) {
                if (stripos($agent, $search) !== false) {
                    // Set the browser name
                    $info['browser'] = $name;

                    if (preg_match('#'.preg_quote($search).'[^0-9.]*+([0-9.][0-9.a-z]*)#i', $agent, $matches)) {
                        // Set the version number
                        $info['version'] = $matches[1];
                    } else {
                        // No version number found
                        $info['version'] = false;
                    }

                    return $info[$value];
                }
            }
        } else {
            // Load the search group for this type
            $group = \Phalcana\Phalcana::$di->get('config')->load('user_agents')->$value;

            foreach ($group as $search => $name) {
                if (stripos($agent, $search) !== false) {
                    // Set the value name
                    return $name;
                }
            }
        }

        // The value requested could not be found
        return false;
    }
}
