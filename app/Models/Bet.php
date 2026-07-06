<?php

namespace Models;

use Core\Model;

class Bet extends Model
{
    public function create(array $data): int
    {
        return $this->pdo->insert(
            'INSERT INTO bets (user_id, event_name, outcome, odds, amount, currency, status)
             VALUES (:user_id, :event_name, :outcome, :odds, :amount, :currency, :status)',
            [
                'user_id' => $data['user_id'],
                'event_name' => $data['event_name'],
                'outcome' => $data['outcome'],
                'odds' => $data['odds'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => 'pending',
            ]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->pdo->selectOne(
            'SELECT * FROM bets WHERE id = :id',
            ['id' => $id]
        );
    }

    public function findByUser(int $userId): array
    {
        return $this->pdo->select(
            'SELECT * FROM bets WHERE user_id = :user_id AND event_name != :test_event ORDER BY created_at DESC',
            ['user_id' => $userId, 'test_event' => 'Test Event']
        );
    }

    public function findByUserAndEvent(int $userId, string $eventName): array
    {
        return $this->pdo->select(
            'SELECT * FROM bets WHERE user_id = :user_id AND event_name = :event_name',
            ['user_id' => $userId, 'event_name' => $eventName]
        );
    }

    public function findByStatus(string $status): array
    {
        return $this->pdo->select(
            'SELECT b.*, u.name as user_name, u.login as user_login
             FROM bets b JOIN users u ON b.user_id = u.id
             WHERE b.status = :status AND b.event_name != :test_event ORDER BY b.created_at DESC',
            ['status' => $status, 'test_event' => 'Test Event']
        );
    }

    public function findAllWithUsers(): array
    {
        return $this->pdo->select(
            'SELECT b.*, u.name as user_name, u.login as user_login
             FROM bets b JOIN users u ON b.user_id = u.id
             WHERE b.event_name != :test_event
             ORDER BY b.created_at DESC',
            ['test_event' => 'Test Event']
        );
    }

    public function settle(int $id, string $status): void
    {
        $this->pdo->update(
            'UPDATE bets SET status = :status, settled_at = NOW() WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }
}
