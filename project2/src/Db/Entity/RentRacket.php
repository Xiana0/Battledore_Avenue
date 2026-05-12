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
 * Entity class for 'rent_rackets' table
 */
#[Entity]
#[Table('rent_rackets')]
class RentRacket extends BaseEntity
{
    #[Id]
    #[Column(name: 'id', type: 'integer', unique: true, insertable: false)]
    #[GeneratedValue]
    private int $id;

    #[Column(name: 'racket_name', type: 'string')]
    private string $racketName;

    #[Column(name: 'brand', type: 'string', nullable: true)]
    private ?string $brand;

    #[Column(name: 'price_per_day', type: 'decimal', nullable: true)]
    private ?string $pricePerDay;

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

    public function getRacketName(): string
    {
        return HtmlDecode($this->racketName);
    }

    public function setRacketName(string $value): static
    {
        $this->racketName = RemoveXss($value);
        return $this;
    }

    public function getBrand(): ?string
    {
        return HtmlDecode($this->brand);
    }

    public function setBrand(?string $value): static
    {
        $this->brand = RemoveXss($value);
        return $this;
    }

    public function getPricePerDay(): ?string
    {
        return $this->pricePerDay;
    }

    public function setPricePerDay(?string $value): static
    {
        $this->pricePerDay = $value;
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
