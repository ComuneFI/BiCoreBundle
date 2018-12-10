<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\BaseCliente;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * App\Entity\Cliente
 *
 * @ORM\Entity(repositoryClass="App\Repository\ClienteRepository")
 *
 */
class Cliente extends BaseCliente
{

    /**
     * @Assert\NotBlank()
     */
    protected $nominativo;

    public function __toString()
    {
        return $this->getNominativo() . ", data di nascita: " . $this->getDatanascita()->format("d/m/Y");
    }
    public function getSaluto()
    {
        return "Ciao " . $this->getNominativo() . "!";
    }
}
