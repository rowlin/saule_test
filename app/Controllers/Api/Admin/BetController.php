<?php

namespace Controllers\Api\Admin;

use Core\Controller;
use Dto\SettleBetDto;
use Models\Bet;
use Services\BetService;

class BetController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function bets(): void
    {
        $betModel = new Bet();
        $this->jsonResponse($betModel->findAllWithUsers());
    }

    public function settleBet(SettleBetDto $dto): void
    {
        $this->validateOrError($dto);

        $adminId = $this->auth->getUserId();
        $betService = $this->container->get(BetService::class);
        $this->jsonResult($betService->settleBet($dto->betId, $dto->result, $adminId));
    }
}
