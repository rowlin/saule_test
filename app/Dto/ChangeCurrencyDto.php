<?php

namespace Dto;

use Validation\Rules\Required;

readonly class ChangeCurrencyDto implements Dto
{
    public function __construct(
        #[Required] public string $currency,
    ) {
    }
}
