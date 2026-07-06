<?php

namespace Controllers\Api;

use Core\Controller;
use Dto\ChangeCurrencyDto;
use Services\BalanceService;

class BalanceController extends Controller
{
    private int $userId;

    public function __construct()
    {
        parent::__construct();
        $this->userId = $this->requireAuth();
    }

    public function show(): void
    {
        $service = $this->container->get(BalanceService::class);
        $this->jsonResponse($service->getUserBalance($this->userId));
    }

    public function changeCurrency(ChangeCurrencyDto $dto): void
    {
        $this->validateOrError($dto);
        $service = $this->container->get(BalanceService::class);
        $this->jsonResult($service->changeCurrency($this->userId, $dto->currency));
    }
}
