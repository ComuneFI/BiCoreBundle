<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\Finder;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function count;

trait FiCoreCrudInlineControllerTrait
{
    private function checkAggiornaRight($id, $token)
    {
        if (0 === $id) {
            if (!$this->getPermessi()->canCreate($this->getController())) {
                throw new AccessDeniedException('Non si hanno i permessi per creare questo contenuto');
            }
        } else {
            if (!$this->getPermessi()->canUpdate($this->getController())) {
                throw new AccessDeniedException('Non si hanno i permessi per modificare questo contenuto');
            }
        }
        $isValidToken = $this->isCsrfTokenValid($id, $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }
    }

    /**
     * Inline existing table entity.
     */
    public function aggiorna(Request $request, $id, $token)
    {
        $this->checkAggiornaRight($id, $token);
        $values = $request->get('values');

        if (0 == $id) {
            $risultato = $this->insertinline($values, $token);
        } else {
            $risultato = $this->updateinline($id, $values, $token);
        }

        return $risultato;
    }

    protected function insertinline($values, $token)
    {
        $this->checkAggiornaRight(0, $token);

        /* @var $em EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

        $em = $this->getDoctrine()->getManager();

        //Insert
        $entity = new $entityclass();

        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($values as $value) {
            $fieldpieces = explode('.', $value['fieldname']);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && 2 == count($fieldpieces)) {
                $field = ucfirst($fieldpieces[1]);
                $fieldvalue = $this->getValueAggiorna($value);
                if ('join' == $value['fieldtype']) {
                    $entityfinder = new Finder($em);
                    $joinclass = $entityfinder->getClassNameFromEntityName($field);
                    $fieldvalue = $em->getRepository($joinclass)->find($fieldvalue);
                }

                if ($accessor->isWritable($entity, $field)) {
                    $accessor->setValue($entity, $field, $fieldvalue);
                } else {
                    throw new Exception($field.' non modificabile');
                }
            } else {
                continue;
            }
        }
        $em->persist($entity);
        $em->flush();
        $em->clear();

        return new JsonResponse(array('errcode' => 0, 'message' => 'Registrazione eseguita'));
    }

    protected function updateinline($id, $values, $token)
    {
        $this->checkAggiornaRight($id, $token);

        /* @var $em EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        //Update
        $entity = $em->getRepository($entityclass)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità '.$controller.' per il record con id '.$id);
        }
        $queryBuilder
                ->update($entityclass, 'u')
                ->where('u.id = :id')
                ->setParameter('id', $id);

        $querydaeseguire = false;

        foreach ($values as $value) {
            $fieldpieces = explode('.', $value['fieldname']);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && 2 == count($fieldpieces)) {
                $field = $fieldpieces[1];
                if ('join' == $value['fieldtype']) {
                    $field = lcfirst($field.'_id');
                }
                $entityutils = new EntityUtils($em);
                $property = $entityutils->getEntityProperties($field, $entity);
                $nomefunzioneget = $property['get'];
                if ($nomefunzioneget != $value['fieldvalue']) {
                    $querydaeseguire = true;
                    $fieldvalue = $this->getValueAggiorna($value);
                    $queryBuilder->set('u.'.$field, ':'.$field);
                    $queryBuilder->setParameter($field, $fieldvalue);
                }
            } else {
                continue;
            }
        }
        if ($querydaeseguire) {
            $queryBuilder->getQuery()->execute();
        }

        return new JsonResponse(array('errcode' => 0, 'message' => 'Registrazione eseguita'));
    }

    private function getValueAggiorna($field)
    {
        $fieldvalue = $field['fieldvalue'];
        if ('' == $fieldvalue) {
            $fieldvalue = null;
        } else {
            $fieldtype = $field['fieldtype'];
            if ('boolean' == $fieldtype) {
                $fieldvalue = !('false' === $field['fieldvalue']);
            }
            if ('date' == $fieldtype) {
                $fieldvalue = DateTime::createFromFormat('d/m/Y', $field['fieldvalue']);
                if (false === $fieldvalue) {
                    throw new Exception('Formato data non valido');
                }
            }
            if ('datetime' == $fieldtype) {
                $fieldvalue = DateTime::createFromFormat('d/m/Y H:i', $field['fieldvalue']);
                if (false === $fieldvalue) {
                    throw new Exception('Formato data ora non valido');
                }
            }
        }

        return $fieldvalue;
    }
}
