<?php

namespace Controllers\Api;

use Core\Controller;
use Models\BalanceLog;

class LogController extends Controller
{
    private int $userId;

    public function __construct()
    {
        parent::__construct();
        $this->userId = $this->requireAuth();
    }

    public function logs(): void
    {
        $logModel = new BalanceLog();
        $this->jsonResponse($logModel->findByUser($this->userId));
    }
}
