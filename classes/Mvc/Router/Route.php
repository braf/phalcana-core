<?php

namespace Phalcana\Mvc\Router;

use Phalcon\Mvc\Router\RouteInterface;
use Phalcon\Mvc\Router\GroupInterface;

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
class Route implements RouteInterface
{
    const REGEX_GROUP = "\(((?:(?>[^()]+)|(?R))*)\)";
    const REGEX_KEY = "<([a-zA-Z0-9_]++)>";
    const REGEX_SEGMENT = "[^/.,;?\n]++";
    const REGEX_ESCAPE = '[.\+*?[^\]${}=!|]';

    protected static $ids = 0;
    protected $id;
    protected $converters;
    protected $name;
    protected $beforeMatch;
    protected $group;
    protected $hostname;
    protected $paths;
    protected $pattern;
    protected $compiledPattern;
    protected $methods;
    protected $beforeMatchCallback;

    /**
     * Constructor
     *
     * @param  string $pattern      The regex pattern to add
     * @param  array  $paths        The mapping for the paths
     * @param  mixed  $httpMethods  The http method or methods allowed
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function __construct($pattern, $paths = null, $httpMethods = null)
    {
        $this->id = self::$ids++;
        $this->pattern = $pattern;
        $this->paths = $paths;
        $this->via($httpMethods);
    }

    /**
     * Replaces placeholders from pattern returning a valid PCRE regular expressio
     *
     * This is based on the Kohana Route::compile function
     *
     * @see    https://kohanaframework.org/3.3/guide-api/Route#compile
     * @param  string $pattern
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function compilePattern($pattern)
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $pattern);

        if (strpos($expression, '(') !== false) {
            // Make optional parts of the URI non-capturing and optional
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }

        // Insert default regex for keys
        $expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);

        $regex = $this->getPaths();

        if ($regex) {
            $search = $replace = array();
            foreach ($regex as $key => $value) {
                $search[]  = "<$key>".self::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            // Replace the default regex with the user-specified regex
            $expression = str_replace($search, $replace, $expression);
        }

        return '#^'.$expression.'$#uD';
    }

    /**
     * Sets a set of HTTP methods that constraint the matching of the route
     *
     * @param  mixed accepts a string or an array or http methods
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function via($httpMethods)
    {
        if (is_string($httpMethods)) {
            $httpMethods = array($httpMethods);
        }

        $this->methods = $httpMethods;

        return $this;
    }

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     *
     * @param  string $pattern
     * @param  array  $paths
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function reConfigure($pattern, $paths = null)
    {
        $this->pattern = $pattern;
        $this->compiledPattern = null;
        $this->paths = $paths;
    }

    /**
     * Returns the route’s name
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the route’s name
     *
     * @param  string
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * Sets a set of HTTP methods that constraint the matching of the route (alias of via)
     *
     * @param  mixed
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setHttpMethods($httpMethods)
    {
        $this->via($httpMethods);

        return $this;
    }

    /**
     * Returns the HTTP methods that constraint matching the route
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getHttpMethods()
    {
        return $this->methods;
    }

    /**
     * Returns the route’s id
     *
     * @return int
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getRouteId()
    {
        return $this->id;
    }

    /**
     * Returns the route’s pattern
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getPattern()
    {
        if ($this->getGroup() != null) {
            return $this->getGroup()->getPrefix(). $this->pattern();
        } else {
            return $this->pattern;
        }
    }

    /**
     * Returns the route’s compiled pattern
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getCompiledPattern()
    {
        if ($this->compiledPattern == null) {
            $this->compiledPattern = $this->compilePattern($this->getPattern());
        }
        return $this->compiledPattern;
    }

    /**
     * Returns the paths
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getPaths()
    {
        if ($this->getGroup() != null) {
            return array_merge($this->getGroup()->getPaths(), $this->paths());
        } else {
            return $this->paths;
        }
    }

    /**
     * Returns the paths using positions as keys and names as values
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getReversedPaths()
    {
        return array_flip($this->getPaths());
    }



    /**
     * Sets a hostname restriction to the route
     *
     * @param  string
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }


    /**
     * Returns the hostname restriction if any
     *
     * @return string
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getHostname()
    {
        if ($this->hostname) {
            return $this->hostname;
        } elseif ($this->getGroup() != null) {
            return $this->getGroup()->getHostname();
        }

        return null;
    }

    /**
     * Add a converter
     *
     * @param  string   $path
     * @param  callable $callback
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function convert($path, callable $callback)
    {
        $this->converters[$path] = $callback;

        return $this;
    }

    /**
     * Get the converters for the route
     *
     * @return array
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getConverters()
    {
        return $this->converters;
    }

    /**
     * Sets a function to run before matching
     *
     * @param  callable
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function beforeMatch(callable $callback)
    {
        $this->beforeMatchCallback = $callback;

        return $this;
    }


    /**
     * Gets the beforeMatch callback
     *
     * @return callable
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getBeforeMatch()
    {
        if ($this->beforeMatchCallback != null) {
            return $this->beforeMatchCallback;
        } elseif ($this->getGroup() != null) {
            return $this->getGroup()->getBeforeMatch();
        }

        return null;
    }



    /**
     * Sets a hostname restriction to the route
     *
     * @param  Phalcon\Mvc\Router\GroupInterface
     * @return Phalcana\Mvc\Router\Route
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function setGroup(GroupInterface $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Returns the group restriction if any
     *
     * @return Phalcon\Mvc\Router\GroupInterface
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Resets the internal id generator
     *
     * @author Neil Brayfield <neil@brayfield.uk>
     */
    public static function reset()
    {
        self::$ids = 0;
    }
}
