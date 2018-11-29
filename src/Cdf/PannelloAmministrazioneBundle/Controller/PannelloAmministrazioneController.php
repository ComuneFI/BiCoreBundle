<?php

namespace Cdf\PannelloAmministrazioneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Fi\OsBundle\DependencyInjection\OsFunctions;
use Cdf\PannelloAmministrazioneBundle\DependencyInjection\PannelloAmministrazioneUtils;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class PannelloAmministrazioneController extends Controller
{

    protected $apppaths;
    protected $locksystem;
    protected $factory;

    public function __construct()
    {
        $store = new FlockStore(sys_get_temp_dir());
        $factory = new Factory($store);
        $this->locksystem = $factory->createLock('pannelloamministrazione-command');
        $this->locksystem->release();
    }

    private function findEntities()
    {
        $entitiesprogetto = array();
        $prefix = 'App\\Entity\\';
        $prefixBase = 'Base';
        $entities = $this->get("doctrine")->getManager()->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        foreach ($entities as $entity) {
            if (substr($entity, 0, strlen($prefix)) == $prefix) {
                if (substr(substr($entity, strlen($prefix)), 0, strlen($prefixBase)) != $prefixBase) {
                    $entitiesprogetto[] = substr($entity, strlen($prefix));
                }
            }
        }
        return $entitiesprogetto;
    }

    public function indexAction()
    {
        $finder = new Finder();
        $fs = new Filesystem();
        $this->apppaths = $this->get("pannelloamministrazione.projectpath");

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
        $svn = $fs->exists($projectDir . '/.svn');
        $git = $fs->exists($projectDir . '/.git');

        if (!OsFunctions::isWindows()) {
            $delcmd = 'rm -rf';
            $setfilelock = "touch " . $this->getParameter("bi_core.lockfile");
            $remfilelock = "rm " . $this->getParameter("bi_core.lockfile");
            $windows = false;
        } else {
            $delcmd = 'del';
            $setfilelock = 'echo $null >> ' . $this->getParameter("bi_core.lockfile");
            $remfilelock = "del " . $this->getParameter("bi_core.lockfile");
            $windows = true;
        }

        $dellogsfiles = $delcmd . ' ' . $this->apppaths->getLogsPath() . DIRECTORY_SEPARATOR . '*';
        $delcacheprodfiles = $delcmd . ' ' . $this->apppaths->getCachePath() . DIRECTORY_SEPARATOR . 'prod' . DIRECTORY_SEPARATOR . '*';
        $delcachedevfiles = $delcmd . ' ' . $this->apppaths->getCachePath() . DIRECTORY_SEPARATOR . 'dev' . DIRECTORY_SEPARATOR . '*';
        $setmaintenancefile = $setfilelock;
        $remmaintenancefile = $remfilelock;

        $projectparentdir = $projectDir . '/../';
        $envvars = $projectparentdir . "/" . "envvars";
        $composercachedir = $projectparentdir . "/" . ".composer";
        $composerinstall = "";
        if ($windows == false) {
            if (file_exists($envvars)) {
                $composerinstall = $composerinstall . ". " . $envvars . " && ";
            }
            if (file_exists($composercachedir)) {
                $composerinstall = $composerinstall . " export COMPOSER_HOME=" . $composercachedir . " && ";
            }
            $composerinstall = $composerinstall . " cd " . $projectDir . " && composer install --no-interaction 2>&1";
            $sed = "sed -i -e 's/cercaquestastringa/sostituisciconquestastringa/g' " . $projectDir . "/.env";
        }

        $comandishell = array(
            $this->fixSlash($dellogsfiles),
            $this->fixSlash($delcacheprodfiles),
            $this->fixSlash($delcachedevfiles),
            $this->fixSlash($setmaintenancefile),
            $this->fixSlash($remmaintenancefile),
            $composerinstall,
            $sed);

        $comandisymfony = array(
            "list",
            "cache:clear --env=prod --no-debug",
            "fos:user:create admin pass admin@admin.it",
            "fos:user:promote username ROLE_SUPER_ADMIN",
            "assets:install ' . $projectDir . ' /public",
            "pannelloamministrazione:checkgitversion"
        );

        $entities = $this->findEntities();
        sort($entities);

        $twigparms = array(
            'svn' => $svn, 'git' => $git, 'mwbs' => $mwbs, 'entities' => $entities,
            'rootdir' => $this->fixSlash($projectDir),
            'comandishell' => $comandishell,
            'comandisymfony' => $comandisymfony,
            'iswindows' => $windows,
        );

        return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:index.html.twig', $twigparms);
    }

    private function fixSlash($path)
    {
        return str_replace('\\', '\\\\', $path);
    }

    private function getLockMessage()
    {
        return "<h2 style='color: orange;'>E' già in esecuzione un comando, riprova tra qualche secondo!</h2>";
    }

    public function aggiornaSchemaDatabaseAction()
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->get("pannelloamministrazione.commands");
            $result = $command->aggiornaSchemaDatabase();

            $this->locksystem->release();
            if ($result['errcode'] != 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* FORMS */

    public function generateFormCrudAction(Request $request)
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $entityform = $request->get('entityform');
            $generatemplate = $request->get('generatemplate') === 'true' ? true : false;
            $this->locksystem->acquire();

            $command = $this->get("pannelloamministrazione.commands");
            $result = $command->generateFormCrud($entityform, $generatemplate);

            $this->locksystem->release();
            //$retcc = '';
            if ($result['errcode'] < 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => "Generazione Form Crud", 'message' => $result['message']);
                return new Response(
                    $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms),
                    500
                );
            } else {
                //$retcc = $command->clearCacheEnv($this->get('kernel')->getEnvironment());
            }
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

            return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
        }
    }

    /* ENTITIES */

    public function generateEntityAction(Request $request)
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $wbFile = $request->get('file');
            $command = $this->get("pannelloamministrazione.commands");
            $result = $command->generateEntity($wbFile);
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
            $this->locksystem->release();
            if ($result['errcode'] != 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* VCS (GIT,SVN) */

    /**
     * @codeCoverageIgnore
     */
    public function getVcsAction()
    {
        set_time_limit(0);
        $this->apppaths = $this->get("pannelloamministrazione.projectpath");
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->get("pannelloamministrazione.commands");
            $result = $command->getVcs();
            $this->locksystem->release();
            $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);

            return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
        }
    }

    /* CLEAR CACHE */

    /**
     * Suppress PMD warnings per exit.
     *
     * @//SuppressWarnings(PHPMD)
     */
    public function clearCacheAction(Request $request)
    {
        set_time_limit(0);
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $command = $this->get("pannelloamministrazione.commands");
            $result = $command->clearcache();

            $this->locksystem->release();

            if ($result['errcode'] != 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
            }
        }
    }

    /* CLEAR CACHE */

    public function symfonyCommandAction(Request $request)
    {
        set_time_limit(0);
        $comando = $request->get('symfonycommand');
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $this->apppaths = $this->get("pannelloamministrazione.projectpath");
            $pammutils = new PannelloAmministrazioneUtils($this->container);
            $phpPath = OsFunctions::getPHPExecutableFromPath();
            $result = $pammutils->runCommand($phpPath . ' ' . $this->apppaths->getConsole() . ' ' . $comando);

            $this->locksystem->release();
            if ($result['errcode'] != 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
            }
        }
    }

    /**
     * Suppress PMD warnings per exit.
     *
     * @SuppressWarnings(PHPMD)
     */
    public function unixCommandAction(Request $request)
    {
        set_time_limit(0);
        $pammutils = new PannelloAmministrazioneUtils($this->container);
        $command = $request->get('unixcommand');
        //Se viene lanciato il comando per cancellare il file di lock su bypassa tutto e si lancia
        $dellockfile = "DELETELOCK";
        if ($command == $dellockfile) {
            $this->locksystem->release();
            return new Response('File di lock cancellato');
        }

        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            $this->locksystem->acquire();
            $result = $pammutils->runCommand($command);

            $this->locksystem->release();
            // eseguito deopo la fine del comando
            if ($result['errcode'] != 0) {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                return new Response($view, 500);
            } else {
                $twigparms = array('errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']);
                return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function phpunittestAction(Request $request)
    {
        set_time_limit(0);
        $this->apppaths = $this->get("pannelloamministrazione.projectpath");
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        } else {
            if (!OsFunctions::isWindows()) {
                $this->locksystem->acquire();
                //$phpPath = OsFunctions::getPHPExecutableFromPath();
                $sepchr = OsFunctions::getSeparator();
                $phpPath = OsFunctions::getPHPExecutableFromPath();

                $command = 'cd ' . $this->apppaths->getRootPath() . $sepchr .
                        $phpPath . ' ' . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'simple-phpunit';

                $process = new Process($command);
                $process->run();

                $this->locksystem->release();
                // eseguito dopo la fine del comando
                if (!$process->isSuccessful()) {
                    $twigparms = array('errcode' => -1, 'command' => $command, 'message' => $process->getOutput() . $process->getErrorOutput());
                    $view = $this->renderView('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                    return new Response($view, 500);
                } else {
                    $twigparms = array('errcode' => 0, 'command' => $command, 'message' => $process->getOutput() . $process->getErrorOutput());
                    return $this->render('PannelloAmministrazioneBundle:PannelloAmministrazione:outputcommand.html.twig', $twigparms);
                }
            } else {
                return new Response('Non previsto in ambiente windows!', 500);
            }
        }
    }
}
