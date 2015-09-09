<?php
namespace Phalcana\Exceptions;

use Phalcon\Mvc\Dispatcher;
use Phalcana;

/**
 * Handle 500 errors
 *
 * @package     Phalcana
 * @category    Exceptions
 */
class HTTP500 extends HTTP
{
    protected $code = 500;

    protected $message = "Internal Server Error";

    protected $debug = "";

    protected $forwards = array(
        'controller' => 'error',
        'action' => 'show500',
    );

    /**
     * Get the debugging response from the error handler
     *
     * @param   string  HTML response from the debugger to send on.
     * @return  void
     **/
    public function __construct($message = "", $code = 500, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous = null)
    {
        if ($previous != null) {
            $this->debug = $message;
            $message = "";
        }

        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    /**
     * undocumented function
     *
     * @return  void
     * @author  Neil Brayfield
     **/
    public function initialize()
    {

        if (Phalcana\Phalcana::$mode <= Phalcana\Phalcana::STAGING) {

            // do stuff with the error handler here


        } else {
            echo $this->debug;
            die();
        }

        parent::initialize();
    }
}
