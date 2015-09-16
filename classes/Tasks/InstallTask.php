<?php

namespace Phalcana\Tasks;

use Phalcon\CLI\Task;

/**
 * This CLI task is designed to steramline the installation of the framework by asking
 * questions interactively to write the config files.
 *
 * There are two ways of running this function. Both are run from the root of the project.
 *
 *  - Using the phalcana shortcut `./phalcana install`
 *  - Using the PHP CLI `php index.php install`
 *
 * @package    Phalcana
 * @category   Tasks
 * @author     Neil Brayfield
 */
class InstallTask extends Task
{

    /**
     * @var array List of modes to select from.
     */
    protected $modes = array(
        1 => 'PRODUCTION',
        2 => 'STAGING',
        3 => 'TESTING',
        4 => 'DEVELOPMENT',
    );


    /**
     * Main action for the task that controls the whole script
     **/
    public function mainAction()
    {
        $this->cli->write($this->cli->color("\nRunning installer\n", 'green'));


        // get options for the config and htaccess
        $mode = $this->getMode();
        $base = $this->getBase();
        $domain = $this->getDomain();

        $this->cli->write();

        $this->updatePermissions();

        $this->cli->write();

        $this->writeConfig($mode, $base, $domain);
        $this->writeHtaccess($base, $domain);


        $this->cli->write($this->cli->color("\nInstallation complete.\n", 'green'));

        // some files may have been cached to load the installer delete them now to prevent permissions errors
        $this->cache->delete('classes');
        exit();

    }

    /**
     * Just install a template
     **/
    public function templateAction()
    {
        $template = $this->selectTemplate();

        $this->installTemplate($template);

    }

    /**
     * Ask the user which development mode to use.
     *
     * @param   bool Ask the question?
     * @return  string  The constant of the environment mode
     **/
    protected function getMode($question = true)
    {
        if ($question) {
            $this->question("What mode would you like to run Phalcana in?");

            foreach ($this->modes as $key => $value) {
                echo "$key - $value \n";
            }
        }

        $this->cli->write();

        $answer = $this->cli->read("Please enter the number of your choice");

        if (isset($this->modes[$answer])) {
            $this->cli->write();
            return 'Phalcana\Phalcana::'.$this->modes[$answer];
        } else {
            $this->cli->write();
            $this->cli->write($this->cli->color('Invalid anwser', 'light_red'));

            return $this->getMode(false);

        }

    }

    /**
     * Gets the path relative to the web root for the config and htaccess
     *
     * @param   bool Ask the question?
     * @return  string The base url to use
     **/
    protected function getBase($choices = false)
    {
        if (!$choices) {
            $choices = array();

            $this->question("What is the base path of your project relative to your web root?");

            $dirs = array_reverse(explode(DIRECTORY_SEPARATOR, DOCROOT));
            foreach ($dirs as $key => $value) {
                $dir = $dirs[0];
                for ($i = 1; $i <= $key; $i++) {
                    $dir = $dirs[$i].'/'.$dir;
                }

                if ($key == count($dirs) - 1) {
                    continue;
                }

                $choices[$key+1] = '/'.$dir;
                echo ($key + 1).' - /'.$dir."\n";

            }
        }

        $this->cli->write();

        $answer = $this->cli->read("Please enter the number of your choice");

        if (isset($choices[$answer])) {
            $this->cli->write();
            return $choices[$answer];
        } else {
            $this->cli->write();
            $this->cli->write($this->cli->color('Invalid anwser', 'light_red'));

            return $this->getBase($choices);

        }

    }

    /**
     * Get the cononical domain
     **/
    public function getDomain()
    {
        $this->question("What is the primary domain of your website or host? (eg. http://localhost)");
        $answer = $this->cli->read("\nPlease enter");

        return $answer;
    }


    /**
     * Write the config file based off the `config/example.setup.php`.
     **/
    public function writeConfig($mode, $base, $domain)
    {

        $setup = file_get_contents(APPPATH.'config/example.setup.php');

        $setup = preg_replace("/(\s*'mode' => )(.*?)(,)/", "$1".$mode.'$3', $setup);

        $setup = preg_replace("/(\s*'base_url' => ')(.*?)(',)/", "$1".$base.'$3', $setup);

        $setup = preg_replace("/(\s*'static_base_url' => ')(.*?)(',)/", "$1".$domain.$base.'$3', $setup);

        $this->cli->write($this->cli->color("Writng - app/config/setup.php", 'cyan'));

        file_put_contents(APPPATH.'config/setup.php', $setup);

    }

    /**
     * Write the config file based off the `config/example.setup.php`.
     **/
    public function writeHtaccess($base, $domain)
    {

        $htaccess = file_get_contents(DOCROOT.'example.htaccess');

        $htaccess = preg_replace("/(\s*RewriteBase\s+)(.*)/", "$1".$base, $htaccess);

        $this->cli->write($this->cli->color("Writng - .htaccess", 'cyan'));

        file_put_contents(DOCROOT.'.htaccess', $htaccess);
    }


    /**
     * Allow write acces to log and cache folders
     **/
    public function updatePermissions()
    {
        $this->cli->write($this->cli->color("Updating permissions", 'cyan'));
        chmod(APPPATH.'cache', 0777);
        chmod(APPPATH.'logs', 0777);
    }


    /**
     * Select a template from the installed templates
     *
     * @uses Phalcana\Core\Filesystem::listFiles
     * @param  bool Options to select from, this also negates the asking of the question
     * @return string template directory to install
     * @return false Don't install a template
     **/
    public function selectTemplate($options = false)
    {

        if (!$options) {
            $options = array();
            $this->question('Select a frontend template to install?');
            $dirs = $this->fs->listFiles('templates');

            foreach ($dirs as $dir => $value) {
                $option = str_replace('templates'.DIRECTORY_SEPARATOR, '', $dir);
                $options[count($options)+1] = $option;
                echo count($options). ' - '. ucwords($option)."\n";
            }
        }

        $this->cli->write();

        $answer = $this->cli->read("Please enter the number of your choice");

        if (isset($options[$answer])) {
            $this->cli->write();
            return $options[$answer];
        } else {
            $this->cli->write();
            $this->cli->write($this->cli->color('Invalid anwser', 'light_red'));

            return $this->selectTemplate($options);

        }



    }

    /**
     * Select a template from the installed templates
     *
     * @uses Phalcana\Core\Filesystem::listFiles
     * @param string Directory of the template to install
     **/
    public function installTemplate($template)
    {
        $readme = false;

        //
        $files = $this->fs->listFiles('templates'.DIRECTORY_SEPARATOR.$template, null, true);
        $files = $this->arr->flatten($files);

        foreach ($files as $key => $file) {
            $relative = str_replace('templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR, '', $key);

            if ($relative == 'README.md') {
                $readme = $file;
                continue;
            }

            $this->cli->write($this->cli->color("Creating - $relative", 'cyan'));

            $targetDir = dirname(DOCROOT.$relative);


            if (!file_exists($targetDir)) {
                mkdir($targetDir, fileperms(dirname($file)), true);
            }

            copy($file, DOCROOT.$relative);
        }

        if ($readme !== false) {
            $this->cli->write($this->cli->color("\nTemplate installed. Please read the readme below for usage instructions\n", 'green'));
            echo file_get_contents($readme);
        } else {
            $this->cli->write($this->cli->color("\nTemplate installed.\n", 'green'));
        }

        $this->cli->write();

    }


    /**
     * Writes a question, this is just a shortcut
     **/
    public function question($text)
    {
        $this->cli->write($this->cli->color($text, 'yellow'));
    }
}
