<?php

namespace Phalcana;

/**
 * File helper class.
 *
 * @package    Phalcana
 * @category   Helpers
 * @author     Kohana Team
 * @author     Neil Brayfield
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class File extends \Phalcon\Di\Injectable
{


    /**
     * Return the mime type of an extension.
     *
     *     $mime = $file->mimeByExt('png'); // "image/png"
     *
     * @param   string  $extension  php, pdf, txt, etc
     * @return  string  mime type on success
     * @return  false   on failure
     */
    public static function mimeByExt($extension)
    {
        // Load all of the mime types
        $mimes = \Phalcana\Phalcana::$di->get('config')->load('mimes')->toArray();

        return isset($mimes[$extension]) ? $mimes[$extension][0] : false;
    }

    /**
     * Lookup MIME types for a file
     *
     * @see File:mimeByExt()
     * @param string $extension Extension to lookup
     * @return array Array of MIMEs associated with the specified extension
     */
    public static function mimesByExt($extension)
    {
        // Load all of the mime types
        $mimes = \Phalcana\Phalcana::$di->get('config')->load('mimes')->toArray();

        return isset($mimes[$extension]) ? ( (array) $mimes[$extension]) : array();
    }

    /**
     * Lookup file extensions by MIME type
     *
     * @param   string  $type File MIME type
     * @return  array   File extensions matching MIME type
     */
    public static function extsByMime($type)
    {
        static $types = array();

        // Fill the array
        if (empty($types)) {
            foreach (\Phalcana\Phalcana::$di->get('config')->load('mimes')->toArray() as $ext => $mimes) {
                foreach ($mimes as $mime) {
                    if ($mime == 'application/octet-stream') {
                        // octet-stream is a generic binary
                        continue;
                    }

                    if (! isset($types[$mime])) {
                        $types[$mime] = array( (string) $ext);
                    } elseif (! in_array($ext, $types[$mime])) {
                        $types[$mime][] = (string) $ext;
                    }
                }
            }
        }

        return isset($types[$type]) ? $types[$type] : false;
    }

    /**
     * Lookup a single file extension by MIME type.
     *
     * @param   string  $type  MIME type to lookup
     * @return  mixed          First file extension matching or false
     */
    public static function extByMime($type)
    {
        return current($this->extsByMime($type));
    }

    /**
     * Split a file into pieces matching a specific size. Used when you need to
     * split large files into smaller pieces for easy transmission.
     *
     *     $count = $file->split($file);
     *
     * @param   string  $filename   file to be split
     * @param   integer $piece_size size, in MB, for each piece to be
     * @return  integer The number of pieces that were created
     */
    public static function split($filename, $piece_size = 10)
    {
        // Open the input file
        $file = fopen($filename, 'rb');

        // Change the piece size to bytes
        $piece_size = floor($piece_size * 1024 * 1024);

        // Write files in 8k blocks
        $block_size = 1024 * 8;

        // Total number of pieces
        $pieces = 0;

        while (! feof($file)) {
            // Create another piece
            $pieces += 1;

            // Create a new file piece
            $piece = strPad($pieces, 3, '0', STR_PAD_LEFT);
            $piece = fopen($filename.'.'.$piece, 'wb+');

            // Number of bytes read
            $read = 0;

            do {
                // Transfer the data in blocks
                fwrite($piece, fread($file, $block_size));

                // Another block has been read
                $read += $block_size;

            } while ($read < $piece_size);

            // Close the piece
            fclose($piece);
        }

        // Close the file
        fclose($file);

        return $pieces;
    }

    /**
     * Join a split file into a whole file. Does the reverse of [$file->split].
     *
     *     $count = $file->join($file);
     *
     * @param   string  $filename   split filename, without .000 extension
     * @return  integer The number of pieces that were joined.
     */
    public static function join($filename)
    {
        // Open the file
        $file = fopen($filename, 'wb+');

        // Read files in 8k blocks
        $block_size = 1024 * 8;

        // Total number of pieces
        $pieces = 0;

        while (is_file($piece = $filename.'.'.strPad($pieces + 1, 3, '0', STR_PAD_LEFT))) {
            // Read another piece
            $pieces += 1;

            // Open the piece for reading
            $piece = fopen($piece, 'rb');

            while (! feof($piece)) {
                // Transfer the data in blocks
                fwrite($file, fread($piece, $block_size));
            }

            // Close the piece
            fclose($piece);
        }

        return $pieces;
    }
}
