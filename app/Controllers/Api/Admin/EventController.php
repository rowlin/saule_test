<?php

namespace Controllers\Api\Admin;

use Core\Controller;
use Dto\AddEventDto;
use Services\EventService;

class EventController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function addEvent(AddEventDto $dto): void
    {
        $this->validateOrError($dto);
        $service = new EventService();
        $this->jsonResult($service->createEvent($dto->name, (float) $dto->team1Win, (float) $dto->draw, (float) $dto->team2Win));
    }
}
