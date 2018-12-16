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
            if (null !== ($colonnatabellacore->getEtichettaindex())) {
                $opzionibuilder[$campo]['etichetta'] = $colonnatabellacore->getEtichettaindex();
            }
            if (null !== ($colonnatabellacore->getLarghezzaindex())) {
                $opzionibuilder[$campo]['larghezza'] = $colonnatabellacore->getLarghezzaindex();
            }
            if (null !== ($colonnatabellacore->getMostraindex())) {
                $opzionibuilder[$campo]['escluso'] = !$colonnatabellacore->getMostraindex();
            }
            if (null !== ($colonnatabellacore->getEditabile())) {
                $opzionibuilder[$campo]['editabile'] = $colonnatabellacore->getEditabile();
            }
            if (null !== ($colonnatabellacore->getOrdineindex())) {
                $opzionibuilder[$campo]['ordine'] = $colonnatabellacore->getOrdineindex();
                $this->setMaxOrdine($colonnatabellacore->getOrdineindex());
            }
            $opzionibuilder[$campo]['campoextra'] = false;
        }
    }
}
