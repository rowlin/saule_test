<?php

namespace Dto;

use Validation\Rules\Required;

readonly class UpdateBalanceDto implements Dto
{
    public function __construct(
        #[Required] public int $userId,
        #[Required] public string $currency,
        #[Required] public string $amount,
    ) {
    }
}
