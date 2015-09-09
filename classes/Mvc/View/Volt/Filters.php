<?php

namespace Phalcana\Mvc\View\Volt;

use Phalcon\Mvc\View\Engine\Volt\Compiler;

/**
 * A collection of additional volt  filters
 *
 * @package     Phalcana
 * @category    View
 * @author      Neil Brayfield
 */
class Filters
{

    /**
     * @var Phalcon\Mvc\View\Engine\Volt\Compiler The compiler to add filters to
     */
    protected $compiler;


    /**
     * @var array An array of functions to add to the compiler that exist within this class
     */
    protected $filters = array(
        'ucfirst' => 'ucfirst',
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

        foreach ($this->filters as $volt => $func) {
            if (is_string($func)) {
                $compiler->addFilter($volt, $func);
            } else {
                $compiler->addFilter($volt, $this->$func);
            }
        }

    }
}
