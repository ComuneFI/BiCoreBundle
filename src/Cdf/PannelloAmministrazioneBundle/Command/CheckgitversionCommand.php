<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class CheckgitversionCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('pannelloamministrazione:checkgitversion')
                ->setDescription('Controllo versioni bundles')
                ->setHelp('Controlla le versioni git dei bundles');
    }

    // @codeCoverageIgnoreStart
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (self::isWindows()) {
            $output->writeln('<info>Non previsto in ambiente windows</info>');

            return 0;
        }

        $composerbundles = array();
        $papath = $this->getContainer()->get('pannelloamministrazione.projectpath');
        $composerbundlespath =  $papath->getVendorBinPath(). '/../fi';
        $findercomposerbundle = new Finder();
        $findercomposerbundle->in($composerbundlespath)->sortByName()->directories()->depth('== 0');

        foreach ($findercomposerbundle as $file) {
            $fullcomposerbundlepath = $composerbundlespath . DIRECTORY_SEPARATOR . $file->getBasename();
            $local = $this->getGitVersion($fullcomposerbundlepath, false);
            $remote = $this->getGitVersion($fullcomposerbundlepath, true);
            $style = new OutputFormatterStyle('blue', 'white', array('bold', 'blink'));
            $output->getFormatter()->setStyle('warning', $style);
            if ($local !== $remote) {
                $remote = '<warning> * ' . $remote . ' * </warning>';
            }
            $output->writeln('<info>' . $file->getBasename() . '</info> ' . $local . ' -> ' . $remote);

            $composerbundles[] = array(
                'name' => $file->getBasename(),
                'path' => $fullcomposerbundlepath,
                'version' => $this->getGitVersion($fullcomposerbundlepath),
            );
        }

        return 0;
    }

    private function getGitVersion($path, $remote = false)
    {
        if (self::isWindows()) {
            return '';
        }

        if ($remote) {
            //Remote
            $cmd = 'cd ' . $path;
            $remotetagscmd = "git ls-remote -t | awk '{print $2}' | cut -d '/' -f 3 | cut -d '^' -f 1 | sort --version-sort | tail -1";
            $remotetag = $cmd . ";" . $remotetagscmd;
            $process = new Process($remotetag);
            $process->setTimeout(60 * 100);
            $process->run();
            if ($process->isSuccessful()) {
                $versions = trim($process->getOutput());
                return $this->getRemoteVersionString($versions);
            }
            return '?';
        } else {
            //Local
            $cmd = 'cd ' . $path;
            $process = new Process($cmd . ';git branch | ' . "grep ' * '");
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

    private function getLocalVersionString($versions)
    {
        foreach ($versions as $line) {
            if (strpos($line, '* ') !== false) {
                $version = trim(strtolower(str_replace('* ', '', $line)));
                return $this->getLocalVersionStringDetail($version);
            }
        }
        return '?';
    }

    private function getLocalVersionStringDetail($versions)
    {
        if ($versions == 'master') {
            return $versions;
        } else {
            if (preg_match('/\d+(?:\.\d+)+/', $versions, $matches)) {
                return $matches[0]; //returning the first match
            }
        }
        return '?';
    }

    private function getRemoteVersionString($versions)
    {
        if (preg_match('/\d+(?:\.\d+)+/', $versions, $matches)) {
            return $matches[0]; //returning the first match
        }
        return '?';
    }

    public static function isWindows()
    {
        if (PHP_OS == 'WINNT') {
            return true;
        } else {
            return false;
        }
    }
// @codeCoverageIgnoreEnd
}
