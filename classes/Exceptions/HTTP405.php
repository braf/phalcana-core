<?php
namespace Phalcana\Exceptions;

use \Phalcon\Mvc\Dispatcher;

/**
 * Method not allowed handler
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP405 extends HTTP
{
    protected $code = 405;

    protected $message = "Method not allowed";

    protected $forwards = array(
        'controller' => 'error',
        'action' => 'show405',
    );
}
