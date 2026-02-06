<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateSubscriptionRequest
{
    #[Assert\Length(max: 120, maxMessage: 'Name cannot be longer than {{ limit }} characters.')]
    public ?string $name = null;

    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Amount must be a valid decimal with up to 2 decimal places.')]
    #[Assert\Positive(message: 'Amount must be greater than 0.')]
    public ?string $amount = null;

    #[Assert\Length(exactly: 3, exactMessage: 'Currency must be exactly 3 characters (ISO-4217).')]
    #[Assert\Regex(pattern: '/^[A-Z]{3}$/', message: 'Currency must be 3 uppercase letters.')]
    public ?string $currency = null;

    #[Assert\Choice(
        choices: ['weekly', 'monthly', 'yearly'],
        message: 'Billing period must be one of: weekly, monthly, yearly.'
    )]
    public ?string $billingPeriod = null;

    #[Assert\Range(
        min: 1,
        max: 31,
        notInRangeMessage: 'Billing day of month must be between {{ min }} and {{ max }}.'
    )]
    public ?int $billingDayOfMonth = null;

    #[Assert\Range(
        min: 1,
        max: 12,
        notInRangeMessage: 'Billing month of year must be between {{ min }} and {{ max }}.'
    )]
    public ?int $billingMonthOfYear = null;

    #[Assert\Date(message: 'Next billing date must be a valid date.')]
    public ?string $nextBillingDate = null;

    #[Assert\Choice(
        choices: ['ai', 'kids', 'streaming', 'work', 'other'],
        message: 'Category must be one of: ai, kids, streaming, work, other.'
    )]
    public ?string $category = null;
}
