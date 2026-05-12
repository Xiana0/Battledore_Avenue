<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{

    public function __construct(protected PropertyAccessorInterface $accessor)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new OAuthUser($identifier);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $user = $this->loadUserByIdentifier($response->getNickname() ?: $response->getUserIdentifier());
        $data = $response->getData();
        $user->setData($data);

        // Set user properties from response using PropertyAccessor
        $properties = ['nickName', 'firstName', 'lastName', 'realName', 'email', 'profilePicture'];
        foreach ($properties as $property) {
            $value = $this->accessor->isReadable($response, $property) ? $this->accessor->getValue($response, $property) : null;
            if ($value) {
                $this->accessor->setValue($user, $property, $value);
            }
        }
        $resourceOwnerName = $response->getResourceOwner()->getName();
        if ($identifyingAttribute = Config('EXTERNAL_LOGIN_PROVIDERS.' . $resourceOwnerName . '.identifyingAttribute')) {
            if (isset($data[$identifyingAttribute]) && $data[$identifyingAttribute]) {
                $user->setUsername($data[$identifyingAttribute]);
            }
        }
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(\sprintf('Unsupported user class "%s"', $user::class));
        }
        $identifier = $user->getUserIdentifier();
        return $this->loadUserByIdentifier($identifier);
    }

    public function supportsClass($class): bool
    {
        return OAuthUser::class === $class;
    }
}
