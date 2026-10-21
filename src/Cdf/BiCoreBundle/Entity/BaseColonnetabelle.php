<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Entity\Colonnetabelle.
 *
 * @SuppressWarnings(PHPMD)
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['base' => 'BaseColonnetabelle', 'extended' => 'Colonnetabelle'])]
#[ORM\Table(name: 'Colonnetabelle')]
#[ORM\Index(name: 'fk_Colonnetabelle_Operatori1_idx', columns: ['operatori_id'])]
class BaseColonnetabelle
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    protected $nometabella;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    protected $nomecampo;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    protected $mostraindex;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $ordineindex;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $larghezzaindex;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $etichettaindex;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => false])]
    protected $registrastorico;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    protected $editabile;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $operatori_id;

    #[ORM\ManyToOne(targetEntity: Operatori::class, inversedBy: 'colonnetabelles')]
    #[ORM\JoinColumn(name: 'operatori_id', referencedColumnName: 'id', nullable: false)]
    protected $operatori;

    /**
     * Set the value of id.
     *
     * @param int $id
     *
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
     * Set the value of nometabella.
     *
     * @param string $nometabella
     *
     */
    public function setNometabella($nometabella): static
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
     */
    public function setNomecampo($nomecampo): static
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
     * @param bool $mostraindex
     *
     */
    public function setMostraindex($mostraindex): static
    {
        $this->mostraindex = $mostraindex;

        return $this;
    }

    /**
     * Get the value of mostraindex.
     *
     * @return bool
     */
    public function getMostraindex()
    {
        return $this->mostraindex;
    }

    /**
     * Set the value of ordineindex.
     *
     * @param int $ordineindex
     *
     */
    public function setOrdineindex($ordineindex): static
    {
        $this->ordineindex = $ordineindex;

        return $this;
    }

    /**
     * Get the value of ordineindex.
     *
     * @return int
     */
    public function getOrdineindex()
    {
        return $this->ordineindex;
    }

    /**
     * Set the value of larghezzaindex.
     *
     * @param int $larghezzaindex
     *
     */
    public function setLarghezzaindex($larghezzaindex): static
    {
        $this->larghezzaindex = $larghezzaindex;

        return $this;
    }

    /**
     * Get the value of larghezzaindex.
     *
     * @return int
     */
    public function getLarghezzaindex()
    {
        return $this->larghezzaindex;
    }

    /**
     * Set the value of etichettaindex.
     *
     * @param string $etichettaindex
     *
     */
    public function setEtichettaindex($etichettaindex): static
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
     * @param bool $registrastorico
     *
     */
    public function setRegistrastorico($registrastorico): static
    {
        $this->registrastorico = $registrastorico;

        return $this;
    }

    /**
     * Get the value of registrastorico.
     *
     * @return bool
     */
    public function getRegistrastorico()
    {
        return $this->registrastorico;
    }

    /**
     * Set the value of editabile.
     *
     * @param bool $editabile
     *
     */
    public function setEditabile($editabile): static
    {
        $this->editabile = $editabile;

        return $this;
    }

    /**
     * Get the value of editabile.
     *
     * @return bool
     */
    public function getEditabile()
    {
        return $this->editabile;
    }

    /**
     * Set the value of operatori_id.
     *
     * @param int $operatori_id
     *
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
     * Set Operatori entity (many to one).
     *
     *
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
        return ['id', 'nometabella', 'nomecampo', 'mostraindex', 'ordineindex', 'larghezzaindex', 'etichettaindex', 'editabile', 'registrastorico', 'operatori_id'];
    }
}
