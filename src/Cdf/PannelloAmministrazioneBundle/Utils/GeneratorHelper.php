<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Exception;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use function count;

class GeneratorHelper
{
    private ProjectPath $apppaths;

    public function __construct(ProjectPath $projectpath)
    {
        $this->apppaths = $projectpath;
    }

    public function getDestinationEntityOrmPath() : string
    {
        $entitypath = realpath($this->apppaths->getSrcPath().'/../src/Entity/');
        if (DIRECTORY_SEPARATOR == '/') {
            return $entitypath;
        } else {
            return str_replace('\\', '\\\\', $entitypath);
        }
    }

    public function checktables(string $destinationPath, string $wbFile, OutputInterface $output) : int
    {
        $fs = new Filesystem();

        $pathdoctrineorm = $destinationPath;

        //Si cercano file con nomi errati
        $finderwrong = new Finder();
        $finderwrong->in($pathdoctrineorm)->files()->name('*_*');
        $wrongfilename = array();
        if (count($finderwrong) > 0) {
            foreach ($finderwrong as $file) {
                $wrongfilename[] = $file->getFileName();
                $fs->remove($pathdoctrineorm.DIRECTORY_SEPARATOR.$file->getFileName());
            }
        }

        //Si cercano file con nomi campo errati
        $finderwrongproperty = new Finder();
        $finderwrongproperty->in($pathdoctrineorm)->files()->name('Base*.php');
        $wrongpropertyname = array();
        foreach ($finderwrongproperty as $file) {
            $ref = new ReflectionClass('App\\Entity\\'.basename($file->getFileName(), '.php'));
            $props = $ref->getProperties();
            foreach ($props as $prop) {
                $f = $prop->getName();
                if ($f !== strtolower($f) && false === strpos($f, 'RelatedBy')) {
                    $wrongpropertyname[] = $file->getFileName();
                    $fullpathprmbase = $pathdoctrineorm.DIRECTORY_SEPARATOR.$file->getFileName();
                    $fullpathprm = $pathdoctrineorm.DIRECTORY_SEPARATOR.substr($file->getFileName(), 4);
                    //$fs->remove($pathdoctrineorm . DIRECTORY_SEPARATOR . $file->getFileName());
                    $fs->rename($fullpathprmbase, $fullpathprmbase.'.ko', true);
                    $fs->rename($fullpathprm, $fullpathprm.'.ko', true);
                }
            }
        }

        if (count($wrongpropertyname) > 0) {
            $errout = '<error>CI SONO CAMPI NEL FILE '.$wbFile.' CON NOMI NON CONSENTITI: '.
                    implode(',', $wrongpropertyname).
                    '. I NOMI DEI CAMPI DEVONO ESSERE MINUSCOLI!</error>';

            $output->writeln($errout);

            return -1;
        } else {
            return 0;
        }
    }

    public function checkprerequisiti(string $mwbfile, OutputInterface $output) : int
    {
        $fs = new Filesystem();

        $wbFile = $this->apppaths->getDocPath().DIRECTORY_SEPARATOR.$mwbfile;
        $bundlePath = $this->apppaths->getSrcPath();

        $viewsPath = $bundlePath.
                DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
        $entityPath = $bundlePath.
                DIRECTORY_SEPARATOR.'Entity'.DIRECTORY_SEPARATOR;
        $formPath = $bundlePath.
                DIRECTORY_SEPARATOR.'Form'.DIRECTORY_SEPARATOR;

        $scriptGenerator = $this->getScriptGenerator();

        $destinationPath = $this->getDestinationEntityOrmPath();
        $output->writeln('Creazione orm entities in '.$destinationPath.' da file '.$mwbfile);

        if (!$fs->exists($bundlePath)) {
            $output->writeln('<error>Non esiste la cartella del bundle '.$bundlePath.'</error>');

            return -1;
        }

        /* Creazione cartelle se non esistono nel bundle per l'esportazione */
        $fs->mkdir($destinationPath);
        $fs->mkdir($entityPath);
        $fs->mkdir($formPath);
        $fs->mkdir($viewsPath);

        if (!$fs->exists($wbFile)) {
            $output->writeln("<error>Nella cartella 'doc' non è presente il file ".$mwbfile.'!');

            return -1;
        }

        if (!$fs->exists($scriptGenerator)) {
            $output->writeln('<error>Non è presente il comando '.$scriptGenerator.' per esportare il modello!</error>');

            return -1;
        }
        if (!$fs->exists($destinationPath)) {
            $output->writeln("<error>Non esiste la cartella per l'esportazione ".$destinationPath.', controllare il nome del Bundle!</error>');

            return -1;
        }

        return 0;
    }

    public function getScriptGenerator() : string
    {
        $scriptGenerator = $this->apppaths->getBinPath() . DIRECTORY_SEPARATOR . 'mysql-workbench-schema-export';
        if (!file_exists($scriptGenerator)) {
            $scriptGenerator = $this->apppaths->getVendorBinPath() . DIRECTORY_SEPARATOR . 'mysql-workbench-schema-export';
            if (!file_exists($scriptGenerator)) {
                throw new Exception("mysql-workbench-schema-export non trovato", -100);
            }
        }
        return $scriptGenerator;
    }

    public function getExportJsonFile() : string
    {
        $fs = new Filesystem();
        $cachedir = $this->apppaths->getCachePath();
        $exportJson = $cachedir.DIRECTORY_SEPARATOR.'export.json';
        if ($fs->exists($exportJson)) {
            $fs->remove($exportJson);
        }

        return $exportJson;
    }

    public function removeExportJsonFile() :bool
    {
        $this->getExportJsonFile();

        return true;
    }

    public static function getJsonMwbGenerator() : string
    {
        $jsonTemplate = <<<EOF
{"export": "doctrine2-annotation",
    "zip": false,
    "dir": "[dir]",
    "params":
            {"indentation": 4,
                "useTabs": false, 
                "filename": "%entity%.%extension%",
                "skipPluralNameChecking": true,
                "backupExistingFile": false,
                "addGeneratorInfoAsComment": false,
                "useLoggedStorage": false,
                "enhanceManyToManyDetection": true,
                "logToConsole": false,
                "logFile": "",
                "bundleNamespace": "App",
                "entityNamespace": "Entity",
                "repositoryNamespace": "App\\\\Entity",
                "useAutomaticRepository": false,
                "generateExtendableEntity": true,
                "extendableEntityHasDiscriminator": false,
                "extendTableNameWithSchemaName": false
            }
}
EOF;

        return $jsonTemplate;
    }
}
