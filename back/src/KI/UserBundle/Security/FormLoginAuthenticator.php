<?php

namespace KI\UserBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use KI\UserBundle\Entity\Achievement;
use KI\UserBundle\Event\AchievementCheckEvent;

class FormLoginAuthenticator extends AbstractGuardAuthenticator
{
    private $passwordEncoder;
    private $jwtManager;
    private $dispatcher;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, JWTManagerInterface $jwtManager, EventDispatcherInterface $dispatcher)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtManager = $jwtManager;
        $this->dispatcher = $dispatcher;
    }

    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login') {
            return;
        }

        return [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password')
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        return $userProvider->loadUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $plainPassword = $credentials['password'];
        if (!$this->passwordEncoder->isPasswordValid($user, $plainPassword)) {
            throw new BadCredentialsException();
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => $exception->getMessageKey()
        ];

        return new JsonResponse($data, 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        // On commence par checker éventuellement l'achievement de login
        $achievementCheck = new AchievementCheckEvent(Achievement::LOGIN);
        $this->dispatcher->dispatch('upont.achievement', $achievementCheck);

        $achievementCheck = new AchievementCheckEvent(Achievement::SPIRIT);
        $this->dispatcher->dispatch('upont.achievement', $achievementCheck);

        $achievementCheck = new AchievementCheckEvent(Achievement::KIEN);
        $this->dispatcher->dispatch('upont.achievement', $achievementCheck);

        $achievementCheck = new AchievementCheckEvent(Achievement::ADMIN);
        $this->dispatcher->dispatch('upont.achievement', $achievementCheck);


        $balance = $user->getBalance();
        if ($balance !== null) {
            if ($balance < 0) {
                $achievementCheck = new AchievementCheckEvent(Achievement::FOYER);
                $this->dispatcher->dispatch('upont.achievement', $achievementCheck);
            } else {
                $achievementCheck = new AchievementCheckEvent(Achievement::FOYER_BIS);
                $this->dispatcher->dispatch('upont.achievement', $achievementCheck);
            }
        }

        $data = [
            'code' => 200,
            'data' => [
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'roles' => $user->getRoles(),
                //'first' => $event->getRequest()->request->has('first'),
            ],
            'token' => $this->jwtManager->create($user),
        ];

        return new JsonResponse($data, 200);
    }

    public function start(Request $request, AuthenticationException $e = null)
    {
        $data = [
            'message' => 'Missing credentials.',
            'code' => 401,
        ];

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
