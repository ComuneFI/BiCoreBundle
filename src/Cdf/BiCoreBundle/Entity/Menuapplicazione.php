<?php
namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cdf\BiCoreBundle\Entity\BaseMenuapplicazione;

/**
 * Cdf\BiCoreBundle\Entity\Menuapplicazione
 *
 * @ORM\Entity(repositoryClass="Cdf\BiCoreBundle\Repository\MenuapplicazioneRepository")
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
