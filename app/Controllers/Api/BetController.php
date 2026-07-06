<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\PlaceBetDto;
use Models\Bet;
use Services\BetService;
use Services\EventService;

class BetController extends Controller
{
    private int $userId;

    public function __construct()
    {
        parent::__construct();
        $this->userId = $this->requireAuth();
    }

    public function events(): void
    {
        $service = new EventService();
        $this->jsonResponse($service->getAll());
    }

    public function myBets(): void
    {
        $betModel = new Bet();
        $this->jsonResponse($betModel->findByUser($this->userId));
    }

    public function placeBet(PlaceBetDto $dto): void
    {
        $this->validateOrError($dto);
        $betService = $this->container->get(BetService::class);
        $this->jsonResult($betService->placeBet($this->userId, $dto->eventName, $dto->outcome, (float) $dto->odds, (float) $dto->amount), 201);
    }
}
