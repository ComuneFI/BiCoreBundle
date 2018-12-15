<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Ordine.
 *
 * @ORM\Entity(repositoryClass="App\Repository\OrdineRepository")
 */
class Ordine extends BaseOrdine
{
    public function __toString()
    {
        return $this->getCliente()->getNominativo().' ha acquistato '.$this->getQuantita().' '.$this->getProdottofornitore()->getDescrizione().' il '.$this->getData()->format('d/m/Y H:m').' dal fornitore '.$this->getProdottofornitore()->getFornitore()->getRagionesociale().' prezzo unitario:'.number_format($this->getProdottofornitore()->getPrezzo(), 2, '.', '').' per un totale di:'.number_format($this->getQuantita() * $this->getProdottofornitore()->getPrezzo(), 2, '.', '');
    }
}
