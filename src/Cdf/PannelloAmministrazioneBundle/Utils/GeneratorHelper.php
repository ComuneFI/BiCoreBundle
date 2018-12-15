<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use MwbExporter\Model\Table;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;

class GeneratorHelper
{

    private $apppaths;

    public function __construct(ProjectPath $projectpath)
    {
        $this->apppaths = $projectpath;
    }

    public function getDestinationEntityOrmPath()
    {
        $entitypath = realpath($this->apppaths->getSrcPath() . '/../src/Entity/');
        if (DIRECTORY_SEPARATOR == '/') {
            return $entitypath;
        } else {
            return str_replace('/', "\/", $entitypath);
        }
    }

    public function checktables($destinationPath, $wbFile, $output)
    {
        $finder = new Finder();
        $fs = new Filesystem();

        $pathdoctrineorm = $destinationPath;

        //Si converte il nome file per l'Orm della tabella se ha undercore
        $finder->in($pathdoctrineorm)->files()->name('*_*');
        $table = new Table();

        foreach ($finder as $file) {
            $oldfilename = $file->getPathName();
            $newfilename = $pathdoctrineorm . DIRECTORY_SEPARATOR . $table->beautify($file->getFileName());
            $fs->rename($oldfilename, $newfilename, true);
        }

        //Si cercano file con nomi errati
        $finderwrong = new Finder();
        $finderwrong->in($pathdoctrineorm)->files()->name('*_*');
        $wrongfilename = array();
        if (count($finderwrong) > 0) {
            foreach ($finderwrong as $file) {
                $wrongfilename[] = $file->getFileName();
                $fs->remove($pathdoctrineorm . DIRECTORY_SEPARATOR . $file->getFileName());
            }
        }

        //Si cercano file con nomi campo errati
        $finderwrongproperty = new Finder();
        $finderwrongproperty->in($pathdoctrineorm)->files()->name('*');
        $wrongpropertyname = array();
        foreach ($finderwrongproperty as $file) {
            $ref = new \ReflectionClass("App\\Entity\\" . basename($file->getFileName(), ".php"));
            $props = $ref->getProperties();
            foreach ($props as $prop) {
                $f = $prop->getName();
                if ($f !== strtolower($f)) {
                    $wrongpropertyname[] = $file->getFileName();
                    $fs->remove($pathdoctrineorm . DIRECTORY_SEPARATOR . $file->getFileName());
                }
            }
        }

        if (count($wrongpropertyname) > 0) {
            $errout = '<error>Ci sono campi nel file ' . $wbFile . ' con nomi non consentiti:' .
                    implode(',', $wrongpropertyname) .
                    '. I nomi dei campi devono essere lower case</error>';

            $output->writeln($errout);

            return -1;
        } else {
            return 0;
        }
    }

    public function checkprerequisiti($mwbfile, $output)
    {
        $fs = new Filesystem();

        $wbFile = $this->apppaths->getDocPath() . DIRECTORY_SEPARATOR . $mwbfile;
        $bundlePath = $this->apppaths->getSrcPath();

        $viewsPath = $bundlePath .
                DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $entityPath = $bundlePath .
                DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR;
        $formPath = $bundlePath .
                DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR;

        $scriptGenerator = $this->getScriptGenerator();

        $destinationPath = $this->getDestinationEntityOrmPath();
        $output->writeln('Creazione orm entities in ' . $destinationPath . ' da file ' . $mwbfile);

        if (!$fs->exists($bundlePath)) {
            $output->writeln('<error>Non esiste la cartella del bundle ' . $bundlePath . '</error>');

            return -1;
        }

        /* Creazione cartelle se non esistono nel bundle per l'esportazione */
        $fs->mkdir($destinationPath);
        $fs->mkdir($entityPath);
        $fs->mkdir($formPath);
        $fs->mkdir($viewsPath);

        if (!$fs->exists($wbFile)) {
            $output->writeln("<error>Nella cartella 'doc' non è presente il file " . $mwbfile . '!');

            return -1;
        }

        if (!$fs->exists($scriptGenerator)) {
            $output->writeln('<error>Non è presente il comando ' . $scriptGenerator . ' per esportare il modello!</error>');

            return -1;
        }
        if (!$fs->exists($destinationPath)) {
            $output->writeln("<error>Non esiste la cartella per l'esportazione " . $destinationPath . ', controllare il nome del Bundle!</error>');

            return -1;
        }

        return 0;
    }

    public function getScriptGenerator()
    {
        $scriptGenerator = "";
        $scriptGenerator = $this->apppaths->getVendorBinPath() . DIRECTORY_SEPARATOR . 'mysql-workbench-schema-export';
        if (!file_exists($scriptGenerator)) {
            $scriptGenerator = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                    'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'mysql-workbench-schema-export';
        }
        if (!$scriptGenerator) {
            throw new \Exception("mysql-workbench-schema-export non trovato", -100);
        }
        return $scriptGenerator;
    }

    public function getExportJsonFile()
    {
        $fs = new Filesystem();
        $cachedir = $this->apppaths->getCachePath();
        $exportJson = $cachedir . DIRECTORY_SEPARATOR . 'export.json';
        if ($fs->exists($exportJson)) {
            $fs->remove($exportJson);
        }

        return $exportJson;
    }

    public function removeExportJsonFile()
    {
        $this->getExportJsonFile();

        return true;
    }

    public static function getJsonMwbGenerator()
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
