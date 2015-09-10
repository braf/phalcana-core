<?php

namespace Phalcana\Exceptions;


use Phalcon\Dispatcher;
use Phalcana\Phalcana;

/**
 * Base class for HTTP exceptions
 *
 * @package     Phalcana
 * @category    Exceptions
 * @author      Neil Brayfield
 */
abstract class HTTP extends Exception
{

    /**
     * @var int HTTP error code number
     */
    protected $code;

    /**
     * @var Phalcon\Di  Dependency injector
     */
    protected $di;

    /**
     * @var string Message for the HTTP Error
     */
    protected $message = "HTTP Error";

    /**
     * @var string Message for the HTTP Error
     */
    public $response;

    /**
     * @var bool/array Controller and action
     */
    protected $forwards = false;

    /**
     * @var array HTTP status codes and messages
     */
    public static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * Constructor to override the current structure
     *
     * @param mixed     Custom error message
     * @param int       Error code number
     * @param int       Severity
     * @param string    Filename path
     * @param int       Line number in the file of the error
     * @param Exception Previous error message
     **/
    public function __construct($message = "", $code = 500, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous = null)
    {
        // Set code
        if (!$this->code) {
            $this->code = $code;
        }

        // get the dependancy injection
        $this->di = \Phalcon\DI::getDefault();

        // load the response
        $this->response = $this->di->get('response');

        if (is_string($message) && $message == "") {

        } else {
            $this->message = $message;

        }

        $this->setStatus();

        $this->initialize();

        $request = \Phalcon\DI::getDefault()->get('request');

        //parent::__construct($this->getMessage(), $this->getCode(), $previous);

    }


    /**
     * Sets the status code on the response
     **/
    public function setStatus()
    {
        $message = (!is_string($this->message) || $this->message == "") ? HTTP::$messages[$this->getCode()] : $this->message;
        $this->response->setStatusCode($this->getCode(), $message);

    }


    /**
     * Does any encoding on the message
     *
     * @return  string  Serialzed object to return, usually json
     **/
    protected function serialize($value)
    {
        return $value;
    }


    /**
     * Primary initialization function
     *
     * @return  void
     **/
    protected function initialize()
    {
        if ($this->forwards) {
            $this->forward(\Phalcon\DI::getDefault()->get('dispatcher'));
        } else {
            // If not specified use class default
            if (is_array($this->message) || is_object($this->message)) {
                $this->message = $this->serialize($this->message);
                $this->response->setJsonContent($this->message);

            } else {
                $this->response->setContent($this->message);
            }

            $this->response->send();

        }


    }


    /**
     * Forward event if required
     *
     * @param   Dispatcher Current dispatcher
     **/
    protected function forward(Dispatcher $dispatcher)
    {


        if (is_array($this->forwards)) {

            if (\Phalcana\Phalcana::$isCli) {
                echo "HTTP Error: ".$this->code;
                echo ' - '.$this->message.PHP_EOL;
                exit;
            }

            if (isset($this->forwards['namespace'])) {
                $dispatcher->setNamespaceName($this->forwards['namespace']);
            } else {
                $dispatcher->setNamespaceName('Phalcana\Controllers');
            }

            $dispatcher->setControllerName($this->forwards['controller']);
            $dispatcher->setActionName($this->forwards['action']);

            $controller = $dispatcher->dispatch();


            $view = $controller->view;

            $view->start();

            // Render the related views
            $view->render(
                $dispatcher->getControllerName(),
                $dispatcher->getActionName(),
                $dispatcher->getParams()
            );

            // Finish the view
            $view->finish();

            $response = $controller->response;


            // Pass the output of the view to the response
            $response->setContent($view->getContent());

            // Send the request headers
            $response->sendHeaders();

            // Print the response
            echo $response->getContent();

            exit(1);
        }

        return true;
    }
}
