<?php

declare(strict_types=1);

namespace Validation\Rules;

use Attribute;
use Validation\Rules\ValidationRuleInterface;
use Validation\Validators\MinMaxValidator;
use Validation\Validators\ValidationInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MinMax implements ValidationRuleInterface
{
    public function __construct(public readonly ?int $min = null, public readonly ?int $max = null)
    {
    }

    public function getValidator(): ValidationInterface
    {
        return new MinMaxValidator(min: $this->min , max : $this->max);
    }
}
