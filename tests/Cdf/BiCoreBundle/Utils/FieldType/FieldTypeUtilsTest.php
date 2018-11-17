<?php

namespace Cdf\BiCoreBundle\Tests\Utils\FieldType;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

class FieldTypeUtilsTest extends WebTestCase
{
    public function testGetBooleanValue()
    {
        $provatrue = true;
        $this->assertSame($provatrue, FieldTypeUtils::getBooleanValue($provatrue));
        $provafalse = false;
        $this->assertSame($provafalse, FieldTypeUtils::getBooleanValue($provafalse));
        $provanull = null;
        $this->assertSame($provanull, FieldTypeUtils::getBooleanValue($provanull));
    }
    public function testGetDateTimeValueFromTimestamp()
    {
        $data = 1272508903;
        $this->assertEquals(new \DateTime('2010-04-29 04:41:43'), FieldTypeUtils::getDateTimeValueFromTimestamp($data));
    }
    public function testExtractDateTime()
    {
        $data = '2018-01-01 00:00:01';
        $this->assertEquals(new \DateTime($data), FieldTypeUtils::extractDateTime($data));
        $data = '2017-12-31 23:59:59';
        $this->assertEquals(new \DateTime($data), FieldTypeUtils::extractDateTime($data));
        $data = '2018-01-01';
        $this->assertEquals(new \DateTime($data), FieldTypeUtils::extractDateTime($data));
        $datacomposta = \Cdf\BiCoreBundle\Utils\Tabella\DatetimeTabella::createFromFormat('Y-m-d', '2018-01-01');
        $datetabella = (json_decode(json_encode($datacomposta), true));
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d', '2018-01-01'), FieldTypeUtils::extractDateTime($datetabella));
        $data = 'prova';
        $this->assertEquals(null, FieldTypeUtils::extractDateTime($data));
        $data = true;
        $this->assertEquals(null, FieldTypeUtils::extractDateTime($data));
        $data = null;
        $this->assertEquals(null, FieldTypeUtils::extractDateTime($data));
    }
}
