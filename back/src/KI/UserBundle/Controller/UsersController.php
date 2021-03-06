<?php

namespace KI\UserBundle\Controller;

use KI\CoreBundle\Controller\ResourceController;
use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Entity\User;
use KI\UserBundle\Event\AchievementCheckEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Exception;

class UsersController extends ResourceController
{
    public function setContainer(ContainerInterface $container = null)
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
     * @Route("/users")
     * @Method("GET")
     */
    public function getUsersAction()
    {
        return $this->getAll();
    }

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
     * @Route("/users/{slug}")
     * @Method("GET")
     */
    public function getUserAction($slug)
    {
        $user = $this->getOne($slug, true);

        return $this->json($user);
    }

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
     * @Route("/users/{slug}")
     * @Method("PATCH")
     */
    public function patchUserAction(Request $request, $slug)
    {
        // Les admissibles et extérieurs ne peuvent pas modifier leur profil
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMISSIBLE')
            || $this->get('security.authorization_checker')->isGranted('ROLE_EXTERIEUR')
        )
            throw new AccessDeniedException();

        if ($request->request->has('image')) {
            $dispatcher = $this->container->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::PHOTO);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }

        // Un utilisateur peut se modifier lui même
        $user = $this->getUser();
        $patchData = $this->patch($slug, $user->getUsername() == $slug);

        $dispatcher = $this->get('event_dispatcher');
        $achievementCheck = new AchievementCheckEvent(Achievement::PROFILE);
        $dispatcher->dispatch('upont.achievement', $achievementCheck);

        if ($request->query->has('achievement')) {
            $dispatcher = $this->get('event_dispatcher');
            $achievementCheck = new AchievementCheckEvent(Achievement::TOUR);
            $dispatcher->dispatch('upont.achievement', $achievementCheck);
        }

        return $this->formJson($patchData);
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
     * @Route("/users/{slug}")
     * @Method("DELETE")
     */
    public function deleteUserAction($slug)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $userManager = $this->get('fos_user.user_manager');
        $user = $this->findBySlug($slug);
        $userManager->deleteUser($user);

        return $this->json(null, 204);
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
     * @Route("/users/{slug}/clubs")
     * @Method("GET")
     */
    public function getUserClubsAction($slug)
    {
        $user = $this->findBySlug($slug);

        $clubs = $this->repository->getUserClubs($user);

        return $this->json($clubs, 200);
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
     * @Route("/users")
     * @Method("POST")
     */
    public function postUsersAction(Request $request)
    {
        //On limite la création de compte aux admins
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

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
        $login = strtolower(str_replace(' ', '-', substr($this->stripAccents($lastName), 0, 7) . $this->stripAccents($firstName)[0]));
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
            'firstName' => $firstName,
            'lastName' => $lastName,
        ];

        $this->get('ki_user.factory.user')->createUser($login, [], $attributes);

        return $this->json(null, 201);
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
     * @Route("/import/users")
     * @Method("POST")
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
        $path = __DIR__ . '/../../../../web/uploads/tmp/';
        $file->move($path, 'users.list');
        $list = fopen($path . 'users.list', 'r+');
        if ($list === false)
            throw new BadRequestHttpException('Erreur lors de l\'upload du fichier');

        // Dans un premier temps on va effectuer une première passe pour vérifier qu'il n'y a pas de duplicatas
        $fails = $success = [];

        while (!feof($list)) {
            // On enlève le caractère de fin de ligne
            $line = str_replace(["\r", "\n"], ['', ''], fgets($list));
            if(empty($line))
                continue;

            $gender = $login = $firstName = $lastName = $email = $promo = $department = $origin = null;
            $explode = explode(',', $line);
            list($gender, $lastName, $firstName, $email, $origin, $department, $promo) = $explode;
            $firstName = ucfirst($firstName);
            $lastName = ucwords(mb_strtolower($lastName));

            $login = explode('@', $email)[0];

            $e = [];
            if (!preg_match('/@(eleves\.)?enpc\.fr$/', $email))
                $e[] = 'Adresse mail non utilisable';

            if (count($e) > 0) {
                $fails[] = $line . ' : ' . implode(', ', $e);
            } else {

                /**
                 * @var $user User
                 */
                $user = $this->repository->findOneBy(['email' => $email]);
                if (!$user) {
                    $attributes = [
                        'username' => $login,
                        'email' => $email,
                        'loginMethod' => 'form',
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'promo' => $promo,
                        'department' => $department,
                        'origin' => $origin,
                    ];

                    $user = $this->get('ki_user.factory.user')->createUser($login, [], $attributes);
                } else {
                    $user->setPromo($promo);
                    $user->setDepartment($department);
                    $user->setOrigin($origin);
                }
                $user->setGender($gender);

                $success[] = $firstName . ' ' . $lastName;
            }
        }

        return $this->json([
            'success' => $success,
            'fails' => $fails,
        ], 201);
    }

    private function stripAccents($string)
    {
        return str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'],
            $string);
    }
}
