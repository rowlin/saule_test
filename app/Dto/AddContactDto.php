<?php

namespace Dto;

use Validation\Rules\Required;

readonly class AddContactDto implements Dto
{
    public function __construct(
        #[Required] public string $type,
        #[Required] public string $value,
    ) {
    }
}
