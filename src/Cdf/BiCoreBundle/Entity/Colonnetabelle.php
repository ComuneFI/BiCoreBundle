<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \Cdf\BiCoreBundle\Repository\ColonnetabelleRepository::class)]
class Colonnetabelle extends BaseColonnetabelle
{
    public function hasMostraindex()
    {
        return $this->mostraindex;
    }

    public function isRegistrastorico()
    {
        return $this->getRegistrastorico();
    }
}
