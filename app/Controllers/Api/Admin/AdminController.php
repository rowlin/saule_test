<?php

namespace Controllers\Api\Admin;

use Core\Controller;
use Dto\SetBalanceDto;
use Dto\UpdateBalanceDto;
use Services\AdminService;
use Services\RateService;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function users(): void
    {
        $service = $this->container->get(AdminService::class);
        $this->jsonResponse($service->getUsers());
    }

    public function updateRates(): void
    {
        $service = $this->container->get(RateService::class);
        $this->jsonResult($service->updateRates($this->getRequestBody()));
    }

    public function updateBalance(UpdateBalanceDto $dto): void
    {
        $this->validateOrError($dto);
        $adminId = $this->auth->getUserId();
        $service = $this->container->get(AdminService::class);
        $this->jsonResult($service->updateUserBalance($adminId, $dto->userId, $dto->currency, (float) $dto->amount));
    }

    public function setBalance(SetBalanceDto $dto): void
    {
        $this->validateOrError($dto);
        $adminId = $this->auth->getUserId();
        $service = $this->container->get(AdminService::class);
        $this->jsonResult($service->setUserBalance($adminId, $dto->userId, $dto->currency, (float) $dto->balance));
    }

    public function userBalances(array $args): void
    {
        $userId = (int) ($args[0] ?? 0);
        if (!$userId) {
            $this->jsonError('User ID required', 400);
            return;
        }

        $balanceModel = $this->container->get(\Models\Balance::class);
        $this->jsonResponse($balanceModel->findByUser($userId));
    }
}
