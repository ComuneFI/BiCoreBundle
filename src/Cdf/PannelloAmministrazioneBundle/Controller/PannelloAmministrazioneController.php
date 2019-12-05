<?php

namespace Cdf\PannelloAmministrazioneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Fi\OsBundle\DependencyInjection\OsFunctions;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility as Pautils;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Cdf\PannelloAmministrazioneBundle\Utils\Commands as Pacmd;

class PannelloAmministrazioneController extends AbstractController
{
    private $apppaths;
    private $pacommands;
    private $pautils;
    protected $locksystem;
    protected $factory;
    private $appname;
    private $lockfile;

    public function __construct($appname, $lockfile, ProjectPath $projectpath, Pacmd $pacommands, Pautils $pautils)
    {
        $store = new FlockStore(sys_get_temp_dir());
        $factory = new Factory($store);
        $this->locksystem = $factory->createLock('pannelloamministrazione-command');
        $this->locksystem->release();
        $this->appname = $appname;
        $this->lockfile = $lockfile;
        $this->apppaths = $projectpath;
        $this->pacommands = $pacommands;
        $this->pautils = $pautils;
    }

    private function findEntities()
    {
        $entitiesprogetto = array();
        $prefix = 'App\\Entity\\';
        $prefixBase = 'Base';
        $entities = $this->get('doctrine')->getManager()->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        foreach ($entities as $entity) {
            if (substr($entity, 0, strlen($prefix)) == $prefix) {
                if (substr(substr($entity, strlen($prefix)), 0, strlen($prefixBase)) != $prefixBase) {
                    $entitiesprogetto[] = substr($entity, strlen($prefix));
                }
            }
        }

        return $entitiesprogetto;
    }

    public function index()
    {
        $finder = new Finder();
        $fs = new Filesystem();

        $projectDir = $this->apppaths->getRootPath();
        $docDir = $this->apppaths->getDocPath();

        $mwbs = array();

        if ($fs->exists($docDir)) {
            $finder->in($docDir)->files()->name('*.mwb');
            foreach ($finder as $file) {
                $mwbs[] = $file->getBasename();
            }
        }
        sort($mwbs);
        $svn = $fs->exists($projectDir.'/.svn');
        $git = $fs->exists($projectDir.'/.git');
        if (!OsFunctions::isWindows()) {
            $delcmd = 'rm -rf';
            $setfilelock = 'touch '.$this->lockfile;
            $remfilelock = 'rm '.$this->lockfile;
            $windows = false;
        } else {
            // @codeCoverageIgnoreStart
            $delcmd = 'del';
            $setfilelock = 'echo $null >> '.$this->lockfile;
            $remfilelock = 'del '.$this->lockfile;
            $windows = true;
            // @codeCoverageIgnoreEnd
        }
        $dellogsfiles = $delcmd.' '.$this->apppaths->getLogsPath().DIRECTORY_SEPARATOR.'*';
        $delcacheprodfiles = $delcmd.' '.$this->apppaths->getCachePath().DIRECTORY_SEPARATOR.'prod'.DIRECTORY_SEPARATOR.'*';
        $delcachedevfiles = $delcmd.' '.$this->apppaths->getCachePath().DIRECTORY_SEPARATOR.'dev'.DIRECTORY_SEPARATOR.'*';
        $setmaintenancefile = $setfilelock;
        $remmaintenancefile = $remfilelock;

        $comandishell = array(
            array('text' => $this->fixSlash($dellogsfiles), 'link' => '#'),
            array('text' => $this->fixSlash($delcacheprodfiles), 'link' => '#'),
            array('text' => $this->fixSlash($delcachedevfiles), 'link' => '#'),
            array('text' => $this->fixSlash($setmaintenancefile), 'link' => '#'),
            array('text' => $this->fixSlash($remmaintenancefile), 'link' => '#'),
                //array("text"=>"prova", "link"=>"#"),
        );
        $composerinstall = '';
        if (false == $windows) {
            $composerinstall = $composerinstall.' cd '.$projectDir.' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1';
            $sed = "sed -i -e 's/cercaquestastringa/sostituisciconquestastringa/g' ".$projectDir.'/.env';
            $comandishell[] = array('text' => $composerinstall, 'link' => '#');
            $comandishell[] = array('text' => $sed, 'link' => '#');
        }

        $comandisymfony = array(
            array('text' => 'list', 'link' => '#'),
            array('text' => 'cache:clear --env=prod --no-debug', 'link' => '#'),
            array('text' => 'fos:user:create admin pass admin@admin.it', 'link' => '#'),
            array('text' => 'fos:user:promote username ROLE_SUPER_ADMIN', 'link' => '#'),
            array('text' => "assets:install $projectDir/public", 'link' => '#'),
            array('text' => 'pannelloamministrazione:checkgitversion', 'link' => '#'),
        );

        $entities = $this->findEntities();
        sort($entities);

        $twigparms = array(
            'svn' => $svn, 'git' => $git, 'mwbs' => $mwbs, 'entities' => $entities,
            'rootdir' => $this->fixSlash($projectDir),
            'comandishell' => $comandishell,
            'comandisymfony' => $comandisymfony,
            'iswindows' => $windows,
            'appname' => $this->appname,
        );

        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/index.html.twig', $twigparms);
    }

    private function fixSlash($path)
    {
        return str_replace('\\', '\\\\', $path);
    }

    private function getLockMessage()
    {
        return "<h2 style='color: orange;'>E' già in esecuzione un comando, riprova tra qualche secondo!</h2>";
    }

    public function aggiornaSchemaDatabase()
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->pacommands;
            $result = $command->aggiornaSchemaDatabase();

            $this->locksystem->release();
            if (0 != $result['errcode']) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

                return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* FORMS */

    public function generateFormCrud(Request $request)
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $entityform = $request->get('entityform');
            $generatemplate = 'true' === $request->get('generatemplate') ? true : false;
            $this->locksystem->acquire();

            $command = $this->pacommands;
            $result = $command->generateFormCrud($entityform, $generatemplate);

            $this->locksystem->release();
            //$retcc = '';
            if ($result['errcode'] < 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => 'Generazione Form Crud', 'message' => $result['message']);

                return new Response(
                    $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms),
                    500
                );
            } else {
                //$retcc = $command->clearCacheEnv($this->get('kernel')->getEnvironment());
            }
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

            return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
        }
    }

    /* ENTITIES */

    public function generateEntity(Request $request)
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $wbFile = $request->get('file');
            $command = $this->pacommands;
            $result = $command->generateEntity($wbFile);
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
            $this->locksystem->release();
            if (0 != $result['errcode']) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

                return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* VCS (GIT,SVN) */

    /**
     * @codeCoverageIgnore
     */
    public function getVcs()
    {
        set_time_limit(0);
        $this->apppaths = $this->apppaths;
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->pacommands;
            $result = $command->getVcs();
            $this->locksystem->release();
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

            return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
        }
    }

    /* CLEAR CACHE */

    /**
     * Suppress PMD warnings per exit.
     *
     * @//SuppressWarnings(PHPMD)
     */
    public function clearCache(Request $request)
    {
        set_time_limit(0);
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->pacommands;
            $result = $command->clearcache();

            $this->locksystem->release();

            if (0 != $result['errcode']) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

                return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* CLEAR CACHE */

    public function symfonyCommand(Request $request)
    {
        set_time_limit(0);

        $simfonycommand = $request->get('symfonycommand');
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $this->apppaths = $this->apppaths;
            $pammutils = $this->pautils;
            $command = $this->apppaths->getConsole().' '.$simfonycommand;
            $result = $pammutils->runCommand($command);

            $this->locksystem->release();
            if (0 != $result['errcode']) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

                return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
            }
        }
    }

    /**
     * Suppress PMD warnings per exit.
     *
     * @SuppressWarnings(PHPMD)
     */
    public function unixCommand(Request $request)
    {
        set_time_limit(0);
        $pammutils = $this->pautils;
        $unixcommand = $request->get('unixcommand');
        //Se viene lanciato il comando per cancellare il file di lock su bypassa tutto e si lancia
        $dellockfile = 'DELETELOCK';
        if ($unixcommand == $dellockfile) {
            $this->locksystem->release();

            return new Response('File di lock cancellato');
        }

        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $result = $pammutils->runCommand($unixcommand);

            $this->locksystem->release();
            // eseguito deopo la fine del comando
            if (0 != $result['errcode']) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

                return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function phpunittest(Request $request)
    {
        set_time_limit(0);
        $this->apppaths = $this->apppaths;
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            if (!OsFunctions::isWindows()) {
                $this->locksystem->acquire();
                //$phpPath = OsFunctions::getPHPExecutableFromPath();
                $command = 'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'simple-phpunit';
                $process = new Process(array($command));
                $process->setWorkingDirectory($this->apppaths->getRootPath());

                $process->run();

                $this->locksystem->release();
                // eseguito dopo la fine del comando
                if (!$process->isSuccessful()) {
                    $twigparms = array('errcode' => -1, 'command' => $command, 'message' => $process->getOutput().$process->getErrorOutput());
                    $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                    return new Response($view, 500);
                } else {
                    $twigparms = array('errcode' => 0, 'command' => $command, 'message' => $process->getOutput().$process->getErrorOutput());

                    return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
                }
            } else {
                // @codeCoverageIgnoreStart
                return new Response('Non previsto in ambiente windows!', 500);
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
