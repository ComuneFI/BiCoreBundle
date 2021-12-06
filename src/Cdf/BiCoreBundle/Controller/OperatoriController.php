<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\UserManipulator;

/**
 * Operatori controller.
 */
class OperatoriController extends FiController
{
    protected LoggerInterface $logger;
    protected UserManipulator $usermanipulator;

    public function __construct(PermessiManager $perm, Environment $template, EntityManagerInterface $em, LoggerInterface $log, UserManipulator $um)
    {
        $this->logger = $log;
        $this->usermanipulator = $um;
        parent::__construct($perm, $template, $em);
    }

    /**
     * Displays a form to create a new table entity.
     */
    public function new(Request $request):Response
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canCreate($controller)) {
            throw new AccessDeniedException('Non si hanno i permessi per creare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace('Entity', 'Form', $entityclass);

        $entity = new $entityclass();
        $formType = $formclass.'Type';
        $form = $this->createForm($formType, $entity, ['attr' => [
                'id' => 'formdati'.$controller,
            ],
            'action' => $this->generateUrl($controller.'_new'),
        ]);

        $form->handleRequest($request);

        $twigparms = [
            'form' => $form->createView(),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'tabellatemplate' => $tabellatemplate,
        ];
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $logger = $this->logger;
                $entity = $form->getData();
                $username = $entity->getUsername();
                $password = $entity->getPassword();
                $entity->setOperatore($username);
                $entity->setEnabled(true);
                $logger->info('Inserimento nuovo utente '.$username);

                $entityManager = $this->em;
                $entityManager->persist($entity);
                $entityManager->flush();

                $content = $this->usermanipulator->changePassword($username, $password);

                $logger->info('Esito inserimento nuovo utente '.$username.' : '.$content);

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
}
