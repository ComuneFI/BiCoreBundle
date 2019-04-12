<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Symfony\Component\Process\Process;
use Symfony\Component\Cache\Simple\FilesystemCache;

class VersioneExtension extends \Twig\Extension\AbstractExtension
{
    private $projectpath;

    public function __construct($projectpath)
    {
        $this->projectpath = $projectpath;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('versione_tag_git', array($this, 'versioneTagGit', 'is_safe' => array('html'))),
        );
    }

    public function versioneTagGit()
    {
        if ($this->isWindows()) {
            return 0;
        }
        $cache = new FilesystemCache();
        if ($cache->has('git_tag')) {
            $version = $cache->get('git_tag');
        } else {
            $projectDir = $this->projectpath;
            $process = new Process(array('git', 'describe', '--tags'));
            $process->setWorkingDirectory($projectDir);
            $process->setTimeout(60 * 100);
            $process->run();
            if ($process->isSuccessful()) {
                $out = explode(chr(10), $process->getOutput());

                $version = isset($out[0]) ? $out[0] : '0';
                $cache->set('git_tag', $version);
            } else {
                $version = '0';
            }
        }

        return $version;
    }

    private function isWindows()
    {
        if (PHP_OS == 'WINNT') {
            return true;
        } else {
            return false;
        }
    }
}
