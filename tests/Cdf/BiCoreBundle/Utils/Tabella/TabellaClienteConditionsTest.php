<?php

namespace Cdf\BiCoreBundle\Tests\Utils\Tabella;

use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Utils\Tabella\DatetimeTabella;

class TabellaClienteConditionsTest extends KernelTestCase
{
    protected $doctrine;
    protected $templating;
    protected $bundle;
    protected $controller;
    protected $entityclassnotation;
    protected $entityclass;
    protected $formclass;
    private $recordstabellacliente = 210;
    private $recordstabellanonattivicliente = 9;
    private $colonnetotalicliente = 8;

    protected function setUp(): void
    {
        //$d = DatetimeTabella::createFromFormat("d/m/Y H:i:s", "05/02/1980 00:00:00", new \DateTimeZone('UTC'));
        //dump($d);exit;
        parent::setUp();

        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine');
        $this->templating = static::$kernel->getContainer()->get('templating');

        //Parametri Tabella
        $this->bundle = 'App';
        $this->controller = 'Cliente';
        $template = $this->bundle.':'.$this->controller.':.html.twig';
        if (!$this->templating->exists($template)) {
            $template = $this->controller.'/Crud/index.html.twig';
        }

        $this->entityclassnotation = 'App:Cliente';
        $this->entityclass = 'App\Entity\Cliente';
        $this->formclass = str_replace('Entity', 'Form', $this->entityclass);
    }

    public function testTabellaClienteIndex()
    {
        $elencotest = $this->elencoTests();
        foreach ($elencotest as $singolotest) {
            $idpassato = $singolotest['idpassato'];
            $modellocolonne = $singolotest['modellocolonne'];
            $colonneordinamento = $singolotest['colonneordinamento'];
            $filtri = $singolotest['filtri'];
            $prefiltri = $singolotest['prefiltri'];
            $permessi = $singolotest['permessi'];
            $righetotali = $singolotest['righetotali'];
            $righeperpagina = $singolotest['righeperpagina'];
            $estraituttirecords = $singolotest['estraituttirecords'];
            $user = $singolotest['user'];
            $errormsg = $singolotest['errormsg'];
            $parametritabella = $this->getParametriTabella($permessi, $modellocolonne, $colonneordinamento, $prefiltri, $filtri, $idpassato, $user, $righeperpagina, $estraituttirecords);

            $tabella = new Tabella($this->doctrine, $parametritabella);

            $this->assertEquals($this->colonnetotalicliente, count($tabella->getConfigurazionecolonnetabella()));
            //Controllo coerenza modellocolonne con configurazione colonne tabella
            foreach ($tabella->getConfigurazionecolonnetabella() as $colonna) {
                if (isset($modellocolonne[$colonna['nometabella'].'.'.$colonna['nomecampo']])) {
                    $modellocolonna = $modellocolonne[$colonna['nometabella'].'.'.$colonna['nomecampo']];
                    $this->assertEquals($colonna['etichetta'], $modellocolonna['etichetta']);
                    $this->assertEquals($colonna['escluso'], $modellocolonna['escluso']);
                    $this->assertEquals($colonna['larghezza'], $modellocolonna['larghezza']);
                    $this->assertEquals($colonna['ordine'], $modellocolonna['ordine']);
                }
            }

            //Controllo che le righe estratte siano quelle indicate come righeperpagina
            $this->assertEquals($righeperpagina, count($tabella->getRecordstabella()), $errormsg);
            //Controllo che le righe totali estratte siano quelle che ci si aspetta
            //dump($tabella->getRighetotali());exit;
            $this->assertEquals($righetotali, $tabella->getRighetotali(), $errormsg);
        }
    }

    public function testTabellaClienteIndexParametriMinimi()
    {
        $permessi = ['read' => true, 'create' => true, 'delete' => true, 'update' => true];

        $parametritabella = $this->getParametriTabella($permessi);

        $tabella = new Tabella($this->doctrine, $parametritabella);
        $this->assertEquals(15, count($tabella->getRecordstabella()));
        $this->assertEquals($this->recordstabellacliente, $tabella->getRighetotali());
    }

    private function getParametriTabella($permessi = array(), $modellocolonne = array(), $colonneordinamento = array(), $prefiltri = array(), $filtri = array(), $idpassato = '', $user = null, $righeperpagina = 15, $estraituttirecords = 0)
    {
        $useradmin = $this->doctrine->getManager()->getRepository('BiCoreBundle:Operatori')->findOneByUsername('admin');
        if (!$user) {
            $user = $useradmin;
        }

        return array('em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($this->controller),
            'nomecontroller' => ParametriTabella::setParameter($this->controller),
            'bundle' => ParametriTabella::setParameter($this->bundle),
            'entityname' => ParametriTabella::setParameter($this->entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($this->entityclass),
            'formclass' => ParametriTabella::setParameter($this->formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($permessi)),
            'urltabella' => ParametriTabella::setParameter('/'.$this->controller.'/'.'Tabella'),
            'baseurl' => ParametriTabella::setParameter('/'),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter('Elenco '.$this->controller),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righeperpagina' => ParametriTabella::setParameter($righeperpagina),
            'estraituttirecords' => ParametriTabella::setParameter($estraituttirecords),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'user' => $user,
        );
    }

    private function elencoTests()
    {
        $useradmin = $this->doctrine->getManager()->getRepository('BiCoreBundle:Operatori')->findOneByUsername('admin');
        $usernoroles = $this->doctrine->getManager()->getRepository('BiCoreBundle:Operatori')->findOneByUsername('usernorole');
        $userreadroles = $this->doctrine->getManager()->getRepository('BiCoreBundle:Operatori')->findOneByUsername('userreadrole');

        $alltests = array();
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 15,
            'righetotali' => 210,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'Senza filtri',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.datanascita',
                    'operatore' => '=',
                    'valore' => '1980-02-05', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'datanascita = "1980-02-05")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => \Doctrine\Common\Collections\Expr\Comparison::CONTAINS,
                    'valore' => 'tà', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo LIKE "tà")',
        );
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => 'LIKE',
                    'valore' => 'tà', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo LIKE "tà")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => \Doctrine\Common\Collections\Expr\Comparison::ENDS_WITH,
                    'valore' => 'tà', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo ENDSWITH "tà")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => \Doctrine\Common\Collections\Expr\Comparison::STARTS_WITH,
                    'valore' => 'AN', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 2,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo STARTSWITH "AN")',
        );
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => \Doctrine\Common\Collections\Expr\Comparison::NEQ,
                    'valore' => 'ANDREA Manzi', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 8,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo DIVERSO "Andrea Manzi")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.nominativo',
                    'operatore' => \Doctrine\Common\Collections\Expr\Comparison::CONTAINS,
                    'valore' => "colò Degl'Inno", ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'nominativo CONTAINS "colò Degl\'Inno")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.datanascita',
                    'operatore' => '=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y H:i:s', '05/02/1980 00:00:00'),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'datanascita = DatetimeTabella::createFromFormat("d/m/Y H:i:s", "05/02/1980 00:00:00")',
        );
        /* $alltests[] = array(
          "idpassato" => "",
          "modellocolonne" => array(
          array("nometabella" => $this->controller, "nomecampo" => $this->controller . ".nominativo", "etichetta" => "Nominativo", "ordine" => 10, "larghezza" => 200, "escluso" => false),
          array("nometabella" => $this->controller, "nomecampo" => $this->controller . ".datanascita", "etichetta" => "Data di nascita", "ordine" => 20, "larghezza" => 100, "escluso" => false),
          ),
          "colonneordinamento" => array($this->controller . '.id' => "ASC"),
          "filtri" => array(
          array(
          "nomecampo" => $this->controller . ".datanascita",
          "operatore" => "=",
          "valore" => DatetimeTabella::createFromFormat("d/m/Y", "05/02/1980")
          ),
          ),
          "prefiltri" => array(
          array(
          "nomecampo" => $this->controller . ".attivo",
          "operatore" => '=',
          "valore" => true,
          )),
          "permessi" => ["read" => true, "create" => true, "delete" => true, "update" => true],
          "righeperpagina" => 1,
          "righetotali" => 1,
          "estraituttirecords" => 0,
          "user" => $useradmin,
          "errormsg" => 'datanascita = DatetimeTabella::createFromFormat("d/m/Y", "05/02/1980")',
          ); */
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.datanascita',
                    'operatore' => '<>',
                    //"valore" => DatetimeTabella::createFromFormat("d/m/Y H:i:s", "05/02/1980 00:00:00"))
                    'valore' => '1980-02-05', ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 8,
            'righetotali' => 8,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'datanascita <> "1980-02-05")',
        );
        /* test */
        /* $alltests[] = array(
          "idpassato" => "",
          "modellocolonne" => array(
          array("nometabella" => $this->controller, "nomecampo" => $this->controller . ".nominativo", "etichetta" => "Nominativo", "ordine" => 10, "larghezza" => 200, "escluso" => false),
          array("nometabella" => $this->controller, "nomecampo" => $this->controller . ".datanascita", "etichetta" => "Data di nascita", "ordine" => 20, "larghezza" => 100, "escluso" => false),
          ),
          "colonneordinamento" => array($this->controller . '.id' => "ASC"),
          "filtri" => array(
          array(
          "nomecampo" => $this->controller . ".datanascita",
          "operatore" => "IN",
          "valore" => array(
          DatetimeTabella::createFromFormat("d/m/Y H:i:s", "05/02/1980 00:00:00"),
          DatetimeTabella::createFromFormat("d/m/Y H:i:s", "20/08/1978 00:00:00"),

          )
          //"valore" => array("1980-02-05","1978-08-20")
          )),
          "prefiltri" => array(
          array(
          "nomecampo" => $this->controller . ".attivo",
          "operatore" => '=',
          "valore" => true,
          )),
          "permessi" => ["read" => true, "create" => true, "delete" => true, "update" => true],
          "righeperpagina" => 2,
          "righetotali" => 2,
          "estraituttirecords" => 0,
          "user" => $useradmin,
          ); */
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.note',
                    'operatore' => '=',
                    'valore' => null,
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 9,
            'righetotali' => 9,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'note = null)',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.datanascita',
                    'operatore' => '>=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y', '01/01/1980'),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => false,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 1,
            'righetotali' => 1,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'datanascita >= DatetimeTabella::createFromFormat("d/m/Y", "01/01/1980")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.datanascita',
                    'operatore' => '>=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y', '01/01/1970'),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 4,
            'righetotali' => 4,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'datanascita >= DatetimeTabella::createFromFormat("d/m/Y", "01/01/1970")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.iscrittoil',
                    'operatore' => '>=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y H:i:s', '01/01/2000 00:00:00'),
                ),
                array(
                    'nomecampo' => $this->controller.'.iscrittoil',
                    'operatore' => '<=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y H:i:s', '31/12/2005 00:00:00'),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 2,
            'righetotali' => 2,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'iscrittoil >= DatetimeTabella::createFromFormat("d/m/Y H:i:s", "01/01/2000 00:00:00") and iscrittoil <= DatetimeTabella::createFromFormat("d/m/Y H:i:s", "31/12/2005 00:00:00")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.iscrittoil',
                    'operatore' => '>=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y H:i:s', '01/01/2000 00:00:00'),
                ),
                array(
                    'nomecampo' => $this->controller.'.iscrittoil',
                    'operatore' => '<=',
                    'valore' => DatetimeTabella::createFromFormat('d/m/Y H:i:s', '31/12/2005 00:00:00'),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '<>',
                    'valore' => null,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 2,
            'righetotali' => 2,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'iscrittoil >= DatetimeTabella::createFromFormat("d/m/Y H:i:s", "01/01/2000 00:00:00") and iscrittoil <= DatetimeTabella::createFromFormat("d/m/Y H:i:s", "31/12/2005 00:00:00")',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.creditoresiduo',
                    'operatore' => '>=',
                    'valore' => 12.50,
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 3,
            'righetotali' => 3,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'creditoresiduo >= 12.50',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.punti',
                    'operatore' => 'in',
                    'valore' => array(1000, 100, 0),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 3,
            'righetotali' => 3,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'punti IN array(1000, 100, 0)',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.punti',
                    'operatore' => 'IN',
                    'valore' => array(1000, 100),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => false,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 0,
            'righetotali' => 0,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'punti IN array(1000, 100)',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.nominativo', 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.datanascita', 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(
                array(
                    'nomecampo' => $this->controller.'.punti',
                    'operatore' => 'not in',
                    'valore' => array(1000, 100),
                ),
            ),
            'prefiltri' => array(
                array(
                    'nomecampo' => $this->controller.'.attivo',
                    'operatore' => '=',
                    'valore' => true,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 7,
            'righetotali' => 7,
            'estraituttirecords' => 0,
            'user' => $useradmin,
            'errormsg' => 'punti not in array(1000, 100)',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(),
            'colonneordinamento' => array(),
            'filtri' => array(),
            'prefiltri' => array(),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 210,
            'righetotali' => 210,
            'estraituttirecords' => '1',
            'user' => $useradmin,
            'errormsg' => 'Estrai tutti i record per pagina',
        );

        return $alltests;
    }
}
