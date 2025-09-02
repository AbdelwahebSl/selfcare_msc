<?php

namespace App\Security;

use App\Service\UserProviderService;
use HWI\Bundle\OAuthBundle\HWIOAuthBundle;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use App\Entity\SelfcareUser;

class UserProviderAzure implements UserProviderInterface, OAuthAwareUserProviderInterface

{ //
    private $em;
    private $property = 'email';
    private $request;
    private $service;

    public function __construct(EntityManagerInterface $em, RequestStack $request, UserProviderService $service)
    {
        $this->em = $em;
        $this->request = $request;
        $this->service = $service;
    }

    /**
     * @return UserInterface
     * @return string
     * @throws UsernameNotFoundException
     *
     */
    public function loadUserByUsername(UserResponseInterface $response)
    {

        $username = $response->getEmail();


        $repository = $this->em->getRepository(SelfcareUser::class);

        if (null !== $this->property) {
            $user = $repository->findOneBy([$this->property => $username]);
        } else {
            if (!$repository instanceof UserLoaderInterface) {
                throw new \InvalidArgumentException(sprintf('You must either make the "%s" entity Doctrine Repository ("%s") implement "Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface" or set the "property" option in the corresponding entity provider configuration.', $this->classOrAlias, \get_class($repository)));
            }
            $user = $repository->loadUserByUsername($username);
        }


        $resourceOwner = $response->getResourceOwner();

        if (null === $user) {

            $content = $this->service->getJobTitle($response->getAccessToken());
            $user = new SelfcareUser();
            $data = $response->getData();
            $user->setFullName($data['name']);
            if ($content['jobTitle'] != 'Student' || $content['jobTitle'] != 'Etudiant') {
                $user->setRoles(["ROLE_PROFESSIONAL"]);
            } else {
                $user->setRoles(["ROLE_STUDENT"]);
            }

            $user->setEmail($username);
            $user->setName($data['given_name']);
            $user->setLastName($data['family_name']);
            // jobTitle
            $user->setPassword('');
            $user->isStudent(false);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SelfcareUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', SelfcareUser::class));
        }

        $repository = $this->em->getRepository(SelfcareUser::class);

        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            $refreshedUser = $repository->find($user->getId());
            if (null === $refreshedUser) {
                throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($user->getId())));
            }
        }

        return $refreshedUser;
    }

    /**
     * @return UserInterface
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {

        return $this->loadUserByUsername($response);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class)
    {
        return SelfcareUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // TODO: Implement loadUserByIdentifier() method.
    }
}