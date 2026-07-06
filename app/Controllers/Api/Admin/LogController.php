<?php

namespace Controllers\Api\Admin;

use Core\Controller;
use Models\BalanceLog;

class LogController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function adminLogs(): void
    {
        $logModel = new BalanceLog();
        $userId = (int) ($_GET['user_id'] ?? 0);

        if ($userId) {
            $this->jsonResponse($logModel->findByUser($userId));
        } else {
            $this->jsonResponse($logModel->findAll());
        }
    }
}
