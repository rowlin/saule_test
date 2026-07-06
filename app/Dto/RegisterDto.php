<?php

namespace Dto;

use Validation\Rules\Required;
use Validation\Rules\MinMax;

readonly class RegisterDto implements Dto
{
    public function __construct(
        #[Required] #[MinMax(min: 3, max: 50)] public string $login,
        #[Required] #[MinMax(min: 6, max: 60)] public string $password,
        #[Required] #[MinMax(min: 1, max: 100)] public string $name,
        #[Required] public string $gender,
        #[Required] public string $birthDate,
        public string $phone = '',
        public string $email = '',
    ) {
    }
}
