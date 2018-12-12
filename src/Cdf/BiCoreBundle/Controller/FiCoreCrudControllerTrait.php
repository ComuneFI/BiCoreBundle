<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;

trait FiCoreCrudControllerTrait
{

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

    private function checkAggiornaRight($id)
    {
        if ($id === 0) {
            if (!$this->getPermessi()->canCreate()) {
                throw new AccessDeniedException("Non si hanno i permessi per creare questo contenuto");
            }
        } else {
            if (!$this->getPermessi()->canUpdate()) {
                throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
            }
        }
    }

    /**
     * Inline existing table entity.
     */
    public function aggiorna(Request $request, $id, $token)
    {
        $this->checkAggiornaRight($id);

        /* @var $em \Doctrine\ORM\EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();
        $insert = ($id === 0);

        if ($insert) {
            //Insert
            $entity = new $entityclass();
            $queryBuilder
                    ->insert($entityclass)
            ;
        } else {
            //Update
            $entity = $em->getRepository($entityclass)->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
            }
            $queryBuilder
                    ->update($entityclass, "u")
                    ->where("u.id = :id")
                    ->setParameter("id", $id);
        }
        $values = $request->get("values");
        $isValidToken = $this->isCsrfTokenValid($id, $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

        $querydaeseguire = false;

        foreach ($values as $value) {
            $fieldpieces = explode(".", $value["fieldname"]);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && count($fieldpieces) == 2) {
                $field = $fieldpieces[1];
                if ($value["fieldtype"] == 'join') {
                    $field = lcfirst($field . "_id");
                }
                if ($insert) {
                    $queryBuilder->setValue($field, ':' . $field);
                    $fieldvalue = $this->getValueAggiorna($value);
                    $queryBuilder->setParameter($field, $fieldvalue);
                    $querydaeseguire = true;
                } else {
                    $entityutils = new EntityUtils($em);
                    $property = $entityutils->getEntityProperties($field, $entity);
                    $nomefunzioneget = $property["get"];
                    if ($nomefunzioneget != $value["fieldvalue"]) {
                        $querydaeseguire = true;
                        $fieldvalue = $this->getValueAggiorna($value);
                        $queryBuilder->set("u." . $field, ':' . $field);
                        $queryBuilder->setParameter($field, $fieldvalue);
                    }
                }
            } else {
                continue;
            }
        }
        if ($querydaeseguire) {
            $queryBuilder->getQuery()->execute();
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse(array("errcode" => 0, "message" => "Registrazione eseguita"));
    }

    private function getValueAggiorna($field)
    {
        $fieldvalue = $field["fieldvalue"];
        $fieldtype = $field["fieldtype"];
        if ($fieldtype == "date") {
            $fieldvalue = \DateTime::createFromFormat("d/m/Y", $field["fieldvalue"]);
            if ($fieldvalue === false) {
                throw new \Exception("Formato data non valido");
            }
        }
        if ($fieldtype == "datetime") {
            $fieldvalue = \DateTime::createFromFormat("d/m/Y H:i", $field["fieldvalue"]);
            if ($fieldvalue === false) {
                throw new \Exception("Formato data ora non valido");
            }
        }
        return $fieldvalue;
    }

    /**
     * Deletes a table entity.
     */
    public function delete(Request $request, $token)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        if (!$this->getPermessi()->canDelete()) {
            throw new AccessDeniedException("Non si hanno i permessi per eliminare questo contenuto");
        }
        $entityclass = $this->getEntityClassName();

        $isValidToken = $this->isCsrfTokenValid($this->getController(), $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

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
}
