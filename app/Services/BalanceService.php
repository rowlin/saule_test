<?php

namespace Services;

use Enums\Currency;
use Models\Balance;
use Models\BalanceLog;
use Models\Rate;
use Models\User;

class BalanceService
{
    public function __construct(
        private Balance $balanceModel,
        private Rate $rateModel,
        private User $userModel,
        private BalanceLog $logModel,
    ) {}

    public function getUserBalance(int $userId): array
    {
        $currency = $this->userModel->getDefaultCurrency($userId);
        $balance = $this->balanceModel->findUserBalance($userId, $currency);

        $amount = $balance ? (float) $balance['amount'] : 0;
        $rateMap = $this->rateModel->getRateMap();

        return [
            'currency' => $currency,
            'amount' => $amount,
            'converted' => $this->rateModel->convert($amount, $currency, $rateMap),
        ];
    }

    public function changeCurrency(int $userId, string $newCurrency): array
    {
        if (!Currency::isValid($newCurrency)) {
            return ['success' => false, 'error' => 'Invalid currency'];
        }

        $oldCurrency = $this->userModel->getDefaultCurrency($userId);

        if ($oldCurrency === $newCurrency) {
            return ['success' => true, 'message' => 'Already using this currency'];
        }

        $balance = $this->balanceModel->findUserBalance($userId, $oldCurrency);
        $amount = $balance ? (float) $balance['amount'] : 0;

        $rate = $this->rateModel->getRate($oldCurrency, $newCurrency);
        if (!$rate) {
            return ['success' => false, 'error' => 'Conversion rate not available'];
        }

        $newAmount = round($amount * $rate, 2);

        $this->logModel->log(
            userId: $userId,
            action: 'currency_change',
            currency: $oldCurrency,
            amount: 0,
            balanceBefore: $amount,
            balanceAfter: $newAmount,
            note: "Currency changed from $oldCurrency to $newCurrency (rate: $rate)",
        );

        $this->balanceModel->ensureBalance($userId, $newCurrency, $newAmount);
        $this->balanceModel->updateAmount($userId, $oldCurrency, 0);
        $this->userModel->updateDefaultCurrency($userId, $newCurrency);

        return [
            'success' => true,
            'old_currency' => $oldCurrency,
            'new_currency' => $newCurrency,
            'old_amount' => $amount,
            'new_amount' => $newAmount,
        ];
    }

    private function logBalanceChange(int $userId, string $action, string $currency, float $amount, float $balanceBefore, float $balanceAfter, ?int $adminId = null, string $note = ''): void
    {
        $this->logModel->log(
            userId: $userId,
            action: $action,
            currency: $currency,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            adminId: $adminId,
            note: $note,
        );
    }
}
