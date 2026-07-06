<?php

namespace Models;

use Core\Model;
use Enums\Currency;

class Rate extends Model
{
    public function getAll(): array
    {
        return $this->pdo->select('SELECT * FROM currency_rates ORDER BY from_currency, to_currency');
    }

    public function getRateMap(): array
    {
        $rates = [];
        foreach ($this->getAll() as $r) {
            $rates[$r['from_currency'] . '_' . $r['to_currency']] = (float) $r['rate'];
        }
        return $rates;
    }

    public function convert(float $amount, string $currency, array $rateMap): array
    {
        $converted = [];
        foreach (Currency::targets($currency) as $target) {
            $key = $currency . '_' . $target;
            if (isset($rateMap[$key])) {
                $converted[$target] = round($amount * $rateMap[$key], 2);
            }
        }
        return $converted;
    }

    public function getRate(string $from, string $to): ?float
    {
        $row = $this->pdo->selectOne(
            'SELECT rate FROM currency_rates WHERE from_currency = :from AND to_currency = :to',
            ['from' => $from, 'to' => $to]
        );
        return $row ? (float) $row['rate'] : null;
    }

    public function updateRate(string $from, string $to, float $rate): void
    {
        $this->pdo->update(
            'UPDATE currency_rates SET rate = :rate WHERE from_currency = :from AND to_currency = :to',
            ['rate' => $rate, 'from' => $from, 'to' => $to]
        );
    }
}
