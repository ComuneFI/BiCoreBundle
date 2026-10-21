<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;

class Utility
{

    public function __construct(private readonly Kernel $kernel, private readonly ProjectPath $apppaths)
    {
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
     * @return array<mixed>
     */
    public static function runCommand(string $command, string $workingdir = '.'): array
    {
        /* @var $process \Symfony\Component\Process\Process */
        $process = Process::fromShellCommandline($command);

        if ($workingdir) {
            $process->setWorkingDirectory($workingdir);
        }
        $process->setTimeout(60 * 60 * 24);
        $process->run();

        if (!$process->isSuccessful()) {
            return ['errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando ' . $command . "\n" . $process->getErrorOutput() . "\n" . $process->getOutput(),];
        }

        return ['errcode' => 0,
            'command' => $command,
            'message' => $process->getOutput(),
        ];
    }
    
    /**
     *
     * @param array<mixed> $options
     * @return array<mixed>
     */
    public function runSymfonyCommand(string $command, array $options = []) : array
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $cmdoptions = array_merge(['command' => $command], $options);

        $outputbuf = new BufferedOutput();
        // return the output, don't use if you used NullOutput()
        $returncode = $application->run(new ArrayInput($cmdoptions), $outputbuf);
        $output = $outputbuf->fetch();

        return ['errcode' => (0 === $returncode ? 0 : 1), 'command' => $cmdoptions['command'], 'message' => $output];
    }
}
