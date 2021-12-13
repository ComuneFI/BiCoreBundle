<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Cdf\BiCoreBundle\Entity\Opzionitabelle;

trait TabellaOpzioniFromCoreTrait
{
    /** @return array<mixed> */
    protected function getOpzionitabellaFromCore() : array
    {
        /** @var \Cdf\BiCoreBundle\Repository\OpzionitabelleRepository $repoopzionitabelle */
        $repoopzionitabelle = $this->em->getRepository(Opzionitabelle::class);
        /** @var \Cdf\BiCoreBundle\Repository\ColonnetabelleRepository $repocolonnetabelle */
        $repocolonnetabelle = $this->em->getRepository(Colonnetabelle::class);
        $opzionitabella = $repoopzionitabelle->findOpzioniTabella($this->tablename);
        $colonnetabella = $repocolonnetabelle->findOpzioniColonnetabella($this->tablename, $this->user);

        return array('opzionitabella' => $opzionitabella, 'colonnetabella' => $colonnetabella);
    }

    /**
     *
     * @param array<mixed> $opzionibuilder
     * @return void
     */
    protected function setOpzioniTabellaFromCore(&$opzionibuilder) : void
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

    /**
     *
     * @param string $campo
     * @param string $modellocolonneindex
     * @param string $entityproperty
     * @param string $colonnatabellacore
     * @param array<mixed> $opzionibuilder
     */
    protected function buildOpzioneTabellaFromCore($campo, $modellocolonneindex, $entityproperty, $colonnatabellacore, &$opzionibuilder) : void
    {
        if (null !== ($colonnatabellacore->$entityproperty())) {
            $opzionibuilder[$campo][$modellocolonneindex] = $colonnatabellacore->$entityproperty();
        }
    }
}
