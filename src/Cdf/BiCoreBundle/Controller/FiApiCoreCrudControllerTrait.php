<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\BiCoreBundle\Utils\Entity\ModelUtils;

trait FiApiCoreCrudControllerTrait
{
    use FiApiCoreCrudInlineControllerTrait;

    /**
     * Displays a form to create a new table entity.
     */
    public function new(Request $request)
    {
        /* @var $em EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canCreate($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per creare questo contenuto');
        }

        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $parametriform = $request->get('parametriform') ? json_decode($request->get('parametriform'), true) : [];

        //$entityclass = $this->getModelClassName();
        $entityclass = $this->getControllerItemName();
        $entity = new $entityclass();

        //$formclass = str_replace('Entity', 'Form', $entityclass);
        $formclass = $this->getFormName();
        $formType = $formclass.'Type';

        $attrArray = ['attr' => [
            'id' => 'formdati'.$controller,
                    ],
            'action' => $this->generateUrl($controller.'_new'), 'parametriform' => $parametriform,
            'extra-options' => []
                ];

        foreach ($this->options as $key => $option) {
            $attrArray['extra-options'][$key] = $option;
        }

        $form = $this->createForm(
            $formType,
            $entity,
            $attrArray
        );

        $form->handleRequest($request);

        $twigparms = [
            'form' => $form->createView(),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'tabellatemplate' => $tabellatemplate,
        ];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                //TODO: evaluate if this part can be improved
                $parameters = $request->request->get($form->getName());
                $entity = $form->getData();
                $this->setIdObjectfromSelect($entity, $parameters);

                $apiClass = $this->apiController;
                $apiObject = new $apiClass();
                $apiBook = new ApiUtils($this->collection);
                $createMethod = $apiBook->getCreate();

                /*$response = */
                $apiObject->$createMethod($entity);
                
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
        $bundle = $this->getBundle();
        $controller = $this->getController();

        if (!$this->getPermessi()->canUpdate($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per modificare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $formclass = $this->getFormName();
        $formType = $formclass.'Type';

        $apiClass = $this->apiController;
        $apiObject = new $apiClass();
        $apiBook = new ApiUtils($this->collection);
        $getMethod = $apiBook->getItem();

        //TODO: response belongs to last operation
        $entityorig = $apiObject->$getMethod($id);

        $elencomodifiche = $this->elencoModifiche($controller, $id);

        $modelutils = new ModelUtils();
        $entity = $modelutils->setApiValues($entityorig);


        $attrArray = ['attr' => [
                        'id' => 'formdati'.$controller,
                                ],
                    'action' => $this->generateUrl($controller.'_update', ['id' => $entity->getId()]),
                    'extra-options' => []
                            ];
        foreach ($this->options as $key => $option) {
            $attrArray['extra-options'][$key] = $option;
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            $attrArray
        );

        return $this->render(
            $crudtemplate,
            [
                            'entity' => $entity,
                            'nomecontroller' => ParametriTabella::setParameter($controller),
                            'tabellatemplate' => $tabellatemplate,
                            'edit_form' => $editForm->createView(),
                            'elencomodifiche' => $elencomodifiche,
                        ]
        );
    }

    /**
     * Update value of _id field with value selected on select list.
     * //TODO: review duplicated code
     */
    private function setIdfromSelect(&$parameters)
    {
        foreach (array_keys($parameters) as $key) {
            if (\str_contains($key, '_id')) {
                $sourceKey = substr($key, 0, strpos($key, '_id'));
                if (isset($parameters[$sourceKey])) {
                    $parameters[$key] = $parameters[$sourceKey];
                }
            } elseif (\str_contains($key, '_enum')) {
                $sourceKey = substr($key, 0, strpos($key, '_enum'));
                if (isset($parameters[$sourceKey])) {
                    $parameters[$key] = $parameters[$sourceKey];
                }
            }
        }
    }

    /**
     * Update value of _id fields of an object with value selected on select list.
     * It forces the received field to be an INT (It applies a cast)
     */
    private function setIdObjectfromSelect(&$classItem, &$parameters)
    {
        //TODO: (int) cast that is fixed
        $setters = $classItem::setters();
        foreach (array_keys($parameters) as $key) {
            if (\str_contains($key, '_id')) {
                $setMethod = $setters[$key];
                $sourceKey = substr($key, 0, strpos($key, '_id'));
                $classItem->$setMethod((int)$parameters[$sourceKey]);
            } elseif (\str_contains($key, '_enum')) {
                $setMethod = $setters[$key];
                $sourceKey = substr($key, 0, strpos($key, '_enum'));
                $classItem->$setMethod((int)$parameters[$sourceKey]);
            }
        }
    }

    /**
     * Edits an existing table entity.
     */
    public function update(Request $request, $id)
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canUpdate($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per modificare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, 'edit');
        $tabellatemplate = $this->getTabellaTemplate($controller);
        $elencomodifiche = $this->elencoModifiche($controller, $id);

        $formclass = $this->getFormName();
        $formType = $formclass.'Type';

        $apiClass = $this->apiController;
        $apiObject = new $apiClass();
        $apiBook = new ApiUtils($this->collection);
        $getMethod = $apiBook->getItem();

        //TODO: response belongs to last operation
        $entityorig = $apiObject->$getMethod($id);

        $modelutils = new ModelUtils();
        $entity = $modelutils->setApiValues($entityorig);

        $attrArray = ['attr' => [
            'id' => 'formdati'.$controller,
                    ],
        'action' => $this->generateUrl($controller.'_update', ['id' => $entity->getId()]),
        'extra-options' => []
                ];

        foreach ($this->options as $key => $option) {
            $attrArray['extra-options'][$key] = $option;
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            $attrArray
        );

        $parameters = $request->request->get($editForm->getName());

        $this->setIdfromSelect($parameters);
        $editForm->submit($parameters);

        if ($editForm->isValid()) {
            $entityItem = $editForm->getData();

            //$entityItem = $modelutils->getControllerItem($modelEntity , $this->getControllerItemName());
            

            $apiClass = $this->apiController;
            $apiObject = new $apiClass();
            $apiBook = new ApiUtils($this->collection);
            $updateMethod = $apiBook->getUpdateItem();

            /*$responseMessage = */
            $apiObject->$updateMethod($entityItem, $id);

            $continua = (int) $request->get('continua');
            if (0 === $continua) {
                return new Response('OK');
            } else {
                return $this->redirect($this->generateUrl($controller.'_edit', ['id' => $id]));
            }
        }

        return new Response($this->renderView(
            $crudtemplate,
            [
                            'entity' => $entity,
                            'edit_form' => $editForm->createView(),
                            'nomecontroller' => ParametriTabella::setParameter($controller),
                            'tabellatemplate' => $tabellatemplate,
                            'elencomodifiche' => $elencomodifiche,
            ]
        ), 400);
    }

    /**
     * Deletes a table entity.
     */
    public function delete(Request $request, $token)
    {
        /* @var $em EntityManager */
        if (!$this->getPermessi()->canDelete($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per eliminare questo contenuto');
        }
        //$entityclass = $this->getEntityClassName();

        $isValidToken = $this->isCsrfTokenValid($this->getController(), $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

        try {
            $ids = explode(',', $request->get('id'));

            $apiClass = $this->apiController;
            $apiObject = new $apiClass();
            $apiBook = new ApiUtils($this->collection);
            $deleteMethod = $apiBook->getDelete();

            foreach ($ids as $id) {
              //TODO: response belongs to last operation
                $response = $apiObject->$deleteMethod($id);
            }
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode('200');

            return $response;
        }

        return new Response('Operazione eseguita con successo');
    }

    public function elencoModifiche($controller, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $risultato = $em->getRepository('BiCoreBundle:Storicomodifiche')->findBy(
            [
                    'nometabella' => $controller,
                    'idtabella' => $id,
                ],
            ['giorno' => 'DESC']
        );

        return $risultato;
    }
}
