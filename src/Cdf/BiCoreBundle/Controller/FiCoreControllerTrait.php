<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Arrays\ArrayUtils;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\Finder;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait FiCoreControllerTrait
{

    /**
     * Lists all tables entities.
     */
    public function index(Request $request, Packages $assetsmanager): Response
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');

        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

        $modellocolonne = [
                /*
                  array(
                  "nometabella" => $controller,
                  "nomecampo" => $controller . ".nominativo",
                  "etichetta" => "Nominativo",
                  "ordine" => 10,
                  "larghezza" => 200,
                  "escluso" => false
                  ),
                  array(
                  "nometabella" => $controller,
                  "nomecampo" => $controller . ".datanascita",
                  "etichetta" => "Data di nascita",
                  "ordine" => 20,
                  "larghezza" => 100,
                  "escluso" => false
                  ),

                 */
        ];

        $filtri = [];
        $prefiltri = [];
        $entityutils = new EntityUtils($this->em);
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $colonneordinamento = [$tablenamefromentity . '.id' => 'DESC'];
        $parametritabella = ['em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('')),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter('Elenco ' . $controller),
            'multiselezione' => ParametriTabella::setParameter('0'),
            'editinline' => ParametriTabella::setParameter('0'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righetotali' => ParametriTabella::setParameter('0'),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            'estraituttirecords' => ParametriTabella::setParameter('0'),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(''),
        ];

        return $this->render($crudtemplate, ['parametritabella' => $parametritabella]);
    }

    /**
     * Lists all tables entities.
     */
    public function indexDettaglio(Request $request, Packages $assetsmanager): Response
    {
        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }

        $bundle = $this->getBundle();
        $controller = $this->getController();
        $parametripassati = json_decode($request->get('parametripassati'), true);

        $filtri = $this->getParametroIndexDettaglio($parametripassati, 'filtri', []);
        $prefiltri = $this->getParametroIndexDettaglio($parametripassati, 'prefiltri', []);
        $titolotabella = $this->getParametroIndexDettaglio($parametripassati, 'titolotabella', 'Elenco ' . $controller);
        $modellocolonne = $this->getParametroIndexDettaglio($parametripassati, 'modellocolonne', []);
        $colonneordinamento = $this->getParametroIndexDettaglio($parametripassati, 'colonneordinamento', []);
        $multiselezione = $this->getParametroIndexDettaglio($parametripassati, 'multiselezione', 0);
        $parametriform = $this->getParametroIndexDettaglio($parametripassati, 'parametriform', []);

        $template = $bundle . ':' . $controller . ':' . $this->getThisFunctionName() . '.html.twig';
        if (!$this->template->getLoader()->exists($template)) {
            $template = $controller . '/Crud/' . $this->getThisFunctionName() . '.html.twig';
        }

        $entityclassnotation = $this->getEntityClassName();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

        $entityutils = new EntityUtils($this->em);

        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $parametritabella = ['em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'parametriform' => ParametriTabella::setParameter(json_encode($parametriform)),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('')),
            'idpassato' => ParametriTabella::setParameter(0),
            'titolotabella' => ParametriTabella::setParameter($titolotabella),
            'multiselezione' => ParametriTabella::setParameter($multiselezione),
            'editinline' => ParametriTabella::setParameter('1'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(''),
        ];

        return $this->render(
            $template,
            [
                            'parametritabella' => $parametritabella,
                        ]
        );
    }

    /**
     *
     * @param mixed $parametripassati
     * @param string $keyparametro
     * @param mixed $defaultvalue
     * @return mixed
     */
    protected function getParametroIndexDettaglio($parametripassati, string $keyparametro, $defaultvalue)
    {
        if (isset($parametripassati[$keyparametro])) {
            $parametro = $parametripassati[$keyparametro];
        } else {
            $parametro = $defaultvalue;
        }

        return $parametro;
    }

    /**
     * Lists all tables entities.
     */
    public function lista(Request $request): JsonResponse
    {
        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }

        $entityclassnotation = $this->getEntityClassName();
        $em = $this->em;
        /** @var class-string $entityclassnotation */
        $righe = $em->getRepository($entityclassnotation)->findAll();

        $lista = [];
        foreach ($righe as $riga) {
            /** @phpstan-ignore-next-line */
            $lista[] = ['id' => $riga->getId(), 'descrizione' => $riga->__toString()];
        }

        return new JsonResponse(ArrayUtils::arrayOrderby($lista, 'descrizione', SORT_ASC));
    }

    protected function getTabellaTemplate(string $controller): string
    {
        $tabellatemplate = $controller . '/Tabella/tabellaform.html.twig';
        if (!$this->template->getLoader()->exists($tabellatemplate)) {
            $tabellatemplate = '@BiCore/' . $controller . '/Tabella/tabellaform.html.twig';
            if (!$this->template->getLoader()->exists($tabellatemplate)) {
                $tabellatemplate = '@BiCore/Standard/Tabella/tabellaform.html.twig';
            }
        }

        return $tabellatemplate;
    }

    protected function getCrudTemplate(string $bundle, string $controller, string $operation): string
    {
        $crudtemplate = $bundle . '/' . $controller . '/Crud/' . $operation . '.html.twig';
        if (!$this->template->getLoader()->exists($crudtemplate)) {
            $crudtemplate = $controller . '/Crud/' . $operation . '.html.twig';
            if (!$this->template->getLoader()->exists($crudtemplate)) {
                $crudtemplate = '@BiCore/Standard/Crud/' . $operation . '.html.twig';
            }
        }

        return $crudtemplate;
    }

    /**
     * Returns the calling function through a backtrace.
     */
    protected function getThisFunctionName(): string
    {
        // a funciton x has called a function y which called this
        // see stackoverflow.com/questions/190421
        $caller = debug_backtrace();
        $caller = $caller[1];

        return $caller['function'];
    }

    protected function getEntityClassNotation(): string
    {
        $entityutils = new EntityUtils($this->em);
        return $entityutils->getClassNameToShortcutNotations($this->getEntityClassName());
    }

    protected function getEntityClassName(): string
    {
        $entityfinder = new Finder($this->em);

        return $entityfinder->getClassNameFromEntityName($this->controller);
    }
}
