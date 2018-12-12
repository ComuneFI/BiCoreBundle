<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Cdf\BiCoreBundle\Utils\Entity\Finder;

trait FiCoreCrudInlineControllerTrait
{

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
        $values = $request->get("values");

        if ($id == 0) {
            $risultato = $this->insertinline($values, $token);
        } else {
            $risultato = $this->updateinline($id, $values, $token);
        }

        return $risultato;
    }

    protected function insertinline($values, $token)
    {

        $this->checkAggiornaRight(0);

        /* @var $em \Doctrine\ORM\EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

        $em = $this->getDoctrine()->getManager();

        //Insert
        $entity = new $entityclass();


        $isValidToken = $this->isCsrfTokenValid(0, $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

        $querydaeseguire = false;
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($values as $value) {
            $fieldpieces = explode(".", $value["fieldname"]);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && count($fieldpieces) == 2) {
                $field = ucfirst($fieldpieces[1]);
                $fieldvalue = $this->getValueAggiorna($value);
                if ($value["fieldtype"] == 'join') {
                    $entityfinder = new Finder($em, $field);
                    $joinclass = $entityfinder->getClassNameFromEntityName();
                    $fieldvalue = $em->getRepository($joinclass)->find($fieldvalue);
                }


                if ($accessor->isWritable($entity, $field)) {
                    $accessor->setValue($entity, $field, $fieldvalue);
                } else {
                    throw new \Exception($field . ' non modificabile');
                }

                $querydaeseguire = true;
            } else {
                continue;
            }
        }
        if ($querydaeseguire) {
            $em->persist($entity);
            $em->flush();
            $em->clear();
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse(array("errcode" => 0, "message" => "Registrazione eseguita"));
    }

    protected function updateinline($id, $values, $token)
    {

        $this->checkAggiornaRight($id);

        /* @var $em \Doctrine\ORM\EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        //Update
        $entity = $em->getRepository($entityclass)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
        }
        $queryBuilder
                ->update($entityclass, "u")
                ->where("u.id = :id")
                ->setParameter("id", $id);

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
                $entityutils = new EntityUtils($em);
                $property = $entityutils->getEntityProperties($field, $entity);
                $nomefunzioneget = $property["get"];
                if ($nomefunzioneget != $value["fieldvalue"]) {
                    $querydaeseguire = true;
                    $fieldvalue = $this->getValueAggiorna($value);
                    $queryBuilder->set("u." . $field, ':' . $field);
                    $queryBuilder->setParameter($field, $fieldvalue);
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
        if ($fieldvalue == '') {
            $fieldvalue = null;
        } else {
            $fieldtype = $field["fieldtype"];
            if ($fieldtype == "date") {
            }
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
        }
        return $fieldvalue;
    }
}
