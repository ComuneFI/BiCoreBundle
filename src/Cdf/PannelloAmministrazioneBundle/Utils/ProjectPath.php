<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Exception;

class ProjectPath
{
    /**
     * La funzione ritorna un array con i path dell'applicazione.
     *
     * @param $container Container dell'applicazione
     *
     * @return array Ritorna l'array contenente i path
     */
    private $rootdir;
    private $projectdir;
    private $cacheDir;
    private $logsDir;

    public function setRootDir($projectDir)
    {
        $this->projectdir = dirname($projectDir);
        $this->rootdir = dirname($projectDir);
    }

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function setLogsDir($logsDir)
    {
        $this->logsDir = $logsDir;
    }

    public function getRootPath()
    {
        return $this->rootdir;
    }

    public function getProjectPath()
    {
        return $this->projectdir;
    }

    public function getBinPath()
    {
        $bindir = $this->getProjectPath().'/bin';
        if (!file_exists($bindir)) {
            $bindir = realpath($this->getProjectPath().'/../bin');
        }
        if (!file_exists($bindir)) {
            throw new Exception('Cartella Bin non trovata', -100);
        }

        return $bindir;
    }

    public function getVendorBinPath()
    {
        $vendorbindir = $this->getProjectPath().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin';
        if (!file_exists($vendorbindir)) {
            $vendorbindir = dirname($this->getProjectPath()).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin';
            if (!file_exists($vendorbindir)) {
                throw new Exception('Cartella Bin in vendor non trovata', -100);
            }
        }

        return $vendorbindir;
    }

    public function getSrcPath()
    {
        $srcdir = $this->getProjectPath().DIRECTORY_SEPARATOR.'src';

        return $srcdir;
    }

    public function getPublicPath()
    {
        $publicdir = $this->getProjectPath().DIRECTORY_SEPARATOR.'public';

        return $publicdir;
    }

    public function getTemplatePath()
    {
        $srcdir = $this->getProjectPath().DIRECTORY_SEPARATOR.'templates';

        return realpath($srcdir);
    }

    public function getVarPath()
    {
        $vardir = $this->getProjectPath().DIRECTORY_SEPARATOR.'var';

        return realpath($vardir);
    }

    public function getDocPath()
    {
        $docdir = $this->getProjectPath().DIRECTORY_SEPARATOR.'doc';

        return realpath($docdir);
    }

    public function getCachePath()
    {
        $cachedir = $this->cacheDir;
        if (!file_exists($cachedir)) {
            throw new Exception('Cache non trovata', -100);
        }

        return $cachedir;
    }

    public function getLogsPath()
    {
        $logsdir = $this->logsDir;
        if (!file_exists($logsdir)) {
            throw new Exception('Logs non trovata', -100);
        }

        return $logsdir;
    }

    public function getConsole()
    {
        $console = $this->getBinPath().'/console';
        // Questo codice per versioni che usano un symfony 2 o 3
        if (!file_exists($console)) {
            throw new Exception('Console non trovata', -100);
        }

        return $console;
    }
}
