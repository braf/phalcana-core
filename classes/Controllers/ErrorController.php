<?php

namespace Phalcana\Controllers;

use Phalcon\Mvc\View;

/**
 * Main error controller. This is just a base class, to create your own error handler copy this into your app directory.
 *
 *
 * @package     Phalcana
 * @category    Error
 * @author      Neil Brayfield
 */
class ErrorController extends \Phalcon\Mvc\Controller
{

    /**
     * Initialize the controller
     *
     * @author    Neil Brayfield
     **/
    public function initialize()
    {
        $this->view->disableLevel(array(
            View::LEVEL_BEFORE_TEMPLATE => true,
            View::LEVEL_AFTER_TEMPLATE => true
        ));
        $this->view->enable();
        $this->view->setMainView('error');

    }


    /**
     * 404 Error page
     *
     * @author    Neil Brayfield
     **/
    public function show404Action()
    {
        $this->view->setVar('title', '404 Page not found');
    }

    /**
     * 405 Error page
     *
     * @author    Neil Brayfield
     **/
    public function show405Action()
    {
        $this->view->setVar('title', '405 Method not allowed');
    }

    /**
     * 500 System Error page
     *
     * @author    Neil Brayfield
     **/
    public function show500Action()
    {
        $this->view->setVar('title', '500 Internal server error');
    }


    /**
     * Holding page
     *
     * @author    Neil Brayfield
     **/
    public function show503Action()
    {
        $this->view->setVar('title', 'This site is currently down for maintainance');
    }
}
