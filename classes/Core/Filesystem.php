<?php

namespace Phalcana\Core;

use Phalcon\Mvc\Dispatcher;

/**
 * The base class for the cascading file system
 *
 * @package     Phalcana
 * @category    Core
 * @author      Neil Brayfield
 */
class Filesystem extends \Phalcon\Di\Injectable
{

    /**
     * @var array List of modules to loop through
     **/
    protected $modules = array('Phalcana' => SYSPATH);

    /**
     * @var array Temporary store for the cached files
     **/
    private $cacheData;



    /**
     * Sets the modules and paths for the cascading file system
     *
     * @param array
     **/
    public function setModules($modules)
    {
        foreach ($modules as $namespace => $path) {
            $modules[$namespace] = $path;
        }
        $modules['Phalcana'] = SYSPATH;

        $this->modules = $modules;
    }

    /**
     * Get the current module list
     *
     * @return  array
     **/
    public function getModules()
    {
        return $this->modules;
    }


    /**
     * Cascading filesystem, this is only used if the path is pointed at the APPPATH
     *
     * @param Event The event object passed by the class loader
     * @param Phalcon\Loader The loader processing the event
     * @param string The current class being loaded
     **/
    public function afterCheckClass($event, $loader, $class)
    {
        // Check that the namespace is Phalcana
        if (strpos($class, 'Phalcana') !== 0) {
            return false;
        }

        // Loop through modules and swap out the directories and attempt to load manually
        foreach ($this->modules as $path):
            $new_path = str_replace(APPPATH, $path, $loader->getCheckedPath());
            if (file_exists($new_path)) {
                $new_path = realpath($new_path);
                require_once $new_path;
                $this->saveToCache($class, $new_path);
                break;
            }

        endforeach;

    }


    /**
     * Find a file through the cascading file system
     *
     * @param   string  The directory to search in
     * @param   string  The file to search for
     * @param   string  File extension to search for
     * @param   bool    Wether to bother searching in the app directory
     * @param   bool    Wether to get all the files found or just the highest one found
     * @return  false If no file is found
     **/
    public function findFile($dir, $file, $ext = 'php', $includeApp = true, $array = false)
    {

        if ($dir != '') {
            $dir .= '/';
        }

        $paths = $includeApp ? array('app' => APPPATH) + $this->modules : $this->modules;

        if ($array) {
            $result = array();
        }

        // Loop through modules and try to find an existing file
        foreach ($paths as $path):

            $new_path = $path.$dir.$file.'.'.$ext;
            if (file_exists($new_path)) {
                if ($array) {
                    $result[] = $new_path;
                } else {
                    return realpath($new_path);
                }
            }

        endforeach;

        // nothing found
        if ($array && count($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Find a file through the cascading file system
     *
     * @param   string Directory to search for files in
     * @param   array  The paths to search in. By default this is all the modules and the `APPPATH` and `SYSPATH` folders
     * @param   bool  Show hidden files
     * @return  array All the files found within the directory structure
     **/
    public function listFiles($directory, array $paths = null, $hidden = false)
    {
        if ($paths === null) {
            $paths = array('app' => APPPATH) + $this->modules;
        }

        $directory .= DIRECTORY_SEPARATOR;

        // Create an array for the files
        $found = array();

        foreach ($paths as $path) {
            if (is_dir($path.$directory)) {

                // Create a new directory iterator
                $dir = new \DirectoryIterator($path.$directory);

                // Iterate the tree
                foreach ($dir as $file) {
                    // Get the file name
                    $filename = $file->getFilename();

                    if (($filename[0] === '.' && $hidden == false) || $filename[strlen($filename)-1] === '~') {
                        // Skip all hidden files and UNIX backup files
                        continue;
                    }

                    if ($hidden == true && $filename[0] === '.') {
                        if ($filename === '.' || $filename === '..') {
                            continue;
                        }
                    }

                    // Relative filename is the array key
                    $key = $directory.$filename;

                    if ($file->isDir()) {
                        if ($sub_dir = $this->listFiles($key, $paths, $hidden)) {
                            if (isset($found[$key])) {
                                // Append the sub-directory list
                                $found[$key] += $sub_dir;
                            } else {
                                // Create a new sub-directory list
                                $found[$key] = $sub_dir;
                            }
                        }
                    } else {
                        if (! isset($found[$key])) {
                            // Add new files to the list
                            $found[$key] = realpath($file->getPathName());
                        }
                    }
                }
            }
        }

        // Sort the results alphabetically
        ksort($found);

        return $found;

    }

    /**
     * Saves paths to the cache
     *
     * @param string Class name
     * @param string Resolved path location
     **/
    public function saveToCache($class, $path)
    {
        if (!$this->setup->path_cache) {
            return;
        } elseif ($this->setup->path_cache == 'auto' && \Phalcana\Phalcana::$mode === \Phalcana\Phalcana::DEVELOPMENT) {
            return;
        }

        // cache is empty load a new array
        if (is_null($this->cacheData)) {
            $arr = $this->cache->get('classes');
            if (!is_array($arr)) {
                $this->cacheData = array();
            }
        }

        $this->cacheData[$class] = $path;

        $this->cache->save('classes', $this->cacheData);
    }
}
