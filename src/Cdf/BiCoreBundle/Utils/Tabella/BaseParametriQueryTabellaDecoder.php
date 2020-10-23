<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use DateTime;
use Doctrine\Common\Collections\Expr\Comparison;

class BaseParametriQueryTabellaDecoder
{

    protected $fieldname;
    protected $fieldoperator;
    protected $fieldvalue;
    protected $fieldqueryparameter;
    protected $criteria;
    protected $parameters;
    protected $fieldinfo;

    public function __construct($fieldname, $fieldoperator, $fieldvalue, $fieldqueryparameter, $fieldinfo)
    {
        $this->fieldname = $fieldname;
        $this->fieldoperator = $fieldoperator;
        if (is_string($fieldvalue)) {
            $this->fieldvalue = urldecode($fieldvalue);
        } else {
            $this->fieldvalue = $fieldvalue;
        }
        $this->fieldqueryparameter = $fieldqueryparameter;
        $this->fieldinfo = $fieldinfo;
        $this->parameters = array();
        /** @phpstan-ignore-next-line */
        $this->buildQuery();
    }

    public function getQueryCriteria()
    {
        return $this->criteria;
    }

    public function getQueryParameters()
    {
        return $this->parameters;
    }

    public function getDescrizioneFiltro()
    {
        $descrizionevalore = '';

        switch (true) {
            case $this->getDescrizioneFiltroIsNull($descrizionevalore):
                break;
            case $this->getDescrizioneFiltroDecodifiche($descrizionevalore):
                break;
            case $this->getDescrizioneFiltroBoolean($descrizionevalore):
                break;
            case $this->getDescrizioneFiltroDate($descrizionevalore):
                break;
            case $this->getDescrizioneFiltroArray($descrizionevalore):
                break;
            case $this->getDescrizioneFiltroString($descrizionevalore):
                break;
            default:
                $this->getDescrizioneFiltroAltro($descrizionevalore);
                break;
        }
        $nomecampo = substr($this->fieldname, stripos($this->fieldname, '.') + 1);
        $filtro = $nomecampo . ' ' . $this->operatorToString($this->fieldoperator) . ' ' . $descrizionevalore;

        return $filtro;
    }

    protected function getDescrizioneFiltroAltro(&$descrizionevalore)
    {
        if ('' == $descrizionevalore) {
            $descrizionevalore = "'" . $this->fieldvalue . "'";
        }
    }

    protected function getDescrizioneFiltroDate(&$descrizionevalore)
    {
        $trovato = false;
        if ('date' == $this->fieldinfo['tipocampo']) {
            if (is_a($this->fieldvalue, "\DateTime")) {
                $descrizionevalore = $this->fieldvalue->format('d/m/Y');
            } else {
                $descrizionevalore = DateTime::createFromFormat('Y-m-d', $this->fieldvalue)->format('d/m/Y');
            }
            $trovato = true;
        }
        if ('datetime' == $this->fieldinfo['tipocampo']) {
            if (is_a($this->fieldvalue, "\DateTime")) {
                $descrizionevalore = $this->fieldvalue->format('d/m/Y H:i:s');
            } else {
                $descrizionevalore = DateTime::createFromFormat('Y-m-d', $this->fieldvalue)->format('d/m/Y');
            }
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroString(&$descrizionevalore)
    {
        $trovato = false;
        if (is_string($this->fieldvalue)) {
            $descrizionevalore = $descrizionevalore = "'" . $this->fieldvalue . "'";
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroDecodifiche(&$descrizionevalore)
    {
        $trovato = false;
        if (isset($this->fieldinfo['decodifiche'])) {
            $decodifiche = $this->fieldinfo['decodifiche'];
            if ($decodifiche) {
                if (is_array($this->fieldvalue)) {
                    foreach ($this->fieldvalue as $value) {
                        $descrizionevalore = $descrizionevalore . "'" . $decodifiche[$value] . "', ";
                    }
                } else {
                    if (isset($decodifiche[$this->fieldvalue])) {
                        $descrizionevalore = $descrizionevalore = "'" . $decodifiche[$this->fieldvalue] . "'";
                    } else {
                        $descrizionevalore = $descrizionevalore = "'" . $this->fieldvalue . "'";
                    }
                }
                $trovato = true;
            }
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroIsNull(&$descrizionevalore)
    {
        $trovato = false;
        if (is_null($this->fieldvalue)) {
            $descrizionevalore = '(vuoto)';
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroBoolean(&$descrizionevalore)
    {
        $trovato = false;
        if (is_bool($this->fieldvalue)) {
            $descrizionevalore = $this->fieldvalue ? 'SI' : 'NO';
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroArray(&$descrizionevalore)
    {
        $trovato = false;
        if (is_array($this->fieldvalue)) {
            foreach ($this->fieldvalue as $value) {
                if (is_numeric($value)) {
                    $descrizionevalore = $descrizionevalore . $value . ', ';
                } else {
                    $descrizionevalore = $descrizionevalore . "'" . $value . "', ";
                }
                $trovato = true;
            }
            $descrizionevalore = substr($descrizionevalore, 0, -2);
        }

        return $trovato;
    }

    protected function operatorToString($operator)
    {
        $decoder = array(
            Comparison::LT => 'minore di',
            Comparison::LTE => 'minore o uguale di',
            Comparison::GT => 'maggiore di',
            Comparison::GTE => 'maggiore o uguale di',
            Comparison::CONTAINS => 'contiene',
            Comparison::STARTS_WITH => 'inizia con',
            Comparison::ENDS_WITH => 'finisce con',
            Comparison::IN => 'compreso tra',
            Comparison::NIN => 'non compreso tra',
            Comparison::EQ => 'uguale a',
            Comparison::NEQ => 'diverso da',
        );

        return $decoder[$operator];
    }
}
