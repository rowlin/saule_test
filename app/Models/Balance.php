<?php

namespace Models;

use Core\Model;

class Balance extends Model
{
    public function findByUser(int $userId): array
    {
        return $this->pdo->select(
            'SELECT * FROM balances WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
    }

    public function findUserBalance(int $userId, string $currency): ?array
    {
        return $this->pdo->selectOne(
            'SELECT * FROM balances WHERE user_id = :user_id AND currency = :currency',
            ['user_id' => $userId, 'currency' => $currency]
        );
    }

    public function updateAmount(int $userId, string $currency, float $newAmount): void
    {
        $this->pdo->update(
            'UPDATE balances SET amount = :amount WHERE user_id = :user_id AND currency = :currency',
            ['amount' => round($newAmount, 2), 'user_id' => $userId, 'currency' => $currency]
        );
    }

    public function ensureBalance(int $userId, string $currency, float $amount): void
    {
        $existing = $this->findUserBalance($userId, $currency);
        if ($existing) {
            $this->updateAmount($userId, $currency, $amount);
        } else {
            $this->pdo->insert(
                'INSERT INTO balances (user_id, currency, amount) VALUES (:user_id, :currency, :amount)',
                ['user_id' => $userId, 'currency' => $currency, 'amount' => round($amount, 2)]
            );
        }
    }

    public function addBalance(int $userId, string $currency, float $amount): void
    {
        $rounded = round($amount, 2);
        $affected = $this->pdo->update(
            'UPDATE balances SET amount = amount + :amount WHERE user_id = :user_id AND currency = :currency',
            ['amount' => $rounded, 'user_id' => $userId, 'currency' => $currency]
        );
        if ($affected === 0) {
            $this->pdo->insert(
                'INSERT INTO balances (user_id, currency, amount) VALUES (:user_id, :currency, :amount)',
                ['user_id' => $userId, 'currency' => $currency, 'amount' => $rounded]
            );
        }
    }

    public function deduct(int $userId, string $currency, float $amount): bool
    {
        $rounded = round($amount, 2);
        $affected = $this->pdo->update(
            'UPDATE balances SET amount = amount - :amount WHERE user_id = :user_id AND currency = :currency AND amount >= :amount_check',
            ['amount' => $rounded, 'amount_check' => $rounded, 'user_id' => $userId, 'currency' => $currency]
        );
        return $affected > 0;
    }

    public function findUserBalanceLock(int $userId, string $currency): ?array
    {
        return $this->pdo->selectOne(
            'SELECT * FROM balances WHERE user_id = :user_id AND currency = :currency FOR UPDATE',
            ['user_id' => $userId, 'currency' => $currency]
        );
    }
}
