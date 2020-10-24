<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * App\Entity\Prodottofornitore
 *
 * @ORM\Entity()
 * @ORM\Table(name="Prodottofornitore", indexes={@ORM\Index(name="fk_Prodottofornitore_Fornitore1_idx", columns={"fornitore_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base": "BaseProdottofornitore", "extended": "Prodottofornitore"})
 */
class BaseProdottofornitore
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
    protected $fornitore_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $descrizione;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $quantitadisponibile;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $prezzo;

    /**
     * @ORM\OneToMany(targetEntity="Ordine", mappedBy="prodottofornitore")
     * @ORM\JoinColumn(name="id", referencedColumnName="prodottofornitore_id", nullable=false)
     */
    protected $ordines;

    /**
     * @ORM\ManyToOne(targetEntity="Fornitore", inversedBy="prodottofornitores")
     * @ORM\JoinColumn(name="fornitore_id", referencedColumnName="id", nullable=false)
     */
    protected $fornitore;

    public function __construct()
    {
        $this->ordines = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \App\Entity\Prodottofornitore
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
     * Set the value of fornitore_id.
     *
     * @param integer $fornitore_id
     * @return \App\Entity\Prodottofornitore
     */
    public function setFornitoreId($fornitore_id)
    {
        $this->fornitore_id = $fornitore_id;

        return $this;
    }

    /**
     * Get the value of fornitore_id.
     *
     * @return integer
     */
    public function getFornitoreId()
    {
        return $this->fornitore_id;
    }

    /**
     * Set the value of descrizione.
     *
     * @param string $descrizione
     * @return \App\Entity\Prodottofornitore
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * Get the value of descrizione.
     *
     * @return string
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * Set the value of quantitadisponibile.
     *
     * @param integer $quantitadisponibile
     * @return \App\Entity\Prodottofornitore
     */
    public function setQuantitadisponibile($quantitadisponibile)
    {
        $this->quantitadisponibile = $quantitadisponibile;

        return $this;
    }

    /**
     * Get the value of quantitadisponibile.
     *
     * @return integer
     */
    public function getQuantitadisponibile()
    {
        return $this->quantitadisponibile;
    }

    /**
     * Set the value of prezzo.
     *
     * @param float $prezzo
     * @return \App\Entity\Prodottofornitore
     */
    public function setPrezzo($prezzo)
    {
        $this->prezzo = $prezzo;

        return $this;
    }

    /**
     * Get the value of prezzo.
     *
     * @return float
     */
    public function getPrezzo()
    {
        return $this->prezzo;
    }

    /**
     * Add Ordine entity to collection (one to many).
     *
     * @param \App\Entity\Ordine $ordine
     * @return \App\Entity\Prodottofornitore
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
     * @return \App\Entity\Prodottofornitore
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

    /**
     * Set Fornitore entity (many to one).
     *
     * @param \App\Entity\Fornitore $fornitore
     * @return \App\Entity\Prodottofornitore
     */
    public function setFornitore(Fornitore $fornitore = null)
    {
        $this->fornitore = $fornitore;

        return $this;
    }

    /**
     * Get Fornitore entity (many to one).
     *
     * @return \App\Entity\Fornitore
     */
    public function getFornitore()
    {
        return $this->fornitore;
    }

    public function __sleep()
    {
        return array('id', 'fornitore_id', 'descrizione', 'quantitadisponibile', 'prezzo');
    }
}