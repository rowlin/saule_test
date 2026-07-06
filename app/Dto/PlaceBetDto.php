<?php

namespace Dto;

use Validation\Rules\Required;

readonly class PlaceBetDto implements Dto
{
    public function __construct(
        #[Required] public string $eventName,
        #[Required] public string $outcome,
        #[Required] public string $odds,
        #[Required] public string $amount,
    ) {
    }
}
