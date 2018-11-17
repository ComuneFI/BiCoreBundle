<?php
namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MenuapplicazioneRepository extends EntityRepository
{

    public function findAllMenuapplicazione()
    {
        $em = $this->getEntityManager();
        $menus = array();
        if ($em->getRepository('BiCoreBundle:Menuapplicazione')) {
            $results = $em->getRepository('BiCoreBundle:Menuapplicazione')->findBy([], ['nome' => 'ASC']);

            $menus = array("" => null);
            foreach ($results as $bu) {
                $menus[$bu->getNome()] = $bu->getId();
            }
        }

        return $menus;
    }
}
