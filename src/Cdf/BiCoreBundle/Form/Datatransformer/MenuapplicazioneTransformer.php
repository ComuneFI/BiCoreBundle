<?php

// src/Form/DataTransformer/IssueToNumberTransformer.php
namespace Cdf\BiCoreBundle\Form\Datatransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Cdf\BiCoreBundle\Entity\Menuapplicazione;

class MenuapplicazioneTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Menuapplicazione) to a string (number).
     *
     * @param  Menuapplicazione|null $menu
     * @return string
     */
    public function reverseTransform($menu)
    {
        if (null === $menu) {
            return null;
        }

        return $menu->getId();
    }

    /**
     * Transforms a string (number) to an object (Menuapplicazione).
     *
     * @param  string $menuId
     * @return Menuapplicazione|null
     * @throws TransformationFailedException if object (Menuapplicazione) is not found.
     */
    public function transform($menuId)
    {
        // no issue number? It's optional, so that's ok
        if (!$menuId) {
            return;
        }

        $menu = $this->entityManager
            ->getRepository(Menuapplicazione::class)
            // query for the issue with this id
            ->find($menuId)
        ;

        if (null === $menuId) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Un menu con id "%s" non esiste!',
                $menu
            ));
        }

        return $menu;
    }
}
