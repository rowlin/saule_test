<?php

namespace Services;

use Models\Event;

class EventService
{
    private Event $eventModel;

    public function __construct(?Event $eventModel = null)
    {
        $this->eventModel = $eventModel ?? new Event();
    }

    public function getAll(): array
    {
        return $this->eventModel->getAll();
    }

    public function findById(int $id): ?array
    {
        return $this->eventModel->findById($id);
    }

    public function createEvent(string $name, float $team1Win, float $draw, float $team2Win): array
    {
        $existing = $this->eventModel->findByName($name);
        if ($existing) {
            return ['success' => false, 'error' => 'Event with this name already exists'];
        }

        $id = $this->eventModel->create([
            'name' => $name,
            'team1_win' => round($team1Win, 2),
            'draw' => round($draw, 2),
            'team2_win' => round($team2Win, 2),
        ]);

        return ['success' => true, 'id' => $id];
    }
}
