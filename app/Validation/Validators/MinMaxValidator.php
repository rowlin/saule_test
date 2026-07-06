<?php

namespace Validation\Validators;

use Validation\Validators\ValidationInterface;

class MinMaxValidator implements ValidationInterface
{
    public function __construct(protected readonly int $min = 1, protected readonly int $max = 99)
    {
    }

    public function validate($value): bool
    {
        
        return (strlen($value) >= $this->min && strlen($value) <= $this->max);
    }
}
