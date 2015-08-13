<?php

namespace KI\DvpBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use KI\CoreBundle\Controller\ResourceController;
use KI\DvpBundle\Entity\BasketOrder;

class BasketsController extends ResourceController
{
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Basket', 'Dvp');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste les paniers",
     *  output="KI\DvpBundle\Entity\Basket",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     */
    public function getBasketsAction()
    {
        return $this->getAll($this->is('EXTERIEUR'));
    }

    /**
     * @ApiDoc(
     *  description="Retourne un panier",
     *  output="KI\DvpBundle\Entity\Basket",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     */
    public function getBasketAction($slug)
    {
        return $this->getOne($slug, $this->is('EXTERIEUR'));
    }

    /**
     * @ApiDoc(
     *  description="Crée un panier",
     *  input="KI\DvpBundle\Form\BasketType",
     *  output="KI\DvpBundle\Entity\Basket",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     */
    public function postBasketAction()
    {
        return $this->post($this->isClubMember('dvp'));
    }

    /**
     * @ApiDoc(
     *  description="Modifie un panier",
     *  input="KI\DvpBundle\Form\BasketType",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     */
    public function patchBasketAction($slug)
    {
        return $this->patch($slug, $this->isClubMember('dvp'));
    }

    /**
     * @ApiDoc(
     *  description="Supprime un panier",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     */
    public function deleteBasketAction($slug)
    {
        $basket = $this->findBySlug($slug);

        // On n'oublie pas de supprimer toutes les commandes associées
        $repository = $this->manager->getRepository('KIDvpBundle:BasketOrder');
        $basketOrder = $repository->findByBasket($basket);

        foreach ($basketOrder as $item) {
            $this->manager->remove($item);
        }

        return $this->delete($slug, $this->isClubMember('dvp'));
    }

    /**
     * @ApiDoc(
     *  description="Crée une commande",
     *  requirements={
     *   {
     *    "name"="email",
     *    "dataType"="string",
     *    "description"="Adresse mail du client"
     *   },
     *   {
     *    "name"="phone",
     *    "dataType"="string",
     *    "description"="Numéro de téléphone du client"
     *   }
     *  },
     *  input="KI\DvpBundle\Form\BasketOrderType",
     *  output="KI\DvpBundle\Entity\BasketOrder",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     * @Route\Post("/baskets/{slug}/order")
     */
    public function postBasketOrderAction($slug)
    {
        $basket = $this->findBySlug($slug);

        // On vérifie que la commande n'a pas déjà été faite
        $repository = $this->manager->getRepository('KIDvpBundle:BasketOrder');
        $basketOrder = $repository->findBy(array(
            'basket' => $basket,
            'user' => $this->user
            ));

        if (count($basketOrder) != 0)
            return;

        $basketOrder = new BasketOrder();
        $basketOrder->setBasket($basket);
        $basketOrder->setUser($this->user);

        // Si l'utilisateur n'est pas dans uPont on remplit les infos
        $request = $this->getRequest()->request;
        if ((!isset($this->user) && !($request->has('firstName')
                            && $request->has('lastName')
                            && $request->has('email')
                            && $request->has('phone')))
            || ($this->user->getPhone() === null && !$request->has('phone'))
           ) {
            throw new BadRequestHttpException('Formulaire incomplet');
        }

        if (!isset($this->user)) {
            $basketOrder->setFirstName($request->get('firstName'));
            $basketOrder->setLastName($request->get('lastName'));
            $basketOrder->setEmail($request->get('email'));
            $basketOrder->setPhone($request->get('phone'));
        } else {
            $basketOrder->setFirstName($this->user->getFirstName());
            $basketOrder->setLastName($this->user->getLastName());
            $basketOrder->setEmail($this->user->getEmail());
            if ($this->user->getPhone() === null) {
                $this->user->setPhone($request->get('phone'));
            }
            $basketOrder->setPhone($this->user->getPhone());
        }

        $this->manager->persist($basketOrder);
        $this->manager->flush();

        return $this->jsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *  description="Modifie une commande",
     *  input="KI\DvpBundle\Form\BasketOrderType",
     *  output="KI\DvpBundle\Entity\BasketOrder",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="DévelopPonts"
     * )
     * @Route\Patch("/baskets/{slug}/order/{username}")
     */
    public function patchBasketOrderAction($slug, $username)
    {
        $basket = $this->findBySlug($slug);
        $repository = $this->manager->getRepository('KIUserBundle:User');
        $user = $repository->findOneByUsername($username);

        // On vérifie que la commande existe
        $this->switchClass('BasketOrder');
        $basketOrder = $this->repository->findOneBy(array('basket' => $basket, 'user' => $user));

        if ($basketOrder === null) {
            throw new BadRequestHttpException('Commande non trouvée');
        }

        return $this->patch($basketOrder->getId(), $this->isClubMember('dvp'));
    }
}
