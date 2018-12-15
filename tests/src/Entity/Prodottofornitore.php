<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Prodottofornitore.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProdottofornitoreRepository")
 */
class Prodottofornitore extends BaseProdottofornitore
{
    public function __toString()
    {
        return $this->getDescrizione().' di '.$this->getFornitore()->getRagionesociale();
    }
}
