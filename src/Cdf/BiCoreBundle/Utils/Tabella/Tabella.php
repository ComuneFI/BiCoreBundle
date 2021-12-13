<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\ModelUtils;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Tabella
{
    use TabellaQueryTrait,
        TabellaOpzioniTrait,
        TabellaDecoderTrait;

    /** @var array<mixed> */
    protected array $parametri;
    /** @var array<mixed> */
    protected array $colonnedatabase ;
    /** @var array<mixed> */
    protected $opzionitabellacore;
    /** @var array<mixed> */
    protected $configurazionecolonnetabella;
    protected string $entityname;
    protected string $tablename;
    
    /** @var array<mixed> */
    protected array $modellocolonne;
    protected int $paginacorrente;
    protected int $righeperpagina;
    protected bool $estraituttirecords;
    
    /** @var array<mixed> */
    protected array $prefiltri;
    /** @var array<mixed> */
    protected array $filtri;
    
    /** @var string|null */
    protected $wheremanuale;
    /** @var array<mixed> */
    protected $colonneordinamento;
    /** @var mixed */
    protected $permessi;
    /** @var array<mixed> */
    protected $records;
    protected int $paginetotali;
    protected int $righetotali;
    
    /** @var string|null */
    protected $traduzionefiltri;
    protected int $maxordine = 0;
    protected EntityManagerInterface $em;
    /** @ phpstan-ignore-next-line */
    protected $user;
    protected string $apiController;
    protected string $apiCollection;
    protected ApiUtils $apiBook;

    /**
     *
     * @param ManagerRegistry $doctrine
     * @param array<mixed> $parametri
     */
    public function __construct(ManagerRegistry $doctrine, array $parametri)
    {
        $this->parametri = $parametri;
        if (isset($this->parametri['em'])) {
            /** @phpstan-ignore-next-line */
            $this->em = $doctrine->getManager(ParametriTabella::getParameter($this->parametri['em']));
        } else {
            /** @phpstan-ignore-next-line */
            $this->em = $doctrine->getManager();
        }

        $this->tablename = $this->getTabellaParameter('tablename');
        $this->entityname = $this->getTabellaParameter('entityclass');
        $this->entityname = str_replace('FiBiCoreBundle', 'BiCoreBundle', $this->entityname);
        $this->permessi = json_decode($this->getTabellaParameter('permessi'));
        $this->modellocolonne = json_decode($this->getTabellaParameter('modellocolonne', '{}'), true);
        $this->paginacorrente = (int) $this->getTabellaParameter('paginacorrente');
        $this->paginetotali = (int) $this->getTabellaParameter('paginetotali');
        $this->righeperpagina = (int) $this->getTabellaParameter('righeperpagina', 15);

        $this->estraituttirecords = '1' === $this->getTabellaParameter('estraituttirecords', 0) ? true : false;
        $this->colonneordinamento = json_decode($this->getTabellaParameter('colonneordinamento', '{}'), true);
        $this->prefiltri = json_decode($this->getTabellaParameter('prefiltri', '{}'), true);
        $this->filtri = json_decode($this->getTabellaParameter('filtri', '{}'), true);
        $this->wheremanuale = $this->getTabellaParameter('wheremanuale', null);
        $this->user = $this->parametri['user'];

        if (!isset($this->parametri['isapi'])) {
            /** @phpstan-ignore-next-line */
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

    /**
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    private function getTabellaParameter(string $name, $default = null)
    {
        $risposta = null;
        if (isset($this->parametri[$name])) {
            $risposta = ParametriTabella::getParameter($this->parametri[$name]);
        } else {
            $risposta = $default;
        }

        return $risposta;
    }

    public function calcolaPagineTotali(int $limit) : float
    {
        if (0 == $this->righetotali) {
            return 1;
        }
        /* calcola in mumero di pagine totali necessarie */
        return ceil($this->righetotali / (0 == $limit ? 1 : $limit));
    }

    public function getPaginacorrente() : int
    {
        return $this->paginacorrente;
    }

    public function getPaginetotali() : int
    {
        return $this->paginetotali;
    }

    public function getRigheperpagina() : int
    {
        return $this->righeperpagina;
    }

    public function getRighetotali() : int
    {
        return $this->righetotali;
    }

    /**
     *
     * @return string|null
     */
    public function getTraduzionefiltri()
    {
        return $this->traduzionefiltri;
    }

    /**
     *
     * @return array<mixed>
     */
    public function getConfigurazionecolonnetabella() : array
    {
        return $this->configurazionecolonnetabella;
    }

    protected function setMaxOrdine(int $ordinecorrente) : void
    {
        if ($ordinecorrente > $this->maxordine) {
            $this->maxordine = $ordinecorrente;
        }
    }

    protected function getMaxOrdine() : int
    {
        return $this->maxordine;
    }
}
