<?php

namespace Dto;

use Validation\Rules\Required;

readonly class SetBalanceDto implements Dto
{
    public function __construct(
        #[Required] public int $userId,
        #[Required] public string $currency,
        #[Required] public string $balance,
    ) {
    }
}
