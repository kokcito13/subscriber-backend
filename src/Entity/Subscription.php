<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
#[ORM\Index(columns: ['user_id', 'status'], name: 'idx_subscription_user_status')]
#[ORM\Index(columns: ['user_id', 'next_billing_date'], name: 'idx_subscription_user_billing_date')]
#[ORM\Index(columns: ['user_id', 'billing_period'], name: 'idx_subscription_user_period')]
#[ORM\HasLifecycleCallbacks]
class Subscription
{
    public const BILLING_PERIOD_WEEKLY = 'weekly';
    public const BILLING_PERIOD_MONTHLY = 'monthly';
    public const BILLING_PERIOD_YEARLY = 'yearly';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELED = 'canceled';

    public const CATEGORY_AI = 'ai';
    public const CATEGORY_KIDS = 'kids';
    public const CATEGORY_STREAMING = 'streaming';
    public const CATEGORY_WORK = 'work';
    public const CATEGORY_OTHER = 'other';

    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'User is required.')]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 120)]
    #[Assert\NotBlank(message: 'Name cannot be blank.')]
    #[Assert\Length(max: 120, maxMessage: 'Name cannot be longer than {{ limit }} characters.')]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Amount cannot be blank.')]
    #[Assert\Positive(message: 'Amount must be greater than 0.')]
    private ?string $amount = null;

    #[ORM\Column(type: Types::STRING, length: 3)]
    #[Assert\NotBlank(message: 'Currency cannot be blank.')]
    #[Assert\Length(exactly: 3, exactMessage: 'Currency must be exactly 3 characters (ISO-4217).')]
    private ?string $currency = null;

    #[ORM\Column(name: 'billing_period', type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: 'Billing period cannot be blank.')]
    #[Assert\Choice(
        choices: [self::BILLING_PERIOD_WEEKLY, self::BILLING_PERIOD_MONTHLY, self::BILLING_PERIOD_YEARLY],
        message: 'Billing period must be one of: weekly, monthly, yearly.'
    )]
    private ?string $billingPeriod = null;

    #[ORM\Column(name: 'billing_day_of_month', type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(
        min: 1,
        max: 31,
        notInRangeMessage: 'Billing day of month must be between {{ min }} and {{ max }}.'
    )]
    private ?int $billingDayOfMonth = null;

    #[ORM\Column(name: 'billing_month_of_year', type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(
        min: 1,
        max: 12,
        notInRangeMessage: 'Billing month of year must be between {{ min }} and {{ max }}.'
    )]
    private ?int $billingMonthOfYear = null;

    #[ORM\Column(name: 'next_billing_date', type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'Next billing date cannot be blank.')]
    private ?\DateTimeImmutable $nextBillingDate = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    #[Assert\Choice(
        choices: [self::CATEGORY_AI, self::CATEGORY_KIDS, self::CATEGORY_STREAMING, self::CATEGORY_WORK, self::CATEGORY_OTHER],
        message: 'Category must be one of: ai, kids, streaming, work, other.'
    )]
    private ?string $category = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => self::STATUS_ACTIVE])]
    #[Assert\Choice(
        choices: [self::STATUS_ACTIVE, self::STATUS_PAUSED, self::STATUS_CANCELED],
        message: 'Status must be one of: active, paused, canceled.'
    )]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = strtoupper($currency);
        return $this;
    }

    public function getBillingPeriod(): ?string
    {
        return $this->billingPeriod;
    }

    public function setBillingPeriod(string $billingPeriod): self
    {
        $this->billingPeriod = $billingPeriod;
        return $this;
    }

    public function getBillingDayOfMonth(): ?int
    {
        return $this->billingDayOfMonth;
    }

    public function setBillingDayOfMonth(?int $billingDayOfMonth): self
    {
        $this->billingDayOfMonth = $billingDayOfMonth;
        return $this;
    }

    public function getBillingMonthOfYear(): ?int
    {
        return $this->billingMonthOfYear;
    }

    public function setBillingMonthOfYear(?int $billingMonthOfYear): self
    {
        $this->billingMonthOfYear = $billingMonthOfYear;
        return $this;
    }

    public function getNextBillingDate(): ?\DateTimeImmutable
    {
        return $this->nextBillingDate;
    }

    public function setNextBillingDate(\DateTimeImmutable $nextBillingDate): self
    {
        $this->nextBillingDate = $nextBillingDate;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->validateBillingPeriodConstraints();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->validateBillingPeriodConstraints();
    }

    private function validateBillingPeriodConstraints(): void
    {
        if ($this->billingPeriod === self::BILLING_PERIOD_WEEKLY) {
            if ($this->billingDayOfMonth !== null || $this->billingMonthOfYear !== null) {
                throw new \InvalidArgumentException('Weekly billing period cannot have billingDayOfMonth or billingMonthOfYear set.');
            }
        } elseif ($this->billingPeriod === self::BILLING_PERIOD_MONTHLY) {
            if ($this->billingDayOfMonth === null) {
                throw new \InvalidArgumentException('Monthly billing period requires billingDayOfMonth to be set.');
            }
            if ($this->billingMonthOfYear !== null) {
                throw new \InvalidArgumentException('Monthly billing period cannot have billingMonthOfYear set.');
            }
        } elseif ($this->billingPeriod === self::BILLING_PERIOD_YEARLY) {
            if ($this->billingDayOfMonth === null || $this->billingMonthOfYear === null) {
                throw new \InvalidArgumentException('Yearly billing period requires both billingDayOfMonth and billingMonthOfYear to be set.');
            }
        }
    }
}
