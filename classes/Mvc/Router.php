<?php

namespace Phalcana\Mvc;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Router\GroupInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Phalcana\Mvc\Router\Route;

/**
 * Custom router for the Phalcon framework. Using the interface
 * from the Phalcon framework but implements the Kohana style
 * of defining routes. This gives you the advantage of using
 * optional routes.
 *
 * @package     Phalcana
 * @category    Routing
 * @author      Neil Brayfield
 */
class Router extends Injectable implements RouterInterface
{
    const URI_SOURCE_GET_URL = 1;
    const URI_SOURCE_SERVER_REQUEST_URI = 2;
    const POSITION_FIRST = 3;
    const POSITION_LAST = 4;

    protected $defaults = array(
        "controller" => 'index',
        "action" => 'index',
    );

    protected $uriSource = 1;
    protected $wasMatch = false;
    protected $routes = array();
    protected $params = array();
    protected $matches;
    protected $matchedRoute;
    protected $notFoundPaths;
    protected $namespace;
    protected $module;
    protected $controller;
    protected $action;

    /**
     * Constructor for the router
     *
     * @param  bool $defaultroutes
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function __construct($defaultRoutes = true)
    {
        if ($defaultRoutes) {
            $this->add('(<controller>(/<action>(/<id>)))');
        }
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     * @param  string $uri Handle a manually passed uri
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function handle($uri = false)
    {
        if (!$uri) {
            $uri = $this->getRewriteUri();
        }

        $uri = trim($uri, '/');

        $reverse = array_reverse($this->getRoutes());

        foreach ($reverse as $route) {
            if ($this->matchesMethod($route)
                && $this->hostnameMatch($route)
                && $this->beforeMatch($route, $uri)
                && $this->matches($route, $uri)) {
                break;
            }
        }

        if (!$this->wasMatched() && $this->notFoundPaths != null) {
            if (isset($this->notFoundPaths['namespace'])) {
                $this->namespace = $this->notFoundPaths['namespace'];
            }

            if (isset($this->notFoundPaths['module'])) {
                $this->module = $this->notFoundPaths['module'];
            }

            if (isset($this->notFoundPaths['controller'])) {
                $this->controller = $this->notFoundPaths['controller'];
            }

            if (isset($this->notFoundPaths['action'])) {
                $this->action = $this->notFoundPaths['action'];
            }

        }
    }

    /**
     * Checks if the current http method is allowed
     *
     * @uses   Phalcon\Http\Request
     * @param  Phalcon\Mvc\Router\RouteInterface
     * @return bool
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    protected function matchesMethod(RouteInterface $route)
    {
        $methods = $route->getHttpMethods();
        if ($methods == null) {
            return true;
        }

        return $this->request->isMethod($methods);
    }

    /**
     * Run the before match callback on the route
     *
     * @param  Phalcon\Mvc\Router\RouteInterface
     * @param  string
     * @return bool
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    protected function beforeMatch(RouteInterface $route, $uri)
    {
        if ($route->getBeforeMatch() != null) {
            return call_user_func($route->getBeforeMatch(), $uri, $route);
        } else {
            return true;
        }
    }

    /**
     * Checks that the host name matches
     *
     * @uses   Phalcon\Http\Request
     * @param  Phalcon\Mvc\Router\RouteInterface
     * @return bool
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    protected function hostnameMatch(RouteInterface $route)
    {
        $hostname = $route->getHostname();
        if ($hostname == null) {
            return true;
        }

        return preg_match("/^{$hostname}$/", $this->request->getHttpHost());
    }

    /**
     * Checks if a route matches the pattern
     *
     * @param  Phalcon\Mvc\Router\RouteInterface
     * @param  string
     * @return bool
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    protected function matches(RouteInterface $route, $uri)
    {
        $pattern = $route->getCompiledPattern();

        if (!preg_match($pattern, $uri, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                // Skip all unnamed keys
                continue;
            }

            // Set the value for all matched keys
            $this->params[$key] = $value;
        }

        $this->wasMatch = true;
        $this->matchedRoute = $route;
        $this->matches = $route->getPaths();

        $converters = $route->getConverters();
        if (is_array($converters)) {
            foreach ($this->params as $part => &$value) {
                if (isset($converter[$part])) {
                    $value = call_user_func($converter[$part], $value);
                }
            }
        }

        if (isset($this->params['namespace'])) {
            $this->namespace = $this->params['namespace'];
            unset($this->params['namespace']);
        } elseif (isset($this->matches['namespace'])) {
            $this->namespace = $this->matches['namespace'];
        }

        if (isset($this->params['module'])) {
            $this->module = $this->params['module'];
            unset($this->params['module']);
        } elseif (isset($this->matches['module'])) {
            $this->module = $this->matches['module'];
        }

        if (isset($this->params['controller'])) {
            $this->controller = $this->params['controller'];
            unset($this->params['controller']);
        } elseif (isset($this->matches['controller'])) {
            $this->controller = $this->matches['controller'];
        }

        if (isset($this->params['action'])) {
            $this->action = $this->params['action'];
            unset($this->params['action']);
        } elseif (isset($this->matches['action'])) {
            $this->action = $this->matches['action'];
        }

        return true;
    }

    /**
     * Get rewrite info. This info is read from $_GET[‘_url’].
     * This returns ‘/’ if the rewrite information cannot be read
     *
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getRewriteUri()
    {
        if ($this->uriSource == self::URI_SOURCE_GET_URL) {
            return isset($_GET['_url']) ? $_GET['_url'] : '/';
        } elseif ($this->uriSource == self::URI_SOURCE_SERVER_REQUEST_URI) {
            return $_SERVER['request_uri'];
        }

        return '/';
    }

    /**
     * Sets the URI source. One of the URI_SOURCE_* constants
     *
     * @param  int $uriSource The uri source
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setUriSource($uriSource)
    {
        $this->uriSource = $uriSource;
    }

    /**
     * Set the default controller
     *
     * @param  string
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setDefaultController($controller)
    {
        $this->defaults["controller"] = $controller;
    }

    /**
     * Set the default module
     *
     * @param  string
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setDefaultModule($module)
    {
        $this->defaults["module"] = $module;
    }

    /**
     * Set the default action
     *
     * @param  string
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setDefaultAction($action)
    {
        $this->defaults["action"] = $action;
    }

    /**
     * Set the defaults
     *
     * @param  array
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }


    /**
     * Gets the defaults
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Get the routes added to the router
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Clear all the routes defined
     *
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function clear()
    {
        $this->routes = array();
    }


    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function add($pattern, $paths = null, $httpMethods = null, $position = 4)
    {
        $route = new Route($pattern, $paths, $httpMethods);

        if ($position == self::POSITION_FIRST) {
            array_unshift($this->routes, $route);
        } else {
            array_push($this->routes, $route);
        }

        return  $route;
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addGet($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'GET', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addPost($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'POST', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addPut($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'PUT', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addPatch($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'PATCH', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addDelete($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'DELETE', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addOptions($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'OPTIONS', $position);
    }

    /**
     * Add a route to the router
     *
     * @param  string $pattern  The regex pattern to add
     * @param  array  $paths    The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function addHead($pattern, $paths = null, $position = false)
    {
        return $this->add($pattern, $paths, 'HEAD', $position);
    }

    /**
     * Mount a group of routes to the router
     *
     * @return void
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function mount(GroupInterface $group)
    {
        foreach ($group->getRoutes() as $route) {
            array_push($this->routes, $route);
        }

        return $this;
    }

    /**
     * Gets the processed module name
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getModuleName()
    {
        return $this->module;
    }

    /**
     * Gets the processed namespace
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getNamespaceName()
    {
        return $this->namespace;
    }

    /**
     * Gets the processed controller name
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * Gets the processed action name
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getActionName()
    {
        return $this->action;
    }

    /**
     * Gets the processed module name
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns the route that matchs the handled URI
     *
     * @return  Phalcon\Mvc\Router\Route
     * @author  Neil Brayfield <neil@brayfield.uk>
     */
    public function getMatchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     *
     * @return  array
     * @author  Neil Brayfield <neil@brayfield.uk>
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Checks if the router macthes any of the defined routes
     *
     * @return  bool
     * @author  Neil Brayfield <neil@brayfield.uk>
     */
    public function wasMatched()
    {
        return $this->wasMatch;
    }

    /**
     * Returns a route object by its id
     *
     * @param  string $id
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getRouteById($id)
    {
        foreach ($this->getRoutes() as $key => $route) {
            if ($route->getRouteId() == $id) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns a route object by its name
     *
     * @param  string $name
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getRouteByName($name)
    {
        foreach ($this->getRoutes() as $key => $route) {
            if ($route->getName() == $id) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Set the route not found paths
     *
     * @param  array $paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function notFound(array $paths)
    {
        $this->notFoundPaths = $paths;
    }
}
