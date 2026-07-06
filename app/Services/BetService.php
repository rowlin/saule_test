<?php

namespace Services;

use Core\Db;
use Enums\Currency;
use Models\Bet;
use Models\Balance;
use Models\BalanceLog;
use Models\Rate;
use Models\User;

class BetService
{
    private Db $db;

    public function __construct(
        private Bet $betModel,
        private Balance $balanceModel,
        private User $userModel,
        private BalanceLog $logModel,
        private Rate $rateModel,
    ) {
        $config = require __DIR__ . '/../config.php';
        $dbCfg = $config['db'];
        $this->db = new Db(dbhost: $dbCfg['host'], dbname: $dbCfg['name'], username: $dbCfg['user'], password: $dbCfg['password'], driver: $dbCfg['driver'] ?? 'mysql');
    }

    public function placeBet(int $userId, string $eventName, string $outcome, float $odds, float $amount, ?int $adminId = null): array
    {
        $currency = $this->userModel->getDefaultCurrency($userId);
        $maxAmount = $this->resolveMaxBet($currency);

        if ($amount < MIN_BET_AMOUNT || $amount > $maxAmount) {
            return ['success' => false, 'error' => 'Bet amount must be between ' . MIN_BET_AMOUNT . ' and ' . $maxAmount];
        }

        if ($odds < MIN_BET_ODDS || $odds > MAX_BET_ODDS) {
            return ['success' => false, 'error' => 'Invalid odds value'];
        }

        if (!in_array($outcome, BET_OUTCOMES)) {
            return ['success' => false, 'error' => 'Invalid outcome'];
        }

        $existing = $this->betModel->findByUserAndEvent($userId, $eventName);
        if (!empty($existing)) {
            return ['success' => false, 'error' => 'You already placed a bet on this event'];
        }

        return $this->executeInTransaction(function () use ($userId, $eventName, $outcome, $odds, $amount, $currency, $adminId) {
            $balance = $this->balanceModel->findUserBalanceLock($userId, $currency);
            if (!$balance || (float) $balance['amount'] < $amount) {
                return ['success' => false, 'error' => 'Insufficient balance'];
            }

            $balanceBefore = (float) $balance['amount'];

            $this->balanceModel->deduct($userId, $currency, $amount);

            $betId = $this->betModel->create([
                'user_id' => $userId,
                'event_name' => $eventName,
                'outcome' => $outcome,
                'odds' => $odds,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            $balanceAfter = $balanceBefore - $amount;

            $this->logModel->log(
                userId: $userId,
                action: 'bet_placed',
                currency: $currency,
                amount: -$amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                adminId: $adminId,
                note: "Bet #$betId on $eventName",
            );

            return ['success' => true, 'bet_id' => $betId];
        });
    }

    public function settleBet(int $betId, string $result, ?int $adminId = null): array
    {
        if (!in_array($result, ['won', 'lost'])) {
            return ['success' => false, 'error' => 'Result must be won or lost'];
        }

        return $this->executeInTransaction(function () use ($betId, $result, $adminId) {
            $bet = $this->betModel->findById($betId);
            if (!$bet) {
                return ['success' => false, 'error' => 'Bet not found'];
            }

            if ($bet['status'] !== 'pending') {
                return ['success' => false, 'error' => 'Bet already settled'];
            }

            $currency = $bet['currency'];
            $userId = (int) $bet['user_id'];

            if ($result === 'won') {
                $currentBal = $this->balanceModel->findUserBalanceLock($userId, $currency);
                $balanceBefore = $currentBal ? (float) $currentBal['amount'] : 0;

                $payout = round((float) $bet['amount'] * (float) $bet['odds'], 2);
                $this->balanceModel->addBalance($userId, $currency, $payout);

                $balanceAfter = $balanceBefore + $payout;
                $this->logModel->log(
                    userId: $userId,
                    action: 'bet_won',
                    currency: $currency,
                    amount: $payout,
                    balanceBefore: $balanceBefore,
                    balanceAfter: $balanceAfter,
                    adminId: $adminId,
                    note: "Bet #$betId settled as won ({$bet['event_name']})",
                );
            } else {
                $currentBal = $this->balanceModel->findUserBalanceLock($userId, $currency);
                $balanceBefore = $currentBal ? (float) $currentBal['amount'] : 0;

                $this->logModel->log(
                    userId: $userId,
                    action: 'bet_lost',
                    currency: $currency,
                    amount: 0,
                    balanceBefore: $balanceBefore,
                    balanceAfter: $balanceBefore,
                    adminId: $adminId,
                    note: "Bet #$betId settled as lost ({$bet['event_name']})",
                );
            }

            $this->betModel->settle($betId, $result);
            return ['success' => true];
        });
    }

    private function resolveMaxBet(string $currency): int
    {
        if ($currency === Currency::EUR->value) {
            return MAX_BET_AMOUNT;
        }

        $rate = $this->rateModel->getRate(Currency::EUR->value, $currency);
        if (!$rate) {
            $reverseRate = $this->rateModel->getRate($currency, Currency::EUR->value);
            if ($reverseRate) {
                $rate = round(1 / $reverseRate, 6);
            }
        }

        return $rate ? (int) round(MAX_BET_AMOUNT * $rate) : MAX_BET_AMOUNT;
    }

    private function executeInTransaction(callable $fn): array
    {
        try {
            $this->db->beginTransaction();
            $result = $fn();
            if ($result['success']) {
                $this->db->commit();
            } else {
                $this->db->rollback();
            }
            return $result;
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
}
