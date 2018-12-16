<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Entity\Opzionitabelle;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;

trait TabellaOpzioniFromCoreTrait
{
    protected function getOpzionitabellaFromCore()
    {
        $repoopzionitabelle = $this->em->getRepository(Opzionitabelle::class);
        $repocolonnetabelle = $this->em->getRepository(Colonnetabelle::class);
        $opzionitabella = $repoopzionitabelle->findOpzioniTabella($this->tablename);
        $colonnetabella = $repocolonnetabelle->findOpzioniColonnetabella($this->tablename, $this->user);

        return array('opzionitabella' => $opzionitabella, 'colonnetabella' => $colonnetabella);
    }

    protected function setOpzioniTabellaFromCore($colonnadatabase, &$opzionibuilder)
    {
        $colonnetabellacore = $this->opzionitabellacore['colonnetabella'];
        //$nomecolonna = $this->tablename . "." . $colonnadatabase["fieldName"];
        /* @var $colonnatabellacore \Cdf\BiCoreBundle\Entity\Colonnetabelle */
        foreach ($colonnetabellacore as $colonnatabellacore) {
            $campodabonificare = $colonnatabellacore->getNometabella().'.'.$colonnatabellacore->getNomecampo();
            $campo = $this->bonificaNomeCampo($campodabonificare);
            $this->buildOpzioneTabellaFromCore($campo, 'etichetta', 'getEtichettaindex', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'larghezza', 'getLarghezzaindex', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'escluso', 'getMostraindex', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'editabile', 'getEditabile', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'ordine', 'getOrdineindex', $colonnatabellacore, $opzionibuilder);
            $opzionibuilder[$campo]['campoextra'] = false;
        }
    }

    protected function buildOpzioneTabellaFromCore($campo, $modellocolonneindex, $entityproperty, $colonnatabellacore, &$opzionibuilder)
    {
        if (null !== ($colonnatabellacore->$entityproperty())) {
            $opzionibuilder[$campo][$modellocolonneindex] = $colonnatabellacore->$entityproperty();
        }
    }
}
