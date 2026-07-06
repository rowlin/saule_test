<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\ChangeCurrencyDto;
use Services\BalanceService;

class BalanceController extends Controller
{
    public function show(): void
    {
        $userId = $this->requireAuth();

        $service = $this->container->get(BalanceService::class);
        $this->jsonResponse($service->getUserBalance($userId));
    }

    public function changeCurrency(ChangeCurrencyDto $dto): void
    {
        $userId = $this->requireAuth();
        $this->validateOrError($dto);
        $service = $this->container->get(BalanceService::class);
        $this->jsonResult($service->changeCurrency($userId, $dto->currency));
    }
}
