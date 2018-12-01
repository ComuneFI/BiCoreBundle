<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Doctrine\Common\Persistence\ObjectManager;

class Finder
{

    private $entityname;
    private $em;

    public function __construct(ObjectManager $em, $entityname)
    {
        $this->em = $em;
        $this->entityname = $entityname;
    }
    public function getClassNameFromEntityName()
    {
        $entities = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $entityclassname = "";
        foreach ($entities as $entity) {
            $parti = explode("\\", $entity);
            if ($parti[count($parti) - 1] == $this->entityname) {
                $entityclassname = $entity;
            }
        }
        if (!$entityclassname) {
            throw new \Exception("Non riesco a trovare l'entità '" . $this->entityname . "', è stata generata?");
        }
        return $entityclassname;
    }
}
