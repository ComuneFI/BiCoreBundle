<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
/**
 * @property \Doctrine\ORM\EntityManager $em
 * @property \Cdf\BiCoreBundle\Utils\Permessi\PermessiUtils $permessi
 * @property \Symfony\Component\Security\Core\Security $user
 */
class TabellaBase
{
    protected $parametri;
    protected $colonnedatabase;
    protected $opzionitabellacore;
    protected $configurazionecolonnetabella;
    protected $entityname;
    protected $tablename;
    protected $modellocolonne;
    protected $paginacorrente;
    protected $righeperpagina;
    protected $estraituttirecords;
    protected $prefiltri;
    protected $filtri;
    protected $wheremanuale;
    protected $colonneordinamento;
    protected $permessi;
    protected $records;
    protected $paginetotali;
    protected $righetotali;
    protected $traduzionefiltri;
    protected $em;
    protected $user;

    public function __construct($doctrine, $parametri = '{}')
    {
        $this->parametri = $parametri;
        if (isset($this->parametri['em'])) {
            $this->em = $doctrine->getManager(ParametriTabella::getParameter($this->parametri['em']));
        } else {
            $this->em = $doctrine->getManager();
        }

        $this->tablename = $this->getTabellaParameter("tablename");
        $this->entityname = $this->getTabellaParameter("entityclass");
        $this->entityname = str_replace("FiBiCoreBundle", "BiCoreBundle", $this->entityname);
        $this->permessi = json_decode($this->getTabellaParameter("permessi"));
        $this->modellocolonne = json_decode($this->getTabellaParameter('modellocolonne', array()), true);
        $this->paginacorrente = $this->getTabellaParameter("paginacorrente");
        $this->paginetotali = $this->getTabellaParameter("paginetotali");
        $this->righeperpagina = $this->getTabellaParameter('righeperpagina', 15);

        $this->estraituttirecords = $this->getTabellaParameter('estraituttirecords', 0) === "1" ? true : false;
        $this->colonneordinamento = json_decode($this->getTabellaParameter('colonneordinamento', array()), true);
        $this->prefiltri = json_decode($this->getTabellaParameter('prefiltri', array()), true);
        $this->filtri = json_decode($this->getTabellaParameter('filtri', array()), true);
        $this->wheremanuale = $this->getTabellaParameter("wheremanuale", null);
        $this->user = $this->parametri["user"];
        
        $utils = new EntityUtils($this->em, $this->entityname);
        $this->colonnedatabase = $utils->getEntityColumns($this->entityname);
        $this->opzionitabellacore = $this->getOpzionitabellaFromCore();
        $this->configurazionecolonnetabella = $this->getAllOpzioniTabella();
    }
    private function getTabellaParameter($name, $default = null)
    {
        $risposta = null;
        if (isset($this->parametri[$name])) {
            $risposta = ParametriTabella::getParameter($this->parametri[$name]);
        } else {
            $risposta = $default;
        }
        return $risposta;
    }
    public function getPaginacorrente()
    {
        return $this->paginacorrente;
    }
    public function getPaginetotali()
    {
        return $this->paginetotali;
    }
    public function getRigheperpagina()
    {
        return $this->righeperpagina;
    }
    public function getRighetotali()
    {
        return $this->righetotali;
    }
    public function getTraduzionefiltri()
    {
        return $this->traduzionefiltri;
    }
    public function getConfigurazionecolonnetabella()
    {
        return $this->configurazionecolonnetabella;
    }
}
