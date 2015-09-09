<?php
namespace Phalcana\Exceptions;

use \Phalcon\Mvc\Dispatcher;

/**
 * Error page typically used for access denied
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP403 extends HTTP
{
    protected $code = 403;

    protected $message = "Access denied";
}
