<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Datetime;
use Doctrine\Common\Collections\Expr\Comparison;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriQueryTabellaDecoder;
use Exception;

trait TabellaDecoderTrait
{
    /** @var array<mixed> */
    private array $aliasGenerati;
    /** @var array<mixed> */
    private array $decodificaAlias;

    /**
     *
     * @param array<mixed> $filtrocorrente
     */
    protected function getDescrizioneFiltro(string &$descrizionefiltri, array $filtrocorrente, ParametriQueryTabellaDecoder $criteria) : void
    {
        if (false === $filtrocorrente['prefiltro']) {
            $descrizionefiltri = $descrizionefiltri.', '.$criteria->getDescrizioneFiltro();
        }
    }

    protected function getFieldValue(array $fieldvalue): \Datetime|array
    {
        if (isset($fieldvalue['date'])) {
            return new Datetime($fieldvalue['date']);
        }
        return $fieldvalue;
    }

    protected function getOperator(string $operator): string
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

    /**
     *
     * @param mixed $nomepadre
     * @param array<mixed> $ancestors
     */
    protected function generaAlias(string $nometabella, $nomepadre = false, $ancestors = []) : string
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
            $this->aliasGenerati[$primalettera] += 1;
        } else {
            $risposta = $primalettera;
            $this->aliasGenerati[$primalettera] = 1;
        }

        $this->decodificaAlias[ucfirst(implode('.', $ancestors))] = ['alias' => $risposta];

        return $risposta;
    }

    protected function findAliasByTablename(string $tablename): string
    {
        if (!array_key_exists($tablename, $this->decodificaAlias)) {
            $ex = 'BiCore: table or association '.$tablename." not found, did you mean one of these:\n".
                    implode("\n", array_keys($this->decodificaAlias)).
                    ' ?';
            throw new Exception($ex);
        }

        return $this->getAliasGenerato($tablename);
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    protected function findFieldnameByAlias(string $nomecampo)
    {
        if (!array_key_exists($nomecampo, $this->configurazionecolonnetabella)) {
            $ex = 'BiCore: field or association '.$nomecampo." not found, did you mean one of these:\n".
                    implode("\n", array_keys($this->configurazionecolonnetabella)).
                    ' ?';
            throw new Exception($ex);
        }

        return $this->configurazionecolonnetabella[$nomecampo];
    }

    public function getAliasGenerato(string $tablename) : string
    {
        return $this->decodificaAlias[$tablename]['alias'];
    }
}
