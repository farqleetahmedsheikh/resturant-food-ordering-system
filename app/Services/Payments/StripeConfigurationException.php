<?php

namespace App\Services\Payments;

use App\Exceptions\BusinessRuleException;

class StripeConfigurationException extends BusinessRuleException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 503);
    }
}
