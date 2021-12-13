<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;

class Utility
{

    private ProjectPath $apppaths;
    private Kernel $kernel;

    public function __construct(Kernel $kernel, ProjectPath $projectpath)
    {
        $this->apppaths = $projectpath;
        $this->kernel = $kernel;
    }
    /**
     *
     * @param string $env
     * @return array<mixed>
     */
    public function clearcache($env = '') : array
    {
        if (!$env) {
            $env = $this->kernel->getEnvironment();
        }

        $command = $this->apppaths->getConsoleExecute() . ' cache:clear --env=' . $env;

        return self::runCommand($command);
    }
    /**
     *
     * @param string $command
     * @param string $workingdir
     * @return array<mixed>
     */
    public static function runCommand(string $command, string $workingdir = '.')
    {
        /* @var $process \Symfony\Component\Process\Process */
        $process = Process::fromShellCommandline($command);

        if ($workingdir) {
            $process->setWorkingDirectory($workingdir);
        }
        $process->setTimeout(60 * 60 * 24);
        $process->run();

        if (!$process->isSuccessful()) {
            $return = array('errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando ' . $command . "\n" . $process->getErrorOutput() . "\n" . $process->getOutput(),);
        } else {
            $return = array('errcode' => 0,
                'command' => $command,
                'message' => $process->getOutput(),
            );
        }

        return $return;
    }
    
    /**
     *
     * @param string $command
     * @param array<mixed> $options
     * @return array<mixed>
     */
    public function runSymfonyCommand(string $command, array $options = array()) : array
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
