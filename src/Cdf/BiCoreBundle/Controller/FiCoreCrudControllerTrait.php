<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Entity\Storicomodifiche;

trait FiCoreCrudControllerTrait
{
    use FiCoreCrudInlineControllerTrait;

    /**
     * Displays a form to create a new table entity.
     *
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function new(Request $request): Response
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

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace('Entity', 'Form', $entityclass);

        $entity = new $entityclass();
        $formType = $formclass . 'Type';
        $form = $this->createForm($formType, $entity, ['attr' => [
                'id' => 'formdati' . $controller,
            ],
            'action' => $this->generateUrl($controller . '_new'), 'parametriform' => $parametriform,
        ]);

        $form->handleRequest($request);

        $twigparms = [
            'form' => $form->createView(),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'tabellatemplate' => $tabellatemplate,
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
        ];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entity = $form->getData();

                $entityManager = $this->em;
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
     *
     * @param Request $request
     * @param string|int $id
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function edit(Request $request, $id): Response
    {
        /* @var $this->em EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();

        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per modificare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace('Entity', 'Form', $entityclass);

        $formType = $formclass . 'Type';

        $elencomodifiche = $this->elencoModifiche($controller, $id);

        /** @var class-string $entityclass */
        $entity = $this->em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' del record con id ' . $id . '.');
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            ['attr' => [
                        'id' => 'formdati' . $controller,
                    ],
                    'action' => $this->generateUrl($controller . '_update', ['id' => $entity->getId()]),
                ]
        );

        return $this->render(
            $crudtemplate,
            [
                            'entity' => $entity,
                            'nomecontroller' => ParametriTabella::setParameter($controller),
                            'tabellatemplate' => $tabellatemplate,
                            'edit_form' => $editForm->createView(),
                            'elencomodifiche' => $elencomodifiche,
                            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
                        ]
        );
    }

    /**
     * Update an existing table entity.
     *
     * @param Request $request
     * @param string|int $id
     * @return Response
     * @throws AccessDeniedException
     */
    public function update(Request $request, $id): Response
    {
        /* @var $ EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canUpdate($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per modificare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, 'edit');
        $tabellatemplate = $this->getTabellaTemplate($controller);
        $elencomodifiche = $this->elencoModifiche($controller, $id);

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace('Entity', 'Form', $entityclass);
        $formType = $formclass . 'Type';

        /** @var class-string $entityclass */
        $entity = $this->em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            ['attr' => [
                        'id' => 'formdati' . $controller,
                    ],
                    'action' => $this->generateUrl($controller . '_update', ['id' => $entity->getId()]),
                ]
        );

        $editForm->submit($request->request->get($editForm->getName()));

        if ($editForm->isValid()) {
            $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($entity);

            $this->em->persist($entity);
            $this->em->flush();

            $newData = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            /** @var \Cdf\BiCoreBundle\Repository\StoricomodificheRepository $repoStorico */
            $repoStorico = $this->em->getRepository(Storicomodifiche::class);
            $changes = $repoStorico->isRecordChanged($controller, $originalData, $newData);

            if ($changes) {
                /** @var \Cdf\BiCoreBundle\Entity\Operatori $currentUser */
                $currentUser = $this->getUser();
                $repoStorico->saveHistory($controller, $changes, $id, $currentUser);
            }

            $continua = (int) $request->get('continua');
            if (0 === $continua) {
                return new Response('OK');
            } else {
                return $this->redirect($this->generateUrl($controller . '_edit', ['id' => $id]));
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
    public function delete(Request $request, string $token): Response
    {
        /* @var $this->em EntityManager */
        if (!$this->getPermessi()->canDelete($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per eliminare questo contenuto');
        }
        $entityclass = $this->getEntityClassName();

        $isValidToken = $this->isCsrfTokenValid($this->getController(), $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido:' . $this->getController() . " " . $token);
        }

        try {
            $qb = $this->em->createQueryBuilder();
            $ids = explode(',', $request->get('id'));
            $qb->delete($entityclass, 'u')
                    ->andWhere('u.id IN (:ids)')
                    ->setParameter('ids', $ids);

            $query = $qb->getQuery();
            $query->execute();
        } catch (ForeignKeyConstraintViolationException $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode(501);

            return $response;
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode(200);

            return $response;
        }

        return new Response('Operazione eseguita con successo');
    }

    /**
     *
     * @param string $controller
     * @param string|int $id
     * @return array<Storicomodifiche>
     */
    public function elencoModifiche(string $controller, $id): array
    {
        $risultato = $this->em->getRepository(Storicomodifiche::class)->findBy(
            [
                    'nometabella' => $controller,
                    'idtabella' => $id,
                ],
            ['giorno' => 'DESC']
        );

        return $risultato;
    }
}
