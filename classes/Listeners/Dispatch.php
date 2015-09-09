<?php

namespace Phalcana\Listeners;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception;
use Phalcana;
use Phalcana\Exceptions\HTTP503;
use Phalcana\Exceptions\HTTP404;

/**
 * The base class for the cascading file system
 *
 * @package     Phalcana
 * @category    Listeners
 * @author      Neil Brayfield
 */
class Dispatch extends \Phalcon\Di\Injectable
{

    /**
     * Check for the holding page and apply it if appropriate
     *
     * @param object Event
     * @param Phalcon\Mvc\Dispatcher
     * @throws Phalcana\Exceptions\HTTP503
     * @return  false If the holding page is up
     **/
    public function beforeDispatchLoop($event, $dispatcher)
    {
        if ($this->setup->get('holding', false) && $dispatcher->getControllerName() != 'error') {
            throw new HTTP503;
            return false;
        }

    }


    /**
     * Check the dispatch events for errors that would translate as 404's and throw the appropriate exception
     *
     * @param object Event
     * @param Phalcon\Mvc\Dispatcher
     * @param Exception The exception that occured
     * @throws Phalcana\Exceptions\HTTP404
     * @return  false If Dispatcher exception error occurs
     **/
    public function beforeException($event, $dispatcher, $exception)
    {
        if ($exception instanceof Exception) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_INVALID_HANDLER:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    throw new HTTP404;
                    return false;
            }

        }
    }
}
