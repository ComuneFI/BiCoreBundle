<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cdf\BiCoreBundle\Entity\BaseRuoli;

/**
 * Cdf\BiCoreBundle\Entity\Ruoli
 *
 * @ORM\Entity()
 */
class Ruoli extends BaseRuoli
{

    public function isSuperadmin()
    {
        return $this->superadmin;
    }

    public function isAdmin()
    {
        return $this->admin;
    }

    public function isUser()
    {
        return $this->user;
    }
    
    public function __toString()
    {
        return $this->getRuolo();
    }
}
