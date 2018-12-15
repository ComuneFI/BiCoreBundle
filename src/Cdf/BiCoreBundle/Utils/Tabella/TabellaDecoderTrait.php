<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Doctrine\Common\Collections\Expr\Comparison;

trait TabellaDecoderTrait
{
    private $aliasGenerati;
    private $decodificaAlias;

    protected function getDescrizioneFiltro(&$descrizionefiltri, $filtrocorrente, $criteria)
    {
        if (false === $filtrocorrente['prefiltro']) {
            $descrizionefiltri = $descrizionefiltri.', '.$criteria->getDescrizioneFiltro();
        }
    }

    protected function getFieldValue($fieldvalue)
    {
        if (isset($fieldvalue['date'])) {
            return new \Datetime($fieldvalue['date']);
        } else {
            return $fieldvalue;
        }
    }

    protected function getOperator($operator)
    {
        switch (strtoupper($operator)) {
            case 'LIKE':
                $operator = Comparison::CONTAINS;
                break;
            case 'IN':
                $operator = Comparison::IN;
                break;
            case 'NOT IN':
                $operator = Comparison::NIN;
                break;
            default:
                break;
        }

        return $operator;
    }

    protected function generaAlias($nometabella, $nomepadre = false, $ancestors = array())
    {
        $nometabellapulito = preg_replace('/[^a-z0-9\.]/i', '', $nometabella);
        $primalettera = strtolower(substr($nometabellapulito, 0, 1));
        if ($nomepadre && !in_array($nomepadre, $ancestors)) {
            $ancestors[] = $nomepadre;
        }
        if (!in_array($nometabella, $ancestors)) {
            $ancestors[] = $nometabella;
        }

        if (isset($this->aliasGenerati[$primalettera])) {
            $risposta = $primalettera.$this->aliasGenerati[$primalettera];
            $this->aliasGenerati[$primalettera] = $this->aliasGenerati[$primalettera] + 1;
        } else {
            $risposta = $primalettera;
            $this->aliasGenerati[$primalettera] = 1;
        }

        $this->decodificaAlias[ucfirst(implode('.', $ancestors))] = array('alias' => $risposta);

        return $risposta;
    }

    protected function findAliasByTablename($tablename)
    {
        if (!array_key_exists($tablename, $this->decodificaAlias)) {
            $ex = 'Fifree: table or association '.$tablename." not found, did you mean one of these:\n".
                    implode("\n", array_keys($this->decodificaAlias)).
                    ' ?';
            throw new \Exception($ex);
        }

        return $this->getAliasGenerato($tablename);
    }

    protected function findFieldnameByAlias($nomecampo)
    {
        if (!array_key_exists($nomecampo, $this->configurazionecolonnetabella)) {
            $ex = 'Fifree: field or association '.$nomecampo." not found, did you mean one of these:\n".
                    implode("\n", array_keys($this->configurazionecolonnetabella)).
                    ' ?';
            throw new \Exception($ex);
        }

        return $this->configurazionecolonnetabella[$nomecampo];
    }

    public function getAliasGenerato($tablename)
    {
        return $this->decodificaAlias[$tablename]['alias'];
    }
}
