<?php
namespace Phalcana\Exceptions;

use \Phalcon\Mvc\Dispatcher;

/**
 * This error is typically for the holding page
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP503 extends HTTP
{
    protected $code = 503;

    protected $message = "Service Unavailable";

    protected $forwards = array(
        'controller' => 'error',
        'action' => 'show503',
    );
}
