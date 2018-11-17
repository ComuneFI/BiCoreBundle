<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\BaseFornitore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * App\Entity\Fornitore
 *
 * @ORM\Entity()
 */
class Fornitore extends BaseFornitore
{

    /**
     * @Assert\NotBlank()
     */
    protected $ragionesociale;

    public function __toString()
    {
        return $this->getRagionesociale();
    }
}
