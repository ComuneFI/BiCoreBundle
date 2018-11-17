<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Fi\OsBundle\DependencyInjection\OsFunctions;

class GenerateFormCommand extends ContainerAwareCommand
{

    protected $apppaths;
    protected $genhelper;
    protected $pammutils;

    protected function configure()
    {
        $this
                ->setName('pannelloamministrazione:generateformcrud')
                ->setDescription('Genera le views per il crud')
                ->setHelp('Genera le views per il crud, <br/>bi.mwb AppBundle default [--schemaupdate]<br/>')
                ->addArgument('entityform', InputArgument::REQUIRED, 'Il nome entity del form da creare');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        $this->apppaths = $this->getContainer()->get("pannelloamministrazione.projectpath");
        $pammutils = $this->getContainer()->get("pannelloamministrazione.utils");

        $bundlename = "App";
        $entityform = $input->getArgument('entityform');

        $phpPath = OsFunctions::getPHPExecutableFromPath();
        $command = $phpPath . ' ' . $this->apppaths->getConsole() . ' --env=dev make:form ';
        $resultcrud = $pammutils->runCommand($command . $entityform . "Type" . " " . $entityform);
        if ($resultcrud['errcode'] == 0) {
            $fs = new Filesystem();
            //Controller
            $controlleFile = $this->apppaths->getSrcPath() . '/Controller/' . $entityform . 'Controller.php';
            
            $formFile = $this->apppaths->getSrcPath() . '/Form/' . $entityform . 'Type.php';
            $line_i_am_looking_for = 8;
            $lines = file($formFile, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = 'use Symfony\Component\Form\Extension\Core\Type\SubmitType;';
            file_put_contents($formFile, implode("\n", $lines));
            
            $line_i_am_looking_for = 12;
            $lines = file($formFile, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = "        {\$submitparms = array("
                    . "'label' => 'Aggiorna ".$entityform."','attr' => array(\"class\" => \"btn-outline-primary bisubmit\"));";
            file_put_contents($formFile, implode("\n", $lines));
            
            $line_i_am_looking_for = 13;
            $lines = file($formFile, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = "        \$builder->add('submit', SubmitType::class, \$submitparms)";
            file_put_contents($formFile, implode("\n", $lines));

            $code = $this->getControllerCode(str_replace('/', '\\', $bundlename), $entityform);
            $fs->dumpFile($controlleFile, $code);
            $output->writeln("<info>Creato " . $controlleFile . "</info>");

            //Routing
            $retmsg = $this->generateFormRouting($entityform);
            //Twig template
            $this->copyTableStructureWiew($entityform);

            $this->generateFormsDefaultTableValues($entityform);
            $output->writeln("<info>" . $retmsg . "</info>");
            return 0;
        } else {
            $output->writeln("<error>" . $resultcrud['errmsg'] . "</error>");
            return 1;
        }
    }
    private function generateFormRouting($entityform)
    {
        //Routing del form
        $bundlename = 'App';
        $fs = new Filesystem();
        $routingFile = $this->apppaths->getSrcPath() . '/../config/routes/' . strtolower($entityform) . '.yml';

        $code = $this->getRoutingCode(str_replace('/', '', $bundlename), $entityform);
        $fs->dumpFile($routingFile, $code);

        $dest = $this->apppaths->getSrcPath() . '/../config/routes.yaml';

        $routingContext = str_replace('/', '', $bundlename) . '_' . $entityform . ':' . "\n" .
                '  resource: routes/' . strtolower($entityform) . '.yml' . "\n" .
                '  prefix: /' . $entityform . "\n\n";

        //Si fa l'append nel file routing del bundle per aggiungerci le rotte della tabella che stiamo gestendo
        $fh = file_get_contents($dest);
        if ($fh !== false) {
            file_put_contents($dest, $routingContext . $fh);
            $retmsg = 'Routing ' . $dest . " generato automaticamente da pannelloammonistrazionebundle\n\n* * * * CLEAR CACHE * * * *\n";
        } else {
            $retmsg = 'Impossibile generare il ruoting automaticamente da pannelloammonistrazionebundle\n';
        }

        return $retmsg;
    }
    private function copyTableStructureWiew($entityform)
    {
        $fs = new Filesystem();
        $publicfolder = $this->apppaths->getPublicPath();

        if (!$fs->exists($publicfolder)) {
            $publicfolder = realpath($this->apppaths->getVendorPath() . DIRECTORY_SEPARATOR . '../../public');
            $fs->mkdir($publicfolder . "/js", 0777);
            $fs->mkdir($publicfolder . "/css", 0777);
        }

        $templatetablefolder = $this->apppaths->getTemplatePath() . DIRECTORY_SEPARATOR . $entityform;
        $crudfolder = $this->apppaths->getVendorPath()
                . DIRECTORY_SEPARATOR . 'comunedifirenze/bicorebundle/src/Cdf/BiCoreBundle/Resources/views/Standard/Crud';
        if (!$fs->exists($crudfolder)) {
            $crudfolder = realpath($this->apppaths->getSrcPath()
                    . DIRECTORY_SEPARATOR . '../../src/Cdf/BiCoreBundle/Resources/views/Standard/Crud');
        }
        $tabellafolder = $this->apppaths->getVendorPath()
                . DIRECTORY_SEPARATOR . 'comunedifirenze/bicorebundle/src/Cdf/BiCoreBundle/Resources/views/Standard/Tabella';

        if (!$fs->exists($tabellafolder)) {
            $tabellafolder = realpath($this->apppaths->getVendorPath()
                    . DIRECTORY_SEPARATOR . '../../src/Cdf/BiCoreBundle/Resources/views/Standard/Tabella');
        }
        $fs->mirror($crudfolder, $templatetablefolder . '/Crud');
        $fs->mirror($tabellafolder, $templatetablefolder . '/Tabella');

        $fs->touch($publicfolder . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $entityform . ".js");
        $fs->touch($publicfolder . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $entityform . ".css");
    }
    private function generateFormsDefaultTableValues($entityform)
    {
        //Si inserisce il record di default nella tabella permessi
        $em = $this->getContainer()->get('doctrine')->getManager();
        $ruoloAmm = $em->getRepository('BiCoreBundle:Ruoli')->findOneBy(array('superadmin' => true)); //SuperAdmin

        $newPermesso = new \Cdf\BiCoreBundle\Entity\Permessi();
        $newPermesso->setCrud('crud');
        $newPermesso->setModulo($entityform);
        $newPermesso->setRuoli($ruoloAmm);
        $em->persist($newPermesso);
        $em->flush();

        $tabelle = new \Cdf\BiCoreBundle\Entity\Colonnetabelle();
        $tabelle->setNometabella($entityform);
        $em->persist($tabelle);
        $em->flush();
    }
    private function getControllerCode($bundlename, $tabella)
    {
        $codeTemplate = <<<EOF
<?php
namespace [bundle]\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Controller\FiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use [bundle]\Entity\[tabella];
use [bundle]\Form\[tabella]Type;
                
/**
* [tabella] controller.
*
*/

class [tabella]Controller extends FiController {

}
EOF;
        $codebundle = str_replace('[bundle]', $bundlename, $codeTemplate);
        $code = str_replace('[tabella]', $tabella, $codebundle);

        return $code;
    }
    private function getRoutingCode($bundlename, $tabella)
    {
        $codeTemplate = <<<'EOF'
[tabella]_container:
    path:  /
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::index' }

[tabella]_new:
    path:  /new
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::new' }
    requirements: { methods: get|post }

[tabella]_edit:
    path:  /{id}/edit
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::edit' }

[tabella]_update:
    path:  /{id}/update
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::update' }
    requirements: { methods: post|put }

[tabella]_aggiorna:
    path:  /aggiorna
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::aggiorna' }
    requirements: { methods: post|put }

[tabella]_delete:
    path:  /{id}/delete
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::delete' }
    requirements: { methods: post|delete }

[tabella]_deletemultiple:
    path:  /delete
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::delete' }
    requirements: { methods: post|delete }

[tabella]_tabella:
    path:  /tabella
    defaults: { _controller: '[bundle]\Controller\[tabella]Controller::tabella' }
    requirements: { methods: post }
EOF;
        $codebundle = str_replace('[bundle]', $bundlename, $codeTemplate);
        $code = str_replace('[tabella]', $tabella, $codebundle);

        return $code;
    }
}
