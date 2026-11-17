<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Menuapplicazione.
 *
 * @SuppressWarnings(PHPMD)
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['base' => 'BaseMenuapplicazione', 'extended' => 'Menuapplicazione'])]
#[ORM\Table(name: 'Menuapplicazione')]
class BaseMenuapplicazione
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $nome;

    #[ORM\Column(type: 'string', length: 640, nullable: true)]
    protected $percorso;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $padre;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $ordine;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $attivo;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $target;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    protected $tag;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $notifiche;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $autorizzazionerichiesta;

    #[ORM\Column(type: 'string', length: 640, nullable: true)]
    protected $percorsonotifiche;

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
     * Set the value of nome.
     *
     * @param string $nome
     *
     */
    public function setNome($nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get the value of nome.
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of percorso.
     *
     * @param string $percorso
     *
     */
    public function setPercorso($percorso): static
    {
        $this->percorso = $percorso;

        return $this;
    }

    /**
     * Get the value of percorso.
     *
     * @return string
     */
    public function getPercorso()
    {
        return $this->percorso;
    }

    /**
     * Set the value of padre.
     *
     * @param int $padre
     *
     */
    public function setPadre($padre): static
    {
        $this->padre = $padre;

        return $this;
    }

    /**
     * Get the value of padre.
     *
     * @return int
     */
    public function getPadre()
    {
        return $this->padre;
    }

    /**
     * Set the value of ordine.
     *
     * @param int $ordine
     *
     */
    public function setOrdine($ordine): static
    {
        $this->ordine = $ordine;

        return $this;
    }

    /**
     * Get the value of ordine.
     *
     * @return int
     */
    public function getOrdine()
    {
        return $this->ordine;
    }

    /**
     * Set the value of attivo.
     *
     * @param bool $attivo
     *
     */
    public function setAttivo($attivo): static
    {
        $this->attivo = $attivo;

        return $this;
    }

    /**
     * Get the value of attivo.
     *
     * @return bool
     */
    public function getAttivo()
    {
        return $this->attivo;
    }

    /**
     * Set the value of target.
     *
     * @param string $target
     *
     */
    public function setTarget($target): static
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get the value of target.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the value of tag.
     *
     * @param string $tag
     *
     */
    public function setTag($tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get the value of tag.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set the value of notifiche.
     *
     * @param bool $notifiche
     *
     */
    public function setNotifiche($notifiche): static
    {
        $this->notifiche = $notifiche;

        return $this;
    }

    /**
     * Get the value of notifiche.
     *
     * @return bool
     */
    public function getNotifiche()
    {
        return $this->notifiche;
    }

    /**
     * Set the value of autorizzazionerichiesta.
     *
     * @param bool $autorizzazionerichiesta
     *
     */
    public function setAutorizzazionerichiesta($autorizzazionerichiesta): static
    {
        $this->autorizzazionerichiesta = $autorizzazionerichiesta;

        return $this;
    }

    /**
     * Get the value of autorizzazionerichiesta.
     *
     * @return bool
     */
    public function getAutorizzazionerichiesta()
    {
        return $this->autorizzazionerichiesta;
    }

    /**
     * Set the value of percorsonotifiche.
     *
     * @param string $percorsonotifiche
     *
     */
    public function setPercorsonotifiche($percorsonotifiche): static
    {
        $this->percorsonotifiche = $percorsonotifiche;

        return $this;
    }

    /**
     * Get the value of percorsonotifiche.
     *
     * @return string
     */
    public function getPercorsonotifiche()
    {
        return $this->percorsonotifiche;
    }

    public function __sleep()
    {
        return ['id', 'nome', 'percorso', 'padre', 'ordine', 'attivo', 'target', 'tag', 'notifiche', 'autorizzazionerichiesta', 'percorsonotifiche'];
    }
}
