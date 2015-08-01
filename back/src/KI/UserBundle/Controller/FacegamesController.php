<?php

namespace KI\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use FOS\RestBundle\View\View as RestView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Event\AchievementCheckEvent;

class FacegamesController extends \KI\CoreBundle\Controller\ResourceController
{
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Facegame', 'Users');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste les jeux",
     *  output="KI\UserBundle\Entity\Facegame",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function getFacegamesAction() { return $this->getAll(); }

    /**
     * @ApiDoc(
     *  description="Retourne un jeu",
     *  output="KI\UserBundle\Entity\Facegame",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée"
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function getFacegameAction($id, $auth = false)
    {
        if (isset($this->user) && $this->get('security.context')->isGranted('ROLE_EXTERIEUR') && !$auth)
            throw new AccessDeniedException();
        $game = $this->repo->findOneById($id);
        if (!isset($game))
            throw new NotFoundHttpException('Jeu non trouvé');
        return $game;
    }

    // On remplit la listUsers selon les paramètres rentrés
    // Chaque array contient les noms proposés, une image
    // et la position de la proposition correcte
    protected function postListUsersAction($facegame)
    {
        $repo = $this->em->getRepository('KIUpontBundle:Users\User');
        $list = $facegame->getListUsers();
        $userGame = $facegame->getUser();

        // Options
        $hardcore = $facegame->getHardcore();
        if ($hardcore) {
            $defaultTraits = array('department', 'promo', 'location');
        }

        // Promo
        $promo = $facegame->getPromo();
        $arrayUsers = ($promo != null) ? $repo->findByPromo($promo) : $arrayUsers = $repo->findAll();

        $max = count($arrayUsers);
        if ($max < 5)
            return false;

        // Gestion du nombre de questions possibles
        $nbQuestions = min(10, $max/2 - 1);
        $nbProps = 3;

        // $answers = []; // Array d'ids
        while(count($list) < $nbQuestions) {
            $tempList = [];
            $ids = [];

            if ($hardcore) {
                do {
                    $trait = $defaultTraits[rand(0, count($defaultTraits) - 1)];
                // Si la promo est déjà établie on ne va pas la demander comme carac
                } while ($promo !== null && $trait == 'promo');
                $tempList['trait'] = $trait;
                $userTraits = [];
            }

            // La réponse est décidée aléatoirement
            $tempList['answer'] = rand(0, $nbProps - 1);

            for ($i = 0 ; $i < $nbProps ; $i ++) {
                do {
                    do {
                        $tempId = rand(0, $max - 1);
                    // On vérifie qu'on ne propose pas deux fois le même nom
                    } while (in_array($tempId, $ids)
                        // || in_array($tempId, $answers)
                        );

                    $ids[] = $tempId;
                    $user = $arrayUsers[$tempId];

                    if ($hardcore) {
                        $tempTrait = $this->postTraitsAction($user, $trait);
                    }
                }
                // On vérifie que l'user existe, qu'il a une image de profil,
                // qu'on ne propose pas le nom de la personne ayant lancé le test
                // et qu'on ne propose pas 2 fois la même caractéristique
                while (!isset($user)
                || $user->getImage() === null
                || $user->getPromo() === null
                || $user->getUsername() == $userGame->getUsername()
                || $hardcore && (($tempTrait === null || in_array($tempTrait, $userTraits, true)))
                );
                $tempList[$i][0] = $user->getFirstName().' '.$user->getLastName();
                $tempList[$i][1] = $user->getImage()->getWebPath();

                if ($hardcore) {
                    $userTraits[] = $tempTrait;
                    $tempList[$i][2] = $tempTrait;
                }

                // if ($i == $tempList['answer'])
                    // $answers[] = $tempId;
            }
            $list[] = $tempList;
        }

        $facegame->setListUsers($list);
        return true;
    }

    protected function postTraitsAction($user, $trait)
    {
        switch ($trait) {
            case 'department':
                $return = $user->getDepartment();
                break;

            case 'promo':
                $return = $user->getPromo();
                break;

            case 'location':
                $return = $user->getLocation();
                break;

            default:
                throw new BadRequestHttpException('Caractéristique inexistante '.$trait);
                break;
        }

        return $return;
    }

    /**
     * @ApiDoc(
     *  description="Crée un jeu",
     *  input="KI\UserBundle\Form\FacegameType",
     *  output="KI\UserBundle\Entity\Facegame",
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function postFacegameAction() {
        $return = $this->partialPost($this->get('security.context')->isGranted('ROLE_USER'));
        if ($return['code'] == 400)
            return RestView::create($return['form'], 400);

        else if ($return['code'] == 201) {
            // On modifie légèrement la ressource qui vient d'être créée
            $return['item']->setDate(time());
            $return['item']->setUser($this->container->get('security.context')->getToken()->getUser());

            if (!$this->postListUsersAction($return['item'])) {
                $this->em->detach($return['item']);
                return RestView::create($return['item'], 400);
            }

            $this->em->flush();
            return RestView::create($return['item'],
                201,
                array(
                    'Location' => $this->generateUrl(
                        'get_'.strtolower($this->className),
                        array('id' => $return['item']->getId()),
                        true
                    )
                )
            );
        }

        else {
            $this->em->flush();
            return RestView::create(null, 204);
        }
    }

    /**
     * @ApiDoc(
     *  description="Modifie un jeu",
     *  input="KI\UserBundle\Form\FacegameType",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function patchFacegameAction($slug)
    {
        $game = $this->findBySlug($slug);

        if (!empty($game->getDuration())) {
            throw new BadRequestHttpException('Jeu déjà fini');
        }

        $request = $this->getRequest()->request;
        if (!$request->has('wrongAnswers')) {
            throw new BadRequestHttpException('Paramètre manquant');
        }

        $wrongAnswers = $request->get('wrongAnswers');
        $game->setWrongAnswers($wrongAnswers);
        $duration = time() + 5*$wrongAnswers - $game->getDate();
        $game->setDuration($duration);
        $this->em->flush();

        $dispatcher = $this->container->get('event_dispatcher');
        $achievementCheck = new AchievementCheckEvent(Achievement::GAME_PLAY);
        $dispatcher->dispatch('upont.achievement', $achievementCheck);

        $promoUser = (int) $this->user->getPromo();
        $promoGame = (int) $game->getPromo();

        if ($wrongAnswers == 0 && $promoGame == $promoUser-1 && $duration < 60) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::GAME_BEFORE);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        } else if ($wrongAnswers == 0 && $promoGame == $promoUser && $duration < 60) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::GAME_SELF);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        } else if ($wrongAnswers == 0 && $promoGame == $promoUser+1 && $duration < 60) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::GAME_NEXT);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }
        if ($wrongAnswers == 0 && $promoGame < $promoUser && $game->getHardcore() && $duration < 60) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::GAME_OLD);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }
        return $this->jsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *  description="Supprime un jeu",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function deleteFacegameAction($id, $auth = false)
    {
        if (isset($this->user) &&
            (($this->get('security.context')->isGranted('ROLE_ADMISSIBLE')
                || $this->get('security.context')->isGranted('ROLE_EXTERIEUR'))
            && !$auth))
            throw new AccessDeniedException('Accès refusé');
        $item = $this->repo->findOneById($id);
        if (!isset($item))
            throw new NotFoundHttpException('Jeu non trouvé');
        $this->em->remove($item);
        $this->em->flush();
    }
}
