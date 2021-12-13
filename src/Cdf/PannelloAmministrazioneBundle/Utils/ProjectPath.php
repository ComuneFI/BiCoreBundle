<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Exception;

class ProjectPath
{

    private string $rootdir;
    private string $projectdir;
    private string $cacheDir;
    private string $logsDir;

    public function setRootDir(string $projectDir): void
    {
        $this->projectdir = $projectDir;
        $this->rootdir = $projectDir;
    }

    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    public function setLogsDir(string $logsDir): void
    {
        $this->logsDir = $logsDir;
    }

    public function getRootPath(): string
    {
        return $this->rootdir;
    }

    public function getProjectPath(): string
    {
        return $this->projectdir;
    }

    public function getBinPath(): string
    {
        $bindir = $this->getProjectPath() . '/bin';
        if (!file_exists($bindir)) {
            $bindir = realpath($this->getProjectPath() . '/../bin');
        }
        if (!file_exists($bindir)) {
            throw new Exception('Cartella Bin non trovata', -100);
        }

        return $bindir;
    }

    public function getVendorBinPath(): string
    {
        $vendorbindir = $this->getVendorPath() . DIRECTORY_SEPARATOR . 'bin';
        return $vendorbindir;
    }

    public function getVendorPath(): string
    {
        $vendorbindir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'vendor';
        if (!file_exists($vendorbindir)) {
            $vendorbindir = dirname($this->getProjectPath()) . DIRECTORY_SEPARATOR . 'vendor';
            if (!file_exists($vendorbindir)) {
                throw new Exception('Cartella vendor non trovata', -100);
            }
        }

        return $vendorbindir;
    }

    public function getSrcPath(): string
    {
        $srcdir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'src';

        return $srcdir;
    }

    public function getPublicPath(): string
    {
        $publicdir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'public';

        return $publicdir;
    }

    public function getTemplatePath(): string
    {
        $srcdir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'templates';

        return realpath($srcdir);
    }

    public function getVarPath(): string
    {
        $vardir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'var';

        return realpath($vardir);
    }

    public function getDocPath(): string
    {
        $docdir = $this->getProjectPath() . DIRECTORY_SEPARATOR . 'doc';

        return realpath($docdir);
    }

    public function getCachePath(): string
    {
        $cachedir = $this->cacheDir;
        if (!file_exists($cachedir)) {
            throw new Exception('Cache non trovata', -100);
        }

        return $cachedir;
    }

    public function getLogsPath(): string
    {
        $logsdir = $this->logsDir;
        if (!file_exists($logsdir)) {
            throw new Exception('Logs non trovata', -100);
        }

        return $logsdir;
    }

    public function getConsole(): string
    {
        $console = $this->getBinPath() . '/console';
        // Questo codice per versioni che usano un symfony 2 o 3
        if (!file_exists($console)) {
            throw new Exception('Console non trovata', -100);
        }

        return $console;
    }

    public function getConsoleExecute(): string
    {
        $console = $this->getConsole();
        if (\Fi\OsBundle\DependencyInjection\OsFunctions::isWindows()) {
            $console = \Fi\OsBundle\DependencyInjection\OsFunctions::getPHPExecutableFromPath() . " " . $console;
        }

        return $console;
    }
}
