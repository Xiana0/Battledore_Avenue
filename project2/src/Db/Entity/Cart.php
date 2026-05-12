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
 * Entity class for 'cart' table
 */
#[Entity]
#[Table('cart')]
class Cart extends BaseEntity
{
    #[Id]
    #[Column(name: 'id', type: 'integer', unique: true, insertable: false)]
    #[GeneratedValue]
    private int $id;

    #[Column(name: 'user_id', type: 'integer', nullable: true)]
    private ?int $userId;

    #[Column(name: 'product_name', type: 'string', nullable: true)]
    private ?string $productName;

    #[Column(name: 'product_type', type: 'string', nullable: true)]
    private ?string $productType;

    #[Column(name: 'quantity', type: 'integer', nullable: true)]
    private ?int $quantity;

    #[Column(name: 'price', type: 'decimal', nullable: true)]
    private ?string $price;

    #[Column(name: 'image', type: 'string', nullable: true)]
    private ?string $image;

    public function __construct()
    {
        $this->quantity = 1;
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $value): static
    {
        $this->userId = $value;
        return $this;
    }

    public function getProductName(): ?string
    {
        return HtmlDecode($this->productName);
    }

    public function setProductName(?string $value): static
    {
        $this->productName = RemoveXss($value);
        return $this;
    }

    public function getProductType(): ?string
    {
        return HtmlDecode($this->productType);
    }

    public function setProductType(?string $value): static
    {
        $this->productType = RemoveXss($value);
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $value): static
    {
        $this->quantity = $value;
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
}
