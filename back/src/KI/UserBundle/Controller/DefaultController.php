<?php

namespace KI\UserBundle\Controller;

use KI\CoreBundle\Controller\BaseController;
use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Event\AchievementCheckEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends BaseController
{
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('User', 'User');
    }

    /**
     * @ApiDoc(
     *  description="Retourne les utilisateurs étant connectés et si le KI est ouvert",
     *  parameters={
     *   {
     *    "name"="delay",
     *    "dataType"="integer",
     *    "required"=false,
     *    "description"="Temps de l'intervalle considéré en minutes (30 minutes par défaut)"
     *   }
     *  },
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     * @Route("/refresh")
     * @Method("GET")
     */
    public function refreshAction(Request $request)
    {
        $delay = $request->query->has('delay') ? (int)$request->query->get('delay') : 30;
        $clubRepo = $this->manager->getRepository('KIUserBundle:Club');

        return $this->json([
                                'online' => $this->repository->getOnlineUsers($delay),
                                'open' => $clubRepo->findOneBySlug('ki')->getOpen()
                           ]);
    }


    /**
     * @ApiDoc(
     *  description="Envoie un mail permettant de reset le mot de passe",
     *  requirements={
     *   {
     *    "name"="username",
     *    "dataType"="string",
     *    "description"="Le nom d'utilisateur"
     *   }
     *  },
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Mauvaise combinaison username/password ou champ nom rempli",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Général"
     * )
     * @Route("/resetting/request")
     * @Method("POST")
     */
    public function resettingAction(Request $request)
    {
        if (!$request->request->has('username'))
            throw new BadRequestHttpException('Aucun nom d\'utilisateur fourni');

        $manager = $this->getDoctrine()->getManager();
        $repo = $manager->getRepository('KIUserBundle:User');
        $user = $repo->findOneByUsername($request->request->get('username'));

        if ($user) {
            if ($user->hasRole('ROLE_ADMISSIBLE'))
                return $this->json(null, 403);

            $token = $this->get('ki_user.service.token')->getToken($user);
            $message = \Swift_Message::newInstance()
                ->setSubject('Réinitialisation du mot de passe')
                ->setFrom('noreply@upont.enpc.fr')
                ->setTo($user->getEmail())
                ->setBody($this->renderView('KIUserBundle::resetting.txt.twig', ['token' => $token, 'name' => $user->getFirstName()]));
            $this->get('mailer')->send($message);

            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::PASSWORD, $user);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);

            return $this->json(null, 204);
        } else
            throw new NotFoundHttpException('Utilisateur non trouvé');
    }

    /**
     * @ApiDoc(
     *  description="Reset son mot de passe à partir du mail",
     *  requirements={
     *   {
     *    "name"="password",
     *    "dataType"="string",
     *    "description"="Le mot de passe"
     *   },
     *   {
     *    "name"="check",
     *    "dataType"="string",
     *    "description"="Le mot de passe une seconde fois (confirmation)"
     *   }
     *  },
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Mauvaise combinaison username/password ou champ nom rempli",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Général"
     * )
     * @Route("/resetting/token/{token}")
     * @Method("POST")
     */
    public function resettingTokenAction(Request $request, $token)
    {
        if (!$request->request->has('password') || !$request->request->has('check'))
            throw new BadRequestHttpException('Champs password/check non rempli(s)');

        $manager = $this->getDoctrine()->getManager();
        $repo = $manager->getRepository('KIUserBundle:User');
        $user = $repo->findOneByToken($token);

        if ($user) {
            if ($user->hasRole('ROLE_ADMISSIBLE'))
                return $this->json(null, 403);

            $username = $user->getUsername();

            // Pour changer le mot de passe on doit passer par le UserManager
            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($username);


            if ($request->request->get('password') != $request->request->get('check'))
                throw new BadRequestHttpException('Mots de passe non identiques');

            $user->setPlainPassword($request->request->get('password'));
            $userManager->updateUser($user, true);

            return $this->json(null, 204);
        } else
            throw new NotFoundHttpException('Utilisateur non trouvé');
    }
}
