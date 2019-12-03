<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

set_time_limit(0);

require __DIR__.'/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new \RuntimeException('You need to add "symfony/framework-bundle" as a Composer dependency.');
}

if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv(true))->load(__DIR__.'/../tests/.env');
}
$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4('Cdf\\BiCoreBundle\\Tests\\', __DIR__.'/Cdf/Tests', true);
$classLoader->register();

date_default_timezone_set('Europe/Rome');
cleanFilesystem();

//databaseinit();
function clearcache()
{
    passthru(sprintf(
                    '"%s/console" cache:clear --env=test', __DIR__.'/../bin'
    ));
}

function databaseinit()
{
    passthru(sprintf(
                    '"%s/console" bicorebundle:dropdatabase --force', __DIR__.'/../bin'
    ));
    passthru(sprintf(
                    '"%s/console" bicorebundle:install admin admin admin@admin.it', __DIR__.'/../bin'
    ));
    passthru(sprintf(
                    '"%s/console" bicoredemo:loaddefauldata', __DIR__.'/../bin'
    ));

    //sleep(1);
}

function removecache()
{
    $vendorDir = dirname(dirname(__FILE__));
    //$envs = ['test', 'dev', 'prod'];
    $envs[] = getenv("APP_ENV");
    foreach ($envs as $env) {
        $cachedir = $vendorDir.'/tests/var/cache/'.$env;
        if (file_exists($cachedir)) {
            $command = 'rm -rf '.$cachedir;
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(60 * 100);
            $process->run();
            if (!$process->isSuccessful()) {
                echo getErrorText($process, $command);
            } else {
                echo $process->getOutput();
            }
        } else {
            //echo $testcache . " not found";
        }
    }
}

function getErrorText($process, $command)
{
    $error = ($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());

    return 'Errore nel comando '.$command.' '.$error.' ';
}

function cleanFilesystem()
{
    $vendorDir = dirname(dirname(__FILE__).'/tests');
    $publicDir = realpath(dirname(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.'public';
    //deleteLineFromFile($kernelfile, $DELETE);
    $routingfile = $vendorDir.'/config/routes.yaml';

    $line = fgets(fopen($routingfile, 'r'));
    if ('App_Prova:' == substr($line, 0, -1)) {
        for ($index = 0; $index < 4; ++$index) {
            deleteFirstLineFile($routingfile);
        }
    }

    $line = fgets(fopen($routingfile, 'r'));
    if ('App_Tabellacollegata:' == substr($line, 0, -1)) {
        for ($index = 0; $index < 4; ++$index) {
            deleteFirstLineFile($routingfile);
        }
    }

    //$configfile = $vendorDir . '/app/config/config.yml';
    //$remove = '- { resource: "@FiProvaBundle/Resources/config/services.yml" }';
    //deleteLineFromFile($configfile, $remove);

    $fs = new Filesystem();

    $entityfile = $vendorDir.'/src/Entity/Prova.php';

    if ($fs->exists($entityfile)) {
        $fs->remove($entityfile);
    }
    $entityfile2 = $vendorDir.'/src/Entity/BaseProva.php';

    if ($fs->exists($entityfile2)) {
        $fs->remove($entityfile2);
    }
    $entityfile3 = $vendorDir.'/src/Entity/BaseTabellacollegata.php';

    if ($fs->exists($entityfile3)) {
        $fs->remove($entityfile3);
    }
    $entityfile4 = $vendorDir.'/src/Entity/Tabellacollegata.php';

    if ($fs->exists($entityfile4)) {
        $fs->remove($entityfile4);
    }
    $entityfile5 = $vendorDir.'/src/Entity/BaseTabellaDarinominare.php';

    if ($fs->exists($entityfile5)) {
        $fs->remove($entityfile5);
    }
    $entityfile6 = $vendorDir.'/src/Entity/TabellaDarinominare.php';

    if ($fs->exists($entityfile6)) {
        $fs->remove($entityfile6);
    }
    
    $entityfile7 = $vendorDir.'/src/Entity/BaseTabellaMinuscola.php.ko';

    if ($fs->exists($entityfile7)) {
        $fs->remove($entityfile7);
    }
    $entityfile8 = $vendorDir.'/src/Entity/TabellaMinuscola.php.ko';

    if ($fs->exists($entityfile8)) {
        $fs->remove($entityfile8);
    }
    
    $routingfile = $vendorDir.'/config/routes/prova.yml';

    if ($fs->exists($routingfile)) {
        $fs->remove($routingfile);
    }
    $routingfile = $vendorDir.'/config/routes/tabellacollegata.yml';

    if ($fs->exists($routingfile)) {
        $fs->remove($routingfile);
    }
    $resources = $vendorDir.'/templates/Prova';
    if ($fs->exists($resources)) {
        $fs->remove($resources, true);
    }

    $resources = $vendorDir.'/templates/Tabellacollegata';
    if ($fs->exists($resources)) {
        $fs->remove($resources, true);
    }

    $form = $vendorDir.'/src/Form/ProvaType.php';
    if ($fs->exists($form)) {
        $fs->remove($form, true);
    }

    $form = $vendorDir.'/src/Form/TabellacollegataType.php';
    if ($fs->exists($form)) {
        $fs->remove($form, true);
    }

    $controller = $vendorDir.'/src/Controller/ProvaController.php';
    if ($fs->exists($controller)) {
        $fs->remove($controller, true);
    }
    $controller = $vendorDir.'/src/Controller/TabellacollegataController.php';
    if ($fs->exists($controller)) {
        $fs->remove($controller, true);
    }

    if ($fs->exists($publicDir.'/js/Prova.js')) {
        $fs->remove($publicDir.'/js/Prova.js');
    }
    if ($fs->exists($publicDir.'/js/Tabellacollegata.js')) {
        $fs->remove($publicDir.'/js/Tabellacollegata.js');
    }
    if ($fs->exists($publicDir.'/css/Prova.css')) {
        $fs->remove($publicDir.'/css/Prova.css');
    }
    if ($fs->exists($publicDir.'/css/Tabellacollegata.css')) {
        $fs->remove($publicDir.'/css/Tabellacollegata.css');
    }
    //Questo mi tocca tenerlo perchè fallisce il routing fos js se trova già una cartella js
    if ($fs->exists($publicDir.'/js')) {
        $fs->remove($publicDir.'/js', true);
    }
    /* $bundlesrcdir = $vendorDir . '/src';

      if ($fs->exists($bundlesrcdir)) {
      $fs->remove($bundlesrcdir, true);
      } */
}

function deleteFirstLineFile($file)
{
    $handle = fopen($file, 'r');
    fgets($handle, 2048); //get first line.
    $outfile = 'temp';
    $o = fopen($outfile, 'w');
    while (!feof($handle)) {
        $buffer = fgets($handle, 2048);
        fwrite($o, $buffer);
    }
    fclose($handle);
    fclose($o);
    rename($outfile, $file);
}

function deleteLineFromFile($file, $DELETE)
{
    $data = file($file);

    $out = array();

    foreach ($data as $line) {
        if (trim($line) != $DELETE) {
            $out[] = $line;
        }
    }

    $fp = fopen($file, 'w+');
    flock($fp, LOCK_EX);
    foreach ($out as $line) {
        fwrite($fp, $line);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
}

function writestdout($buffer)
{
    fwrite(STDOUT, print_r($buffer."\n", true));
}
