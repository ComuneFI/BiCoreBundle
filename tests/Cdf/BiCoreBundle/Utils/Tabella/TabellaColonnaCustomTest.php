<?php

namespace Cdf\BiCoreBundle\Tests\Utils\Tabella;

use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Utils\Tabella\DatetimeTabella;

class TabellaColonnaCustomTest extends KernelTestCase
{
    protected $doctrine;
    protected $templating;
    protected $bundle;
    protected $controller;
    protected $entityclassnotation;
    protected $entityclass;
    protected $formclass;

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
            $colonnetotaliordine = $singolotest['colonnetotaliordine'];
            $assertfields = $singolotest['assertfield'];

            $user = $singolotest['user'];
            $errormsg = $singolotest['errormsg'];
            $parametritabella = $this->getParametriTabella($permessi, $modellocolonne, $colonneordinamento, $prefiltri, $filtri, $idpassato, $user);

            $tabella = new Tabella($this->doctrine, $parametritabella);
            $configurazionetabella = $tabella->getConfigurazionecolonnetabella();
            $this->assertEquals($colonnetotaliordine, count($configurazionetabella), $errormsg);
            //Controllo coerenza modellocolonne con configurazione colonne tabella

            foreach ($configurazionetabella as $colonna) {
                if (isset($modellocolonne[$colonna['nometabella'].'.'.$colonna['nomecampo']])) {
                    $modellocolonna = $modellocolonne[$colonna['nometabella'].'.'.$colonna['nomecampo']];
                    $this->assertEquals($colonna['etichetta'], $modellocolonna['etichetta'], $errormsg);
                    $this->assertEquals($colonna['escluso'], $modellocolonna['escluso'], $errormsg);
                    $this->assertEquals($colonna['larghezza'], $modellocolonna['larghezza'], $errormsg);
                    $this->assertEquals($colonna['ordine'], $modellocolonna['ordine'], $errormsg);
                }
            }

            foreach ($assertfields as $keyct => $assertfield) {
                foreach ($assertfield as $keysc => $value) {
                    //dump($configurazionetabella);
                    $this->assertEquals($configurazionetabella[$keyct][$keysc], $value, $errormsg);
                }
            }

            //Controllo che le righe estratte siano quelle indicate come righeperpagina
            $this->assertEquals($righeperpagina, count($tabella->getRecordstabella()), $errormsg);
            //Controllo che le righe totali estratte siano quelle che ci si aspetta
            //dump($tabella->getRighetotali());exit;
            $this->assertEquals($righetotali, $tabella->getRighetotali(), $errormsg);
        }
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
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.data', 'etichetta' => 'Data ordine', 'ordine' => 10, 'larghezza' => 200, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.quantita', 'etichetta' => 'Quantità ordine', 'ordine' => 20, 'larghezza' => 100, 'escluso' => false),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.cliente', 'escluso' => true),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.prodottofornitore', 'escluso' => true),
                array('nometabella' => $this->controller, 'nomecampo' => $this->controller.'.Prodottofornitore.Fornitore.ragionesociale', 'etichetta' => 'Ragione sociale fornitore', 'escluso' => false),
            ),
            'colonneordinamento' => array($this->controller.'.id' => 'ASC'),
            'filtri' => array(),
            'prefiltri' => array(),
            'permessi' => ['read' => true, 'create' => true, 'delete' => true, 'update' => true],
            'colonnetotaliordine' => 25,
            'righeperpagina' => 14,
            'righetotali' => 14,
            'assertfield' => array(
                'Ordine.data' => array('etichetta' => 'Data ordine', 'escluso' => false),
                'Ordine.quantita' => array('etichetta' => 'Quantità ordine', 'escluso' => false),
                'Ordine.cliente' => array('escluso' => true),
                'Ordine.prodottofornitore' => array('escluso' => true),
                'Ordine.Prodottofornitore.Fornitore.ragionesociale' => array('etichetta' => 'Ragione sociale fornitore', 'escluso' => false),
            ),
            'user' => $useradmin,
            'errormsg' => 'Ordine senza filtri',
        );

        return $alltests;
    }
}
