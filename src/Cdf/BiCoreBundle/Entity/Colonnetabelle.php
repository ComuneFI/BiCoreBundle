<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cdf\BiCoreBundle\Entity\BaseTabelle;

/**
 * @ORM\Entity(repositoryClass="Cdf\BiCoreBundle\Repository\ColonnetabelleRepository")
 */
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
