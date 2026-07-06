<?php

namespace Services;

use Enums\Currency;
use Models\Balance;
use Models\Rate;
use Models\User;

class UserService
{
    public function __construct(
        private User $userModel,
        private Balance $balanceModel,
        private Rate $rateModel,
    ) {}

    public function getProfile(int $userId): array
    {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return [];
        }

        unset($user['password']);
        $user['contacts'] = $this->userModel->getContacts($userId);

        $currency = $user['default_currency'];
        $balance = $this->balanceModel->findUserBalance($userId, $currency);
        $amount = $balance ? (float) $balance['amount'] : 0;

        $rateMap = $this->rateModel->getRateMap();

        $rate = $this->rateModel->getRate(Currency::EUR->value, $currency);
        if (!$rate) {
            $reverseRate = $this->rateModel->getRate($currency, Currency::EUR->value);
            $rate = $reverseRate ? round(1 / $reverseRate, 6) : null;
        }
        $maxBet = $rate ? (int) round(MAX_BET_AMOUNT * $rate) : MAX_BET_AMOUNT;

        $user['balance'] = [
            'currency' => $currency,
            'amount' => $amount,
            'converted' => $this->rateModel->convert($amount, $currency, $rateMap),
        ];
        $user['max_bet'] = $maxBet;

        return $user;
    }
}
