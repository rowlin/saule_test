<?php

namespace Models;

use Core\Model;

class Event extends Model
{
    public function getAll(): array
    {
        return $this->pdo->select('SELECT * FROM events ORDER BY created_at DESC');
    }

    public function findById(int $id): ?array
    {
        return $this->pdo->selectOne('SELECT * FROM events WHERE id = :id', ['id' => $id]);
    }

    public function findByName(string $name): ?array
    {
        return $this->pdo->selectOne('SELECT * FROM events WHERE name = :name', ['name' => $name]);
    }

    public function create(array $data): int
    {
        return $this->pdo->insert(
            'INSERT INTO events (name, team1_win, draw, team2_win) VALUES (:name, :team1_win, :draw, :team2_win)',
            [
                'name' => $data['name'],
                'team1_win' => $data['team1_win'],
                'draw' => $data['draw'],
                'team2_win' => $data['team2_win'],
            ]
        );
    }
}
