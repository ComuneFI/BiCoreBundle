<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class BiTest
{
    public static function clearcache(): void
    {
        passthru(sprintf('"%s/console" cache:clear --env=test', __DIR__.'/../../../../bin'));
    }

    public static function getErrorText(): string
    {
        $error = ($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());

        return 'Errore nel comando '.$command.' '.$error.' ';
    }

    public static function deleteFirstLineFile($file)
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

    public static function deleteLineFromFile(): void
    {
        $data = file($file);

        $out = [];

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

    public static function removecache()
    {
        $vendorDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        //$envs = ['test', 'dev', 'prod'];
        $envs[] = getenv('APP_ENV');
        foreach ($envs as $env) {
            $cachedir = $vendorDir.'/tests/var/cache/'.$env;
            if (file_exists($cachedir)) {
                $command = 'rm -rf '.$cachedir;
                $process = Process::fromShellCommandline($command);
                $process->setTimeout(60 * 100);
                $process->run();
                if (!$process->isSuccessful()) {
                    $error = ($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());
                    echo 'Errore nel comando '.$command.' '.$error.' ';
                } else {
                    echo $process->getOutput();
                }
            } else {
                //echo $testcache . " not found";
            }
        }
    }

    public static function cleanFilesystem(): void
    {
        $vendorDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/tests';
        $publicDir = realpath(dirname(dirname(dirname(dirname(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR))))).DIRECTORY_SEPARATOR.'public';
        //deleteLineFromFile($kernelfile, $DELETE);
        $routingfile = $vendorDir.'/config/routes.yaml';

        $line = fgets(fopen($routingfile, 'r'));
        if ('App_Prova:' == substr($line, 0, -1)) {
            for ($index = 0; $index < 4; ++$index) {
                self::deleteFirstLineFile($routingfile);
            }
        }

        $line = fgets(fopen($routingfile, 'r'));
        if ('App_Tabellacollegata:' == substr($line, 0, -1)) {
            for ($index = 0; $index < 4; ++$index) {
                self::deleteFirstLineFile($routingfile);
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
}
