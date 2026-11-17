<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Opzionitabelle.
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['base' => 'BaseOpzionitabelle', 'extended' => 'Opzionitabelle'])]
#[ORM\Table(name: 'Opzionitabelle')]
class BaseOpzionitabelle
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    protected $nometabella;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $descrizione;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $parametro;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $valore;

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
     * Set the value of descrizione.
     *
     * @param string $descrizione
     *
     */
    public function setDescrizione($descrizione): static
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
     * Set the value of parametro.
     *
     * @param string $parametro
     *
     */
    public function setParametro($parametro): static
    {
        $this->parametro = $parametro;

        return $this;
    }

    /**
     * Get the value of parametro.
     *
     * @return string
     */
    public function getParametro()
    {
        return $this->parametro;
    }

    /**
     * Set the value of valore.
     *
     * @param string $valore
     *
     */
    public function setValore($valore): static
    {
        $this->valore = $valore;

        return $this;
    }

    /**
     * Get the value of valore.
     *
     * @return string
     */
    public function getValore()
    {
        return $this->valore;
    }

    public function __sleep()
    {
        return ['id', 'nometabella', 'descrizione', 'parametro', 'valore'];
    }
}
