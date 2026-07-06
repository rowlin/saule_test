<?php

namespace Models;

use Core\Model;

class BalanceLog extends Model
{
    public function log(
        int $userId,
        string $action,
        string $currency,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        ?int $adminId = null,
        ?string $note = null,
    ): int {
        return $this->pdo->insert(
            'INSERT INTO balance_logs (user_id, admin_id, action, currency, amount, balance_before, balance_after, note)
             VALUES (:user_id, :admin_id, :action, :currency, :amount, :balance_before, :balance_after, :note)',
            [
                'user_id' => $userId,
                'admin_id' => $adminId,
                'action' => $action,
                'currency' => $currency,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'note' => $note,
            ]
        );
    }

    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->pdo->select(
            'SELECT l.*, u.name as user_name
             FROM balance_logs l
             JOIN users u ON l.user_id = u.id
             WHERE l.user_id = :user_id
             ORDER BY l.created_at DESC
             LIMIT :limit',
            ['user_id' => $userId, 'limit' => $limit]
        );
    }

    public function findAll(int $limit = 100): array
    {
        return $this->pdo->select(
            'SELECT l.*, u.name as user_name, a.name as admin_name
             FROM balance_logs l
             JOIN users u ON l.user_id = u.id
             LEFT JOIN users a ON l.admin_id = a.id
             ORDER BY l.created_at DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }

    public function getBalanceBefore(int $userId, string $currency): float
    {
        $row = $this->pdo->selectOne(
            'SELECT balance_after FROM balance_logs
             WHERE user_id = :user_id AND currency = :currency
             ORDER BY id DESC LIMIT 1',
            ['user_id' => $userId, 'currency' => $currency]
        );
        if ($row) {
            return (float) $row['balance_after'];
        }
        $balanceModel = new Balance();
        $bal = $balanceModel->findUserBalance($userId, $currency);
        return $bal ? (float) $bal['amount'] : 0;
    }
}
