<?php

namespace Cdf\BiCoreBundle\Tests\Utils\Arrays;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cdf\BiCoreBundle\Utils\Arrays\ArrayUtils;

class ArrayUtilsTest extends WebTestCase
{
    private $rubrica = array();

    public function setUp(): void
    {
        $this->rubrica[] = array('matricola' => 99996, 'cognome' => 'manzi', 'nome' => 'andrea');
        $this->rubrica[] = array('matricola' => 99994, 'cognome' => 'piariello', 'nome' => 'emidio');
        $this->rubrica[] = array('matricola' => 99993, 'cognome' => 'zarra', 'nome' => 'zorro');
        $this->rubrica[] = array('matricola' => 99992, 'cognome' => 'aiazzi', 'nome' => 'andrea');
        $this->rubrica[] = array('matricola' => 99999, 'cognome' => 'rossi', 'nome' => 'mario');
        $this->rubrica[] = array('matricola' => 99998, 'cognome' => 'bianchi', 'nome' => 'andrea');
        $this->rubrica[] = array('matricola' => 99997, 'cognome' => 'verdi', 'nome' => 'michele');
    }

    public function testOrderBy()
    {
        $arr = new ArrayUtils();
        $risultato = $arr->arrayOrderby($this->rubrica, 'cognome', SORT_ASC);
        $this->assertEquals(99992, $risultato[0]['matricola']);
        $this->assertEquals(99993, $risultato[(count($risultato) - 1)]['matricola']);
    }

    public function testInMultiarray()
    {
        $arr = new ArrayUtils();

        $risultatoa = $arr->inMultiarray('aiazzii', $this->rubrica, 'cognome');
        $this->assertFalse($risultatoa);

        $risultatob = $arr->inMultiarray('aiazzi', $this->rubrica, 'cognome');
        $this->assertEquals(3, $risultatob);

        $risultatoc = $arr->inMultiarray('andrea', $this->rubrica, 'nome');
        $this->assertEquals(0, $risultatoc);
    }

    public function testInMultiarrayTutti()
    {
        $arr = new ArrayUtils();
        $risultatoa = $arr->inMultiarrayTutti('aiazzii', $this->rubrica, 'cognome', false);
        $this->assertFalse($risultatoa);

        $risultatob = $arr->inMultiarrayTutti('aiazzi', $this->rubrica, 'cognome', false);
        $retarray = array(3);
        $this->assertEquals($retarray, $risultatob);

        $risultatoc = $arr->inMultiarrayTutti('andrea', $this->rubrica, 'nome', false);
        $this->assertEquals(array(0, 3, 5), $risultatoc);
    }

    public function testMultiInMultiarray()
    {
        $arr = new ArrayUtils();
        $risultatoa = $arr->multiInMultiarray($this->rubrica, array('cognome' => 'aiazzii'));
        $this->assertFalse($risultatoa);

        $risultatob = $arr->multiInMultiarray($this->rubrica, array('cognome' => 'aiazzi'));
        $this->assertEquals(3, $risultatob);

        $risultatoc = $arr->multiInMultiarray($this->rubrica, array('nome' => 'andrea'));
        $this->assertEquals(0, $risultatoc);

        $risultatod = $arr->multiInMultiarray($this->rubrica, array('nome' => 'andrea'), false, true);
        $this->assertEquals(array(0, 3, 5), $risultatod);
    }

    public function testsortMultiAssociativeArray()
    {
        $testarray = array(
            'nominativo' => ['nomecampo' => 'nominativo', 'ordine' => 30],
            'datanascita' => ['nomecampo' => 'datanascita', 'ordine' => 10],
            'telefono' => ['nomecampo' => 'telefono', 'ordine' => 20], )
        ;
        /* ritorna:
          array["datanascita" => ["nomecampo" => "datanascita","ordine" => 10],
          "telefono" => ["nomecampo" => "telefono","ordine" => 20],
          "nominativo" => ["nomecampo" => "nominativo","ordine" => 30] */
        //dump($testarray);exit;

        $arr = new ArrayUtils();
        $arr->sortMultiAssociativeArray($testarray, 'ordine');

        $attendedkeyarray1 = array('datanascita', 'telefono', 'nominativo');
        $idx1 = 0;
        foreach ($testarray as $key => $value) {
            $this->assertTrue($attendedkeyarray1[$idx1] == $key);
            ++$idx1;
        }

        $arr->sortMultiAssociativeArray($testarray, 'ordine', false);

        $attendedkeyarray2 = array('nominativo', 'telefono', 'datanascita');
        $idx2 = 0;
        foreach ($testarray as $key => $value) {
            $this->assertTrue($attendedkeyarray2[$idx2] == $key);
            ++$idx2;
        }
    }
}
