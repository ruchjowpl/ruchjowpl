<?php

//require_once 'phing/Task.php';
require_once '../../vendor/phing/phing/classes/phing/Task.php';

/**
 * Executes a Symfony console command. Build on ExecTask
 *
 * @author Jakub Paszkiewicz <j.paszkiewicz@stermedia.pl>
 * @license MIT
 * @package   phing.tasks.ext
 */
class Symfony2ConsoleTask extends Task
{

    /**
     * path to Symfony console application
     * @var string
     */
    private $symfonyConsole = 'app/console';

    /**
     * Symfony env
     * @var string
     */
    private $symfonyEnv = 'dev';

    /**
     * Given command
     * @var string
     */
    protected $command;

    /**
     * Commandline managing object
     *
     * @var Commandline
     */
    protected $commandline;

    /**
     * Working directory.
     * @var PhingFile
     */
    protected $dir;

    /**
     * Operating system.
     * @var string
     */
    protected $os;

    /**
     * Whether to escape shell command using escapeshellcmd().
     * @var boolean
     */
    protected $escape = false;

    /**
     * Where to direct output.
     * @var File
     */
    protected $output;

    /**
     * Whether to use PHP's passthru() function instead of exec()
     * @var boolean
     */
    protected $passthru = true;

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     * @var boolean
     */
    protected $logOutput = false;

    /**
     * Logging level for status messages
     * @var integer
     */
    protected $logLevel = Project::MSG_VERBOSE;

    /**
     * Where to direct error output.
     * @var File
     */
    protected $error;

    /**
     * If spawn is set then [unix] programs will redirect stdout and add '&'.
     * @var boolean
     */
    protected $spawn = false;

    /**
     * Property name to set with return value from exec call.
     *
     * @var string
     */
    protected $returnProperty;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Whether to check the return code.
     * @var boolean
     */
    protected $checkReturn = false;


    /**
     *
     */
    public function __construct()
    {
        $this->commandline = new Commandline();
    }

    /**
     * Initialize the interpreter with the Phing property php.interpreter
     */
    public function init()
    {
        $this->setSymfonyEnv($this->project->getProperty('symfony.env'));
    }

    /**
     * Main method: wraps execute() command.
     *
     * @return void
     */
    public function main()
    {
        if (!$this->isApplicable()) {
            return;
        }

        $this->prepare();
        $this->buildCommand();
        list($return, $output) = $this->executeCommand();
        $this->cleanup($return, $output);
    }

    /**
     * Checks whether the command shall be executed
     *
     * @return boolean False if the exec command shall not be run
     */
    protected function isApplicable()
    {
        if ($this->os === null) {
            return true;
        }

        $myos = Phing::getProperty('os.name');
        $this->log('Myos = ' . $myos, Project::MSG_VERBOSE);

        if (strpos($this->os, $myos) !== false) {
            // this command will be executed only on the specified OS
            // OS matches
            return true;
        }

        $this->log(
            sprintf(
                'Operating system %s not found in %s',
                $myos,
                $this->os
            ),
            Project::MSG_VERBOSE
        );

        return false;
    }

    /**
     * Prepares the command building and execution, i.e.
     * changes to the specified directory.
     *
     * @throws BuildException
     * @return void
     */
    protected function prepare()
    {
        if ($this->dir === null) {
            return;
        }

        // expand any symbolic links first
        if (!$this->dir->getCanonicalFile()->isDirectory()) {
            throw new BuildException(
                "'" . (string) $this->dir . "' is not a valid directory"
            );
        }
        $this->currdir = getcwd();
        @chdir($this->dir->getPath());
    }

    /**
     * Builds the full command to execute and stores it in $command.
     *
     * @throws BuildException
     * @return void
     * @uses   $command
     */
    protected function buildCommand()
    {


        if ($this->command === null) {
            throw new BuildException(
                'Symfony2ConsoleTask: Please provide "command"'
            );
        } else {
            if ($this->escape == true) {
                $this->command = escapeshellcmd($this->command);
            }
        }

        if ($this->error !== null) {
            $this->command .= ' 2> ' . escapeshellarg($this->error->getPath());
            $this->log(
                "Writing error output to: " . $this->error->getPath(),
                $this->logLevel
            );
        }

        if ($this->output !== null) {
            $this->command .= ' 1> ' . escapeshellarg($this->output->getPath());
            $this->log(
                "Writing standard output to: " . $this->output->getPath(),
                $this->logLevel
            );
        } elseif ($this->spawn) {
            $this->command .= ' 1>/dev/null';
            $this->log("Sending output to /dev/null", $this->logLevel);
        }

        // If neither output nor error are being written to file
        // then we'll redirect error to stdout so that we can dump
        // it to screen below.

        if ($this->output === null && $this->error === null && $this->passthru === false) {
            $this->command .= ' 2>&1';
        }

        // we ignore the spawn boolean for windows
        if ($this->spawn) {
            $this->command .= ' &';
        }

        $this->commandline->setExecutable($this->project->getProperty('php.interpreter'));
        $this->commandline->createArgument(true)->setValue($this->getCommand());
        $this->commandline->createArgument(true)->setValue('--env='.$this->getSymfonyEnv());
        $this->commandline->createArgument(true)->setValue($this->getSymfonyConsole());
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @return array array(return code, array with output)
     */
    protected function executeCommand()
    {
        $this->log("Executing command: " . $this->command, $this->logLevel);

        $output = array();
        $return = null;

        if ($this->passthru) {
            passthru($this->commandline, $return);
        } else {
            exec($this->commandline, $output, $return);
        }

        return array($return, $output);
    }

    /**
     * Runs all tasks after command execution:
     * - change working directory back
     * - log output
     * - verify return value
     *
     * @param integer $return Return code
     * @param array $output Array with command output
     *
     * @throws BuildException
     * @return void
     */
    protected function cleanup($return, $output)
    {
        if ($this->dir !== null) {
            @chdir($this->currdir);
        }

        $outlogLevel = $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE;
        foreach ($output as $line) {
            $this->log($line, $outlogLevel);
        }

        if ($this->returnProperty) {
            $this->project->setProperty($this->returnProperty, $return);
        }

        if ($this->outputProperty) {
            $this->project->setProperty(
                $this->outputProperty,
                implode("\n", $output)
            );
        }

        if ($return != 0 && $this->checkReturn) {
            throw new BuildException("Task exited with code $return");
        }
    }

    /**
     * sets the path to Symfony console
     *
     * @param string $symfonyConsole
     */
    public function setSymfonyConsole($symfonyConsole)
    {
        $this->symfonyConsole = $symfonyConsole;
    }

    /**
     * returns the path to Symfony console
     *
     * @return string
     */
    public function getSymfonyConsole()
    {
        return $this->symfonyConsole;
    }

    /**
     * sets the Symfony Env
     *
     * @param string $symfonyEnv
     */
    public function setSymfonyEnv($symfonyEnv)
    {
        $this->symfonyEnv = $symfonyEnv;
    }

    /**
     * returns the Symfony Env
     *
     * @return string
     */
    public function getSymfonyEnv()
    {
        return $this->symfonyEnv;
    }

    /**
     * The command to use.
     *
     * @param mixed $command String or string-compatible (e.g. w/ __toString()).
     *
     * @return void
     */
    public function setCommand($command)
    {
        $this->command = "" . $command;
    }

    /**
     * Returns  the command to use.
     * @return string
     */
    public function getCommand()
    {
       return $this->command;
    }

    /**
     * Whether to use escapeshellcmd() to escape command.
     *
     * @param boolean $escape If the command shall be escaped or not
     *
     * @return void
     */
    public function setEscape($escape)
    {
        $this->escape = (bool) $escape;
    }

    /**
     * Specify the working directory for executing this command.
     *
     * @param PhingFile $dir Working directory
     *
     * @return void
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Specify OS (or muliple OS) that must match in order to execute this command.
     *
     * @param string $os Operating system string (e.g. "Linux")
     *
     * @return void
     */
    public function setOs($os)
    {
        $this->os = (string) $os;
    }

    /**
     * File to which output should be written.
     *
     * @param PhingFile $f Output log file
     *
     * @return void
     */
    public function setOutput(PhingFile $f)
    {
        $this->output = $f;
    }

    /**
     * File to which error output should be written.
     *
     * @param PhingFile $f Error log file
     *
     * @return void
     */
    public function setError(PhingFile $f)
    {
        $this->error = $f;
    }

    /**
     * Whether to use PHP's passthru() function instead of exec()
     *
     * @param boolean $passthru If passthru shall be used
     *
     * @return void
     */
    public function setPassthru($passthru)
    {
        $this->passthru = (bool) $passthru;
    }

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
     *
     * @param boolean $logOutput If output shall be logged visibly
     *
     * @return void
     */
    public function setLogoutput($logOutput)
    {
        $this->logOutput = (bool) $logOutput;
    }

    /**
     * Whether to suppress all output and run in the background.
     *
     * @param boolean $spawn If the command is to be run in the background
     *
     * @return void
     */
    public function setSpawn($spawn)
    {
        $this->spawn = (bool) $spawn;
    }

    /**
     * Whether to check the return code.
     *
     * @param boolean $checkReturn If the return code shall be checked
     *
     * @return void
     */
    public function setCheckReturn($checkReturn)
    {
        $this->checkReturn = (bool) $checkReturn;
    }

    /**
     * The name of property to set to return value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setReturnProperty($prop)
    {
        $this->returnProperty = $prop;
    }

    /**
     * The name of property to set to output value from exec() call.
     *
     * @param string $prop Property name
     *
     * @return void
     */
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }

    /**
     * Set level of log messages generated (default = verbose)
     *
     * @param string $level Log level
     *
     * @throws BuildException
     * @return void
     */
    public function setLevel($level)
    {
        switch ($level) {
            case 'error':
                $this->logLevel = Project::MSG_ERR;
                break;
            case 'warning':
                $this->logLevel = Project::MSG_WARN;
                break;
            case 'info':
                $this->logLevel = Project::MSG_INFO;
                break;
            case 'verbose':
                $this->logLevel = Project::MSG_VERBOSE;
                break;
            case 'debug':
                $this->logLevel = Project::MSG_DEBUG;
                break;
            default:
                throw new BuildException(
                    sprintf('Unknown log level "%s"', $level)
                );
        }
    }

    /**
     * Creates a nested <arg> tag.
     *
     * @return CommandlineArgument Argument object
     */
    public function createArg()
    {
        return $this->commandline->createArgument();
    }
}
