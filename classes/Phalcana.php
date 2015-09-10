<?php

namespace Phalcana;

use Phalcon\DI;
use Phalcon\Di\Injectable;
use Phalcon\CLI\Console;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url;
use Phalcon\Cli\Router as CliRouter;
use Phalcon\Cli\Dispatcher as CliDispatcher;
use Phalcon\Cache\Backend\Apc as ApcCache;
use Phalcon\Cache\Backend\File as FileCache;
use Phalcon\Cache\Frontend\Data as DataFrontend;
use Phalcon\Events\Manager;
use Phalcana\Core\Error;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcana\Mvc\Router;
use Phalcana\Mvc\View\Volt\Filters;
use Phalcana\Mvc\View\Volt\Functions;
use Phalcana\Core\Filesystem;
use Phalcana\Listeners\Dispatch;
use Phalcana\Mvc\View;

/**
 * The base class for loading and executing the framework flow
 *
 * @package     Phalcana
 * @category    Core
 * @author      Neil Brayfield
 */
class Phalcana extends Injectable
{

    // Common environment type constants for consistency and convenience
    const PRODUCTION  = 10;
    const STAGING     = 20;
    const TESTING     = 30;
    const DEVELOPMENT = 40;

    const VERSION     = '0.2.0';
    const CODENAME    = 'adio';

    /**
     * @var  boolean  true if system is running on windows
     */
    public static $isWindows = false;

    /**
     * @var  boolean  true if PHP was called from the command line
     */
    public static $isCli = false;

    /**
     * @var  boolean  true if PHP is in safe mode
     */
    public static $safeMode = false;

    /**
     * @var Phalcon\Di Main dependency injector
     **/
    public static $di;

    /**
     * @var string Specify the mode, the various mode values are defined as constants in this class.
     * The mode is typically set in the setup config file in `APPPATH/config/setup.php`
     **/
    public static $mode = 10;

    /**
     * @var  string  character set of input and output
     */
    public static $charset = 'utf-8';

    /**
     * Load Phalcana and it's core services
     **/
    public function __construct()
    {
        // Determine if we are running in a Windows environment
        self::$isWindows = (DIRECTORY_SEPARATOR === '\\');

        // Determine if we are running in safe mode
        self::$safeMode = (bool) ini_get('safe_mode');

        // Determin if we are running as the CLI
        self::$isCli = php_sapi_name() === 'cli';

        // load core classes required for operation
        $di = new DI;
        self::$di = $di;
        $this->setDi($di);


        // load the filesystem
        $di->setShared('fs', new Filesystem());
        $di->setShared('phalcana', $this);


        // Load the logger
        $di->setShared('logger', function () {

            $dir = APPPATH.'logs/';

            if (! is_dir($dir) || ! is_writable($dir)) {
                die('Logs directory MUST be writable');
            }

            if (!file_exists($dir.date('Y/m/'))) {
                mkdir($dir.date('Y/m/'), 0777, true);
            }

            $logger = new \Phalcon\Logger\Adapter\File($dir.date('Y/m/d').".php");

            return $logger;
        });

        $di->setShared('cache', $this->loadCache());



        // Load the primary config
        $di->setShared('setup', $this->configure());

        // set mode
        self::$mode = $this->setup->get('mode', self::PRODUCTION);

        // Set the paths for the cascading file system
        $this->fs->setModules($this->setup->modules->toArray());

        $di->setShared('loader', $this->loadLoader());


        // Load the error handler
        $di->setShared('error', $this->errorHandler());

        // Load the services into the dependency injector
        $this->loadSharedServices();

        if (self::$isCli) {
            $this->loadCliServices();
        } else {
            $this->loadServices();
        }

        // Run the inititalization files
        $this->init();

    }

    /**
     * Initiate the Dispatch flow.
     *
     * Once called this checks the routes and loads the controller, subsquently rendering any appropriate views and sending the response
     *
     **/
    public function main()
    {
        $di = $this->getDI();

        if (self::$isCli) {

            global $argv;

            $arguments = array();
            foreach ($argv as $k => $arg) {
                if ($k == 1) {
                    $arguments['task'] = $arg;
                } elseif ($k == 2) {
                    $arguments['action'] = $arg;
                } elseif ($k >= 3) {
                    $arguments['params'][] = $arg;
                }
            }

            // define global constants for the current task and action
            define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
            define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

            $this->console->handle($arguments);

        } else {

            // Get the 'router' service
            $this->router->handle();


            // Pass the processed router parameters to the dispatcher
            $this->dispatcher->setNamespaceName($this->router->getNamespaceName());
            $this->dispatcher->setControllerName($this->router->getControllerName());
            $this->dispatcher->setActionName($this->router->getActionName());
            $this->dispatcher->setParams($this->router->getParams());

            // Dispatch the request
            $controller = $this->dispatcher->dispatch();

            // Find the name space added directory
            $namespaceExtension = explode('Controllers\\', $this->router->getNamespaceName());
            $namespaceDirectory = strtolower($namespaceExtension[count($namespaceExtension)-1]);

            if ($namespaceDirectory != '') {
                $namespaceDirectory .= '/';
            }

            // Start the view
            $view = $controller->view;
            $view->start();

            // Render the related views
            $view->render(
                $namespaceDirectory.$this->dispatcher->getControllerName(),
                $this->dispatcher->getActionName(),
                $this->dispatcher->getParams()
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

        }

        exit;


    }



    /**
     * Loads the cache
     *
     * @return  void
     * @author  Neil Brayfield
     **/
    protected function loadCache()
    {
        // Create the cache
        $dataCache = new DataFrontend(array(
            "lifetime" => 3600
        ));

        if (extension_loaded('apc')) {
            $cache = new ApcCache($dataCache, array(
                'prefix' => md5(DOCROOT),
            ));
        } else {
            $cache = new FileCache($dataCache, array(
                "cacheDir" => APPPATH."cache/"
            ));
        }

        return $cache;

    }


    /**
     * Create the loader
     *
     * @return  Phalcon\Loader
     **/
    protected function loadLoader()
    {
        $events = new Manager();

        // Register namespaces
        $loader = new Loader();
        $loader->registerNamespaces(array('Phalcana' => APPPATH.'classes'.DIRECTORY_SEPARATOR));
        $events->attach('loader', $this->fs);
        $loader->setEventsManager($events);

        // Load cache into classes
        $classes = $this->cache->get('classes');
        if (is_array($classes)) {
            $loader->registerClasses($classes, true);
        }

        $loader->register();

        return $loader;
    }


    /**
     * Loads the core services into the dependency injector
     *
     **/
    protected function loadSharedServices()
    {
        $di = $this->getDI();

        $setup = $di->get('setup');
        $fs = $di->get('fs');

        $di->setShared('assets', 'Phalcon\Assets\Manager');
        $di->setShared('escaper', 'Phalcon\Escaper');
        $di->setShared('request', 'Phalcon\Http\Request');
        $di->setShared('response', 'Phalcon\Http\Response');

        $di->setShared('config', 'Phalcana\Config');
        $di->setShared('inflector', 'Phalcana\Inflector');
        $di->setShared('arr', 'Phalcana\Arr');
        $di->setShared('num', 'Phalcana\Num');
        $di->setShared('tag', 'Phalcon\Tag');
        $di->setShared('filter', 'Phalcon\Filter');
        $di->setShared('text', 'Phalcana\Text');
        $di->setShared('utf8', 'Phalcana\UTF8');
        $di->setShared('file', 'Phalcana\File');
        $di->setShared('date', 'Phalcana\Date');
        $di->setShared('title', 'Phalcana\Title');



        /**
         * The URL component is used to generate all kind of urls in the application
         */
        $di->setShared('url', function () use ($setup) {
            $url = new Url();
            $url->setBaseUri($setup->base_url);
            $url->setStaticBaseUri($setup->static_base_url);

            return $url;
        });


        //Registering the view component
        $di->set('view', function () use ($fs) {
            $view = new View();
            $view->setViewsDir('app/views/');

            $view->setCascadeDefault(APPPATH.'views');
            $view->setCascadePaths($fs->getModules());


            $view->registerEngines(array(
                '.volt' => function ($view, $di) {

                    $volt = new Volt($view, $di);


                    $volt->setOptions(array(
                        'compiledPath' => APPPATH.'cache/',
                        'compiledSeparator' => '_',
                        'compileAlways' => \Phalcana\Phalcana::$mode >= \Phalcana\Phalcana::PRODUCTION,
                    ));

                    $compiler = $volt->getCompiler();

                    new Filters($compiler);
                    new Functions($compiler);


                    return $volt;
                }
            ));

            return $view;
        });

        if (isset($setup->database)) {
            $di->set('modelsManager', 'Phalcon\Mvc\Model\Manager');
            $di->set('modelsMetadata', 'Phalcon\Mvc\Model\MetaData\Memory');
            $di->setShared('db', function () use ($setup) {
                return new \Phalcon\Db\Adapter\Pdo\Mysql($setup->database->toArray());
            });
        }
    }

    /**
     * Loads the core services into the dependency injector
     *
     **/
    protected function loadServices()
    {
        $di = $this->getDI();

        // Register the dispatcher setting a Namespace for controllers
        $di->setShared('dispatcher', function () {


            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Phalcana\Controllers');

            $listener = new Dispatch;
            $events = new Manager();
            $events->attach('dispatch', $listener);
            $dispatcher->setEventsManager($events);

            return $dispatcher;
        });

        $di->setShared('router', $this->loadRoutes(new Router(false)));
    }

    /**
     * Loads the core cli services into the dependency injector
     *
     **/
    protected function loadCliServices()
    {
        $di = $this->getDI();

        // Register the dispatcher setting a Namespace for controllers
        $di->setShared('dispatcher', function () {

            $dispatcher = new CliDispatcher();
            $dispatcher->setDefaultNamespace('Phalcana\Tasks');

            $listener = new Dispatch;
            $events = new Manager();
            $events->attach('dispatch', $listener);
            $dispatcher->setEventsManager($events);

            return $dispatcher;
        });

        $console = new Console();

        $di->setShared('cli', 'Phalcana\CLI');

        $di->setShared('console', $console);
        $di->setShared('router', $this->loadRoutes(new CliRouter, 'cliroutes'));
    }


    /**
     * Loads the routes from the cascading file system.
     *
     * This loads all `/config/routes.php` files.
     *
     * @return  Phalcon\Router
     **/
    protected function loadRoutes($router, $filename = 'routes')
    {
        $files = $this->fs->findFile('config', $filename, 'php', true, true);

        if (!$files) {
            return $router;
        }

        foreach ($files as $file) {
            include $file;
        }

        return $router;
    }

    /**
     * Initialize all modules by loading the `init.php` files in the routes of each module folder and the app folder.
     **/
    protected function init()
    {
        $files = array_reverse($this->fs->findFile('', 'init', 'php', true, true));

        $di = $this->getDI();

        foreach ($files as $file) {
            include $file;
        }

    }


    /**
     * Load the setup config. This is the main configuration for the application.
     * Setup includes two key files.
     *
     * - `APPPATH/config/static.php` Containing config options to be commited into your repo
     * - `APPPATH/config/setup.php`  Dynamic config file that bootstraps the system to your configuration. Values defined here override the static config
     *
     *
     * @return  Phalcon\Config
     **/
    protected function configure()
    {
        //  load config
        $static = include APPPATH.'config/static.php';

        if (file_exists(APPPATH.'config/setup.php')) {
            $setup = include APPPATH.'config/setup.php';
            $static->merge($setup);
        }

        return $static;
    }


    /**
     * Load and register the error handler by setting the PHP callbacks.
     *
     * @return  Phalcana\Core\Error
     **/
    protected function errorHandler()
    {
        $error = new Error;

        // Enable Phalcana exception handling, adds stack traces and error source.
        set_exception_handler(array($error, 'handler'));

        // Enable Phalcana error handling, converts all PHP errors to exceptions.
        set_error_handler(array($error, 'errorHandler'));

        // Enable the Phalcana shutdown handler, which catches E_FATAL errors.
        register_shutdown_function(array($error, 'shutdown'));

        return $error;
    }
}
