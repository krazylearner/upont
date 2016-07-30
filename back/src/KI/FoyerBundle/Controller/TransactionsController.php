<?php

namespace KI\FoyerBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use KI\CoreBundle\Controller\ResourceController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransactionsController extends ResourceController
{
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Transaction', 'Foyer');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste toutes les transactions",
     *  output="KI\FoyerBundle\Entity\Transaction",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Foyer"
     * )
     * @Route\Get("/transactions")
     */
    public function getTransactionsAction()
    {
        return $this->getAll($this->isClubMember('foyer'));
    }

    /**
     * @ApiDoc(
     *  description="Liste les utilisateurs ayant bu dernièrement",
     *  output="KI\UserBundle\Entity\User",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Foyer"
     * )
     * @Route\Get("/userbeers")
     */
    public function getUserBeersAction()
    {
        $this->trust($this->isClubMember('foyer') || $this->is('ADMIN'));

        $helper = $this->get('ki_foyer.helper.beer');
        $users = $helper->getUserOrderedList();
        return $this->restResponse($users);
    }

    /**
     * @ApiDoc(
     *  description="Liste les transactions d'un utilisateur",
     *  output="KI\FoyerBundle\Entity\Transaction",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Foyer"
     * )
     * @Route\Get("/users/{slug}/transactions")
     */
    public function getUserTransactionsAction($slug)
    {
        $userRepository = $this->getDoctrine()->getManager()->getRepository('KIUserBundle:User');
        $user = $userRepository->findOneByUsername($slug);

        $this->trust($this->isClubMember('foyer') || $this->is('ADMIN') || $this->user == $user);

        $transactions = $this->repository->findBy(['user' => $user]);
        return $this->restResponse($transactions);
    }


    /**
     * @ApiDoc(
     *  description="Crée une transaction - conso ou compte crédité (L'UN OU L'AUTRE, PAS LES DEUX EN MÊME TEMPS)",
     *  requirements={
     *   {
     *    "name"="user",
     *    "dataType"="string",
     *    "description"="Le slug de l'utilisateur"
     *   }
     *  },
     *  parameters={
     *   {
     *    "name"="beer",
     *    "dataType"="string",
     *    "required"=false,
     *    "description"="Le slug de la bière SI C'EST UNE CONSO"
     *   },
     *   {
     *    "name"="credit",
     *    "dataType"="string",
     *    "required"=false,
     *    "description"="Le montant à créditer SI C'EST UNE TRANSACTION DE CRÉDIT"
     *   }
     *  },
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Foyer"
     * )
     * @Route\Post("/transactions")
     */
    public function postTransactionAction(Request $request)
    {
        $this->trust($this->isClubMember('foyer') || $this->is('ADMIN'));

        if (!$request->request->has('user')) {
            throw new BadRequestHttpException('User obligatoire');
        }
        if (!($request->request->has('beer') xor $request->request->has('credit'))) {
            throw new BadRequestHttpException('On rajoute une conso ou du crédit, pas les deux');
        }

        $helper = $this->get('ki_foyer.helper.transaction');
        if ($request->request->has('beer')) {
            $id = $helper->addBeerTransaction($request->request->get('user'), $request->request->get('beer'));
        } else if ($request->request->has('credit')) {
            $id = $helper->addCreditTransaction($request->request->get('user'), $request->request->get('credit'));
        }

        return $this->json($id, 201);
    }

    /**
     * @ApiDoc(
     *  description="Supprime une transaction",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Foyer"
     * )
     */
    public function deleteTransactionAction($id)
    {
        $this->trust($this->isClubMember('foyer') || $this->is('ADMIN'));

        $transaction = $this->findBySlug($id);
        $helper = $this->get('ki_foyer.helper.transaction');
        $helper->updateBalance($transaction->getUser(), -1*$transaction->getAmount());

        return $this->delete($id, $this->isClubMember('foyer'));
    }
}
