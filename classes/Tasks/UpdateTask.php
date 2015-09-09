<?php

namespace Phalcana\Tasks;



/**
 * Install task. This is design to steramline the installation of the framework.
 *
 * The asks questions interactively to perform the setup
 *
 * @package    Phalcana
 * @category   Tasks
 * @author     Neil Brayfield
 */
class UpdateTask extends \Phalcon\CLI\Task
{

    /**
     * Update tasks like clearing cache etc
     *
     * @return  void
     **/
    public function mainAction()
    {
        $this->cli->write($this->cli->color("\nRunning update tasks\n", 'green'));



        $this->cli->write($this->cli->color("\Update complete.\n", 'green'));


    }
}
