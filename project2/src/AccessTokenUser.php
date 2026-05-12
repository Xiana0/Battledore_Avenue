<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\User\UserInterface;
use UnexpectedValueException;

/**
 * Access token user
 */
class AccessTokenUser implements UserInterface
{
    public array $attributes = [];

    /**
     * Constructor
     */
    public function __construct(
        public string $userIdentifier,
        public mixed $identifier = null,
        public ?string $webSiteURL = null, // User website, blog, web page
        public ?string $profileURL = null, // URL link to profile page on the IDp web site
        public ?string $photoURL = null, // URL link to user photo or avatar
        public ?string $displayName = null, // User displayName provided by the IDp or a concatenation of first and last name.
        public ?string $description = null, // A short about_me
        public ?string $firstName = null, // User's first name
        public ?string $lastName = null, // User's last name
        public ?string $gender = null, // Gender
        public ?string $language = null, // Language
        public ?int $age = null, // User age, return it as is if the IdP provide it
        public ?int $birthDay = null, // User birth day
        public ?int $birthMonth = null, // User birth month
        public ?int $birthYear = null, // User birth year
        public ?string $email = null, // User email (Note: Not all IdP grant access to the user email)
        public ?string $emailVerified = null, // Verified user email (Note: Not all IdP grant access to verified user email)
        public ?string $phone = null, // Phone number
        public ?string $address = null, // Complete user address
        public ?string $country = null, // User country
        public ?string $region = null, // Region
        public ?string $city = null, // City
        public ?string $zip = null, // Postal code
        ...$attributes
    ) {
        $this->attributes = $attributes; // Store the extras explicitly
    }

    /**
     * Prevent the providers adapters from adding new fields
     *
     * @throws UnexpectedValueException
     * @var mixed $value
     *
     * @var string $name
     */
    public function __set(string $name, mixed $value): void
    {
        throw new UnexpectedValueException(sprintf('Adding new property "%s" to %s is not allowed.', $name, __CLASS__));
    }

    /**
     * Returns the roles granted to the user
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * Removes sensitive data from the user
     */
    #[\Deprecated(since: 'symfony/security-core 7.3')]
    public function eraseCredentials(): void
    {
    }

    /**
     * Returns the identifier for this user (e.g. username or email address)
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
