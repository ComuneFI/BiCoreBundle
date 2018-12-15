<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

class Utility
{
    private $apppaths;
    private $kernel;

    public function __construct($kernel, ProjectPath $projectpath)
    {
        $this->apppaths = $projectpath;
        $this->kernel = $kernel;
    }

    public function clearcache($env = '')
    {
        if (!$env) {
            $env = $this->kernel->getEnvironment();
        }

        $command = $this->apppaths->getConsole();
        $parametri = array('cache:clear', '--env='.$env);

        return self::runCommand($command, $parametri);
    }

    public static function runCommand($command, $parametri = array(), $workingdir = '')
    {
        /* @var $process \Symfony\Component\Process\Process */
        $process = new Process(array_merge(array($command), $parametri));
        if ($workingdir) {
            $process->setWorkingDirectory($workingdir);
        }
        $process->setTimeout(60 * 60 * 24);
        $process->run();

        if (!$process->isSuccessful()) {
            $return = array('errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando '.$command."\n".$process->getErrorOutput()."\n".$process->getOutput(), );
        } else {
            $return = array('errcode' => 0,
                'command' => $command,
                'message' => $process->getOutput(),
            );
        }

        return $return;
    }

    public function runSymfonyCommand($command, array $options = array())
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $cmdoptions = array_merge(array('command' => $command), $options);

        $outputbuf = new BufferedOutput();
        // return the output, don't use if you used NullOutput()
        $returncode = $application->run(new ArrayInput($cmdoptions), $outputbuf);
        $output = $outputbuf->fetch();

        return array('errcode' => (0 == $returncode ? 0 : 1), 'command' => $cmdoptions['command'], 'message' => $output);
    }
}
