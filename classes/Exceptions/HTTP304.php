<?php
namespace Phalcana\Exceptions;

use \Phalcon\Mvc\Dispatcher;

/**
 * undocumented class
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
class HTTP304 extends HTTP
{
    protected $code = 304;

    protected $message = "Not Modified";
}
