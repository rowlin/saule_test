<?php

namespace Dto;

use Validation\Rules\Required;

readonly class SettleBetDto implements Dto
{
    public function __construct(
        #[Required] public int $betId,
        #[Required] public string $result,
    ) {
    }
}
