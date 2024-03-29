<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Cdf\BiCoreBundle\Entity\Operatori.
 *
 * @ORM\Entity()
 * @ORM\Table(name="Operatori", indexes={@ORM\Index(name="fk_operatori_ruoli2_idx", columns={"ruoli_id"})})
 */
class Operatori extends BaseUser implements EquatableInterface, UserInterface, PasswordAuthenticatedUserInterface
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
    protected $operatore;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ruoli_id;

    /**
     * @ORM\OneToMany(targetEntity="Colonnetabelle", mappedBy="operatori")
     * @ORM\JoinColumn(name="id", referencedColumnName="operatori_id", nullable=false)
     */
    protected $colonnetabelles;

    /**
     * @ORM\OneToMany(targetEntity="Permessi", mappedBy="operatori")
     * @ORM\JoinColumn(name="id", referencedColumnName="operatori_id", nullable=false)
     */
    protected $permessis;

    /**
     * @ORM\OneToMany(targetEntity="Storicomodifiche", mappedBy="operatori")
     * @ORM\JoinColumn(name="id", referencedColumnName="operatori_id", nullable=false)
     */
    protected $storicomodifiches;

    /**
     * @ORM\ManyToOne(targetEntity="Ruoli", inversedBy="operatoris")
     * @ORM\JoinColumn(name="ruoli_id", referencedColumnName="id")
     */
    protected $ruoli;

    public function __construct()
    {
        $this->colonnetabelles = new ArrayCollection();
        $this->permessis = new ArrayCollection();
        $this->storicomodifiches = new ArrayCollection();
        parent::__construct();
    }

    /**
     * Set the value of id.
     *
     * @param int $id
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function setId($id) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set the value of operatore.
     *
     * @param string $operatore
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function setOperatore($operatore) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->operatore = $operatore;

        return $this;
    }

    /**
     * Get the value of operatore.
     *
     * @return string
     */
    public function getOperatore() : ?string
    {
        return $this->operatore;
    }

    /**
     * Set the value of ruoli_id.
     *
     * @param int $ruoli_id
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function setRuoliId($ruoli_id) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->ruoli_id = $ruoli_id;

        return $this;
    }

    /**
     * Get the value of ruoli_id.
     *
     * @return ?int
     */
    public function getRuoliId() : ?int
    {
        return $this->ruoli_id;
    }

    /**
     * Add Colonnetabelle entity to collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Colonnetabelle $colonnetabelle
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function addColonnetabelle(Colonnetabelle $colonnetabelle) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->colonnetabelles[] = $colonnetabelle;

        return $this;
    }

    /**
     * Remove Colonnetabelle entity from collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Colonnetabelle $colonnetabelle
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function removeColonnetabelle(Colonnetabelle $colonnetabelle) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->colonnetabelles->removeElement($colonnetabelle);

        return $this;
    }

    /**
     * Get Colonnetabelle entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColonnetabelles() : \Doctrine\Common\Collections\Collection
    {
        return $this->colonnetabelles;
    }

    /**
     * Add Permessi entity to collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Permessi $permessi
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function addPermessi(Permessi $permessi) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->permessis[] = $permessi;

        return $this;
    }

    /**
     * Remove Permessi entity from collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Permessi $permessi
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function removePermessi(Permessi $permessi) :\Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->permessis->removeElement($permessi);

        return $this;
    }

    /**
     * Get Permessi entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPermessis(): \Doctrine\Common\Collections\Collection
    {
        return $this->permessis;
    }

    /**
     * Add Storicomodifiche entity to collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Storicomodifiche $storicomodifiche
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function addStoricomodifiche(Storicomodifiche $storicomodifiche) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->storicomodifiches[] = $storicomodifiche;

        return $this;
    }

    /**
     * Remove Storicomodifiche entity from collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Entity\Storicomodifiche $storicomodifiche
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function removeStoricomodifiche(Storicomodifiche $storicomodifiche) : \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->storicomodifiches->removeElement($storicomodifiche);

        return $this;
    }

    /**
     * Get Storicomodifiche entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStoricomodifiches() : \Doctrine\Common\Collections\Collection
    {
        return $this->storicomodifiches;
    }

    /**
     * Set Ruoli entity (many to one).
     *
     * @param \Cdf\BiCoreBundle\Entity\Ruoli $ruoli
     *
     * @return \Cdf\BiCoreBundle\Entity\Operatori
     */
    public function setRuoli(Ruoli $ruoli = null): \Cdf\BiCoreBundle\Entity\Operatori
    {
        $this->ruoli = $ruoli;

        return $this;
    }

    /**
     * Get Ruoli entity (many to one).
     *
     * @return \Cdf\BiCoreBundle\Entity\Ruoli
     */
    public function getRuoli() : ?\Cdf\BiCoreBundle\Entity\Ruoli
    {
        return $this->ruoli;
    }

    public function __sleep() : array
    {
        return array('id', 'operatore', 'ruoli_id');
    }

    public function isEqualTo(UserInterface $user) : bool
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @return string the identifier for this user
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
    
    /**
     * @return string the hashed password for this user
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
