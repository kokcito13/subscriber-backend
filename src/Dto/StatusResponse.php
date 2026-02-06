<?php

declare(strict_types=1);

namespace App\Dto;

class StatusResponse
{
    public string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }
}
