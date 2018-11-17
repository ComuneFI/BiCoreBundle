<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cdf\BiCoreBundle\Utils;

use Cdf\BiCoreBundle\Entity\Permessi;

class PermessiUtils implements \JsonSerializable
{

    private $read = false;
    private $create = false;
    private $delete = false;
    private $edit = false;

    public function __construct($em, $modulo, $operatore)
    {
        $permessi = $em->getRepository(Permessi::class)->findPermessoModuloOperatore($modulo, $operatore);

        if ($permessi) {
            if (stripos(strtoupper($permessi->getCrud()), 'C') !== false) {
                $this->create = true;
            }
            if (stripos(strtoupper($permessi->getCrud()), 'R') !== false) {
                $this->read = true;
            }
            if (stripos(strtoupper($permessi->getCrud()), 'U') !== false) {
                $this->edit = true;
            }
            if (stripos(strtoupper($permessi->getCrud()), 'D') !== false) {
                $this->delete = true;
            }
        } else {
            /* Per il superadmin si restituisce sempre tutti i permessi a true */
            if ($operatore->isSuperadmin()) {
                $this->create = true;
                $this->read = true;
                $this->edit = true;
                $this->delete = true;
            }
        }
    }
    public function canRead()
    {
        return $this->read;
    }
    public function canCreate()
    {
        return $this->create;
    }
    public function canUpdate()
    {
        return $this->edit;
    }
    public function canDelete()
    {
        return $this->delete;
    }
    public function jsonSerialize()
    {
        return array("read" => $this->canRead(), "create" => $this->canCreate(), "delete" => $this->canDelete(), "update" => $this->canUpdate());
    }
    public function __toString()
    {
        return json_encode($this);
    }
}
