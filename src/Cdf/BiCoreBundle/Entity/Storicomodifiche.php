<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \Cdf\BiCoreBundle\Repository\StoricomodificheRepository::class)]
class Storicomodifiche extends BaseStoricomodifiche
{
}
