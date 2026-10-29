<?php

namespace Cdf\PannelloAmministrazioneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Fi\OsBundle\DependencyInjection\OsFunctions;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\LockInterface;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility as Pautils;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Cdf\PannelloAmministrazioneBundle\Utils\Commands as Pacmd;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Doctrine\ORM\EntityManagerInterface as EM;


class PannelloAmministrazioneController extends AbstractController
{
    protected LockInterface $locksystem;
    protected LockFactory $factory;

    public function __construct(private readonly string $appname, private readonly string $lockfile, private readonly ProjectPath $apppaths, private readonly Pacmd $pacommands, private readonly Pautils $pautils, private readonly EM $em)
    {
        $store = new FlockStore(sys_get_temp_dir());
        $factory = new LockFactory($store);
        $this->locksystem = $factory->createLock('pannelloamministrazione-command');
        $this->locksystem->release();
    }

    /**
     *
     * @return array<mixed>
     */
    private function findEntities() : array
    {
        $entitiesprogetto = [];
        $prefix = 'App\\Entity\\';
        $prefixBase = 'Base';
        $entities = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        // compute additional API models since external bundles
        $additionalEntities = $this->findAPIModels();
        foreach ($entities as $entity) {
            if (str_starts_with($entity, $prefix)) {
                if (!str_starts_with(substr($entity, strlen($prefix)), $prefixBase)) {
                    $entitiesprogetto[] = substr($entity, strlen($prefix));
                }
            }
        }
        // merge of found arrays
        $outcomes = array_merge($entitiesprogetto, $additionalEntities);
        return $outcomes;
    }

    /**
     * It looks for Models existent into included external bundles.
     * It uses ApiUtils in order to know where to search and what look for.
     *
     * @return array<mixed>
     */
    private function findAPIModels(): array
    {
        $apiUtil = new ApiUtils();
        $apiUtil->setRootDir($this->apppaths->getVendorPath());
        return $apiUtil->apiModels();
    }

    public function index() : Response
    {
        ini_set("memory_milit", "256M");
        $finder = new Finder();
        $fs = new Filesystem();

        $projectDir = $this->apppaths->getRootPath();
        $docDir = $this->apppaths->getDocPath();

        $mwbs = [];

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

        $comandishell = [
            ['text' => $this->fixSlash($dellogsfiles), 'link' => '#'],
            ['text' => $this->fixSlash($delcacheprodfiles), 'link' => '#'],
            ['text' => $this->fixSlash($delcachedevfiles), 'link' => '#'],
            ['text' => $this->fixSlash($setmaintenancefile), 'link' => '#'],
            ['text' => $this->fixSlash($remmaintenancefile), 'link' => '#'],
                //array("text"=>"prova", "link"=>"#"),
        ];
        $composerinstall = '';
        if (false === $windows) {
            $composerinstall = $composerinstall.' cd '.$projectDir.' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1';
            $sed = "sed -i -e 's/cercaquestastringa/sostituisciconquestastringa/g' ".$projectDir.'/.env';
            $comandishell[] = ['text' => $composerinstall, 'link' => '#'];
            $comandishell[] = ['text' => $sed, 'link' => '#'];
        }

        $comandisymfony = [
            ['text' => 'list', 'link' => '#'],
            ['text' => 'cache:clear --env=prod --no-debug', 'link' => '#'],
            ['text' => 'cdf:user:create admin pass admin@admin.it', 'link' => '#'],
            ['text' => 'cdf:user:promote username ROLE_SUPER_ADMIN', 'link' => '#'],
            ['text' => "assets:install $projectDir/public", 'link' => '#'],
            ['text' => 'pannelloamministrazione:checkgitversion', 'link' => '#'],
        ];

        $entities = $this->findEntities();
        sort($entities);

        $twigparms = [
            'svn' => $svn, 'git' => $git, 'mwbs' => $mwbs, 'entities' => $entities,
            'rootdir' => $this->fixSlash($projectDir),
            'comandishell' => $comandishell,
            'comandisymfony' => $comandisymfony,
            'iswindows' => $windows,
            'appname' => $this->appname,
        ];

        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/index.html.twig', $twigparms);
    }

    private function fixSlash(string $path) : string
    {
        return str_replace('\\', '\\\\', $path);
    }

    private function getLockMessage() : string
    {
        return "<h2 style='color: orange;'>E' già in esecuzione un comando, riprova tra qualche secondo!</h2>";
    }

    public function aggiornaSchemaDatabase(): Response
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $this->locksystem->acquire();
        $command = $this->pacommands;
        $result = $command->aggiornaSchemaDatabase();
        $this->locksystem->release();
        if (0 != $result['errcode']) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
            $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

            return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /* FORMS */

    /**
     * Generate form item, controllers and twigs for the given entity/model
     */
    public function generateFormCrud(Request $request) : Response
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $entityform = $request->get('entityform');
        $generatemplate = 'true' === $request->get('generatemplate');
        $this->locksystem->acquire();
        //we are working with an API?
        $isApi = false;
        //verify if provided string belongs to an API model
        if (\str_contains($entityform, '(API)')) {
            $isApi = true;
            $entityform = trim(\str_replace('(API)', '', $entityform));
        }
        // Setup an utility pack of commands (Commands.php)
        $command = $this->pacommands;
        $result = $command->generateFormCrud($entityform, $generatemplate, $isApi);
        $this->locksystem->release();
        //$retcc = '';
        if ($result['errcode'] < 0) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => 'Generazione Form Crud', 'message' => $result['message']];

            return new Response(
                $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms),
                \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        //$retcc = $command->clearCacheEnv($this->get('kernel')->getEnvironment());

        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /* ENTITIES */

    public function generateEntity(Request $request) : Response
    {
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $this->locksystem->acquire();
        $wbFile = $request->get('file');
        $command = $this->pacommands;
        $result = $command->generateEntity($wbFile);
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        $this->locksystem->release();
        if (0 != $result['errcode']) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
            $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

            return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /* VCS (GIT,SVN) */

    /**
     * @codeCoverageIgnore
     */
    public function getVcs() : Response
    {
        set_time_limit(0);
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $this->locksystem->acquire();
        $command = $this->pacommands;
        $result = $command->getVcs();
        $this->locksystem->release();
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /* CLEAR CACHE */

    /**
     * Suppress PMD warnings per exit.
     *
     * @//SuppressWarnings(PHPMD)
     */
    public function clearCache(): Response
    {
        set_time_limit(0);
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $this->locksystem->acquire();
        $command = $this->pacommands;
        $result = $command->clearcache();
        $this->locksystem->release();
        if (0 != $result['errcode']) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
            $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

            return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /* CLEAR CACHE */

    public function symfonyCommand(Request $request) : Response
    {
        set_time_limit(0);

        $simfonycommand = $request->get('symfonycommand');
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        $this->locksystem->acquire();
        $pammutils = $this->pautils;
        $command = $this->apppaths->getConsoleExecute().' '.$simfonycommand;
        $result = $pammutils->runCommand($command);
        $this->locksystem->release();
        if (0 != $result['errcode']) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
            $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

            return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /**
     * Suppress PMD warnings per exit.
     *
     * @SuppressWarnings(PHPMD)
     */
    public function unixCommand(Request $request): Response
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
        }
        $this->locksystem->acquire();
        $result = $pammutils->runCommand($unixcommand);
        $this->locksystem->release();
        // eseguito deopo la fine del comando
        if (0 != $result['errcode']) {
            $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
            $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

            return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $twigparms = ['errcode' => $result['errcode'], 'command' => $result['command'], 'message' => $result['message']];
        return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
    }

    /**
     * @codeCoverageIgnore
     */
    public function phpunittest(): Response
    {
        set_time_limit(0);
        if (!$this->locksystem->acquire()) {
            return new Response($this->getLockMessage());
        }
        if (!OsFunctions::isWindows()) {
            $this->locksystem->acquire();
            //$phpPath = OsFunctions::getPHPExecutableFromPath();
            $command = 'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'simple-phpunit';
            $process = new Process([$command]);
            $process->setWorkingDirectory($this->apppaths->getRootPath());

            $process->run();

            $this->locksystem->release();
            // eseguito dopo la fine del comando
            if (!$process->isSuccessful()) {
                $twigparms = ['errcode' => -1, 'command' => $command, 'message' => $process->getOutput().$process->getErrorOutput()];
                $view = $this->renderView('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);

                return new Response($view, \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $twigparms = ['errcode' => 0, 'command' => $command, 'message' => $process->getOutput().$process->getErrorOutput()];
            return $this->render('@PannelloAmministrazione/PannelloAmministrazione/outputcommand.html.twig', $twigparms);
        }
        // @codeCoverageIgnoreStart
        return new Response('Non previsto in ambiente windows!', \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        // @codeCoverageIgnoreEnd
    }
}
