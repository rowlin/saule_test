<?php

namespace Validation\Validators;

use Validation\Validators\ValidationInterface;

class RequiredValidator implements ValidationInterface
{
    public function validate($value): bool
    {
        return !empty($value);
    }
}
