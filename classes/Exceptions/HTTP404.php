<?php
namespace Phalcana\Exceptions;

use \Phalcon\Mvc\Dispatcher;

/**
 * 404 error class
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP404 extends HTTP
{
    protected $code = 404;

    protected $message = "Page not found";

    protected $forwards = array(
        'controller' => 'error',
        'action' => 'show404',
    );
}
