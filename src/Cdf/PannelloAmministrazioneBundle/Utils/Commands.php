<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;

class Commands
{
    /* @var $apppaths ProjectPath */

    private $apppaths;
    /* @var $pammutils Utility */
    private $pammutils;

    public function __construct(ProjectPath $projectpath, Utility $pautils)
    {
        $this->apppaths = $projectpath;
        $this->pammutils = $pautils;
    }

    // @codeCoverageIgnoreStart
    public function getVcs()
    {
        $fs = new Filesystem();

        $projectDir = $this->apppaths->getRootPath();
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.svn')) {
            $command = 'svn update';
        }
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.git')) {
            $command = 'git pull';
        }
        if (!$command) {
            throw new Exception('Vcs non trovato', 100);
        }

        return $this->pammutils->runCommand($command, $projectDir);
    }

    // @codeCoverageIgnoreEnd
    public function generateEntity($wbFile)
    {
        $command = 'pannelloamministrazione:generateormentities';
        $result = $this->pammutils->runSymfonyCommand($command, array('mwbfile' => $wbFile));

        if (0 != $result['errcode']) {
            return array(
                'errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando:' . $command . ';' . $result['message'],
            );
        }

        return array(
            'errcode' => 0,
            'command' => $command,
            'message' => 'Eseguito comando:' . $command . ';' . $result['message'],);
    }

    public function generateFormCrud($entityform, $generatemplate, $isAPI = false)
    {
        // check if some item already exist, and it interrupts the execution if any
        $pannelloamministrazioneentity = $entityform;
        /* @var $fs Filesystem */
        if ($isAPI) {
            $entityform = substr($pannelloamministrazioneentity, strpos($pannelloamministrazioneentity, ".") + 1);
            $projectname = substr($pannelloamministrazioneentity, 0, strpos($pannelloamministrazioneentity, "."));
        } else {
            $entityform = $pannelloamministrazioneentity;
            $projectname = "";
        }
        $resultchk = $this->checkFormCrud($entityform, $projectname, $isAPI);

        if (0 !== $resultchk['errcode']) {
            return $resultchk;
        }
        $formcrudparms = array('entityform' => $entityform, '--generatemplate' => $generatemplate);
        if ($isAPI) {
            $formcrudparms['--isApi'] = true;
            $formcrudparms['--projectname'] = $projectname;
        }

        $retmsggenerateform = $this->pammutils->runSymfonyCommand('pannelloamministrazione:generateformcrud', $formcrudparms);

        $retmsg = array(
            'errcode' => $retmsggenerateform['errcode'],
            'command' => $retmsggenerateform['command'],
            'message' => $retmsggenerateform['message'],
        );

        return $retmsg;
    }

    public function checkFormCrud($entityform, string $projectname = "", bool $isAPI = false)
    {
        /* @var $fs Filesystem */
        $fs = new Filesystem();
        $srcPath = $this->apppaths->getSrcPath();
        $appPath = $srcPath;
        if (!is_writable($appPath)) {
            return array('errcode' => -1, 'message' => $appPath . ' non scrivibile');
        }

        if (!$isAPI) {
            //Look for Entities... but they should already exist...
            $entityPath = $appPath . '/Entity' . DIRECTORY_SEPARATOR . $entityform . '.php';
            if (!$fs->exists($entityPath)) {
                return array('errcode' => -1, 'message' => $entityPath . ' entity non trovata');
            }
        } else {
            $modelClass = ApiUtils::getModelClass($projectname, $entityform);
            if (!class_exists($modelClass)) {
                return array('errcode' => -1, 'message' => $modelClass . ' model not found');
            }
        }

        $formPath = $appPath . '/Form/' . $entityform . 'Type.php';
        if ($fs->exists($formPath)) {
            return array('errcode' => -1, 'message' => $formPath . ' esistente');
        }

        $controllerPath = $appPath . '/Controller' . DIRECTORY_SEPARATOR . $entityform . 'Controller.php';

        if ($fs->exists($controllerPath)) {
            return array('errcode' => -1, 'message' => $controllerPath . ' esistente');
        }

        $viewPathSrc = $this->apppaths->getTemplatePath() . DIRECTORY_SEPARATOR . $entityform;

        if ($fs->exists($viewPathSrc)) {
            return array('errcode' => -1, 'message' => $viewPathSrc . ' esistente');
        }

        return array('errcode' => 0, 'message' => 'OK');
    }

    public function clearcache()
    {
        $cmdoutput = '';
        //$envs = array('dev', 'test', 'prod');
        $envs[] = getenv("APP_ENV");
        foreach ($envs as $env) {
            $result = $this->pammutils->clearcache($env);
            $cmdoutput = $cmdoutput . $result['message'];
            if (0 !== $result['errcode']) {
                return $result;
            }
            $result['message'] = $cmdoutput;
        }

        return $result;
    }

    public function aggiornaSchemaDatabase()
    {
        $result = $this->pammutils->runSymfonyCommand('doctrine:schema:update', array('--force' => true));

        return $result;
    }
}
