<?php

namespace Phalcana\Core;

use Phalcon\Logger;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DI;
use Phalcana\Phalcana;
use Phalcana\Exceptions\HTTP;
use Phalcana\Exceptions\HTTP404;
use Phalcana\Exceptions\HTTP500;
use Phalcana\Debug;

/**
 * Error handler
 *
 * @package     Phalcana
 * @category    Error
 */
class Error
{


    /**
     * @var  array  Types of errors to display at shutdown
     */
    public static $shutdownErrors = array(E_PARSE, E_ERROR, E_USER_ERROR);





    /**
     * This handler is for dealing with errors and converting any non HTTP errors to HTTP 500 errors
     *
     * @throws  Phalcon\Exceptions\HTTP500
     * @param   Exception The original error exception to be transformed.
     **/
    public function handler(\Exception $e)
    {
        try {
            if ($e instanceof HTTP == false) {
                throw new HTTP500($this->response($e), 500, 1, $e->getFile(), $e->getLine(), $e);
            }

        } catch (\Exception $n) {
            if (Phalcana\Phalcana::$mode >= Phalcana\Phalcana::PRODUCTION) {
                echo "<h1>Internal server error</h1>";
                echo "<p>Error handler failed with {$n->getMessage()} in {$n->getFile()} [{$n->getLine()}]</p>";
                echo "<p>Original error was {$e->getMessage()} in {$e->getFile()} [{$e->getLine()}]</p>";
            } else {
                echo "<h1>Internal server error</h1>";
                echo "<p>An Unexpected error has occured</p>";
            }
            die();
        }
    }

    /**
     * PHP error handler, converts all errors into ErrorExceptions. This handler
     * respects error_reporting settings.
     *
     * @throws  ErrorException
     * @return  true
     */
    public function errorHandler($code, $error, $file = null, $line = null)
    {
        if (error_reporting() & $code) {
            // This error is not suppressed by current error reporting settings
            // Convert the error into an ErrorException
            throw new \ErrorException($error, $code, 0, $file, $line);
        }

        // Do not execute the PHP error handler
        return true;
    }

    /**
     * Catches errors that are not caught by the error handler, such as E_PARSE.
     *
     * @uses Phalcon\Core\Error::handler
     */
    public function shutdown()
    {
        $error = error_get_last();

        if (in_array($error['type'], self::$shutdownErrors)) {
            // Clean the output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Fake an exception for nice debugging
            $this->handler(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }




    /**
     * Generate error response page
     *
     * @param   Exception   The original error exception to be transformed.
     * @return  string      Debug page HTML
     **/
    public function response(\Exception $e)
    {

        // Get the exception information
        $class   = get_class($e);
        $code    = $e->getCode();
        $message = $e->getMessage();
        $file    = $e->getFile();
        $line    = $e->getLine();
        $trace   = $e->getTrace();
        $source = Debug::source($file, $line);


        try {



            if ($e instanceof HTTP) {

            } else {

                self::log($e);


                // Purge the response
                while (ob_get_level()) {
                    ob_end_clean();
                }


                ob_start();



                if (! headers_sent()) {
                    header('Content-Type: text/html; charset=utf-8', true, 500);
                }



                if (DI::getDefault()->get('request')->isAjax()) {
                    echo "$message <small>in $file [$line]</small>";
                } elseif (php_sapi_name() == 'cli' || $_SERVER['HTTP_USER_AGENT'] == 'cURL') {
                    echo self::text($e);
                    echo PHP_EOL;
                } else {
                    include Phalcana\Phalcana::$di->get('fs')->findFile('views', 'error', 'html');

                }



                return ob_get_clean();

            }



        } catch (\Exception $n) {

            self::log($n, Logger::EMERGENCY);


            ob_start();

            echo $n->getMessage().'<br/>';
            echo ' line: '.$n->getLine() . ' File: '.$n->getFile().'<br/>';

            echo $message.'<br/>';
            echo ' line: '.$line . ' File: '.$file.'<br/>';

            return ob_get_clean();
        }
    }


    /**
     * Logs an exception.
     *
     * @uses    Error::text
     * @param   Exception   Error exception to log
     * @param   int         The error level to log. While this is an int you can use constants from the [Phalcon\Logger] Class
     */
    public static function log(\Exception $e, $level = Logger::ERROR)
    {
        $di = DI::getDefault();

        if ($di->has('logger')) {
            // Create a text version of the exception
            $error = self::text($e);

            // get logger and start transaction
            $log = $di->get('logger');
            $log->begin();

            // Add this exception to the log
            $log->log($level, $error);

            // Make sure the logs are written
            $log->commit();
        }
    }


    /**
     * Get a single line of text representing the exception:
     *
     *
     *
     * @param   Exception   Error exception to log
     * @return  string      In the format Error [ Code ]: Message ~ File [ Line ]
     */
    public static function text(\Exception $e)
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e),
            $e->getCode(),
            strip_tags($e->getMessage()),
            Debug::path($e->getFile()),
            $e->getLine()
        );
    }
}
