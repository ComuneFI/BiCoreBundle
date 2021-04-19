<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cdf\BiCoreBundle\Service\Permessi;

use Cdf\BiCoreBundle\Entity\Permessi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PermessiManager
{
    private $em;
    private $user;
    
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $user)
    {
        $this->em = $em;
        $this->user = $user->getToken()->getUser();
    }

    public function canRead($modulo)
    {
        $permessi = $this->em->getRepository(Permessi::class)->findPermessoModuloOperatore($modulo, $this->user);
        $canread = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'R')) {
                $canread = true;
            }
        } else {
            if ($this->user->getRuoli()->isSuperadmin()) {
                $canread = true;
            }
        }

        return $canread;
    }

    public function canCreate($modulo)
    {
        $permessi = $this->em->getRepository(Permessi::class)->findPermessoModuloOperatore($modulo, $this->user);
        $cancreate = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'C')) {
                $cancreate = true;
            }
        } else {
            if ($this->user->getRuoli()->isSuperadmin()) {
                $cancreate = true;
            }
        }

        return $cancreate;
    }

    public function canUpdate($modulo)
    {
        $permessi = $this->em->getRepository(Permessi::class)->findPermessoModuloOperatore($modulo, $this->user);
        $canupdate = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'U')) {
                $canupdate = true;
            }
        } else {
            if ($this->user->getRuoli()->isSuperadmin()) {
                $canupdate = true;
            }
        }

        return $canupdate;
    }

    public function canDelete($modulo)
    {
        $permessi = $this->em->getRepository(Permessi::class)->findPermessoModuloOperatore($modulo, $this->user);
        $candelete = false;
        if ($permessi) {
            if (false !== stripos(strtoupper($permessi->getCrud()), 'D')) {
                $candelete = true;
            }
        } else {
            if ($this->user->getRuoli()->isSuperadmin()) {
                $candelete = true;
            }
        }

        return $candelete;
    }

    public function toJson($modulo)
    {
        return array(
            'read' => $this->canRead($modulo),
            'create' => $this->canCreate($modulo),
            'delete' => $this->canDelete($modulo),
            'update' => $this->canUpdate($modulo),
        );
    }
}
