<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Expr\Comparison;
use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;
use Exception;
use function count;

trait TabellaQueryTrait
{

    /**
     *
     * @return mixed
     */
    protected function biQueryBuilder()
    {
        $nometabellaalias = $this->generaAlias($this->tablename);
        $qb = $this->em->createQueryBuilder()
                ->select(array($nometabellaalias))
                ->from($this->entityname, $nometabellaalias);
        /* @phpstan-ignore-next-line */
        $campi = array_keys($this->em->getMetadataFactory()->getMetadataFor($this->entityname)->reflFields);
        $this->recursiveJoin($qb, $campi, $this->tablename, $nometabellaalias);
        $this->buildWhere($qb);
        $this->orderByBuilder($qb);

        return $qb;
    }

    /**
     *
     * @param mixed $qb
     * @param array<mixed> $campi
     * @param string $nometabella
     * @param string $alias
     * @param array<mixed> $ancestors
     */
    protected function recursiveJoin(&$qb, $campi, $nometabella, $alias, $ancestors = array()): void
    {
        foreach ($campi as $campo) {
            if (false !== strpos(strtolower($campo), 'relatedby')) {
                continue;
            }
            if (!in_array($nometabella, $ancestors)) {
                $ancestors[] = $nometabella;
            }

            $configurazionecampo = isset($this->configurazionecolonnetabella[ucfirst(implode('.', $ancestors)) . '.' . $campo]) ?
                    $this->configurazionecolonnetabella[ucfirst(implode('.', $ancestors)) . '.' . $campo] : false;
            if ($configurazionecampo && true === $configurazionecampo['association']) {
                // crea la relazione con $padre = $nometabella in corso e figlio = $nomecampo con $alias generato
                if ((isset($configurazionecampo['sourceentityclass'])) && (null !== $configurazionecampo['sourceentityclass'])) {
                    $entitysrc = $configurazionecampo['sourceentityclass'];
                    $nometabellasrc = $this->em->getClassMetadata($entitysrc)->getTableName();
                } else {
                    $nometabellasrc = $nometabella;
                }

                $entitytarget = $configurazionecampo['associationtable']['targetEntity'];
                $nometabellatarget = $this->em->getClassMetadata($entitytarget)->getTableName();
                $aliastarget = $this->generaAlias($nometabellatarget, $nometabellasrc, $ancestors);
                //$qb->leftJoin($alias . "." . $configurazionecampo["nomecampo"], $aliastarget);
                //$camporelazionejoin = strtolower(substr($configurazionecampo["nomecampo"], strpos($configurazionecampo["nomecampo"], ".") + 1));
                $parti = explode('.', $configurazionecampo['nomecampo']);

                $camporelazionejoin = strtolower($parti[count($parti) - 1]);
                $qb->leftJoin($alias . '.' . $camporelazionejoin, $aliastarget);
                $campitarget = array_keys($this->em->getMetadataFactory()->getMetadataFor($entitytarget)->reflFields);
                $this->recursiveJoin($qb, $campitarget, $nometabellatarget, $aliastarget, $ancestors);

                // lancia rescursiveJoin su questo campo con padre = $aliasgenerato
                // --- figlio = $nomecampo
                // --- alias = alias generato nuovo
            }
        }
    }

    /**
     *
     * @param mixed $qb
     */
    protected function buildWhere(&$qb): void
    {

        $filtro = '';
        $prefiltro = '';
        foreach ($this->prefiltri as $key => $prefiltro) {
            $this->prefiltri[$key]['prefiltro'] = true;
        }
        foreach ($this->filtri as $key => $filtro) {
            $this->filtri[$key]['prefiltro'] = false;
        }
        $tuttifiltri = array_merge($this->filtri, $this->prefiltri);
        $parametribag = array();
        if (count($tuttifiltri)) {
            $descrizionefiltri = '';
            foreach ($tuttifiltri as $num => $filtrocorrente) {
                $strpos = strripos($filtrocorrente['nomecampo'], '.');
                if ($strpos === false) {
                    throw new Exception("Impossibile trovare il . in " . $filtrocorrente['nomecampo']);
                }
                $tablename = substr($filtrocorrente['nomecampo'], 0, $strpos);
                $alias = $this->findAliasByTablename($tablename);
                $fieldname = $alias . '.' . (substr($filtrocorrente['nomecampo'], strripos($filtrocorrente['nomecampo'], '.') + 1));
                $fieldvalue = $this->getFieldValue($filtrocorrente['valore']);
                $fieldoperator = $this->getOperator($filtrocorrente['operatore']);
                $fitrocorrenteqp = 'fitrocorrente' . $num;
                $filtronomecampocorrente = $this->findFieldnameByAlias($filtrocorrente['nomecampo']);
                $criteria = new ParametriQueryTabellaDecoder(
                    $fieldname,
                    $fieldoperator,
                    $fieldvalue,
                    $fitrocorrenteqp,
                    $filtronomecampocorrente
                );

                $querycriteria = $criteria->getQueryCriteria();
                $queryparameter = $criteria->getQueryParameters();

                if ($querycriteria) {
                    $qb->andWhere($querycriteria);
                    $parametribag = array_merge($queryparameter, $parametribag);
                } else {
                    $qb->andWhere($fieldname . ' ' . $fieldoperator . ' ' . ":$fitrocorrenteqp");
                    $parametribag = array_merge(array($fitrocorrenteqp => $fieldvalue), $parametribag);
                }
                $this->getDescrizioneFiltro($descrizionefiltri, $filtrocorrente, $criteria);
            }
            $this->traduzionefiltri = substr($descrizionefiltri, 2);
        }
        $qb->setParameters($parametribag);

        if (isset($this->wheremanuale)) {
            $qb->andWhere($this->wheremanuale);
        }
    }

    /**
     *
     * @param mixed $qb
     */
    protected function orderByBuilder(&$qb): void
    {
        foreach ($this->colonneordinamento as $nomecampo => $tipoordinamento) {
            $strpos = strripos($nomecampo, '.');
            if ($strpos === false) {
                throw new Exception("Impossibile trovare il . in " . $nomecampo);
            }
            $tablename = substr($nomecampo, 0, $strpos);
            $alias = $this->getAliasGenerato($tablename);
            $fieldname = $alias . '.' . (substr($nomecampo, strripos($nomecampo, '.') + 1));
            $qb->addOrderBy($fieldname, $tipoordinamento);
        }
    }

    /**
     * Attempt to translate the user given value into a boolean valid field
     */
    private function translateBoolValue(string $fieldvalue): string
    {
        switch (strtoupper($fieldvalue)) {
            case 'SI':
                $fieldvalue = 'true';
                break;
            case '1':
                $fieldvalue = 'true';
                break;
            case 'NO':
                $fieldvalue = 'false';
                break;
            case '0':
                $fieldvalue = 'false';
                break;
            default:
                $fieldvalue = 'false';
                break;
        }
        return $fieldvalue;
    }

    /**
     * It appends the new filter string part to the given filter string ($filterString)
     */
    private function appendFilterString(string &$filterString, ?string $swaggerType, string $swaggerKind, string $fieldvalue): void
    {
        if ($swaggerKind == 'bool') {
            $filterString .= $this->translateBoolValue($fieldvalue);
        } elseif ($swaggerType == null /* || $swaggerFormats[ $nomeCampo ] == 'datetime' */) {
            //"%" chars will be applied by insurance back-end API
            $filterString .= '"' . $fieldvalue . '"';
        } elseif ($swaggerType == 'datetime' || $swaggerType == 'date') {
            $fieldvalue = \str_replace("/", "-", $fieldvalue);
            //does it contain an hour ?
            $hour = strpos($fieldvalue, ":");
            $time = strtotime($fieldvalue);
            if ($time === false) {
                throw new Exception("time non valido: " . $time);
            }

            $backend_format = FieldTypeUtils::getEnvVar("BE_DATETIME_FORMAT", "Y-m-d\TH:i:sP");
            if ($hour === false) {
                $backend_format = FieldTypeUtils::getEnvVar("BE_DATE_FORMAT", "Y-m-d");
            }
            $filterString .= date($backend_format, $time);
        } else {
            $filterString .= $fieldvalue;
        }
    }

    /**
     * It composes filtering string to be used with api rest services
     */
    protected function filterByApiBuilder(): ?string
    {
        $filterString = null;
        $filtro = '';
        $prefiltro = '';
        foreach ($this->prefiltri as $key => $prefiltro) {
            $this->prefiltri[$key]['prefiltro'] = true;
        }
        foreach ($this->filtri as $key => $filtro) {
            $this->filtri[$key]['prefiltro'] = false;
        }
        $tuttifiltri = array_merge($this->filtri, $this->prefiltri);
        //$parametribag = array();
        if (count($tuttifiltri)) {
            $attributeMap = $this->entityname::attributeMap();
            $swaggerFormats = $this->entityname::swaggerFormats();
            $swaggerTypes = $this->entityname::swaggerTypes();
            //compose the string
            $descrizionefiltri = '';
            foreach ($tuttifiltri as $num => $filtrocorrente) {
                $nomeCampo = substr($filtrocorrente['nomecampo'], strripos($filtrocorrente['nomecampo'], '.') + 1);
                $fieldname = ' ' . $nomeCampo;
                $filteringValues = $this->getFieldValue($filtrocorrente['valore']);
                $fieldoperator = $this->getOperator($filtrocorrente['operatore']);
                $fitrocorrenteqp = 'fitrocorrente' . $num;
                $filtronomecampocorrente = $this->findFieldnameByAlias($filtrocorrente['nomecampo']);

                $criteria = new ParametriQueryTabellaDecoder(
                    $fieldname,
                    $fieldoperator,
                    $filteringValues,
                    $fitrocorrenteqp,
                    $filtronomecampocorrente
                );

                if (is_array($filteringValues)) {
                    $fieldstring = '( ';
                    foreach ($filteringValues as $num => $filterValue) {
                        $fieldstring .= $attributeMap[$nomeCampo];
                        $fieldstring .= ' ' . $this->getApiOperator($filtrocorrente['operatore']) . ' ';
                        $fieldvalue = urldecode($filterValue);
                        $this->appendFilterString($fieldstring, $swaggerFormats[$nomeCampo], $swaggerTypes[$nomeCampo], $fieldvalue);
                        if ($num < count($filteringValues) - 1) {
                            $fieldstring .= ' OR ';
                        }
                    }
                    $fieldstring .= ' )';
                } else {
                    $fieldstring = $attributeMap[$nomeCampo];
                    $fieldstring .= ' ' . $this->getApiOperator($filtrocorrente['operatore']) . ' ';
                    $fieldvalue = urldecode($filteringValues);
                    $this->appendFilterString($fieldstring, $swaggerFormats[$nomeCampo], $swaggerTypes[$nomeCampo], $filteringValues);
                }

                if ($filterString != null) {
                    $filterString .= ' AND ';
                }
                $filterString .= $fieldstring;
                $this->getDescrizioneFiltro($descrizionefiltri, $filtrocorrente, $criteria);
            }
            $this->traduzionefiltri = substr($descrizionefiltri, 2);
        }
        return $filterString;
    }

    /**
     * Return the operator to be used
     */
    private function getApiOperator(string $operator): string
    {
        switch (strtoupper($operator)) {
            case Comparison::CONTAINS:
                $operator = '~';
                break;
            /* case 'IN':
              $operator = Comparison::IN;
              break;
              case 'NOT IN':
              $operator = Comparison::NIN;
              break; */
            default:
                $operator = '=';
                break;
        }

        return $operator;
    }

    /**
     * Build the ordering string compliant with API REST services
     */
    protected function orderByApiBuilder(): ?string
    {
        $attributeMap = $this->entityname::attributeMap();
        $orderingString = null;
        foreach ($this->colonneordinamento as $nomecampo => $tipoordinamento) {
            $fieldname = $attributeMap[substr($nomecampo, strripos($nomecampo, '.') + 1)];
            $fieldname .= ':' . $tipoordinamento;
            if ($orderingString != null) {
                $orderingString .= ',';
            }
            $orderingString .= $fieldname;
        }
        return $orderingString;
    }

    /**
     *
     * @return array<mixed>
     */
    public function getRecordstabella()
    {
        //Look for all tables
        $qb = $this->biQueryBuilder();

        if (false === $this->estraituttirecords) {
            $paginator = new Paginator($qb, true);
            $this->righetotali = count($paginator);
            $this->paginetotali = (int) $this->calcolaPagineTotali($this->getRigheperpagina());
            /* imposta l'offset, ovvero il record dal quale iniziare a visualizzare i dati */
            $offsetrecords = ($this->getRigheperpagina() * ($this->getPaginacorrente() - 1));

            /* Imposta il limite ai record da estrarre */
            if ($this->getRigheperpagina()) {
                $qb = $qb->setMaxResults((int) $this->getRigheperpagina());
            }
            /* E imposta il primo record da visualizzare (per la paginazione) */
            if ($offsetrecords) {
                $qb = $qb->setFirstResult((int) $offsetrecords);
            }
            /* Dall'oggetto querybuilder si ottiene la query da eseguire */
            $recordsets = $qb->getQuery()->getResult();
        } else {
            /* Dall'oggetto querybuilder si ottiene la query da eseguire */
            $recordsets = $qb->getQuery()->getResult();
            $this->righetotali = count($recordsets);
            $this->paginetotali = 1;
        }

        $this->records = array();
        $rigatabellahtml = array();
        foreach ($recordsets as $record) {
            $this->records[$record->getId()] = $record;
            unset($rigatabellahtml);
        }

        return $this->records;
    }

    /**
     * Read the API in order to obtains the pages features
     *
     * @return array<mixed>
     */
    public function getApiRecordstabella()
    {
        $collection = $this->getCollectionName();

        if (false === $this->estraituttirecords) {
            $count = $this->apiManager->getCount($collection);

            $this->righetotali = $count;
            $this->paginetotali = (int) $this->calcolaPagineTotali($this->getRigheperpagina());
            /* imposta l'offset, ovvero il record dal quale iniziare a visualizzare i dati */
            $offsetrecords = ($this->getRigheperpagina() * ($this->getPaginacorrente() - 1));

            $recordsets = $this->apiManager->getAll(
                $collection,
                $offsetrecords,
                $this->getRigheperpagina(),
                $this->orderByApiBuilder(),
                $this->filterByApiBuilder()
            );
        } else {
            /* Dall'oggetto querybuilder si ottiene la query da eseguire */

            $recordsets = $this->apiManager->getAll($collection, 0, null, $this->orderByApiBuilder(), $this->filterByApiBuilder());

            $this->paginetotali = 1;
            $this->righetotali = count($recordsets);
        }
        $this->records = array();
        $rigatabellahtml = array();
        foreach ((array) $recordsets as $record) {
            $this->records[$record->getId()] = $record;
            unset($rigatabellahtml);
        }

        return $this->records;
    }
}
