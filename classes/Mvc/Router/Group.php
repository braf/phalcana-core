<?php

namespace Phalcana\Mvc\Router;

use Phalcon\Mvc\Router\GroupInterface;
use Phalcana\Mvc\Router\Route;
use Phalcana\Mvc\Router;

/**
 * View extension that works the cascading.
 *
 * @package     Phalcana
 * @category    Routing
 * @author      Neil Brayfield
 */
class Group implements GroupInterface
{
    protected $routes = array();
    protected $prefix;
    protected $paths;
    protected $hostname;
    protected $beforeMatchCallback;


    /**
     * Constructor
     *
     * @param  array  $paths        The mapping for the paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function __construct($paths = null)
    {
        $this->paths = $paths;
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
        $route->setGroup($this);

        if ($position == Router::POSITION_FIRST) {
            array_unshift($this->routes, $route);
        } else {
            array_push($this->routes, $route);
        }
        return $route;
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
     * Sets the prefix for the group
     *
     * @param  string $prefix
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }


    /**
     * Gets the prefix for the group
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getPrefix()
    {
        return $this->prefix;
    }


    /**
     * Set the paths
     *
     * @param  array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    /**
     * Returns the paths
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Sets a hostname restriction to the route
     *
     * @param  string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * Returns the hostname restriction if any
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getHostname()
    {
        return $this->hostname;
    }


    /**
     * Gets the beforeMatch callback
     *
     * @return callable
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getBeforeMatch()
    {
        return $this->beforeMatchCallback;
    }

    /**
     * Sets a function to run before matching
     *
     * @param  callable
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function beforeMatch($callback)
    {
        $this->beforeMatchCallback = $callback;
    }
}
