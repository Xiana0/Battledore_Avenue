<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\ManagerRegistry;

class UserProfileFactory
{

    public function __construct(
        protected Language $language,
        protected LoggerInterface $logger,
        protected ManagerRegistry $registry,
        protected Security $symfonySecurity,
        protected AppServiceLocator $locator,
        protected CacheItemPoolInterface $cache,
    ) {}

    public function create(): UserProfile
    {
        // Creates a new UserProfile instance with injected dependencies
        return new UserProfile($this->language, $this->logger, $this->registry, $this->symfonySecurity, $this->locator, $this->cache);
    }
}
