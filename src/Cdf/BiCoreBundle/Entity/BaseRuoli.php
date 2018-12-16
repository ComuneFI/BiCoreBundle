<?php

namespace Cdf\BiCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cdf\BiCoreBundle\Ruoli.
 *
 * @ORM\Entity()
 * @ORM\Table(name="Ruoli")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"base":"BaseRuoli", "extended":"Ruoli"})
 * @SuppressWarnings(PHPMD)
 */
class BaseRuoli
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
    protected $ruolo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $paginainiziale;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $superadmin;

    /**
     * @ORM\Column(name="`admin`", type="boolean", nullable=true)
     */
    protected $admin;

    /**
     * @ORM\Column(name="`user`", type="boolean", nullable=true)
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Operatori", mappedBy="ruoli")
     * @ORM\JoinColumn(name="id", referencedColumnName="ruoli_id", nullable=false)
     */
    protected $operatoris;

    /**
     * @ORM\OneToMany(targetEntity="Permessi", mappedBy="ruoli")
     * @ORM\JoinColumn(name="id", referencedColumnName="ruoli_id", nullable=false)
     */
    protected $permessis;

    public function __construct()
    {
        $this->operatoris = new ArrayCollection();
        $this->permessis = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param int $id
     *
     * @return \Cdf\BiCoreBundle\Ruoli
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
     * Set the value of ruolo.
     *
     * @param string $ruolo
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;

        return $this;
    }

    /**
     * Get the value of ruolo.
     *
     * @return string
     */
    public function getRuolo()
    {
        return $this->ruolo;
    }

    /**
     * Set the value of paginainiziale.
     *
     * @param string $paginainiziale
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function setPaginainiziale($paginainiziale)
    {
        $this->paginainiziale = $paginainiziale;

        return $this;
    }

    /**
     * Get the value of paginainiziale.
     *
     * @return string
     */
    public function getPaginainiziale()
    {
        return $this->paginainiziale;
    }

    /**
     * Set the value of superadmin.
     *
     * @param bool $superadmin
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function setSuperadmin($superadmin)
    {
        $this->superadmin = $superadmin;

        return $this;
    }

    /**
     * Get the value of superadmin.
     *
     * @return bool
     */
    public function getSuperadmin()
    {
        return $this->superadmin;
    }

    /**
     * Set the value of admin.
     *
     * @param bool $admin
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get the value of admin.
     *
     * @return bool
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set the value of user.
     *
     * @param bool $user
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of user.
     *
     * @return bool
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add Operatori entity to collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Operatori $operatori
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function addOperatori(Operatori $operatori)
    {
        $this->operatoris[] = $operatori;

        return $this;
    }

    /**
     * Remove Operatori entity from collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Operatori $operatori
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function removeOperatori(Operatori $operatori)
    {
        $this->operatoris->removeElement($operatori);

        return $this;
    }

    /**
     * Get Operatori entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperatoris()
    {
        return $this->operatoris;
    }

    /**
     * Add Permessi entity to collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Permessi $permessi
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function addPermessi(Permessi $permessi)
    {
        $this->permessis[] = $permessi;

        return $this;
    }

    /**
     * Remove Permessi entity from collection (one to many).
     *
     * @param \Cdf\BiCoreBundle\Permessi $permessi
     *
     * @return \Cdf\BiCoreBundle\Ruoli
     */
    public function removePermessi(Permessi $permessi)
    {
        $this->permessis->removeElement($permessi);

        return $this;
    }

    /**
     * Get Permessi entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPermessis()
    {
        return $this->permessis;
    }

    public function __sleep()
    {
        return array('id', 'ruolo', 'paginainiziale', 'superadmin', 'admin', 'user');
    }
}
