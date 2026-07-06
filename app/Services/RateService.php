<?php

namespace Services;

use Models\Rate;

class RateService
{
    public function __construct(
        private Rate $rateModel,
    ) {}

    public function getAll(): array
    {
        $rates = $this->rateModel->getAll();
        $result = [];
        foreach ($rates as $r) {
            $result[] = [
                'from' => $r['from_currency'],
                'to' => $r['to_currency'],
                'rate' => (float) $r['rate'],
                'updated_at' => $r['updated_at'],
            ];
        }
        return $result;
    }

    public function updateRates(array $data): array
    {
        $eur = \Enums\Currency::EUR->value;
        $usd = \Enums\Currency::USD->value;
        $rub = \Enums\Currency::RUB->value;

        $pairs = [
            [$eur, $usd],
            [$eur, $rub],
        ];

        foreach ($pairs as [$from, $to]) {
            $key = $from . '_' . $to;
            if (isset($data[$key])) {
                $rate = (float) $data[$key];
                if ($rate <= 0) {
                    return ['success' => false, 'error' => "Rate for $from->$to must be positive"];
                }
                $this->rateModel->updateRate($from, $to, $rate);

                $reverseRate = round(1 / $rate, 6);
                $this->rateModel->updateRate($to, $from, $reverseRate);
            }
        }

        return ['success' => true];
    }
}
