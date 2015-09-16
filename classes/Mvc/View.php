<?php

namespace Phalcana\Mvc;

use Phalcon\Cache\BackendInterface;

/**
 * View extension that works the cascading.
 *
 * @package     Phalcana
 * @category    View
 * @author      Neil Brayfield
 */
class View extends \Phalcon\Mvc\View
{

    /**
     * @var array Cascade paths
     **/
    protected $cascadePaths;

    /**
     * @var string Cascade default
     **/
    protected $cascadeDefault;


    /**
     * Override of the _engineRender function that dynamicall resets the cascade paths
     *
     * @return  void
     * @author  Neil Brayfield
     **/
    protected function _engineRender($engines, $viewPath, $silence, $mustClean, BackendInterface $cache = null)
    {
        // get original so it can be restored
        $this->setViewsDir($this->getCascadeDefault());


        // Check directories for existing views
        $cascadePaths = $this->getCascadePaths();

        if ($cascadePaths) {
            // check default
            if (!$this->exists($viewPath)) {
                foreach ($cascadePaths as $path) {
                    $this->setViewsDir($path.'views/');
                    if ($this->exists($viewPath)) {
                        break;
                    }
                }

            }
        }

        parent::_engineRender($engines, $viewPath, $silence, $mustClean, $cache);

    }

    /**
     * undocumented function
     *
     * @return  void
     **/
    public function direct($path, array $vars = null)
    {
        ob_start();
        $this->partial($path, $vars);
        return ob_get_clean();
    }


    /**
     * Set the cascade paths
     *
     * @return  void
     **/
    public function setCascadePaths($paths)
    {
        $this->cascadePaths = $paths;
    }


    /**
     * Gets the cascade paths
     *
     * @return  array
     **/
    public function getCascadePaths()
    {
        if ($this->cascadePaths == null) {
            return false;
        }

        return $this->cascadePaths;
    }

    /**
     * Set the cascade default
     *
     * @return  void
     **/
    public function setCascadeDefault($paths)
    {
        $this->cascadeDefault = $paths;
    }


    /**
     * Gets the cascade default
     *
     * @return  array
     **/
    public function getCascadeDefault()
    {
        if ($this->cascadeDefault == null) {
            return false;
        }

        return $this->cascadeDefault;
    }
}
