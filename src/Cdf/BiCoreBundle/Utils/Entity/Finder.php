<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Doctrine\Common\Persistence\ObjectManager;

class Finder
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function getClassNameFromEntityName($entityname)
    {
        $entities = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $entityclassname = '';
        foreach ($entities as $entity) {
            $parti = explode('\\', $entity);
            if ($parti[count($parti) - 1] == $entityname) {
                $entityclassname = $entity;
            }
        }
        if (!$entityclassname) {
            throw new \Exception("Non riesco a trovare l'entità '".$entityname."', è stata generata?");
        }

        return $entityclassname;
    }
}
