<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * App\Entity\Cliente
 *
 * @ORM\Entity()
 * @ORM\Table(name="Cliente")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base": "BaseCliente", "extended": "Cliente"})
 */
class BaseCliente
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $nominativo;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $attivo;

    /**
     * @ORM\Column(type="date")
     */
    protected $datanascita;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $punti;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $iscrittoil;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $creditoresiduo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @ORM\OneToMany(targetEntity="Ordine", mappedBy="cliente")
     * @ORM\JoinColumn(name="id", referencedColumnName="cliente_id", nullable=false)
     */
    protected $ordines;

    public function __construct()
    {
        $this->ordines = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \App\Entity\Cliente
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of nominativo.
     *
     * @param string $nominativo
     * @return \App\Entity\Cliente
     */
    public function setNominativo($nominativo)
    {
        $this->nominativo = $nominativo;

        return $this;
    }

    /**
     * Get the value of nominativo.
     *
     * @return string
     */
    public function getNominativo()
    {
        return $this->nominativo;
    }

    /**
     * Set the value of attivo.
     *
     * @param boolean $attivo
     * @return \App\Entity\Cliente
     */
    public function setAttivo($attivo)
    {
        $this->attivo = $attivo;

        return $this;
    }

    /**
     * Get the value of attivo.
     *
     * @return boolean
     */
    public function getAttivo()
    {
        return $this->attivo;
    }

    /**
     * Set the value of datanascita.
     *
     * @param \DateTime $datanascita
     * @return \App\Entity\Cliente
     */
    public function setDatanascita($datanascita)
    {
        $this->datanascita = $datanascita;

        return $this;
    }

    /**
     * Get the value of datanascita.
     *
     * @return \DateTime
     */
    public function getDatanascita()
    {
        return $this->datanascita;
    }

    /**
     * Set the value of punti.
     *
     * @param integer $punti
     * @return \App\Entity\Cliente
     */
    public function setPunti($punti)
    {
        $this->punti = $punti;

        return $this;
    }

    /**
     * Get the value of punti.
     *
     * @return integer
     */
    public function getPunti()
    {
        return $this->punti;
    }

    /**
     * Set the value of iscrittoil.
     *
     * @param \DateTime $iscrittoil
     * @return \App\Entity\Cliente
     */
    public function setIscrittoil($iscrittoil)
    {
        $this->iscrittoil = $iscrittoil;

        return $this;
    }

    /**
     * Get the value of iscrittoil.
     *
     * @return \DateTime
     */
    public function getIscrittoil()
    {
        return $this->iscrittoil;
    }

    /**
     * Set the value of creditoresiduo.
     *
     * @param float $creditoresiduo
     * @return \App\Entity\Cliente
     */
    public function setCreditoresiduo($creditoresiduo)
    {
        $this->creditoresiduo = $creditoresiduo;

        return $this;
    }

    /**
     * Get the value of creditoresiduo.
     *
     * @return float
     */
    public function getCreditoresiduo()
    {
        return $this->creditoresiduo;
    }

    /**
     * Set the value of note.
     *
     * @param string $note
     * @return \App\Entity\Cliente
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get the value of note.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Add Ordine entity to collection (one to many).
     *
     * @param \App\Entity\Ordine $ordine
     * @return \App\Entity\Cliente
     */
    public function addOrdine(Ordine $ordine)
    {
        $this->ordines[] = $ordine;

        return $this;
    }

    /**
     * Remove Ordine entity from collection (one to many).
     *
     * @param \App\Entity\Ordine $ordine
     * @return \App\Entity\Cliente
     */
    public function removeOrdine(Ordine $ordine)
    {
        $this->ordines->removeElement($ordine);

        return $this;
    }

    /**
     * Get Ordine entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdines()
    {
        return $this->ordines;
    }

    public function __sleep()
    {
        return array('id', 'nominativo', 'attivo', 'datanascita', 'punti', 'iscrittoil', 'creditoresiduo', 'note');
    }
}