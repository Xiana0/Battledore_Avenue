<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use LogicException;

class UserRepository extends CustomEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{

    public function __construct(
        protected ManagerRegistry $registry,
        protected readonly string $entityClass
    ) {
        if (!$this->entityClass) {
            throw new LogicException('User entity class is not defined.');
        }
        $manager = $this->registry->getManagerForClass($this->entityClass);
        if (!$manager instanceof EntityManagerInterface) {
            throw new LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity\'s metadata.',
                $this->entityClass,
            ));
        }
        $classMetadata = $manager->getClassMetadata($this->entityClass);
        return parent::__construct($manager, $classMetadata);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof $this->entityClass) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }
        $em = $this->getEntityManager();
        $user->set(Config('PASSWORD_FIELD_NAME'), $newHashedPassword);
        $em->flush();
    }

    /**
     * Loads the user for the given user identifier (e.g. username or email)
     *
     * This method must throw UserNotFoundException if the user is not found.
     *
     * @return UserInterface
     *
     * @throws UserNotFoundException
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = parent::findOneBy([Config('USERNAME_PROPERTY_NAME') => $identifier]);
        if (null === $user) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);
            throw $e;
        }
        return $user;
    }
}
