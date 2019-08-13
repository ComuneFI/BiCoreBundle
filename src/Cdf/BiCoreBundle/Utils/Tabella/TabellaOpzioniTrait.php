<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Utils\Arrays\ArrayUtils;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use function count;

trait TabellaOpzioniTrait
{
    use TabellaOpzioniFromCoreTrait, TabellaOpzioniFromModelloColonneTrait;

    protected function getAllOpzioniTabella()
    {
        $opzionibuilder = array();
        foreach ($this->colonnedatabase as $colonnadatabase) {
            // Inserire dati da definizione entity
            $this->setOpzioniTabellaDefault($colonnadatabase, $opzionibuilder, null, false);
        }
        $this->setOpzioniTabellaFromModellocolonne($opzionibuilder);
        $this->setOpzioniTabellaFromCore($colonnadatabase, $opzionibuilder);
        $this->setOrdinaColonneTabella($opzionibuilder);
        $this->setLarghezzaColonneTabella($opzionibuilder);

        return $opzionibuilder;
    }

    private function getOpzionitabellaCampiExtra($campo, $modellocolonna, &$opzionibuilder)
    {
        if ((isset($modellocolonna['campoextra']) && true == $modellocolonna['campoextra'])) {
            $opzionibuilder[$campo] = array(
                'tipocampo' => $modellocolonna['tipocampo'],
                'nomecampo' => $campo,
                'nometabella' => $modellocolonna['nometabella'],
                'entityclass' => null,
                'sourceentityclass' => null,
                'ordine' => null,
                'etichetta' => $campo,
                'larghezza' => 5,
                'editabile' => false,
                'campoextra' => true,
                'association' => null,
                'associationtable' => null,
                'escluso' => false,
            );
        }
    }

    protected function setOpzioniTabellaDefault($infoentity, &$opzionibuilder, $jointable = null, $ricursione = false, $ancestors = array())
    {
        $nometabella = ((isset($jointable)) ? $jointable : $this->tablename);
        if (!in_array($nometabella, $ancestors)) {
            $ancestors[] = $nometabella;
        }
        $nomecolonna = ucfirst(implode('.', $ancestors)).'.'.$infoentity['fieldName'];

        $this->elaboraColonneOpzioniTabellaMancanti($opzionibuilder, $infoentity, $nometabella, $nomecolonna, $ricursione);

        if (isset($infoentity['association'])) {
            $this->elaboraJoin($opzionibuilder, $infoentity, $ancestors);
        }
    }

    private function elaboraColonneOpzioniTabellaMancanti(&$opzionibuilder, $colonnadatabase, $nometabella, $nomecolonna, $ricursione)
    {
        $opzionibuilder[$nomecolonna] = array(
            'tipocampo' => isset($colonnadatabase['association']) ? 'join' : $colonnadatabase['type'],
            'nomecampo' => $nomecolonna,
            'nometabella' => $nometabella,
            'entityclass' => $colonnadatabase['entityClass'],
            'sourceentityclass' => isset($colonnadatabase['sourceEntityClass']) ? $colonnadatabase['sourceEntityClass'] : null,
            'ordine' => null,
            'etichetta' => ucfirst($colonnadatabase['columnName']),
            'larghezza' => 10,
            'editabile' => true,
            'campoextra' => false,
            'association' => isset($colonnadatabase['association']) ? $colonnadatabase['association'] : false,
            'associationtable' => isset($colonnadatabase['associationtable']) ? $colonnadatabase['associationtable'] : null,
            'decodifiche' => null,
            'escluso' => (true === $ricursione) ? true : '_id' == substr($colonnadatabase['fieldName'], -3) ? true : false,
        );
    }

    private function getLarghezzaColonneTabellaTotalePercentuale($opzionibuilder)
    {
        $larghezzatotalepercentuale = 0;
        foreach ($opzionibuilder as $opzione) {
            if (false === $opzione['escluso']) {
                $larghezzatotalepercentuale += $opzione['larghezza'];
            }
        }

        return $larghezzatotalepercentuale;
    }

    private function getLarghezzaColonneTabellaPercentualeFinale()
    {
        // il 5% si lascia per la ruzzolina in fondo alla riga, il 3% si lascia per il checkbox in testa alla riga,
        // quindi per le colonne dati resta il 92%
        $percentualefinale = 95; // il 5% si lascia per la ruzzolina in fondo
        if (true === $this->getTabellaParameter('multiselezione')) {
            $percentualefinale -= 3;
        }

        return $percentualefinale;
    }

    private function setLarghezzaColonneTabella(&$opzionibuilder)
    {
        $larghezzatotalepercentuale = $this->getLarghezzaColonneTabellaTotalePercentuale($opzionibuilder);
        $percentualefinale = $this->getLarghezzaColonneTabellaPercentualeFinale();
        $percentualerelativatotale = $percentualefinale * 100 / $larghezzatotalepercentuale;
        if (0 != $percentualefinale - $larghezzatotalepercentuale) {
            foreach ($opzionibuilder as $key => $opzione) {
                if (false === $opzione['escluso']) {
                    $larghezzapercentualericalcolata = ceil($opzione['larghezza'] * $percentualerelativatotale / 100);
                    $opzionibuilder[$key]['larghezza'] = ($larghezzapercentualericalcolata < 5 ? 5 : $larghezzapercentualericalcolata);
                }
            }
        }
    }

    private function elaboraJoin(&$opzionibuilder, $colonnadatabase, $ancestors)
    {
        $entitycollegata = $colonnadatabase['associationtable']['targetEntity'];
        $utils = new EntityUtils($this->em, $entitycollegata);
        $tablecollegataname = $this->em->getClassMetadata($entitycollegata)->getTableName();
        $colonnecollegate = $utils->getEntityColumns($entitycollegata);
        foreach ($colonnecollegate as $colonnacorrente) {
            if (!isset($colonnacorrente['type'])) {
                $this->setOpzioniTabellaDefault($colonnacorrente, $opzionibuilder, $tablecollegataname, true, $ancestors);
                continue;
            }

            if (!in_array($tablecollegataname, $ancestors)) {
                $ancestors[] = $tablecollegataname;
            }
            $nomecampo = ucfirst(implode('.', $ancestors)).'.'.$colonnacorrente['fieldName'];
            $opzionibuilder[$nomecampo] = array(
                'tipocampo' => $colonnacorrente['type'],
                'nomecampo' => $nomecampo,
                'nometabella' => $tablecollegataname,
                'entityclass' => $colonnadatabase['entityClass'],
                'sourceentityclass' => isset($colonnadatabase['sourceEntityClass']) ? $colonnadatabase['sourceEntityClass'] : null,
                'ordine' => null,
                'etichetta' => ucfirst($colonnacorrente['columnName']),
                'larghezza' => 0,
                'editabile' => false,
                'campoextra' => false,
                'association' => null,
                'associationtable' => null,
                'escluso' => true,
            );
        }
    }

    protected function setOrdinaColonneTabella(&$opzionibuilder)
    {
        foreach ($opzionibuilder as $key => $opzione) {
            if (null === $opzione['ordine']) {
                $newordine = $this->getMaxOrdine() + 10;
                $opzionibuilder[$key]['ordine'] = $newordine;
                $this->setMaxOrdine($newordine);
            }
        }
        // Ordinamento per colonna ordine
        ArrayUtils::sortMultiAssociativeArray($opzionibuilder, 'ordine', true);
    }

    private function bonificaNomeCampo($nomecampo)
    {
        $parti = explode('.', $nomecampo);
        $campo = '';
        for ($index = 0; $index < count($parti); ++$index) {
            if ($index == count($parti) - 1) {
                $campo .= '.'.lcfirst($parti[$index]);
            } else {
                $campo .= '.'.ucfirst($parti[$index]);
            }
        }

        return substr($campo, 1);
    }
}
