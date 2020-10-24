<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Magazzino
 *
 * @ORM\Entity()
 * @ORM\Table(name="Magazzino", indexes={@ORM\Index(name="fk_Magazzino_Ordine1_idx", columns={"ordine_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base": "BaseMagazzino", "extended": "Magazzino"})
 */
class BaseMagazzino
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ordine_id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $evaso;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dataspedizione;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $giornodellasettimana;

    /**
     * @ORM\ManyToOne(targetEntity="Ordine", inversedBy="magazzinos")
     * @ORM\JoinColumn(name="ordine_id", referencedColumnName="id", nullable=false)
     */
    protected $ordine;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \App\Entity\Magazzino
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
     * Set the value of ordine_id.
     *
     * @param integer $ordine_id
     * @return \App\Entity\Magazzino
     */
    public function setOrdineId($ordine_id)
    {
        $this->ordine_id = $ordine_id;

        return $this;
    }

    /**
     * Get the value of ordine_id.
     *
     * @return integer
     */
    public function getOrdineId()
    {
        return $this->ordine_id;
    }

    /**
     * Set the value of evaso.
     *
     * @param boolean $evaso
     * @return \App\Entity\Magazzino
     */
    public function setEvaso($evaso)
    {
        $this->evaso = $evaso;

        return $this;
    }

    /**
     * Get the value of evaso.
     *
     * @return boolean
     */
    public function getEvaso()
    {
        return $this->evaso;
    }

    /**
     * Set the value of dataspedizione.
     *
     * @param \DateTime $dataspedizione
     * @return \App\Entity\Magazzino
     */
    public function setDataspedizione($dataspedizione)
    {
        $this->dataspedizione = $dataspedizione;

        return $this;
    }

    /**
     * Get the value of dataspedizione.
     *
     * @return \DateTime
     */
    public function getDataspedizione()
    {
        return $this->dataspedizione;
    }

    /**
     * Set the value of giornodellasettimana.
     *
     * @param integer $giornodellasettimana
     * @return \App\Entity\Magazzino
     */
    public function setGiornodellasettimana($giornodellasettimana)
    {
        $this->giornodellasettimana = $giornodellasettimana;

        return $this;
    }

    /**
     * Get the value of giornodellasettimana.
     *
     * @return integer
     */
    public function getGiornodellasettimana()
    {
        return $this->giornodellasettimana;
    }

    /**
     * Set Ordine entity (many to one).
     *
     * @param \App\Entity\Ordine $ordine
     * @return \App\Entity\Magazzino
     */
    public function setOrdine(Ordine $ordine = null)
    {
        $this->ordine = $ordine;

        return $this;
    }

    /**
     * Get Ordine entity (many to one).
     *
     * @return \App\Entity\Ordine
     */
    public function getOrdine()
    {
        return $this->ordine;
    }

    public function __sleep()
    {
        return array('id', 'ordine_id', 'evaso', 'dataspedizione', 'giornodellasettimana');
    }
}