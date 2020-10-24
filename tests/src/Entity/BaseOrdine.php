<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * App\Entity\Ordine
 *
 * @ORM\Entity()
 * @ORM\Table(name="Ordine", indexes={@ORM\Index(name="fk_Cliente_has_Fornitore_Cliente_idx", columns={"cliente_id"}), @ORM\Index(name="fk_Ordine_Prodottofornitore1_idx", columns={"prodottofornitore_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base": "BaseOrdine", "extended": "Ordine"})
 */
class BaseOrdine
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
    protected $cliente_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $prodottofornitore_id;

    /**
     * @ORM\Column(name="`data`", type="datetime", nullable=true)
     */
    protected $data;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $quantita;

    /**
     * @ORM\OneToMany(targetEntity="Magazzino", mappedBy="ordine")
     * @ORM\JoinColumn(name="id", referencedColumnName="ordine_id", nullable=false)
     */
    protected $magazzinos;

    /**
     * @ORM\ManyToOne(targetEntity="Cliente", inversedBy="ordines")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id", nullable=false)
     */
    protected $cliente;

    /**
     * @ORM\ManyToOne(targetEntity="Prodottofornitore", inversedBy="ordines")
     * @ORM\JoinColumn(name="prodottofornitore_id", referencedColumnName="id", nullable=false)
     */
    protected $prodottofornitore;

    public function __construct()
    {
        $this->magazzinos = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \App\Entity\Ordine
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
     * Set the value of cliente_id.
     *
     * @param integer $cliente_id
     * @return \App\Entity\Ordine
     */
    public function setClienteId($cliente_id)
    {
        $this->cliente_id = $cliente_id;

        return $this;
    }

    /**
     * Get the value of cliente_id.
     *
     * @return integer
     */
    public function getClienteId()
    {
        return $this->cliente_id;
    }

    /**
     * Set the value of prodottofornitore_id.
     *
     * @param integer $prodottofornitore_id
     * @return \App\Entity\Ordine
     */
    public function setProdottofornitoreId($prodottofornitore_id)
    {
        $this->prodottofornitore_id = $prodottofornitore_id;

        return $this;
    }

    /**
     * Get the value of prodottofornitore_id.
     *
     * @return integer
     */
    public function getProdottofornitoreId()
    {
        return $this->prodottofornitore_id;
    }

    /**
     * Set the value of data.
     *
     * @param \DateTime $data
     * @return \App\Entity\Ordine
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of data.
     *
     * @return \DateTime
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of quantita.
     *
     * @param integer $quantita
     * @return \App\Entity\Ordine
     */
    public function setQuantita($quantita)
    {
        $this->quantita = $quantita;

        return $this;
    }

    /**
     * Get the value of quantita.
     *
     * @return integer
     */
    public function getQuantita()
    {
        return $this->quantita;
    }

    /**
     * Add Magazzino entity to collection (one to many).
     *
     * @param \App\Entity\Magazzino $magazzino
     * @return \App\Entity\Ordine
     */
    public function addMagazzino(Magazzino $magazzino)
    {
        $this->magazzinos[] = $magazzino;

        return $this;
    }

    /**
     * Remove Magazzino entity from collection (one to many).
     *
     * @param \App\Entity\Magazzino $magazzino
     * @return \App\Entity\Ordine
     */
    public function removeMagazzino(Magazzino $magazzino)
    {
        $this->magazzinos->removeElement($magazzino);

        return $this;
    }

    /**
     * Get Magazzino entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMagazzinos()
    {
        return $this->magazzinos;
    }

    /**
     * Set Cliente entity (many to one).
     *
     * @param \App\Entity\Cliente $cliente
     * @return \App\Entity\Ordine
     */
    public function setCliente(Cliente $cliente = null)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get Cliente entity (many to one).
     *
     * @return \App\Entity\Cliente
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set Prodottofornitore entity (many to one).
     *
     * @param \App\Entity\Prodottofornitore $prodottofornitore
     * @return \App\Entity\Ordine
     */
    public function setProdottofornitore(Prodottofornitore $prodottofornitore = null)
    {
        $this->prodottofornitore = $prodottofornitore;

        return $this;
    }

    /**
     * Get Prodottofornitore entity (many to one).
     *
     * @return \App\Entity\Prodottofornitore
     */
    public function getProdottofornitore()
    {
        return $this->prodottofornitore;
    }

    public function __sleep()
    {
        return array('id', 'cliente_id', 'prodottofornitore_id', 'data', 'quantita');
    }
}