<?php

namespace Cdf\PannelloAmministrazioneBundle\DependencyInjection;

use Symfony\Component\Filesystem\Filesystem;
use Fi\OsBundle\DependencyInjection\OsFunctions;

class PannelloamministrazioneCommands
{

    private $container;
    /* @var $apppaths \Cdf\PannelloAmministrazioneBundle\DependencyInjection\ProjectPath */
    private $apppaths;
    /* @var $pammutils \Cdf\PannelloAmministrazioneBundle\DependencyInjection\PannelloAmministrazioneUtils */
    private $pammutils;

    public function __construct($container)
    {
        $this->container = $container;
        $this->apppaths = $container->get("pannelloamministrazione.projectpath");
        $this->pammutils = $container->get("pannelloamministrazione.utils");
    }

    // @codeCoverageIgnoreStart
    public function getVcs()
    {
        $fs = new Filesystem();

        $sepchr = OsFunctions::getSeparator();
        $projectDir = $this->apppaths->getRootPath();
        $vcscommand = "";
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.svn')) {
            $vcscommand = 'svn update';
        }
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.git')) {
            $vcscommand = 'git pull';
        }
        if (!$vcscommand) {
            throw new \Exception("Vcs non trovato", 100);
        }
        $command = 'cd ' . $projectDir . $sepchr . $vcscommand;
        return $this->pammutils->runCommand($command);
    }

    // @codeCoverageIgnoreEnd
    public function generateEntity($wbFile)
    {
        $command = "pannelloamministrazione:generateormentities";
        $result = $this->pammutils->runSymfonyCommand($command, array('mwbfile' => $wbFile));

        if ($result["errcode"] != 0) {
            return array(
                'errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando:' . $command . ';' . $result["message"],
            );
        }

        return array(
            'errcode' => 0,
            'command' => $command,
            'message' => 'Eseguito comando:' . $command . ';' . $result["message"]);
    }

    public function generateFormCrud($entityform, $generatemplate)
    {
        /* @var $fs \Symfony\Component\Filesystem\Filesystem */
        $resultchk = $this->checkFormCrud($entityform);

        if ($resultchk["errcode"] !== 0) {
            return $resultchk;
        }
        $formcrudparms = array("entityform" => $entityform, "--generatemplate" => $generatemplate);

        $retmsggenerateform = $this->pammutils->runSymfonyCommand('pannelloamministrazione:generateformcrud', $formcrudparms);

        $retmsg = array(
            'errcode' => $retmsggenerateform['errcode'],
            'command' => $retmsggenerateform['command'],
            'message' => $retmsggenerateform['message'],
        );

        return $retmsg;
    }

    public function checkFormCrud($entityform)
    {
        /* @var $fs \Symfony\Component\Filesystem\Filesystem */
        $fs = new Filesystem();
        $srcPath = $this->apppaths->getSrcPath();
        $appPath = $srcPath;
        if (!is_writable($appPath)) {
            return array('errcode' => -1, 'message' => $appPath . ' non scrivibile');
        }
        $formPath = $appPath . '/Form/' . $entityform . 'Type.php';

        $entityPath = $appPath . '/Entity' . DIRECTORY_SEPARATOR . $entityform . '.php';

        if (!$fs->exists($entityPath)) {
            return array('errcode' => -1, 'message' => $entityPath . ' entity non trovata');
        }

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
        $cmdoutput = "";
        $envs = array("dev", "test", "prod");
        foreach ($envs as $env) {
            $result = $this->pammutils->clearcache($env);
            $cmdoutput = $cmdoutput . $result["message"];
            if ($result["errcode"] !== 0) {
                return $result;
            }
            $result["message"] = $cmdoutput;
        }
        return $result;
    }

    public function aggiornaSchemaDatabase()
    {
        $result = $this->pammutils->runSymfonyCommand('doctrine:schema:update', array('--force' => true));

        return $result;
    }
}
