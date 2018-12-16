<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

trait TabellaOpzioniFromModelloColonneTrait
{
    protected function setOpzioniTabellaFromModellocolonne(&$opzionibuilder)
    {
        foreach ($this->modellocolonne as $modellocolonna) {
            $campo = $this->bonificaNomeCampo($modellocolonna['nomecampo']);
            $this->getOpzionitabellaCampiExtra($campo, $modellocolonna, $opzionibuilder);
            foreach ($modellocolonna as $key => $value) {
                if (!array_key_exists($campo, $opzionibuilder)) {
                    if ((isset($modellocolonna['campoextra']) && true == $modellocolonna['campoextra'])) {
                        // tuttapposto
                    } else {
                        $ex = 'Fifree: '.$campo." field table option not found, did you mean one of these:\n".
                                implode("\n", array_keys($opzionibuilder)).
                                ' ?';
                        throw new \Exception($ex);
                    }
                }
                if ('ordine' == $key) {
                    $this->setMaxOrdine($value);
                }
                $opzionibuilder[$campo][$key] = $value;
            }
        }
    }
}
