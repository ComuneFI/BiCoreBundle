<?php

namespace Cdf\PannelloAmministrazioneBundle\DependencyInjection;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use MwbExporter\Model\Table;

class GeneratorHelper
{

    private $container;
    private $apppaths;

    public function __construct($container)
    {
        $this->container = $container;
        $this->apppaths = $container->get("pannelloamministrazione.projectpath");
    }

    public function getDestinationEntityYmlPath()
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

        $pathdoctrineyml = $destinationPath;

        //Si converte il nome file tabella.orm.yml se ha undercore
        $finder->in($pathdoctrineyml)->files()->name('*_*');
        $table = new Table();

        foreach ($finder as $file) {
            $oldfilename = $file->getPathName();
            $newfilename = $pathdoctrineyml . DIRECTORY_SEPARATOR . $table->beautify($file->getFileName());
            $fs->rename($oldfilename, $newfilename, true);
        }

        //Si cercano file con nomi errati
        $finderwrong = new Finder();
        $finderwrong->in($pathdoctrineyml)->files()->name('*_*');
        $wrongfilename = array();
        if (count($finderwrong) > 0) {
            foreach ($finderwrong as $file) {
                $wrongfilename[] = $file->getFileName();
                $fs->remove($pathdoctrineyml . DIRECTORY_SEPARATOR . $file->getFileName());
            }
        }
        $finderwrongcapitalize = new Finder();
        $finderwrongcapitalize->in($pathdoctrineyml)->files()->name('*.yml');
        foreach ($finderwrongcapitalize as $file) {
            if (!ctype_upper(substr($file->getFileName(), 0, 1))) {
                $wrongfilename[] = $file->getFileName();
                $fs->remove($pathdoctrineyml . DIRECTORY_SEPARATOR . $file->getFileName());
            }
        }

        if (count($wrongfilename) > 0) {
            $errout = '<error>Ci sono tabelle nel file ' . $wbFile . ' con nomi non consentiti:' .
                    implode(',', $wrongfilename) .
                    '. I nomi tabella devono essere : con la prima lettera maiuscola,underscore ammesso,doppio underscore non ammesso</error>';

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

        $destinationPath = $this->getDestinationEntityYmlPath();
        $output->writeln('Creazione entities yml in ' . $destinationPath . ' da file ' . $mwbfile);

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
