<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cdf\BiCoreBundle\Service\Permessi;

use Cdf\BiCoreBundle\Entity\Permessi;
use Doctrine\ORM\EntityManagerInterface;

class PermessiManager
{

    private EntityManagerInterface $em;
    /** @ phpstan-ignore-next-line */
    private $user;

    /** @ phpstan-ignore-next-line */
    public function __construct(EntityManagerInterface $em, $user)
    {
        $this->em = $em;
        $this->user = $user->getToken()->getUser();
    }

    public function canRead(string $modulo): bool
    {

        $permessi = $this->getPermessi($modulo);
        $canread = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'R')) {
                $canread = true;
            }
        } else {
            if ($this->user->getRuoli() && $this->user->getRuoli()->isSuperadmin()) {
                $canread = true;
            }
        }

        return $canread;
    }

    public function canCreate(string $modulo): bool
    {
        $permessi = $this->getPermessi($modulo);
        $cancreate = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'C')) {
                $cancreate = true;
            }
        } else {
            if ($this->user->getRuoli() && $this->user->getRuoli()->isSuperadmin()) {
                $cancreate = true;
            }
        }

        return $cancreate;
    }

    public function canUpdate(string $modulo): bool
    {
        $permessi = $this->getPermessi($modulo);
        $canupdate = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'U')) {
                $canupdate = true;
            }
        } else {
            if ($this->user->getRuoli() && $this->user->getRuoli()->isSuperadmin()) {
                $canupdate = true;
            }
        }

        return $canupdate;
    }

    public function canDelete(string $modulo): bool
    {
        $permessi = $this->getPermessi($modulo);
        $candelete = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'D')) {
                $candelete = true;
            }
        } else {
            if ($this->user->getRuoli() && $this->user->getRuoli()->isSuperadmin()) {
                $candelete = true;
            }
        }

        return $candelete;
    }

    private function getPermessi(string $modulo): ?Permessi
    {
        /** @var \Cdf\BiCoreBundle\Repository\PermessiRepository $repository */
        $repository = $this->em->getRepository(Permessi::class);
        return $repository->findPermessoModuloOperatore($modulo, $this->user);
    }

    /**
     *
     * @param string $modulo
     * @return array<string, bool>
     */
    public function toJson(string $modulo): array
    {
        return array(
            'read' => $this->canRead($modulo),
            'create' => $this->canCreate($modulo),
            'delete' => $this->canDelete($modulo),
            'update' => $this->canUpdate($modulo),
        );
    }
}
