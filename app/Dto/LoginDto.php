<?php

namespace Dto;

use Validation\Rules\MinMax;
use Validation\Rules\Required;

readonly class LoginDto implements Dto
{
    public function __construct(
        #[Required] #[MinMax(min: 3, max: 60)] public string $login,
        #[Required] #[MinMax(min: 6, max: 60)] public string $password
    ) {
    }
}
