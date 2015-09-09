<?php

namespace Phalcana;

use Phalcon\Config as PhalconConfig;

/**
 * Cascading config loader
 *
 * @package     Phalcana
 * @category    Base
 * @author      Neil Brayfield
 */
class Config extends \Phalcon\Di\Injectable
{
    /**
     * @var array Store for previously loaded config values
     */
    protected $groups = array();

    /**
     * Get merged config set. This merges the contents of all configs in the cascading file system.
     * This is also done recursively so all arrays in the config will be merged also
     *
     * @param   string  The config group to load
     * @return  Phalcon\Config
     **/
    public function load($group)
    {

        if (isset($this->groups[$group])) {
            return $this->groups[$group];
        }

        $files = $this->fs->findFile('config', $group, 'php', true, true);

        if (!$files) {
            return false;
        }

        $files = array_reverse($files);


        foreach ($files as $file) {
            if (!isset($final)) {
                $final = include($file);
            } else {
                $final->merge(include($file));
            }
        }

        $this->groups[$group] = $final;

        return $final;
    }
}
