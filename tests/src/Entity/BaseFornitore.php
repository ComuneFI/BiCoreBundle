<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * App\Entity\Fornitore
 *
 * @ORM\Entity()
 * @ORM\Table(name="Fornitore")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base": "BaseFornitore", "extended": "Fornitore"})
 */
class BaseFornitore
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
    protected $ragionesociale;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $partitaiva;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $capitalesociale;

    /**
     * @ORM\OneToMany(targetEntity="Prodottofornitore", mappedBy="fornitore")
     * @ORM\JoinColumn(name="id", referencedColumnName="fornitore_id", nullable=false)
     */
    protected $prodottofornitores;

    public function __construct()
    {
        $this->prodottofornitores = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \App\Entity\Fornitore
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
     * Set the value of ragionesociale.
     *
     * @param string $ragionesociale
     * @return \App\Entity\Fornitore
     */
    public function setRagionesociale($ragionesociale)
    {
        $this->ragionesociale = $ragionesociale;

        return $this;
    }

    /**
     * Get the value of ragionesociale.
     *
     * @return string
     */
    public function getRagionesociale()
    {
        return $this->ragionesociale;
    }

    /**
     * Set the value of partitaiva.
     *
     * @param string $partitaiva
     * @return \App\Entity\Fornitore
     */
    public function setPartitaiva($partitaiva)
    {
        $this->partitaiva = $partitaiva;

        return $this;
    }

    /**
     * Get the value of partitaiva.
     *
     * @return string
     */
    public function getPartitaiva()
    {
        return $this->partitaiva;
    }

    /**
     * Set the value of capitalesociale.
     *
     * @param float $capitalesociale
     * @return \App\Entity\Fornitore
     */
    public function setCapitalesociale($capitalesociale)
    {
        $this->capitalesociale = $capitalesociale;

        return $this;
    }

    /**
     * Get the value of capitalesociale.
     *
     * @return float
     */
    public function getCapitalesociale()
    {
        return $this->capitalesociale;
    }

    /**
     * Add Prodottofornitore entity to collection (one to many).
     *
     * @param \App\Entity\Prodottofornitore $prodottofornitore
     * @return \App\Entity\Fornitore
     */
    public function addProdottofornitore(Prodottofornitore $prodottofornitore)
    {
        $this->prodottofornitores[] = $prodottofornitore;

        return $this;
    }

    /**
     * Remove Prodottofornitore entity from collection (one to many).
     *
     * @param \App\Entity\Prodottofornitore $prodottofornitore
     * @return \App\Entity\Fornitore
     */
    public function removeProdottofornitore(Prodottofornitore $prodottofornitore)
    {
        $this->prodottofornitores->removeElement($prodottofornitore);

        return $this;
    }

    /**
     * Get Prodottofornitore entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProdottofornitores()
    {
        return $this->prodottofornitores;
    }

    public function __sleep()
    {
        return array('id', 'ragionesociale', 'partitaiva', 'capitalesociale');
    }
}