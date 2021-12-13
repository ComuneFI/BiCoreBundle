<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;

/**
 * @codeCoverageIgnore
 */
class CheckgitversionCommand extends Command
{
    protected static $defaultName = 'pannelloamministrazione:checkgitversion';
    
    private ProjectPath $projectpath;
    
    protected function configure() : void
    {
        $this
                ->setDescription('Controllo versioni bundles')
                ->setHelp('Controlla le versioni git dei bundles');
    }

    public function __construct(ProjectPath $projectpath)
    {
        $this->projectpath = $projectpath;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if (self::isWindows()) {
            $output->writeln('<info>Non previsto in ambiente windows</info>');

            return 0;
        }

        $composerbundles = array();
        $papath = $this->projectpath;
        $composerbundlespath = $papath->getVendorBinPath().'/../fi';
        $findercomposerbundle = new Finder();
        $findercomposerbundle->in($composerbundlespath)->sortByName()->directories()->depth('== 0');

        foreach ($findercomposerbundle as $file) {
            $fullcomposerbundlepath = $composerbundlespath.DIRECTORY_SEPARATOR.$file->getBasename();
            $local = $this->getGitVersion($fullcomposerbundlepath, false);
            $remote = $this->getGitVersion($fullcomposerbundlepath, true);
            $style = new OutputFormatterStyle('blue', 'white', array('bold', 'blink'));
            $output->getFormatter()->setStyle('warning', $style);
            if ($local !== $remote) {
                $remote = '<warning> * '.$remote.' * </warning>';
            }
            $output->writeln('<info>'.$file->getBasename().'</info> '.$local.' -> '.$remote);

            $composerbundles[] = array(
                'name' => $file->getBasename(),
                'path' => $fullcomposerbundlepath,
                'version' => $this->getGitVersion($fullcomposerbundlepath),
            );
        }

        return 0;
    }

    private function getGitVersion(string $path, bool $remote = false) : string
    {
        if (self::isWindows()) {
            return '';
        }

        if ($remote) {
            //Remote
            $cmd = 'cd '.$path;
            $remotetagscmd = "git ls-remote -t | awk '{print $2}' | cut -d '/' -f 3 | cut -d '^' -f 1 | sort --version-sort | tail -1";
            $remotetag = $cmd.';'.$remotetagscmd;
            $process = Process::fromShellCommandline($remotetag);
            $process->setTimeout(60 * 100);
            $process->run();
            if ($process->isSuccessful()) {
                $versions = trim($process->getOutput());

                return $this->getRemoteVersionString($versions);
            }

            return '?';
        } else {
            //Local
            $cmd = 'cd '.$path;
            $process = Process::fromShellCommandline($cmd.';git branch | '."grep ' * '");
            $process->setTimeout(60 * 100);
            $process->run();
            if ($process->isSuccessful()) {
                $versions = explode(chr(10), $process->getOutput());

                return $this->getLocalVersionString($versions);
            } else {
                //echo $process->getErrorOutput();
                return '?';
            }
        }
    }

    /**
     *
     * @param array<string> $versions
     * @return string
     */
    private function getLocalVersionString(array $versions) : string
    {
        foreach ($versions as $line) {
            if (false !== strpos($line, '* ')) {
                $version = trim(strtolower(str_replace('* ', '', $line)));

                return $this->getLocalVersionStringDetail($version);
            }
        }

        return '?';
    }

    private function getLocalVersionStringDetail(string $versions) :string
    {
        if ('master' == $versions) {
            return $versions;
        } else {
            $matches = [];
            if (preg_match('/\d+(?:\.\d+)+/', $versions, $matches)) {
                return $matches[0]; //returning the first match
            }
        }

        return '?';
    }

    private function getRemoteVersionString(string $versions) :string
    {
        $matches = [];
        if (preg_match('/\d+(?:\.\d+)+/', $versions, $matches)) {
            return $matches[0]; //returning the first match
        }

        return '?';
    }

    public static function isWindows() : bool
    {
        if (PHP_OS == 'WINNT') {
            return true;
        } else {
            return false;
        }
    }
}
