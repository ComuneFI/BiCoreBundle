<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Entity\Colonnetabelle
 *
 * @ORM\Entity()
 * @ORM\Table(name="Colonnetabelle", indexes={@ORM\Index(name="fk_Colonnetabelle_Operatori1_idx", columns={"operatori_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base":"BaseColonnetabelle", "extended":"Colonnetabelle"})
 * @SuppressWarnings(PHPMD)
 */
class BaseColonnetabelle
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $nometabella;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $nomecampo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":true})
     */
    protected $mostraindex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ordineindex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $larghezzaindex;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $etichettaindex;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":false})
     */
    protected $registrastorico;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":true})
     */
    protected $editabile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $operatori_id;

    /**
     * @ORM\ManyToOne(targetEntity="Operatori", inversedBy="colonnetabelles")
     * @ORM\JoinColumn(name="operatori_id", referencedColumnName="id", nullable=false)
     */
    protected $operatori;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Cdf\BiCoreBundle\Colonnetabelle
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
     * Set the value of nometabella.
     *
     * @param string $nometabella
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setNometabella($nometabella)
    {
        $this->nometabella = $nometabella;

        return $this;
    }

    /**
     * Get the value of nometabella.
     *
     * @return string
     */
    public function getNometabella()
    {
        return $this->nometabella;
    }

    /**
     * Set the value of nomecampo.
     *
     * @param string $nomecampo
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setNomecampo($nomecampo)
    {
        $this->nomecampo = $nomecampo;

        return $this;
    }

    /**
     * Get the value of nomecampo.
     *
     * @return string
     */
    public function getNomecampo()
    {
        return $this->nomecampo;
    }

    /**
     * Set the value of mostraindex.
     *
     * @param boolean $mostraindex
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setMostraindex($mostraindex)
    {
        $this->mostraindex = $mostraindex;

        return $this;
    }

    /**
     * Get the value of mostraindex.
     *
     * @return boolean
     */
    public function getMostraindex()
    {
        return $this->mostraindex;
    }

    /**
     * Set the value of ordineindex.
     *
     * @param integer $ordineindex
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setOrdineindex($ordineindex)
    {
        $this->ordineindex = $ordineindex;

        return $this;
    }

    /**
     * Get the value of ordineindex.
     *
     * @return integer
     */
    public function getOrdineindex()
    {
        return $this->ordineindex;
    }

    /**
     * Set the value of larghezzaindex.
     *
     * @param integer $larghezzaindex
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setLarghezzaindex($larghezzaindex)
    {
        $this->larghezzaindex = $larghezzaindex;

        return $this;
    }

    /**
     * Get the value of larghezzaindex.
     *
     * @return integer
     */
    public function getLarghezzaindex()
    {
        return $this->larghezzaindex;
    }

    /**
     * Set the value of etichettaindex.
     *
     * @param string $etichettaindex
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setEtichettaindex($etichettaindex)
    {
        $this->etichettaindex = $etichettaindex;

        return $this;
    }

    /**
     * Get the value of etichettaindex.
     *
     * @return string
     */
    public function getEtichettaindex()
    {
        return $this->etichettaindex;
    }

    /**
     * Set the value of registrastorico.
     *
     * @param boolean $registrastorico
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setRegistrastorico($registrastorico)
    {
        $this->registrastorico = $registrastorico;

        return $this;
    }

    /**
     * Get the value of registrastorico.
     *
     * @return boolean
     */
    public function getRegistrastorico()
    {
        return $this->registrastorico;
    }

    /**
     * Set the value of editabile.
     *
     * @param boolean $editabile
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setEditabile($editabile)
    {
        $this->editabile = $editabile;

        return $this;
    }

    /**
     * Get the value of editabile.
     *
     * @return boolean
     */
    public function getEditabile()
    {
        return $this->editabile;
    }

    /**
     * Set the value of operatori_id.
     *
     * @param integer $operatori_id
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setOperatoriId($operatori_id)
    {
        $this->operatori_id = $operatori_id;

        return $this;
    }

    /**
     * Get the value of operatori_id.
     *
     * @return integer
     */
    public function getOperatoriId()
    {
        return $this->operatori_id;
    }

    /**
     * Set Operatori entity (many to one).
     *
     * @param \Cdf\BiCoreBundle\Operatori $operatori
     * @return \Cdf\BiCoreBundle\Colonnetabelle
     */
    public function setOperatori(Operatori $operatori = null)
    {
        $this->operatori = $operatori;

        return $this;
    }

    /**
     * Get Operatori entity (many to one).
     *
     * @return \Cdf\BiCoreBundle\Operatori
     */
    public function getOperatori()
    {
        return $this->operatori;
    }

    public function __sleep()
    {
        return array('id', 'nometabella', 'nomecampo', 'mostraindex', 'ordineindex', 'larghezzaindex', 'etichettaindex', 'editabile', 'registrastorico', 'operatori_id');
    }
}
