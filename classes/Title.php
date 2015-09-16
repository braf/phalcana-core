<?php

namespace Phalcana;

use Phalcon\Di\Injectable;

/**
 * Title helper class.
 *
 * @package    Phalcana
 * @category   Helpers
 * @author     Neil Brayfield
 */
class Title extends Injectable
{

    /**
     * @var string Defines the pattern for the title
     *
     * - `:var:` to inlcude a variable. Variables can be custom or one of the preset vars
     * - `|` Represents a seperator which can be defined by setting the seperator variable
     */
    public $pattern = ":page:|:action:|:controller:|:site:";

    /**
     * @var string The seperator to split sections of the pattern
     */
    public $seperator = " - ";

    /**
     * @var array An array of text replacements for variables.
     */
    protected $replacements = array();


    /**
     * @var array Collection of vars to compile in
     */
    protected $vars;

    /**
     * @var array An array of values to ignore in the string.
     */
    protected $ignores = array('index');




    /**
     * Add a custom variable to be compiled into the pattern
     *
     * @param string The name of the variable to add
     * @param string The value of the variable to add
     **/
    public function setVar($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Merge custom variables into the pattern
     *
     * @uses Phalcana\Arr::merge
     * @param array Associative array of the variables to add.
     **/
    public function setVars($vars)
    {
        $this->vars = Arr::merge($vars, $this->vars);
    }

    /**
     * Add a replacement. This will replace any variables that match the replacement
     *
     * @param string The name of the variable to add
     * @param string The value of the variable to add
     **/
    public function setReplacement($name, $value)
    {
        $this->replacements[$name] = $value;
    }

    /**
     * Add replacements. This will replace any variables that match the replacements
     *
     * @uses Phalcana\Arr::merge
     * @param array Associative array of the replacements to add.
     **/
    public function setReplacements($vars)
    {
        $this->replacements = Arr::merge($vars, $this->replacements);
    }

    /**
     * Remove a replacement
     *
     * @param string The name of the replacement to remove
     **/
    public function removeReplacement($name)
    {
        if (isset($this->replacements[$name])) {
            unset($this->replacements[$name]);
        }
    }

    /**
     * Add values to be ignored from variables
     *
     * @param string The name of the ignore to add
     **/
    public function addIgnore($value)
    {
        $this->ignores[] = $value;
    }


    /**
     * Get a variable or a preset variable
     *
     * @return  string The variable value
     **/
    public function getVar($name)
    {
        $return = false;

        if (isset($this->vars[$name])) {
            $return = $this->vars[$name];
        } elseif ($name == 'site') {
            $return = $this->setup->title;
        } elseif ($name == 'controller') {
            $return = ucwords($this->inflector->decamelize($this->dispatcher->getControllerName()));
        } elseif ($name == 'action') {
            $return = ucwords($this->inflector->decamelize($this->dispatcher->getActionName()));
        }

        if (in_array(strtolower($return), $this->ignores)) {
            $return = false;
        }

        if (isset($this->replacements[$return])) {
            $return = $this->replacements[$return];
        }

         return $return;
    }

    /**
     * Compile the title from the pattern
     *
     * @return  string The compiled title
     **/
    public function get()
    {
        $parts = explode('|', $this->pattern);

        foreach ($parts as $index => &$part) {
            $part = $this->substitute($part);

            if ($part == "") {
                unset($parts[$index]);
            }
        }

        $result = implode($this->seperator, $parts);

        return $result;
    }

    /**
     * Magic method to compile and echo the title
     *
     * @uses Phalcana\Title::compile
     * @return  string
     **/
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Replace substitutions with variables
     *
     * @param   string Input string
     * @return  string Substituted variables
     **/
    private function substitute($str)
    {
        preg_match_all("/\:([a-zA-z0-9]+)\:/", $str, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                $sub = $this->getVar($match);

                if ($sub) {
                    $str = str_replace(":".$match.":", $sub, $str);
                } else {
                    $str = str_replace(":".$match.":", '', $str);
                }

            }

        }

        return $str;


    }
}
