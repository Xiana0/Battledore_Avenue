<?php

namespace PHPMaker2026\Project1\Db\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateInterval;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\SequenceGenerator;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use PHPMaker2026\Project1\AdvancedUserInterface;
use PHPMaker2026\Project1\AdvancedSecurity;
use PHPMaker2026\Project1\UserProfile;
use PHPMaker2026\Project1\UserRepository;
use PHPMaker2026\Project1\CustomEntityRepository;
use PHPMaker2026\Project1\DefaultSequenceGenerator;
use PHPMaker2026\Project1\UuidGenerator;
use PHPMaker2026\Project1\Entity as BaseEntity;
use function PHPMaker2026\Project1\Config;
use function PHPMaker2026\Project1\EntityManager;
use function PHPMaker2026\Project1\ConvertToBool;
use function PHPMaker2026\Project1\ConvertToString;
use function PHPMaker2026\Project1\SameDateTime;
use function PHPMaker2026\Project1\RemoveXss;
use function PHPMaker2026\Project1\HtmlDecode;
use function PHPMaker2026\Project1\HashPassword;
use function PHPMaker2026\Project1\PhpEncrypt;
use function PHPMaker2026\Project1\PhpDecrypt;
use function PHPMaker2026\Project1\Security;
use function PHPMaker2026\Project1\IsEmpty;
use InvalidArgumentException;

/**
 * Entity class for 'accessories' table
 */
#[Entity]
#[Table('accessories')]
class Accessory extends BaseEntity
{
    #[Id]
    #[Column(name: 'id', type: 'integer', unique: true, insertable: false)]
    #[GeneratedValue]
    private int $id;

    #[Column(name: 'accessory_name', type: 'string')]
    private string $accessoryName;

    #[Column(name: 'price', type: 'decimal', nullable: true)]
    private ?string $price;

    #[Column(name: 'image', type: 'string', nullable: true)]
    private ?string $image;

    #[Column(name: 'stock', type: 'integer', nullable: true)]
    private ?int $stock;

    public function __construct()
    {
        $this->stock = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getAccessoryName(): string
    {
        return HtmlDecode($this->accessoryName);
    }

    public function setAccessoryName(string $value): static
    {
        $this->accessoryName = RemoveXss($value);
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $value): static
    {
        $this->price = $value;
        return $this;
    }

    public function getImage(): ?string
    {
        return HtmlDecode($this->image);
    }

    public function setImage(?string $value): static
    {
        $this->image = RemoveXss($value);
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $value): static
    {
        $this->stock = $value;
        return $this;
    }
}
