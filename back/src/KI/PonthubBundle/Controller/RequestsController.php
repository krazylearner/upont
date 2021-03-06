<?php

namespace KI\PonthubBundle\Controller;

use KI\CoreBundle\Controller\ResourceController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestsController extends ResourceController
{
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Request', 'Ponthub');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste les demandes d'ajout de fichier",
     *  output="KI\PonthubBundle\Entity\Request",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests")
     * @Method("GET")
     */
    public function getRequestsAction()
    {
        return $this->getAll();
    }

    /**
     * @ApiDoc(
     *  description="Retourne une demande d'ajout de fichier",
     *  output="KI\PonthubBundle\Entity\Request",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests/{slug}")
     * @Method("GET")
     */
    public function getRequestAction($slug)
    {
        $request = $this->getOne($slug);

        return $this->json($request);
    }

    /**
     * @ApiDoc(
     *  description="Crée une demande d'ajout de fichier",
     *  input="KI\PonthubBundle\Form\RequestType",
     *  output="KI\PonthubBundle\Entity\Request",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests")
     * @Method("POST")
     */
    public function postRequestAction()
    {
        $data = $this->post($this->is('USER'));

        return $this->formJson($data);
    }

    /**
     * @ApiDoc(
     *  description="Supprime une demande d'ajout de fichier",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests/{slug}")
     * @Method("DELETE")
     */
    public function deleteRequestAction($slug)
    {
        $this->delete($slug, $this->is('USER'));

        return $this->json(null, 204);
    }

    /**
     * @ApiDoc(
     *  description="Approuve une demande d'ajout de fichier",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests/{slug}/upvote")
     * @Method("PATCH")
     */
    public function upvoteRequestAction($slug)
    {
        $item = $this->getOne($slug);
        $item->setVotes($item->getVotes() + 1);
        $this->manager->flush();

        return $this->json(null, 204);
    }

    /**
     * @ApiDoc(
     *  description="Désapprouve une demande d'ajout de fichier",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route("/requests/{slug}/downvote")
     * @Method("PATCH")
     */
    public function downvoteRequestAction($slug)
    {
        $item = $this->getOne($slug);
        $item->setVotes($item->getVotes() - 1);
        $this->manager->flush();

        return $this->json(null, 204);
    }
}
