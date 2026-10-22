<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Permessi.
 *
 * @ORM\Entity()
 * @ORM\Table(name="Permessi", indexes={@ORM\Index(name="fk_Permessi_Ruoli1_idx", columns={"ruoli_id"}), @ORM\Index(name="fk_Permessi_Operatori1_idx", columns={"operatori_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base":"BasePermessi", "extended":"Permessi"})
 */
class BasePermessi
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $modulo;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $crud;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ruoli_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $operatori_id;

    /**
     * @ORM\ManyToOne(targetEntity="Ruoli", inversedBy="permessis")
     * @ORM\JoinColumn(name="ruoli_id", referencedColumnName="id")
     */
    protected $ruoli;

    /**
     * @ORM\ManyToOne(targetEntity="Operatori", inversedBy="permessis")
     * @ORM\JoinColumn(name="operatori_id", referencedColumnName="id")
     */
    protected $operatori;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param int $id
     *
     * @return static
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of modulo.
     *
     * @param string $modulo
     *
     * @return static
     */
    public function setModulo($modulo): static
    {
        $this->modulo = $modulo;

        return $this;
    }

    /**
     * Get the value of modulo.
     *
     * @return string
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    /**
     * Set the value of crud.
     *
     * @param string $crud
     *
     * @return static
     */
    public function setCrud($crud): static
    {
        $this->crud = $crud;

        return $this;
    }

    /**
     * Get the value of crud.
     *
     * @return string
     */
    public function getCrud()
    {
        return $this->crud;
    }

    /**
     * Set the value of ruoli_id.
     *
     * @param int $ruoli_id
     *
     * @return static
     */
    public function setRuoliId($ruoli_id): static
    {
        $this->ruoli_id = $ruoli_id;

        return $this;
    }

    /**
     * Get the value of ruoli_id.
     *
     * @return int
     */
    public function getRuoliId()
    {
        return $this->ruoli_id;
    }

    /**
     * Set the value of operatori_id.
     *
     * @param int $operatori_id
     *
     * @return static
     */
    public function setOperatoriId($operatori_id): static
    {
        $this->operatori_id = $operatori_id;

        return $this;
    }

    /**
     * Get the value of operatori_id.
     *
     * @return int
     */
    public function getOperatoriId()
    {
        return $this->operatori_id;
    }

    /**
     * Set Ruoli entity (many to one).
     *
     * @param \Cdf\BiCoreBundle\Entity\Ruoli $ruoli
     *
     * @return static
     */
    public function setRuoli(Ruoli $ruoli = null): static
    {
        $this->ruoli = $ruoli;

        return $this;
    }

    /**
     * Get Ruoli entity (many to one).
     *
     * @return \Cdf\BiCoreBundle\Entity\Ruoli
     */
    public function getRuoli()
    {
        return $this->ruoli;
    }

    /**
     * Set Operatori entity (many to one).
     *
     * @param \Cdf\BiCoreBundle\Entity\Operatori $operatori
     *
     * @return static
     */
    public function setOperatori(Operatori $operatori = null): static
    {
        $this->operatori = $operatori;

        return $this;
    }

    /**
     * Get Operatori entity (many to one).
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function getOperatori()
    {
        return $this->operatori;
    }

    public function __sleep()
    {
        return array('id', 'modulo', 'crud', 'ruoli_id', 'operatori_id');
    }
}
