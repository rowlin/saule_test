<?php

namespace Services;

class EventService
{
    private array $events = [
        [
            'id' => 1,
            'name' => 'Barcelona - Real Madrid',
            'team1_win' => 2.50,
            'draw' => 3.05,
            'team2_win' => 3.15,
        ],
        [
            'id' => 2,
            'name' => 'Liverpool - Manchester United',
            'team1_win' => 1.80,
            'draw' => 3.40,
            'team2_win' => 4.50,
        ],
        [
            'id' => 3,
            'name' => 'Juventus - AC Milan',
            'team1_win' => 2.10,
            'draw' => 3.20,
            'team2_win' => 3.80,
        ],
        [
            'id' => 4,
            'name' => 'Bayern Munich - Borussia Dortmund',
            'team1_win' => 1.45,
            'draw' => 4.00,
            'team2_win' => 6.50,
        ],
        [
            'id' => 5,
            'name' => 'PSG - Marseille',
            'team1_win' => 1.65,
            'draw' => 3.75,
            'team2_win' => 5.20,
        ],
        [
            'id' => 6,
            'name' => 'Ajax - PSV Eindhoven',
            'team1_win' => 2.20,
            'draw' => 3.30,
            'team2_win' => 3.40,
        ],
    ];

    public function getAll(): array
    {
        return $this->events;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->events as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }
        return null;
    }
}
