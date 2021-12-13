<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Exception;

trait TabellaOpzioniFromModelloColonneTrait
{
    /**
     *
     * @param array<mixed> $opzionibuilder
     * @return void
     */
    protected function setOpzioniTabellaFromModellocolonne(array &$opzionibuilder) : void
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

    /**
     *
     * @param string $campo
     * @param array<mixed> $modellocolonna
     * @param array<mixed> $opzionibuilder
     * @return void
     * @throws \Exception
     */
    protected function checkCampoOpzioniTabella(string $campo, array $modellocolonna, array &$opzionibuilder) : void
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
