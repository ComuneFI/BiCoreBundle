<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cdf\BiCoreBundle\Menuapplicazione.
 *
 * @ORM\Entity()
 * @ORM\Table(name="Menuapplicazione")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base":"BaseMenuapplicazione", "extended":"Menuapplicazione"})
 * @SuppressWarnings(PHPMD)
 */
class BaseMenuapplicazione
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $nome;

    /**
     * @ORM\Column(type="string", length=640, nullable=true)
     */
    protected $percorso;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $padre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ordine;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $attivo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $target;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $tag;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $notifiche;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $autorizzazionerichiesta;

    /**
     * @ORM\Column(type="string", length=640, nullable=true)
     */
    protected $percorsonotifiche;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param int $id
     *
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
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
     * Set the value of nome.
     *
     * @param string $nome
     *
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setNome($nome)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setPercorso($percorso)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setPadre($padre)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setOrdine($ordine)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setAttivo($attivo)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setTarget($target)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setTag($tag)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setNotifiche($notifiche)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setAutorizzazionerichiesta($autorizzazionerichiesta)
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
     * @return \Cdf\BiCoreBundle\Entity\Menuapplicazione
     */
    public function setPercorsonotifiche($percorsonotifiche)
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
        return array('id', 'nome', 'percorso', 'padre', 'ordine', 'attivo', 'target', 'tag', 'notifiche', 'autorizzazionerichiesta', 'percorsonotifiche');
    }
}
