<?php

namespace Cdf\BiCoreBundle\Tests\Utils\Tabella;

use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Utils\Tabella\DatetimeTabella;

class TabellaOrdineConditionsTest extends KernelTestCase
{
    protected $doctrine;
    protected $templating;
    protected $bundle;
    protected $controller;
    protected $entityclassnotation;
    protected $entityclass;
    protected $formclass;
    private $recordstabellaordine = 14;
    private $colonnetotaliordine = 25;

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
        $this->controller = 'Ordine';
        $template = $this->bundle.':'.$this->controller.':.html.twig';
        if (!$this->templating->exists($template)) {
            $template = $this->controller.'/Crud/index.html.twig';
        }

        $this->entityclassnotation = 'App:Ordine';
        $this->entityclass = 'App\Entity\Ordine';
        $this->formclass = str_replace('Entity', 'Form', $this->entityclass);
    }

    public function testTabellaOrdineIndex()
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
            $user = $singolotest['user'];
            $errormsg = $singolotest['errormsg'];
            $parametritabella = $this->getParametriTabella($permessi, $modellocolonne, $colonneordinamento, $prefiltri, $filtri, $idpassato, $user);

            $tabella = new Tabella($this->doctrine, $parametritabella);

            $this->assertEquals($this->colonnetotaliordine, count($tabella->getConfigurazionecolonnetabella()));
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

    public function testTabellaOrdineIndexParametriMinimi()
    {
        $permessi = ['read' => true, 'create' => true, 'delete' => true, 'update' => true];

        $parametritabella = $this->getParametriTabella($permessi);

        $tabella = new Tabella($this->doctrine, $parametritabella);
        $this->assertEquals(14, count($tabella->getRecordstabella()));
        $this->assertEquals($this->recordstabellaordine, $tabella->getRighetotali());
    }

    private function getParametriTabella($permessi = array(), $modellocolonne = array(), $colonneordinamento = array(), $prefiltri = array(), $filtri = array(), $idpassato = '', $user = null, $righeperpagina = 15)
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
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 14,
            'righetotali' => 14,
            'user' => $useradmin,
            'errormsg' => 'Senza filtri',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(array('nomecampo' => 'Ordine.quantita',
                    'operatore' => '>',
                    'valore' => 20,
//                    "valore" => array("date" => new DatetimeTabella("1960-01-01 23:59:59"))
                )),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 3,
            'righetotali' => 3,
            'user' => $useradmin,
            'errormsg' => 'Ordine.quantita > 20',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(array('nomecampo' => 'Ordine.Prodottofornitore.Fornitore.ragionesociale',
                    'operatore' => '=',
                    'valore' => 'Barilla',
//                    "valore" => array("date" => new DatetimeTabella("1960-01-01 23:59:59"))
                )),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 7,
            'righetotali' => 7,
            'user' => $useradmin,
            'errormsg' => 'Ordine.Prodottofornitore.Fornitore.ragionesociale = "Barilla" ',
        );
        /* test */
        $alltests[] = array(
            'idpassato' => '',
            'modellocolonne' => array(),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(
                array('nomecampo' => 'Ordine.Prodottofornitore.quantitadisponibile',
                    'operatore' => '>=',
                    'valore' => 500,
                ), ),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'righeperpagina' => 9,
            'righetotali' => 9,
            'user' => $useradmin,
            'errormsg' => 'Ordine.Prodottofornitore.quantitadisponibile >= 500',
        );

        return $alltests;
    }
}
