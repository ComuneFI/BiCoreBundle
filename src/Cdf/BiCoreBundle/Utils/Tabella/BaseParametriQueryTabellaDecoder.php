<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use DateTime;
use Doctrine\Common\Collections\Expr\Comparison;

class BaseParametriQueryTabellaDecoder
{

    /**
     *
     * @var mixed
     */
    protected $fieldvalue;

    /**
     *
     * @var string|null
     */
    protected $criteria;

    /**
     *
     * @var array<mixed>
     */
    protected $parameters;

    /**
     *
     * @param mixed $fieldvalue
     * @param array<mixed> $fieldinfo
     */
    public function __construct(protected string $fieldname, protected string $fieldoperator, $fieldvalue, protected string $fieldqueryparameter, protected $fieldinfo)
    {
        if (is_string($fieldvalue)) {
            $this->fieldvalue = urldecode($fieldvalue);
        } else {
            $this->fieldvalue = $fieldvalue;
        }
        $this->parameters = [];
        /** @phpstan-ignore-next-line */
        $this->buildQuery();
    }

    /**
     *
     * @return string|null
     */
    public function getQueryCriteria()
    {
        return $this->criteria;
    }

    /**
     *
     * @return array<mixed>
     */
    public function getQueryParameters(): array
    {
        return $this->parameters;
    }

    public function getDescrizioneFiltro(): string
    {
        $descrizionevalore = '';

        switch (true) {
            case $this->getDescrizioneFiltroIsNull($descrizionevalore):
            case $this->getDescrizioneFiltroDecodifiche($descrizionevalore):
            case $this->getDescrizioneFiltroBoolean($descrizionevalore):
            case $this->getDescrizioneFiltroDate($descrizionevalore):
            case $this->getDescrizioneFiltroArray($descrizionevalore):
            case $this->getDescrizioneFiltroString($descrizionevalore):
                break;
            default:
                $this->getDescrizioneFiltroAltro($descrizionevalore);
                break;
        }
        $nomecampo = substr($this->fieldname, stripos($this->fieldname, '.') + 1);

        return $nomecampo . ' ' . $this->operatorToString($this->fieldoperator) . ' ' . $descrizionevalore;
    }

    protected function getDescrizioneFiltroAltro(string &$descrizionevalore): void
    {
        if ('' === $descrizionevalore) {
            $descrizionevalore = "'" . $this->fieldvalue . "'";
        }
    }

    protected function getDescrizioneFiltroDate(string &$descrizionevalore): bool
    {
        $trovato = false;
        if ('date' == $this->fieldinfo['tipocampo']) {
            if (is_a($this->fieldvalue, "\DateTime")) {
                $descrizionevalore = $this->fieldvalue->format('d/m/Y');
            } else {
                $dateraw = DateTime::createFromFormat('Y-m-d', $this->fieldvalue);
                if ($dateraw === false) {
                    return false;
                }
                $descrizionevalore = $dateraw->format('d/m/Y');
            }
            $trovato = true;
        }
        if ('datetime' == $this->fieldinfo['tipocampo']) {
            if (is_a($this->fieldvalue, "\DateTime")) {
                $descrizionevalore = $this->fieldvalue->format('d/m/Y H:i:s');
            } else {
                $dateraw = DateTime::createFromFormat('Y-m-d', $this->fieldvalue);
                if ($dateraw === false) {
                    return false;
                }
                $descrizionevalore = $dateraw->format('d/m/Y');
            }
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroString(string &$descrizionevalore): bool
    {
        $trovato = false;
        if (is_string($this->fieldvalue)) {
            $descrizionevalore = "'" . $this->fieldvalue . "'";
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroDecodifiche(string &$descrizionevalore): bool
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
                        $descrizionevalore = "'" . $decodifiche[$this->fieldvalue] . "'";
                    } else {
                        $descrizionevalore = "'" . $this->fieldvalue . "'";
                    }
                }
                $trovato = true;
            }
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroIsNull(string &$descrizionevalore): bool
    {
        $trovato = false;
        if (is_null($this->fieldvalue)) {
            $descrizionevalore = '(vuoto)';
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroBoolean(string &$descrizionevalore): bool
    {
        $trovato = false;
        if (is_bool($this->fieldvalue)) {
            $descrizionevalore = $this->fieldvalue ? 'SI' : 'NO';
            $trovato = true;
        }

        return $trovato;
    }

    protected function getDescrizioneFiltroArray(string &$descrizionevalore): bool
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

    protected function operatorToString(string $operator): string
    {
        $decoder = [
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
        ];

        return $decoder[$operator];
    }
}
