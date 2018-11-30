<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Entity\Finder;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\Asset\Packages;

/**
 * @property \Symfony\Component\Security\Core\Security $user
 */
class FiCrudController extends AbstractController
{

    protected $bundle;
    protected $controller;
    protected $permessi;

    /**
     * Lists all tables entities.
     */
    public function index(Request $request, Packages $assetsmanager)
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');

        if (!$this->getPermessi()->canRead()) {
            throw new AccessDeniedException("Non si hanno i permessi per visualizzare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace("Entity", "Form", $entityclass);

        $modellocolonne = array(
                /*
                  $controller . ".nominativo" => array(
                  "nometabella" => $controller,
                  "nomecampo" => "nominativo",
                  "etichetta" => "Nominativo",
                  "ordine" => 10,
                  "larghezza" => 200,
                  "escluso" => false
                  ),
                  $controller . ".datanascita" => array(
                  "nometabella" => $controller,
                  "nomecampo" => "datanascita",
                  "etichetta" => "Data di nascita",
                  "ordine" => 20,
                  "larghezza" => 100,
                  "escluso" => false
                  ),

                 */
        );

        $colonneordinamento = array($controller . '.id' => "DESC");
        $filtri = array();
        $prefiltri = array();
        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->get("doctrine")->getManager());
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $colonneordinamento = array($tablenamefromentity . '.id' => "DESC");
        $parametritabella = array("em" => ParametriTabella::setParameter("default"),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi())),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter("Elenco " . $controller),
            'multiselezione' => ParametriTabella::setParameter("0"),
            'editinline' => ParametriTabella::setParameter("0"),
            'paginacorrente' => ParametriTabella::setParameter("1"),
            'paginetotali' => ParametriTabella::setParameter(""),
            'righetotali' => ParametriTabella::setParameter("0"),
            'righeperpagina' => ParametriTabella::setParameter("15"),
            'estraituttirecords' => ParametriTabella::setParameter("0"),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(""),
        );

        return $this->render($crudtemplate, array('parametritabella' => $parametritabella,));
    }

    private function getParametroIndexDettaglio($parametripassati, $keyparametro, $defaultvalue)
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
    public function indexDettaglio(Request $request, Packages $assetsmanager)
    {
        if (!$this->getPermessi()->canRead()) {
            throw new AccessDeniedException("Non si hanno i permessi per visualizzare questo contenuto");
        }

        $bundle = $this->getBundle();
        $controller = $this->getController();
        $parametripassati = json_decode($request->get('parametripassati'), true);

        $filtri = $this->getParametroIndexDettaglio($parametripassati, "filtri", array());
        $prefiltri = $this->getParametroIndexDettaglio($parametripassati, "prefiltri", array());
        $titolotabella = $this->getParametroIndexDettaglio($parametripassati, "titolotabella", "Elenco " . $controller);
        $modellocolonne = $this->getParametroIndexDettaglio($parametripassati, "modellocolonne", array());
        $colonneordinamento = $this->getParametroIndexDettaglio($parametripassati, "colonneordinamento", array());
        $multiselezione = $this->getParametroIndexDettaglio($parametripassati, "multiselezione", 0);
        $parametriform = $this->getParametroIndexDettaglio($parametripassati, "parametriform", array());

        $template = $bundle . ':' . $controller . ':' . $this->getThisFunctionName() . '.html.twig';
        if (!$this->get('templating')->exists($template)) {
            $template = $controller . '/Crud/' . $this->getThisFunctionName() . '.html.twig';
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace("Entity", "Form", $entityclass);

        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->get("doctrine")->getManager());

        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $parametritabella = array("em" => ParametriTabella::setParameter("default"),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'parametriform' => ParametriTabella::setParameter(json_encode($parametriform)),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi())),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter(0),
            'titolotabella' => ParametriTabella::setParameter($titolotabella),
            'multiselezione' => ParametriTabella::setParameter($multiselezione),
            'editinline' => ParametriTabella::setParameter("0"),
            'paginacorrente' => ParametriTabella::setParameter("1"),
            'paginetotali' => ParametriTabella::setParameter(""),
            'righeperpagina' => ParametriTabella::setParameter("15"),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(""),
        );

        return $this->render(
            $template,
            array(
                    'parametritabella' => $parametritabella,
                        )
        );
    }

    /**
     * Displays a form to create a new table entity.
     */
    public function new(Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canCreate()) {
            throw new AccessDeniedException("Non si hanno i permessi per creare questo contenuto");
        }

        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $parametriform = $request->get("parametriform") ? json_decode($request->get("parametriform"), true) : array();

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);

        $entity = new $entityclass();
        $formType = $formclass . 'Type';
        $form = $this->createForm($formType, $entity, array('attr' => array(
                'id' => 'formdati' . $controller,
            ),
            'action' => $this->generateUrl($controller . '_new'), "parametriform" => $parametriform
        ));

        $form->handleRequest($request);

        $twigparms = array(
            'form' => $form->createView(),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'tabellatemplate' => $tabellatemplate
        );

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entity = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($entity);
                $entityManager->flush();
                return new Response(
                    $this->renderView($crudtemplate, $twigparms),
                    200
                );
            } else {
                //Quando non passa la validazione
                return new Response(
                    $this->renderView($crudtemplate, $twigparms),
                    400
                );
            }
        } else {
            //Quando viene richiesta una "nuova" new
            return new Response(
                $this->renderView($crudtemplate, $twigparms),
                200
            );
        }
    }

    /**
     * Displays a form to edit an existing table entity.
     */
    public function edit(Request $request, $id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();

        if (!$this->getPermessi()->canUpdate()) {
            throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);

        $formType = $formclass . 'Type';

        $elencomodifiche = $this->elencoModifiche($controller, $id);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' del record con id ' . $id . '.');
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                'id' => 'formdati' . $controller,
                ),
                'action' => $this->generateUrl($controller . '_update', array('id' => $entity->getId())),
                )
        );

        return $this->render(
            $crudtemplate,
            array(
                    'entity' => $entity,
                    'nomecontroller' => ParametriTabella::setParameter($controller),
                    'tabellatemplate' => $tabellatemplate,
                    'edit_form' => $editForm->createView(),
                    'elencomodifiche' => $elencomodifiche,
                        )
        );
    }

    /**
     * Edits an existing table entity.
     */
    public function update(Request $request, $id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canUpdate()) {
            throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, "edit");

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);
        $formType = $formclass . 'Type';

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                'id' => 'formdati' . $controller,
                ),
                'action' => $this->generateUrl($controller . '_update', array('id' => $entity->getId())),
                )
        );

        $editForm->submit($request->request->get($editForm->getName()));

        if ($editForm->isValid()) {
            $originalData = $em->getUnitOfWork()->getOriginalEntityData($entity);

            $em->persist($entity);
            $em->flush();

            $newData = $em->getUnitOfWork()->getOriginalEntityData($entity);
            $repoStorico = $em->getRepository("BiCoreBundle:Storicomodifiche");
            $changes = $repoStorico->isRecordChanged($controller, $originalData, $newData);

            if ($changes) {
                $repoStorico->saveHistory($controller, $changes, $id, $this->getUser());
            }

            $continua = (int) $request->get('continua');
            if ($continua === 0) {
                return new Response('OK');
            } else {
                return $this->redirect($this->generateUrl($controller . '_edit', array('id' => $id)));
            }
        }

        return $this->render(
            $crudtemplate,
            array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'nomecontroller' => ParametriTabella::setParameter($controller),
                        )
        );
    }

    /**
     * Deletes a table entity.
     */
    public function delete(Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        if (!$this->getPermessi()->canDelete()) {
            throw new AccessDeniedException("Non si hanno i permessi per eliminare questo contenuto");
        }
        $entityclass = $this->getEntityClassName();


        try {
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $ids = explode(',', $request->get('id'));
            $qb->delete($entityclass, 'u')
                    ->andWhere('u.id IN (:ids)')
                    ->setParameter('ids', $ids);

            $query = $qb->getQuery();
            $query->execute();
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode('501');
            return $response;
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode('200');
            return $response;
        }

        return new Response('Operazione eseguita con successo');
    }

    private function elencoModifiche($controller, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $risultato = $em->getRepository('BiCoreBundle:Storicomodifiche')->findBy(
            array(
            'nometabella' => $controller,
            'idtabella' => $id,
                ),
            array('giorno' => 'DESC')
        );

        return $risultato;
    }

    protected function getTabellaTemplate($controller)
    {
        $tabellatemplate = $controller . '/Tabella/tabellaform.html.twig';
        if (!$this->get('templating')->exists($tabellatemplate)) {
            $tabellatemplate = 'BiCoreBundle:' . $controller . ':Tabella/tabellaform.html.twig';
            if (!$this->get('templating')->exists($tabellatemplate)) {
                $tabellatemplate = 'BiCoreBundle:Standard:Tabella/tabellaform.html.twig';
            }
        }

        return $tabellatemplate;
    }

    protected function getCrudTemplate($bundle, $controller, $operation)
    {
        $crudtemplate = $bundle . ':' . $controller . ':Crud/' . $operation . '.html.twig';
        if (!$this->get('templating')->exists($crudtemplate)) {
            $crudtemplate = $controller . '/Crud/' . $operation . '.html.twig';
            if (!$this->get('templating')->exists($crudtemplate)) {
                $crudtemplate = 'BiCoreBundle:Standard:Crud/' . $operation . '.html.twig';
            }
        }
        return $crudtemplate;
    }

    protected function getBundle()
    {
        return $this->bundle;
    }

    protected function getController()
    {
        return $this->controller;
    }

    protected function getPermessi()
    {
        return $this->permessi;
    }

    /**
     * Returns the calling function through a backtrace
     */
    protected function getThisFunctionName()
    {
        // a funciton x has called a function y which called this
        // see stackoverflow.com/questions/190421
        $caller = debug_backtrace();
        $caller = $caller[1];
        return $caller['function'];
    }

    protected function getEntityClassNotation()
    {
        $em = $this->get("doctrine")->getManager();
        $entityfinder = new Finder($em, $this->controller);
        $entityutils = new EntityUtils($em);
        return $entityutils->getClassNameToShortcutNotations($entityfinder->getClassNameFromEntityName());
    }

    protected function getEntityClassName()
    {
        $em = $this->get("doctrine")->getManager();
        $entityfinder = new Finder($em, $this->controller);
        return $entityfinder->getClassNameFromEntityName();
    }
}
