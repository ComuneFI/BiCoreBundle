<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;

class Finder
{

    private $entityname;
    private $em;

    public function __construct($em, $entityname)
    {
        $this->entityname = $entityname;
        $this->em = $em;
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
