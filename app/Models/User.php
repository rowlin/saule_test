<?php

namespace Models;

use Core\Model;
use Enums\Currency;

class User extends Model
{
    public function findByLogin(string $login): ?array
    {
        return $this->pdo->selectOne(
            'SELECT * FROM users WHERE login = :login',
            ['login' => $login]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->pdo->selectOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );
    }

    public function getContacts(int $userId): array
    {
        return $this->pdo->select(
            'SELECT * FROM user_contacts WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
    }

    public function addContact(int $userId, string $type, string $value): int
    {
        $existing = $this->pdo->select(
            'SELECT * FROM user_contacts WHERE user_id = :user_id AND type = :type',
            ['user_id' => $userId, 'type' => $type]
        );
        if (count($existing) >= 2) {
            throw new \RuntimeException("Maximum 2 $type contacts allowed");
        }

        return $this->pdo->insert(
            'INSERT INTO user_contacts (user_id, type, value) VALUES (:user_id, :type, :value)',
            ['user_id' => $userId, 'type' => $type, 'value' => $value]
        );
    }

    public function deleteContact(int $contactId, int $userId): void
    {
        $this->pdo->delete(
            'DELETE FROM user_contacts WHERE id = :id AND user_id = :user_id',
            ['id' => $contactId, 'user_id' => $userId]
        );
    }

    public function getAll(): array
    {
        return $this->pdo->select(
            'SELECT u.id, u.login, u.name, u.status, u.is_admin, u.default_currency, COALESCE(b.amount, 0) as balance FROM users u LEFT JOIN balances b ON b.user_id = u.id AND b.currency = u.default_currency ORDER BY u.name'
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->pdo->update(
            'UPDATE users SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }

    public function getDefaultCurrency(int $userId): string
    {
        $user = $this->findById($userId);
        return $user['default_currency'] ?? Currency::default();
    }

    public function updateDefaultCurrency(int $userId, string $currency): void
    {
        $this->pdo->update(
            'UPDATE users SET default_currency = :currency WHERE id = :id',
            ['currency' => $currency, 'id' => $userId]
        );
    }
}
