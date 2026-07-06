<?php

namespace Services;

use Enums\Currency;
use Models\Balance;
use Models\BalanceLog;
use Models\Rate;
use Models\User;

class AdminService
{
    public function __construct(
        private User $userModel,
        private Balance $balanceModel,
        private BalanceLog $logModel,
        private Rate $rateModel,
    ) {}

    public function getUsers(): array
    {
        $users = $this->userModel->getAll();
        $result = [];
        foreach ($users as $user) {
            $user['is_admin'] = (bool) $user['is_admin'];
            $result[] = $user;
        }
        return $result;
    }

    public function setUserBalance(int $adminId, int $userId, string $currency, float $amount): array
    {
        if (!Currency::isValid($currency)) {
            return ['success' => false, 'error' => 'Invalid currency'];
        }

        if ($amount < 0) {
            return ['success' => false, 'error' => 'Balance cannot be negative'];
        }

        $eur = \Enums\Currency::EUR->value;

        $eurAmount = $amount;
        if ($currency !== $eur) {
            $rate = $this->rateModel->getRate($currency, $eur);
            if (!$rate) {
                $reverseRate = $this->rateModel->getRate($eur, $currency);
                if ($reverseRate) {
                    $rate = round(1 / $reverseRate, 6);
                } else {
                    return ['success' => false, 'error' => "Conversion rate from $currency to EUR not available"];
                }
            }
            $eurAmount = round($amount * $rate, 2);
        }

        $current = $this->balanceModel->findUserBalance($userId, $eur);
        $balanceBefore = $current ? (float) $current['amount'] : 0;

        $this->balanceModel->ensureBalance($userId, $eur, $eurAmount);
        if ($currency !== $eur) {
            $this->balanceModel->ensureBalance($userId, $currency, $amount);
        }
        $this->userModel->updateDefaultCurrency($userId, $currency);

        $balanceAfter = $eurAmount;

        $this->logModel->log(
            userId: $userId,
            action: 'balance_set',
            currency: $currency,
            amount: $balanceAfter - $balanceBefore,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            adminId: $adminId,
            note: "Admin set balance ({$currency} converted to EUR)",
        );

        return ['success' => true, 'eur_amount' => $eurAmount];
    }

    public function updateUserBalance(int $adminId, int $userId, string $currency, float $amount): array
    {
        if (!Currency::isValid($currency)) {
            return ['success' => false, 'error' => 'Invalid currency'];
        }

        $current = $this->balanceModel->findUserBalance($userId, $currency);
        $balanceBefore = $current ? (float) $current['amount'] : 0;

        $this->balanceModel->addBalance($userId, $currency, $amount);

        $balanceAfter = $balanceBefore + $amount;

        $this->logModel->log(
            userId: $userId,
            action: 'balance_update',
            currency: $currency,
            amount: round($amount, 2),
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            adminId: $adminId,
            note: "Admin adjusted balance",
        );

        return ['success' => true];
    }
}
