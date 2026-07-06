<?php

declare(strict_types=1);

namespace Validation\Rules;

use Attribute;
use Validation\Rules\ValidationRuleInterface;
use Validation\Validators\ValidationInterface;
use Validation\Validators\RequiredValidator;

#[Attribute]
class Required implements ValidationRuleInterface
{
    public function getValidator(): ValidationInterface
    {
        return new RequiredValidator();
    }
}
