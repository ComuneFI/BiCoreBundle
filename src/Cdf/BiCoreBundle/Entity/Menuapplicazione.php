<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Entity\Menuapplicazione.
 *
 * @ORM\Entity()
 */
class Menuapplicazione extends BaseMenuapplicazione
{
    public function isAttivo()
    {
        return $this->getAttivo();
    }

    public function isAutorizzazionerichiesta()
    {
        return $this->getAutorizzazionerichiesta();
    }

    public function hasNotifiche()
    {
        return $this->getNotifiche();
    }
}
