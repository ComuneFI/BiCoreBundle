<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Storicomodifiche.
 *
 * @ORM\Entity()
 * @ORM\Table(name="Storicomodifiche", indexes={@ORM\Index(name="fk_Storicomodifiche_Operatori1_idx", columns={"operatori_id"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base":"BaseStoricomodifiche", "extended":"Storicomodifiche"})
 */
class BaseStoricomodifiche
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $nometabella;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $nomecampo;

    /**
     * @ORM\Column(type="integer")
     */
    protected $idtabella;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giorno;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $valoreprecedente;

    /**
     * @ORM\Column(type="integer")
     */
    protected $operatori_id;

    /**
     * @ORM\ManyToOne(targetEntity="Operatori", inversedBy="storicomodifiches")
     * @ORM\JoinColumn(name="operatori_id", referencedColumnName="id", nullable=false)
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
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setId($id)
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
     * Set the value of nometabella.
     *
     * @param string $nometabella
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
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
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
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
     * Set the value of idtabella.
     *
     * @param int $idtabella
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setIdtabella($idtabella)
    {
        $this->idtabella = $idtabella;

        return $this;
    }

    /**
     * Get the value of idtabella.
     *
     * @return int
     */
    public function getIdtabella()
    {
        return $this->idtabella;
    }

    /**
     * Set the value of giorno.
     *
     * @param \DateTime $giorno
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setGiorno($giorno)
    {
        $this->giorno = $giorno;

        return $this;
    }

    /**
     * Get the value of giorno.
     *
     * @return \DateTime
     */
    public function getGiorno()
    {
        return $this->giorno;
    }

    /**
     * Set the value of valoreprecedente.
     *
     * @param string $valoreprecedente
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setValoreprecedente($valoreprecedente)
    {
        $this->valoreprecedente = $valoreprecedente;

        return $this;
    }

    /**
     * Get the value of valoreprecedente.
     *
     * @return string
     */
    public function getValoreprecedente()
    {
        return $this->valoreprecedente;
    }

    /**
     * Set the value of operatori_id.
     *
     * @param int $operatori_id
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setOperatoriId($operatori_id)
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
     * Set Operatori entity (many to one).
     *
     * @param \Cdf\BiCoreBundle\Entity\Operatori $operatori
     *
     * @return \Cdf\BiCoreBundle\Entity\Storicomodifiche
     */
    public function setOperatori(Operatori $operatori = null)
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
        return array('id', 'nometabella', 'nomecampo', 'idtabella', 'giorno', 'valoreprecedente', 'operatori_id');
    }
}
