<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\PlaceBetDto;
use Dto\SettleBetDto;
use Models\Bet;
use Services\BetService;
use Services\EventService;

class BetController extends Controller
{
    public function events(): void
    {
        $service = new EventService();
        $this->jsonResponse($service->getAll());
    }

    public function myBets(): void
    {
        $userId = $this->requireAuth();
        $betModel = new Bet();
        $this->jsonResponse($betModel->findByUser($userId));
    }

    public function bets(): void
    {
        $this->requireAdmin();

        $betModel = new Bet();
        $this->jsonResponse($betModel->findAllWithUsers());
    }

    public function placeBet(PlaceBetDto $dto): void
    {
        $userId = $this->requireAuth();
        $this->validateOrError($dto);
        $betService = $this->container->get(BetService::class);
        $this->jsonResult($betService->placeBet($userId, $dto->eventName, $dto->outcome, $dto->odds, $dto->amount), 201);
    }

    public function settleBet(SettleBetDto $dto): void
    {
        $this->requireAdmin();

        $this->validateOrError($dto);

        $adminId = $this->auth->getUserId();
        $betService = $this->container->get(BetService::class);
        $this->jsonResult($betService->settleBet($dto->betId, $dto->result, $adminId));
    }
}
