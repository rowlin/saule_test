<?php

namespace Controllers\Api;

use Core\Controller;
use Models\BalanceLog;

class LogController extends Controller
{
    public function logs(): void
    {
        $userId = $this->requireAuth();
        $logModel = new BalanceLog();
        $this->jsonResponse($logModel->findByUser($userId));
    }

    public function adminLogs(): void
    {
        $this->requireAdmin();

        $logModel = new BalanceLog();
        $userId = (int) ($_GET['user_id'] ?? 0);

        if ($userId) {
            $this->jsonResponse($logModel->findByUser($userId));
        } else {
            $this->jsonResponse($logModel->findAll());
        }
    }
}
