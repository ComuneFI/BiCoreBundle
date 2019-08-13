<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Cdf\BiCoreBundle\Entity\Opzionitabelle;

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
        /* @var $colonnatabellacore Colonnetabelle */
        foreach ($colonnetabellacore as $colonnatabellacore) {
            $campodabonificare = $colonnatabellacore->getNometabella().'.'.$colonnatabellacore->getNomecampo();
            $campo = $this->bonificaNomeCampo($campodabonificare);
            $this->buildOpzioneTabellaFromCore($campo, 'etichetta', 'getEtichettaindex', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'larghezza', 'getLarghezzaindex', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'editabile', 'getEditabile', $colonnatabellacore, $opzionibuilder);
            $this->buildOpzioneTabellaFromCore($campo, 'ordine', 'getOrdineindex', $colonnatabellacore, $opzionibuilder);
            if (null !== ($colonnatabellacore->getMostraindex())) {
                $opzionibuilder[$campo]['escluso'] = !$colonnatabellacore->getMostraindex();
            }
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
