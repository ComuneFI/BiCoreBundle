<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility;

class Commands
{
    public function __construct(private readonly ProjectPath $apppaths, private readonly Utility $pammutils)
    {
    }

    /**
     * @codeCoverageIgnoreStart
     *
     * @return array<mixed>
     * @throws \Exception
     */
    public function getVcs(): array
    {
        $command = "";
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

    /**
     *
     * @codeCoverageIgnoreEnd
     *
     * @return array<mixed>
     */
    public function generateEntity(string $wbFile): array
    {
        $command = 'pannelloamministrazione:generateormentities';
        $result = $this->pammutils->runSymfonyCommand($command, ['mwbfile' => $wbFile]);

        if (0 != $result['errcode']) {
            return [
                'errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando:' . $command . ';' . $result['message'],
            ];
        }

        return [
            'errcode' => 0,
            'command' => $command,
            'message' => 'Eseguito comando:' . $command . ';' . $result['message'],];
    }

    /**
     *
     * @return array<mixed>
     */
    public function generateFormCrud(string $entityform, bool $generatemplate, bool $isAPI = false): array
    {
        // check if some item already exist, and it interrupts the execution if any
        $pannelloamministrazioneentity = $entityform;
        /* @var $fs Filesystem */
        if ($isAPI) {
            $strposEntity = strpos($pannelloamministrazioneentity, ".");
            if ($strposEntity === false) {
                return [
                    'errcode' => "-101",
                    'command' => "Ricerca entity",
                    'message' => "Impossibile trovare il . in " . $pannelloamministrazioneentity,
                ];
            }
            $entityform = substr($pannelloamministrazioneentity, $strposEntity + 1);
            $projectname = substr($pannelloamministrazioneentity, 0, $strposEntity);
        } else {
            $entityform = $pannelloamministrazioneentity;
            $projectname = "";
        }
        $resultchk = $this->checkFormCrud($entityform, $projectname, $isAPI);

        if (0 !== $resultchk['errcode']) {
            return $resultchk;
        }
        $formcrudparms = ['entityform' => $entityform, '--generatemplate' => $generatemplate];
        if ($isAPI) {
            $formcrudparms['--isApi'] = true;
            $formcrudparms['--projectname'] = $projectname;
        }

        $retmsggenerateform = $this->pammutils->runSymfonyCommand('pannelloamministrazione:generateformcrud', $formcrudparms);

        return [
            'errcode' => $retmsggenerateform['errcode'],
            'command' => $retmsggenerateform['command'],
            'message' => $retmsggenerateform['message'],
        ];
    }

    /**
     *
     * @return array<mixed>
     */
    public function checkFormCrud(string $entityform, string $projectname = "", bool $isAPI = false): array
    {
        /* @var $fs Filesystem */
        $fs = new Filesystem();
        $srcPath = $this->apppaths->getSrcPath();
        $appPath = $srcPath;
        if (!is_writable($appPath)) {
            return ['errcode' => -1, 'message' => $appPath . ' non scrivibile'];
        }

        if (!$isAPI) {
            //Look for Entities... but they should already exist...
            $entityPath = $appPath . '/Entity' . DIRECTORY_SEPARATOR . $entityform . '.php';
            if (!$fs->exists($entityPath)) {
                return ['errcode' => -1, 'message' => $entityPath . ' entity non trovata'];
            }
        } else {
            $apiUtil = new ApiUtils();
            $modelClass = $apiUtil->getModelClass($projectname, $entityform);
            if (!class_exists($modelClass)) {
                return ['errcode' => -1, 'message' => $modelClass . ' model not found'];
            }
        }

        $formPath = $appPath . '/Form/' . $entityform . 'Type.php';
        if ($fs->exists($formPath)) {
            return ['errcode' => -1, 'message' => $formPath . ' esistente'];
        }

        $controllerPath = $appPath . '/Controller' . DIRECTORY_SEPARATOR . $entityform . 'Controller.php';

        if ($fs->exists($controllerPath)) {
            return ['errcode' => -1, 'message' => $controllerPath . ' esistente'];
        }

        $viewPathSrc = $this->apppaths->getTemplatePath() . DIRECTORY_SEPARATOR . $entityform;

        if ($fs->exists($viewPathSrc)) {
            return ['errcode' => -1, 'message' => $viewPathSrc . ' esistente'];
        }

        return ['errcode' => 0, 'message' => 'OK'];
    }

    /**
     *
     * @return array<mixed>
     */
    public function clearcache(): array
    {
        $cmdoutput = '';
        //$envs = array('dev', 'test', 'prod');
        $env = getenv("APP_ENV");
        if ($env === false) {
            throw new Exception("APP_ENV non definita");
        }
        $result = $this->pammutils->clearcache($env);
        $cmdoutput .= $result['message'];
        if (0 !== $result['errcode']) {
            return $result;
        }
        $result['message'] = $cmdoutput;

        return $result;
    }

    /**
     *
     * @return array<mixed>
     */
    public function aggiornaSchemaDatabase(): array
    {
        return $this->pammutils->runSymfonyCommand('doctrine:schema:update', ['--force' => true]);
    }
}
