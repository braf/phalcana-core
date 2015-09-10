<?php

namespace Phalcana\Controllers;

use Phalcon\Mvc\View;
use Phalcana\Phalcana;
use Phalcana\Exceptions\HTTP404;
use Phalcana\Exceptions\HTTP304;
use Phalcana\File;

/**
 * Controller for retrieving files through the cascading file system
 *
 * @package     Phalcana
 * @category    Controllers
 * @author      Neil Brayfield
 */
class PublicController extends \Phalcon\Mvc\Controller
{

    /**
     * Initialize
     *
     * @return  void
     **/
    public function initialize()
    {
        $this->view->disable();


    }


    /**
     * undocumented function
     *
     * @return  void
     * @author  Neil Brayfield
     **/
    public function indexAction()
    {
        // Get the file path from the request
        $filename = $this->dispatcher->getParam('file');

        // Find the file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        // Remove the extension from the filename
        $filename = substr($filename, 0, -(strlen($ext) + 1));


        if ($filename = $this->fs->findFile('public', $filename, $ext, false)) {
            // Check if the browser sent an "if-none-match: <etag>" header or the if-modfied-since header, and tell if the file hasn't changed
            $mtime = filemtime($filename);
            $etag = md5($mtime);

            $modified_since = $this->request->getHeader('if-modified-since');
            $non_match = $this->request->getHeader('if-none-match');

            if ($non_match === $etag || ($modified_since != "" && strtotime($modified_since) >= $mtime)) {
                // Throw 304 not modified cache header
                throw new HTTP304();
            }


            $this->response->setEtag($etag);


            // Set the proper headers to allow caching
            $this->response->setHeader('Content-Type', $this->file->mimeByExt($ext));
            $this->response->setHeader('Last-Modified', date('r', $mtime));

            $this->response->sendHeaders();

            while (ob_get_level()) {
                // Flush all output buffers
                ob_end_flush();
            }

            $start = 0;
            $end = filesize($filename);

            // Open the file for reading
            $file = fopen($filename, 'rb');

            // Send data in 16kb blocks
            $block = 1024 * 16;

            fseek($file, $start);

            while (! feof($file) && ($pos = ftell($file)) <= $end) {
                if (connection_aborted()) {
                    break;
                }

                if ($pos + $block > $end) {
                    // Don't read past the buffer.
                    $block = $end - $pos + 1;
                }

                // Output a block of the file
                echo fread($file, $block);

                // Send the data now
                flush();
            }

            // Close the file
            fclose($file);

            exit;
        } else {
            // Return a 404 status
            throw new HTTP404;
        }
    }
}
