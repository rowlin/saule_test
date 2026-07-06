<?php

namespace Dto;

use Validation\Rules\Required;

readonly class AddEventDto implements Dto
{
    public function __construct(
        #[Required] public string $name,
        #[Required] public string $team1Win,
        #[Required] public string $draw,
        #[Required] public string $team2Win,
    ) {
    }
}
