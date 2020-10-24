<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\ModelUtils;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Security;
use \Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @property EntityManager                        $em
 * @property PermessiManager $permessi
 * @property Security          $user
 */

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Tabella
{
    use TabellaQueryTrait,
        TabellaOpzioniTrait,
        TabellaDecoderTrait;

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
    protected $maxordine = 0;
    protected $em;
    protected $user;
    protected $apiController;
    protected $apiCollection;
    protected $apiBook;

    public function __construct(Registry $doctrine, array $parametri)
    {
        $this->parametri = $parametri;
        if (isset($this->parametri['em'])) {
            $this->em = $doctrine->getManager(ParametriTabella::getParameter($this->parametri['em']));
        } else {
            $this->em = $doctrine->getManager();
        }

        $this->tablename = $this->getTabellaParameter('tablename');
        $this->entityname = $this->getTabellaParameter('entityclass');
        $this->entityname = str_replace('FiBiCoreBundle', 'BiCoreBundle', $this->entityname);
        $this->permessi = json_decode($this->getTabellaParameter('permessi'));
        $this->modellocolonne = json_decode($this->getTabellaParameter('modellocolonne', '{}'), true);
        $this->paginacorrente = $this->getTabellaParameter('paginacorrente');
        $this->paginetotali = $this->getTabellaParameter('paginetotali');
        $this->righeperpagina = $this->getTabellaParameter('righeperpagina', 15);

        $this->estraituttirecords = '1' === $this->getTabellaParameter('estraituttirecords', 0) ? true : false;
        $this->colonneordinamento = json_decode($this->getTabellaParameter('colonneordinamento', '{}'), true);
        $this->prefiltri = json_decode($this->getTabellaParameter('prefiltri', '{}'), true);
        $this->filtri = json_decode($this->getTabellaParameter('filtri', '{}'), true);
        $this->wheremanuale = $this->getTabellaParameter('wheremanuale', null);
        $this->user = $this->parametri['user'];

        if (!isset($this->parametri['isapi'])) {
            $utils = new EntityUtils($this->em);
            $this->colonnedatabase = $utils->getEntityColumns($this->entityname);
        } else {
            $this->apiController = $this->getTabellaParameter('apicontroller');
            $this->apiCollection = $this->getTabellaParameter('apicollection');
            $this->apiBook = new ApiUtils($this->apiCollection);
            //in this moment is not set for API
            $modelUtils = new ModelUtils();
            $this->colonnedatabase = $modelUtils->getEntityColumns($this->entityname);
        }
      
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

    public function calcolaPagineTotali($limit)
    {
        if (0 == $this->righetotali) {
            return 1;
        }
        /* calcola in mumero di pagine totali necessarie */
        return ceil($this->righetotali / (0 == $limit ? 1 : $limit));
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

    protected function setMaxOrdine($ordinecorrente)
    {
        if ($ordinecorrente > $this->maxordine) {
            $this->maxordine = $ordinecorrente;
        }
    }

    protected function getMaxOrdine()
    {
        return $this->maxordine;
    }
}
