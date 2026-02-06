<?php

declare(strict_types=1);

namespace App\Dto;

class SubscriptionResponse
{
    public string $id;
    public string $name;
    public string $amount;
    public string $currency;
    public string $billingPeriod;
    public ?int $billingDayOfMonth;
    public ?int $billingMonthOfYear;
    public string $nextBillingDate;
    public ?string $category;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        string $id,
        string $name,
        string $amount,
        string $currency,
        string $billingPeriod,
        ?int $billingDayOfMonth,
        ?int $billingMonthOfYear,
        string $nextBillingDate,
        ?string $category,
        string $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->billingPeriod = $billingPeriod;
        $this->billingDayOfMonth = $billingDayOfMonth;
        $this->billingMonthOfYear = $billingMonthOfYear;
        $this->nextBillingDate = $nextBillingDate;
        $this->category = $category;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
