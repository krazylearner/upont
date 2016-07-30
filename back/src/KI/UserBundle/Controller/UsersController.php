<?php

namespace KI\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Event\AchievementCheckEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UsersController extends \KI\CoreBundle\Controller\ResourceController
{
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('User', 'User');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste les utilisateurs",
     *  output="KI\UserBundle\Entity\User",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function getUsersAction() { return $this->getAll(); }

    /**
     * @ApiDoc(
     *  description="Retourne un utilisateur",
     *  output="KI\UserBundle\Entity\User",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function getUserAction($slug) { return $this->getOne($slug, true); }

    /**
     * @ApiDoc(
     *  description="Modifie un utilisateur",
     *  input="KI\UserBundle\Form\UserType",
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
    public function patchUserAction(Request $request, $slug)
    {
        // Les admissibles et extérieurs ne peuvent pas modifier leur profil
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMISSIBLE')
            || $this->get('security.authorization_checker')->isGranted('ROLE_EXTERIEUR'))
            throw new AccessDeniedException();

        if ($request->request->has('image')) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::PHOTO);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }

        // Un utilisateur peut se modifier lui même
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $response = $this->patch($slug, $user->getUsername() == $slug);

        $dispatcher = $this->get('event_dispatcher');
        $achievementCheck = new AchievementCheckEvent(Achievement::PROFILE);
        $dispatcher->dispatch('upont.achievement', $achievementCheck);

        if ($request->query->has('achievement')) {
            $dispatcher = $this->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::TOUR);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }

        return $response;
    }

    /**
     * @ApiDoc(
     *  description="Supprime un utilisateur",
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
    public function deleteUserAction($slug)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $userManager = $this->get('fos_user.user_manager');
        $user = $this->findBySlug($slug);
        $userManager->deleteUser($user);
    }

    /**
     * @ApiDoc(
     *  description="Récupère la liste des clubs dont l'utilisateur est membre",
     *  output="KI\UserBundle\Entity\Club",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     * @Route\Get("/users/{slug}/clubs")
     */
    public function getUserClubsAction($slug)
    {
        $user = $this->findBySlug($slug);

        $clubs = $this->manager->createQuery('SELECT cu, club
        FROM KIUserBundle:ClubUser cu
        JOIN cu.club club
        WHERE cu.user = :user')
            ->setParameter('user', $user)
            ->getArrayResult();

        return $this->restResponse($clubs, 200);
    }

    /**
     * @ApiDoc(
     *  description="Crée un compte et envoie un mail avec le mot de passe",
     *  requirements={
     *   {
     *    "name"="firstName",
     *    "dataType"="string",
     *    "description"="Prénom"
     *   },
     *   {
     *    "name"="lastName",
     *    "dataType"="string",
     *    "description"="Nom"
     *   },
     *   {
     *    "name"="email",
     *    "dataType"="string",
     *    "description"="Adresse email"
     *   },
     *  },
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     */
    public function postUsersAction(Request $request)
    {
        if (!$request->request->has('firstName') || !$request->request->has('lastName') || !$request->request->has('email'))
            throw new BadRequestHttpException('Champs non rempli(s)');

        $lastName = $request->request->get('lastName');
        $firstName = $request->request->get('firstName');
        $email = $request->request->get('email');

        if (!preg_match('/@eleves\.enpc\.fr$/', $email)) ///@(eleves\.)?enpc\.fr$/
            throw new BadRequestHttpException('Adresse mail non utilisable');

        // On check si l'utilisateur n'existe pas déjà
        $repo = $this->manager->getRepository('KIUserBundle:User');
        $users = $repo->findByEmail($email);

        if (count($users) > 0)
            throw new BadRequestHttpException('Cet utilisateur existe déjà.');

        // Si le login existe déjà, on ajoute une lettre du prénom
        $login = strtolower(str_replace(' ', '-', substr($this->stripAccents($lastName), 0, 7).$this->stripAccents($firstName)[0]));
        $i = 1;
        while (count($repo->findByUsername($login)) > 0) {
            if (isset($firstName[$i]))
                $login .= $firstName[$i];
            else
                $login .= '1';
            $i++;
        }

        $attributes = [
            'username' => $login,
            'email' => $email,
            'password' => substr(str_shuffle(strtolower(sha1(rand().time().'salt'))), 0, 8),
            'loginMethod' => 'form',
            'firstName' => $firstName,
            'lastName' => $lastName,
        ];

        $this->get('ki_user.factory.user')->createUser($login, [], $attributes);

        return $this->restResponse(null, 201);
    }

    /**
     * @ApiDoc(
     *  description="Crée un compte et envoie un mail avec le mot de passe",
     *  requirements={
     *   {
     *    "name"="users",
     *    "dataType"="file",
     *    "description"="Prénom"
     *   },
     *  },
     *  statusCodes={
     *   201="Requête traitée avec succès avec création d’un document",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Utilisateurs"
     * )
     * @Route\Post("/import/users")
     */
    public function importUsersAction(Request $request)
    {
        set_time_limit(3600);
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            return $this->json(null, 403);

        if (!$request->files->has('users'))
            throw new BadRequestHttpException('Aucun fichier envoyé');
        $file = $request->files->get('users');

        // Check CSV
        if ($file->getMimeType() !== 'text/plain' && $file->getMimeType() !== 'text/csv') {
            throw new Exception('L\import doit se faire au moyen d\'un fichier CSV');
        }

        // On récupère le contenu du fichier
        $path = __DIR__.'/../../../../web/uploads/tmp/';
        $file->move($path, 'users.list');
        $list = fopen($path.'users.list', 'r+');
        if ($list === false)
            throw new BadRequestHttpException('Erreur lors de l\'upload du fichier');

        // Dans un premier temps on va effectuer une première passe pour vérifier qu'il n'y a pas de duplicatas
        $emails = $logins = $fails = $success = [];
        $repo = $this->manager->getRepository('KIUserBundle:User');
        foreach ($repo->findAll() as $user) {
            $emails[] = $user->getEmail();
            $logins[] = $user->getUsername();
        }

        while (!feof($list)) {
            // On enlève le caractère de fin de ligne
            $line = str_replace(["\r", "\n"], ['', ''], fgets($list));
            $login = $firstName = $lastName = $email = $promo = $department = $origin = null;
            $explode = explode(',', $line);
            list($login, $email, $firstName, $lastName, $promo, $department) = $explode;
            $firstName = ucfirst($firstName);
            $lastName  = ucfirst($lastName);

            $e = [];
            if (!preg_match('/@(eleves\.)?enpc\.fr$/', $email))
                $e[] = 'Adresse mail non utilisable';
            if (in_array($email, $emails))
                $e[] = 'Adresse mail déja utilisée';
            if (in_array($login, $logins))
                $e[] = 'Login déja utilisé';

            if (count($e) > 0) {
                $fails[] = $line.' : '.implode(', ', $e);
            } else {
                $attributes = [
                    'username' => $login,
                    'email' => $email,
                    'password' => substr(str_shuffle(strtolower(sha1(rand().time().'salt'))), 0, 8),
                    'loginMethod' => 'form',
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'promo' => $promo,
                    'department' => $department,
                    'origin' => $origin,
                ];

                $this->get('ki_user.factory.user')->createUser($login, [], $attributes);

                $success[] = $firstName.' '.$lastName;
            }
        }

        return $this->restResponse(null, 201);
    }

    private function stripAccents($string) {
        return str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'],
            $string);
    }
}
