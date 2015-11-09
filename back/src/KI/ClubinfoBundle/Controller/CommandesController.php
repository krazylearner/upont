<?php

namespace KI\ClubinfoBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\DependencyInjection\ContainerInterface;
use KI\CoreBundle\Controller\ResourceController;

class CommandesController extends ResourceController
{
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Commande', 'Clubinfo');
    }

    /**
     * @ApiDoc(
     *  description="Retourne les commandes associées à une centrale",
     *  output="KI\ClubinfoBundle\Entity\Commande",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Clubinfo"
     * )
     */

    public function getCentraleCommandesAction($centraleSlug)
    {
        $this->switchClass('Centrale');
        $centrale = $this->findBySlug($centraleSlug);
        $this->switchClass();

        return $this->repository->findByCentrale($centrale);
    }

    /**
     * @ApiDoc(
     *  description="Retourne les commandes associées à un utilisateur et une centrale",
     *  output="KI\ClubinfoBundle\Entity\Commande",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Clubinfo"
     * )
     */

    public function getCentraleCommandeAction($centraleSlug, $userSlug)
    {
        $this->switchClass('Centrale');
        $centrale = $this->findBySlug($centraleSlug);
        $this->switchClass();

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->repository->findOneBy(
                    array(
                        'user' => $user,
                        'centrale' => $centrale,
                        )
                    );
    }

    /**
     * @ApiDoc(
     *  description="Crée une commande",
     *  input="KI\ClubinfoBundle\Form\CommandeType",
     *  output="KI\ClubinfoBundle\Entity\Commande",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Clubinfo"
     * )
     */
    public function postCentraleCommandesAction($centraleSlug)
    {
        $this->switchClass('Centrale');
        $centrale = $this->findBySlug($centraleSlug);
        $this->switchClass();

        $return = $this->postData($this->is('USER'));

        if ($return['code'] != 400) {
            $return['item']->setCentrale($centrale);
        }

        return $this->postView($return, $centrale);
    }

    /**
     * @ApiDoc(
     *  description="Modifie une commande",
     *  input="KI\ClubinfoBundle\Form\CommandeType",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Clubinfo"
     * )
     */
    public function patchCentraleCommandeAction($centraleSlug, $userSlug)
    {

        $this->switchClass('Centrale');
        $centrale = $this->findBySlug($centraleSlug);
        $this->switchClass();

        $user = $this->get('security.context')->getToken()->getUser();

        $commande = $this->repository->findOneBy(
                    array(
                        'user' => $user,
                        'centrale' => $centrale,
                        )
                    );

        $this->trust($this->is('MODO') || $user == $commande->getUser());

        $formHelper = $this->get('ki_core.helper.form');
        return $this->postView($formHelper->formData($commande, 'PATCH'), $centrale);
    }

    /**
     * @ApiDoc(
     *  description="Supprime une commande",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Clubinfo"
     * )
     * @Route\Delete("/commandes/{slug}")
     */
    public function deleteCommandeAction($slug)
    {
        $this->switchClass('Centrale');
        $centrale = $this->findBySlug($centraleSlug);
        $this->switchClass();

        $user = $this->get('security.context')->getToken()->getUser();

        $commande = $this->repository->findOneBy(
                    array(
                        'user' => $user,
                        'centrale' => $centrale,
                        )
                    );

        $this->trust($this->is('MODO') || $user == $commande->getUser());

        $this->manager->remove($commande);
        $this->manager->flush();
    }
}