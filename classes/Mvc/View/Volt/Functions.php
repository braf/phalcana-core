<?php

namespace Phalcana\Mvc\View\Volt;

use Phalcon\Mvc\View\Engine\Volt\Compiler;

/**
 * A collection of additional volt functions
 *
 * @package     Phalcana
 * @category    View
 * @author      Neil Brayfield
 */
class Functions
{

    /**
     * @var Phalcon\Mvc\View\Engine\Volt\Compiler The compiler to add functions to
     */
    protected $compiler;


    /**
     * @var array An array of functions to add to the compiler that exist within this class
     */
    protected $functions = array(
        'implode' => 'implode',
    );

    /**
     * undocumented function
     *
     * @param   Compiler
     * @return  void
     **/
    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;

        foreach ($this->functions as $volt => $func) {
            if (is_string($func)) {
                $compiler->addFunction($volt, $func);
            } else {
                $compiler->addFunction($volt, $this->$func);
            }
        }

    }
}
