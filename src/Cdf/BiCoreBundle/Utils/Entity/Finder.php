<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use function count;

class Finder
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getClassNameFromEntityName(string $entityname): string
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
            throw new Exception("Non riesco a trovare l'entità '" . $entityname . "', è stata generata?");
        }

        return $entityclassname;
    }
}
