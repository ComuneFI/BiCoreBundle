<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Exception;

trait TabellaOpzioniFromModelloColonneTrait
{
    protected function setOpzioniTabellaFromModellocolonne(&$opzionibuilder)
    {
        foreach ($this->modellocolonne as $modellocolonna) {
            $campo = $this->bonificaNomeCampo($modellocolonna['nomecampo']);
            $this->getOpzionitabellaCampiExtra($campo, $modellocolonna, $opzionibuilder);
            foreach ($modellocolonna as $key => $value) {
                $this->checkCampoOpzioniTabella($campo, $modellocolonna, $opzionibuilder);
                if ('ordine' == $key) {
                    $this->setMaxOrdine($value);
                }
                $opzionibuilder[$campo][$key] = $value;
            }
        }
    }

    protected function checkCampoOpzioniTabella($campo, $modellocolonna, &$opzionibuilder)
    {
        if (!array_key_exists($campo, $opzionibuilder)) {
            if ((isset($modellocolonna['campoextra']) && true == $modellocolonna['campoextra'])) {
                // tuttapposto
            } else {
                $ex = 'BiCore: '.$campo." field table option not found, did you mean one of these:\n".
                        implode("\n", array_keys($opzionibuilder)).
                        ' ?';
                throw new Exception($ex);
            }
        }
    }
}
