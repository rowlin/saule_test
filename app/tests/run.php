<?php

require __DIR__ . '/bootstrap.php';

function test(string $name, callable $fn): void
{
    try {
        $fn();
        echo "  PASS: {$name}\n";
    } catch (\Throwable $e) {
        echo "  FAIL: {$name} - {$e->getMessage()}\n";
    }
}

function assertTrue(bool $condition, string $msg = ''): void
{
    if (!$condition) {
        throw new \RuntimeException($msg ?: 'Expected true');
    }
}

function assertEquals(mixed $expected, mixed $actual, string $msg = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($msg ?: sprintf("Expected %s, got %s", var_export($expected, true), var_export($actual, true)));
    }
}

echo "Testing Core classes...\n";

test('Db connection', function () {
    $config = require __DIR__ . '/../config.php';
    $dbCfg = $config['db'];
    $db = new \Core\Db(dbhost: $dbCfg['host'], dbname: $dbCfg['name'], username: $dbCfg['user'], password: $dbCfg['password']);
    $result = $db->select('SELECT 1 as val');
    assertTrue(count($result) > 0, 'SELECT 1 failed');
});

test('User model - findByLogin', function () {
    $user = new \Models\User();
    $found = $user->findByLogin('john_doe');
    assertTrue($found !== null, 'User not found');
    assertEquals('John Doe', $found['name']);
});

test('User model - findById', function () {
    $user = new \Models\User();
    $found = $user->findById(1);
    assertTrue($found !== null, 'User not found');
    assertEquals('john_doe', $found['login']);
});

test('User model - getAll', function () {
    $user = new \Models\User();
    $all = $user->getAll();
    assertTrue(count($all) >= 2, 'Expected at least 2 users');
});

function getJohnDoeId(): int
{
    $user = new \Models\User();
    $found = $user->findByLogin('john_doe');
    if (!$found) throw new \RuntimeException('john_doe not found');
    return (int) $found['id'];
}

function getAdminId(): int
{
    $user = new \Models\User();
    $found = $user->findByLogin('admin');
    if (!$found) throw new \RuntimeException('admin not found');
    return (int) $found['id'];
}

test('User model - getContacts', function () {
    $user = new \Models\User();
    $contacts = $user->getContacts(getJohnDoeId());
    assertTrue(is_array($contacts), 'Contacts should be an array');
});

test('Balance model - findByUser', function () {
    $balance = new \Models\Balance();
    $bals = $balance->findByUser(getJohnDoeId());
    assertTrue(count($bals) > 0, 'No balances found');
});

test('Balance model - ensureBalance', function () {
    $balance = new \Models\Balance();
    $bal = $balance->findUserBalance(3, 'EUR');
    assertTrue($bal !== null, 'EUR balance not found');
});

test('Balance model - ensureBalance creates new currency', function () {
    $balance = new \Models\Balance();
    $userId = getAdminId();
    $balance->ensureBalance($userId, 'USD', 500);
    $bal = $balance->findUserBalance($userId, 'USD');
    assertEquals(500.0, (float) $bal['amount']);
    $balance->ensureBalance($userId, 'USD', 0);
});

test('Balance model - currencies are independent', function () {
    $balance = new \Models\Balance();
    $userId = getAdminId();
    $balance->ensureBalance($userId, 'EUR', 100);
    $balance->ensureBalance($userId, 'USD', 200);
    $eur = $balance->findUserBalance($userId, 'EUR');
    $usd = $balance->findUserBalance($userId, 'USD');
    assertEquals(100.0, (float) $eur['amount']);
    assertEquals(200.0, (float) $usd['amount']);
});

test('Balance model - findByUser returns all currencies', function () {
    $balance = new \Models\Balance();
    $userId = getAdminId();
    $balance->ensureBalance($userId, 'EUR', 50);
    $balance->ensureBalance($userId, 'USD', 75);
    $bals = $balance->findByUser($userId);
    $currencies = [];
    foreach ($bals as $b) {
        $currencies[$b['currency']] = (float) $b['amount'];
    }
    assertTrue(isset($currencies['EUR']), 'Should have EUR');
    assertTrue(isset($currencies['USD']), 'Should have USD');
    assertEquals(50.0, $currencies['EUR']);
    assertEquals(75.0, $currencies['USD']);
});

test('Bet model - create and find', function () {
    $bet = new \Models\Bet();
    $id = $bet->create([
        'user_id' => 1,
        'event_name' => 'Barcelona - Real Madrid',
        'outcome' => 'team1_win',
        'odds' => 2.50,
        'amount' => 100,
        'currency' => 'EUR',
    ]);
    assertTrue($id > 0, 'Bet ID should be positive');

    $found = $bet->findById($id);
    assertTrue($found !== null, 'Bet not found');
    assertEquals('Barcelona - Real Madrid', $found['event_name']);
    assertEquals('pending', $found['status']);
});

test('Bet model - findByUser', function () {
    $bet = new \Models\Bet();
    $bets = $bet->findByUser(getJohnDoeId());
    assertTrue(count($bets) > 0, 'No bets found for user 1');
});

test('Balance model - EUR balance exists', function () {
    $balance = new \Models\Balance();
    $userId = getJohnDoeId();
    $bals = $balance->findByUser($userId);
    $hasEur = false;
    foreach ($bals as $b) {
        if ($b['currency'] === 'EUR') $hasEur = true;
    }
    assertTrue($hasEur, 'EUR balance should exist for john_doe');
});

test('EventService - getAll', function () {
    $svc = new \Services\EventService();
    $events = $svc->getAll();
    assertTrue(count($events) > 0, 'No events');
    assertEquals('Barcelona - Real Madrid', $events[0]['name']);
});

test('EventService - findById', function () {
    $svc = new \Services\EventService();
    $event = $svc->findById(1);
    assertTrue($event !== null, 'Event 1 not found');
    assertEquals(2.50, $event['team1_win']);
});

test('User model - addContact and deleteContact', function () {
    $userId = getJohnDoeId();
    $user = new \Models\User();
    $id = $user->addContact($userId, 'email', 'test_add@example.com');
    assertTrue($id > 0, 'Contact should be created');

    $contacts = $user->getContacts($userId);
    $found = false;
    foreach ($contacts as $c) {
        if ($c['value'] === 'test_add@example.com') {
            $found = true;
            $user->deleteContact((int) $c['id'], $userId);
            break;
        }
    }
    assertTrue($found, 'New contact should be in list');

    $contacts2 = $user->getContacts($userId);
    $found2 = false;
    foreach ($contacts2 as $c) {
        if ($c['value'] === 'test_add@example.com') {
            $found2 = true;
            break;
        }
    }
    assertTrue(!$found2, 'Contact should be deleted');
});

test('User model - max 2 contacts per type', function () {
    $userId = getJohnDoeId();
    $user = new \Models\User();

    $existing = $user->getContacts($userId);
    $phoneCount = 0;
    foreach ($existing as $c) {
        if ($c['type'] === 'phone') $phoneCount++;
    }

    if ($phoneCount < 2) {
        $user->addContact($userId, 'phone', '+71111111111');
        $phoneCount++;
    }

    if ($phoneCount >= 2) {
        $threwException = false;
        try {
            $user->addContact($userId, 'phone', '+72222222222');
        } catch (\RuntimeException $e) {
            $threwException = true;
            assertTrue(str_contains($e->getMessage(), 'Maximum 2'), 'Should mention max 2');
        }
        assertTrue($threwException, 'Should throw when adding 3rd phone');
    }
});

test('User model - default currency', function () {
    $user = new \Models\User();
    $found = $user->findByLogin('john_doe');
    assertEquals('EUR', $found['default_currency']);
});

test('Rate model - getAll', function () {
    $rate = new \Models\Rate();
    $rates = $rate->getAll();
    assertTrue(count($rates) >= 2, 'Expected at least 2 rates');
});

test('Rate model - getRate', function () {
    $rate = new \Models\Rate();
    $r = $rate->getRate('EUR', 'USD');
    assertTrue($r !== null, 'EUR->USD rate not found');
    assertTrue($r > 0, 'Rate should be positive');
});

test('Rate model - updateRate', function () {
    $rate = new \Models\Rate();
    $old = $rate->getRate('EUR', 'USD');
    $rate->updateRate('EUR', 'USD', 1.25);
    $new = $rate->getRate('EUR', 'USD');
    assertEquals(1.25, $new);
    $rate->updateRate('EUR', 'USD', $old);
});

test('Auth session', function () {
    $session = new \Core\SessionManager();
    $session->set('test_key', 'test_value');
    assertEquals('test_value', $session->get('test_key'));
    assertTrue($session->has('test_key'));
    $session->remove('test_key');
    assertTrue(!$session->has('test_key'));
});

test('Password verification', function () {
    $userModel = new \Models\User();
    $user = $userModel->findByLogin('john_doe');
    assertTrue(password_verify('password', $user['password']), 'Password should match');
    assertTrue(!password_verify('wrong', $user['password']), 'Wrong password should not match');
});

echo "\nTesting DTOs...\n";

test('PlaceBetDto creation', function () {
    $dto = new \Dto\PlaceBetDto(
        eventName: 'Test',
        outcome: 'team1_win',
        odds: '2.5',
        amount: '100',
    );
    assertEquals('Test', $dto->eventName);
    assertEquals('2.5', $dto->odds);
});

test('SettleBetDto creation', function () {
    $dto = new \Dto\SettleBetDto(betId: 1, result: 'won');
    assertEquals(1, $dto->betId);
    assertEquals('won', $dto->result);
});

test('UpdateBalanceDto creation', function () {
    $dto = new \Dto\UpdateBalanceDto(userId: 1, currency: 'USD', amount: '500');
    assertEquals(1, $dto->userId);
    assertEquals('500', $dto->amount);
});

test('SetBalanceDto creation', function () {
    $dto = new \Dto\SetBalanceDto(userId: 2, currency: 'EUR', balance: '1000');
    assertEquals(2, $dto->userId);
    assertEquals('EUR', $dto->currency);
    assertEquals('1000', $dto->balance);
});

test('ChangeCurrencyDto creation', function () {
    $dto = new \Dto\ChangeCurrencyDto(currency: 'USD');
    assertEquals('USD', $dto->currency);
});

echo "\nTesting services...\n";

function makeBetService(): \Services\BetService
{
    return new \Services\BetService(
        new \Models\Bet(),
        new \Models\Balance(),
        new \Models\User(),
        new \Models\BalanceLog(),
        new \Models\Rate(),
    );
}

test('BetService - validation (amount under 1)', function () {
    $result = makeBetService()->placeBet(1, 'Test', 'team1_win', 2.0, 0.5);
    assertEquals(false, $result['success']);
    assertTrue(str_contains($result['error'] ?? '', '1 and 500'));
});

test('BetService - validation (amount over 500)', function () {
    $result = makeBetService()->placeBet(1, 'Test', 'team1_win', 2.0, 501);
    assertEquals(false, $result['success']);
    assertTrue(str_contains($result['error'] ?? '', '1 and 500'));
});

test('BetService - validation (invalid odds)', function () {
    $result = makeBetService()->placeBet(1, 'Test', 'team1_win', 0.5, 100);
    assertEquals(false, $result['success']);
    assertTrue(str_contains($result['error'] ?? '', 'odds'));
});

test('BetService - validation (invalid outcome)', function () {
    $result = makeBetService()->placeBet(1, 'Test', 'invalid', 2.0, 100);
    assertEquals(false, $result['success']);
    assertTrue(str_contains($result['error'] ?? '', 'outcome'));
});

test('BetService - insufficient balance', function () {
    $result = makeBetService()->placeBet(1, 'Test', 'team1_win', 2.0, 999999);
    assertEquals(false, $result['success']);
});

test('BetService - duplicate bet on same event rejected', function () {
    $svc = makeBetService();
    $userId = getJohnDoeId();
    $balanceModel = new \Models\Balance();
    $balanceModel->ensureBalance($userId, 'EUR', 500);

    $first = $svc->placeBet($userId, 'Duplicate Test Event', 'team1_win', 2.0, 50);
    assertEquals(true, $first['success'], 'First bet should succeed');

    $second = $svc->placeBet($userId, 'Duplicate Test Event', 'draw', 3.0, 30);
    assertEquals(false, $second['success'], 'Second bet on same event should fail');
    assertTrue(str_contains($second['error'] ?? '', 'already placed'), 'Error should mention already placed');

    $balanceModel->ensureBalance($userId, 'EUR', 500);
});

test('BetService - settleBet (invalid result)', function () {
    $result = makeBetService()->settleBet(1, 'invalid');
    assertEquals(false, $result['success']);
    assertTrue(str_contains($result['error'] ?? '', 'won or lost'));
});

test('BetService - won bet pays amount * odds', function () {
    $svc = makeBetService();
    $userId = getJohnDoeId();
    $balanceModel = new \Models\Balance();
    $betModel = new \Models\Bet();

    $balanceModel->ensureBalance($userId, 'EUR', 500);

    $placeResult = $svc->placeBet($userId, 'Test Event', 'team1_win', 2.50, 100);
    assertEquals(true, $placeResult['success']);
    $betId = $placeResult['bet_id'];

    $balBefore = $balanceModel->findUserBalance($userId, 'EUR');
    $amountBefore = $balBefore ? (float) $balBefore['amount'] : 0;

    $settleResult = $svc->settleBet($betId, 'won');
    assertEquals(true, $settleResult['success']);

    $expectedPayout = 100 * 2.50;
    $balAfter = $balanceModel->findUserBalance($userId, 'EUR');
    $amountAfter = $balAfter ? (float) $balAfter['amount'] : 0;
    assertEquals($amountBefore + $expectedPayout, $amountAfter, 'Balance should increase by 100 * 2.50 = 250');

    $bet = $betModel->findById($betId);
    assertEquals('won', $bet['status']);

    $balanceModel->ensureBalance($userId, 'EUR', 500);
});

test('BetService - lost bet does not add balance', function () {
    $svc = makeBetService();
    $userId = getJohnDoeId();
    $balanceModel = new \Models\Balance();
    $betModel = new \Models\Bet();

    $balanceModel->ensureBalance($userId, 'EUR', 500);

    $placeResult = $svc->placeBet($userId, 'Lost Test Event', 'draw', 3.20, 50);
    assertEquals(true, $placeResult['success']);
    $betId = $placeResult['bet_id'];

    $balBefore = $balanceModel->findUserBalance($userId, 'EUR');
    $amountBefore = $balBefore ? (float) $balBefore['amount'] : 0;

    $settleResult = $svc->settleBet($betId, 'lost');
    assertEquals(true, $settleResult['success']);

    $balAfter = $balanceModel->findUserBalance($userId, 'EUR');
    $amountAfter = $balAfter ? (float) $balAfter['amount'] : 0;
    assertEquals($amountBefore, $amountAfter, 'Balance should remain unchanged');

    $bet = $betModel->findById($betId);
    assertEquals('lost', $bet['status']);

    $balanceModel->ensureBalance($userId, 'EUR', 500);
});

test('BalanceLog model - findByUser empty', function () {
    $log = new \Models\BalanceLog();
    $entries = $log->findByUser(999);
    assertEquals([], $entries);
});

test('BalanceLog model - create and find', function () {
    $userId = getJohnDoeId();
    $log = new \Models\BalanceLog();
    $id = $log->log(
        userId: $userId,
        action: 'test_action',
        currency: 'EUR',
        amount: 100,
        balanceBefore: 500,
        balanceAfter: 600,
        note: 'Test note',
    );
    assertTrue($id > 0, 'Log ID should be positive');

    $entries = $log->findByUser($userId);
    $found = false;
    foreach ($entries as $e) {
        if ((int) $e['id'] === $id) {
            $found = true;
            assertEquals('test_action', $e['action']);
            assertEquals('Test note', $e['note']);
            break;
        }
    }
    assertTrue($found, 'Created log entry should exist');
});

test('Balance model - deduct insufficient', function () {
    $balance = new \Models\Balance();
    $result = $balance->deduct(1, 'EUR', 999999999);
    assertEquals(false, $result, 'Should return false when insufficient');
});

test('Balance model - addBalance', function () {
    $adminId = getAdminId();
    $balance = new \Models\Balance();
    $before = $balance->findUserBalance($adminId, 'EUR');
    $amountBefore = $before ? (float) $before['amount'] : 0;

    $balance->addBalance($adminId, 'EUR', 50);

    $after = $balance->findUserBalance($adminId, 'EUR');
    $amountAfter = $after ? (float) $after['amount'] : 0;
    assertEquals($amountBefore + 50, $amountAfter, 'Balance should increase by 50');
});

test('Events have odds in valid range', function () {
    $svc = new \Services\EventService();
    $events = $svc->getAll();
    foreach ($events as $e) {
        assertTrue($e['team1_win'] >= 1.01 && $e['team1_win'] <= 40.00, 'team1_win odds out of range');
        assertTrue($e['draw'] >= 1.01 && $e['draw'] <= 40.00, 'draw odds out of range');
        assertTrue($e['team2_win'] >= 1.01 && $e['team2_win'] <= 40.00, 'team2_win odds out of range');
    }
});

test('Currency conversion consistency', function () {
    $rate = new \Models\Rate();
    $eurUsd = $rate->getRate('EUR', 'USD');
    $usdEur = $rate->getRate('USD', 'EUR');
    assertTrue($eurUsd !== null && $usdEur !== null, 'Rates should exist');
    $recalculated = round(1 / $eurUsd, 6);
    assertEquals($recalculated, $usdEur, 'USD->EUR should equal 1 / EUR->USD');
});

test('Balance log entries have required fields', function () {
    $adminId = getAdminId();
    $adminUser = (new \Models\User())->findById($adminId);
    $log = new \Models\BalanceLog();
    $log->log(
        userId: $adminId,
        action: 'balance_update',
        currency: 'EUR',
        amount: 100,
        balanceBefore: 500,
        balanceAfter: 600,
    );
    $entries = $log->findByUser($adminId);
    $found = false;
    foreach ($entries as $e) {
        if ($e['action'] === 'balance_update' && (float) $e['amount'] === 100.0) {
            $found = true;
            assertTrue(isset($e['user_name']), 'Entry should have user_name');
            assertEquals($adminUser['name'], $e['user_name'], 'User name should match');
            break;
        }
    }
    assertTrue($found, 'Log entry should exist');
});

test('RateService - getAll returns formatted', function () {
    $svc = new \Services\RateService(new \Models\Rate());
    $rates = $svc->getAll();
    assertTrue(count($rates) > 0, 'Should have rates');
    assertTrue(isset($rates[0]['from']), 'Should have from key');
    assertTrue(isset($rates[0]['rate']), 'Should have rate key');
    assertTrue(is_float($rates[0]['rate']), 'Rate should be float');
});

test('RateService - updateRates validates positive', function () {
    $svc = new \Services\RateService(new \Models\Rate());
    $result = $svc->updateRates(['EUR_USD' => -1]);
    assertEquals(false, $result['success']);
});

test('RateService - updateRates success', function () {
    $svc = new \Services\RateService(new \Models\Rate());
    $old = (new \Models\Rate())->getRate('EUR', 'USD');
    $result = $svc->updateRates(['EUR_USD' => 1.15]);
    assertEquals(true, $result['success']);

    $updated = (new \Models\Rate())->getRate('EUR', 'USD');
    assertEquals(1.15, $updated);

    $reverse = (new \Models\Rate())->getRate('USD', 'EUR');
    assertEquals(round(1 / 1.15, 6), $reverse);

    (new \Models\Rate())->updateRate('EUR', 'USD', $old);
    (new \Models\Rate())->updateRate('USD', 'EUR', round(1 / $old, 6));
});

test('BalanceService - getUserBalance', function () {
    $svc = new \Services\BalanceService(
        new \Models\Balance(),
        new \Models\Rate(),
        new \Models\User(),
        new \Models\BalanceLog(),
    );
    $balance = $svc->getUserBalance(getJohnDoeId());
    assertTrue(isset($balance['currency']), 'Should have currency');
    assertTrue(isset($balance['amount']), 'Should have amount');
    assertTrue(isset($balance['converted']), 'Should have converted');
    assertTrue(\Enums\Currency::isValid($balance['currency']), 'Currency should be valid');
});

test('BalanceService - changeCurrency invalid currency', function () {
    $svc = new \Services\BalanceService(
        new \Models\Balance(),
        new \Models\Rate(),
        new \Models\User(),
        new \Models\BalanceLog(),
    );
    $result = $svc->changeCurrency(getJohnDoeId(), 'GBP');
    assertEquals(false, $result['success']);
});

test('BalanceService - changeCurrency same currency', function () {
    $userId = getJohnDoeId();
    $currency = (new \Models\User())->getDefaultCurrency($userId);
    $svc = new \Services\BalanceService(
        new \Models\Balance(),
        new \Models\Rate(),
        new \Models\User(),
        new \Models\BalanceLog(),
    );
    $result = $svc->changeCurrency($userId, $currency);
    assertEquals(true, $result['success']);
    assertTrue(isset($result['message']));
});

test('AdminService - getUsers', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $users = $svc->getUsers();
    assertTrue(count($users) >= 2, 'Should have at least 2 users');
    assertTrue(is_bool($users[0]['is_admin']), 'is_admin should be bool');
    assertTrue(isset($users[0]['default_currency']), 'Should have default_currency');
});

test('AdminService - updateUserBalance invalid currency', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $result = $svc->updateUserBalance(1, 1, 'GBP', 100);
    assertEquals(false, $result['success']);
});

test('AdminService - setUserBalance invalid currency', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $result = $svc->setUserBalance(1, 1, 'GBP', 500);
    assertEquals(false, $result['success']);
});

test('AdminService - setUserBalance with EUR stores directly', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $userId = getAdminId();
    $balanceModel = new \Models\Balance();
    $userModel = new \Models\User();

    $balanceModel->ensureBalance($userId, 'EUR', 200);
    $userModel->updateDefaultCurrency($userId, 'EUR');

    $result = $svc->setUserBalance($userId, $userId, 'EUR', 999.99);
    assertEquals(true, $result['success']);
    assertEquals(999.99, $result['eur_amount']);

    $bal = $balanceModel->findUserBalance($userId, 'EUR');
    assertEquals(999.99, (float) $bal['amount'], 'EUR balance should be exactly 999.99');
    assertEquals('EUR', $userModel->getDefaultCurrency($userId), 'Default currency should be EUR');

    $balanceModel->ensureBalance($userId, 'EUR', 200);
});

test('AdminService - setUserBalance converts non-EUR to EUR', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $userId = getAdminId();
    $balanceModel = new \Models\Balance();
    $userModel = new \Models\User();

    $balanceModel->ensureBalance($userId, 'EUR', 0);
    $userModel->updateDefaultCurrency($userId, 'EUR');

    $rateModel = new \Models\Rate();
    $usdToEur = $rateModel->getRate('USD', 'EUR');
    $expectedEur = round(250 * $usdToEur, 2);

    $result = $svc->setUserBalance($userId, $userId, 'USD', 250);
    assertEquals(true, $result['success']);
    assertEquals($expectedEur, $result['eur_amount']);

    $eurBal = $balanceModel->findUserBalance($userId, 'EUR');
    assertEquals($expectedEur, (float) $eurBal['amount'], "EUR balance should be $expectedEur (250 USD converted)");
    assertEquals('USD', $userModel->getDefaultCurrency($userId), 'Default currency should be USD');

    $balanceModel->ensureBalance($userId, 'EUR', 0);
    $userModel->updateDefaultCurrency($userId, 'EUR');
});

test('AdminService - setUserBalance preserves other currencies', function () {
    $svc = new \Services\AdminService(new \Models\User(), new \Models\Balance(), new \Models\BalanceLog(), new \Models\Rate());
    $userId = getAdminId();
    $balanceModel = new \Models\Balance();
    $userModel = new \Models\User();

    $balanceModel->ensureBalance($userId, 'RUB', 500);
    $balanceModel->ensureBalance($userId, 'EUR', 100);
    $userModel->updateDefaultCurrency($userId, 'EUR');

    $svc->setUserBalance($userId, $userId, 'EUR', 999);

    $eur = $balanceModel->findUserBalance($userId, 'EUR');
    assertEquals(999.0, (float) $eur['amount'], 'EUR should be 999');
    assertEquals('EUR', $userModel->getDefaultCurrency($userId), 'Default currency should be EUR');

    $balanceModel->ensureBalance($userId, 'EUR', 0);
    $balanceModel->ensureBalance($userId, 'RUB', 0);
});

test('UserService - getProfile', function () {
    $svc = new \Services\UserService(new \Models\User(), new \Models\Balance(), new \Models\Rate());
    $profile = $svc->getProfile(getJohnDoeId());
    assertTrue(isset($profile['login']), 'Should have login');
    assertTrue(!isset($profile['password']), 'Should not have password');
    assertTrue(isset($profile['contacts']), 'Should have contacts');
    assertTrue(isset($profile['balance']), 'Should have balance');
    assertTrue(isset($profile['balance']['currency']), 'Balance should have currency');
    assertTrue(isset($profile['balance']['converted']), 'Balance should have converted');
});

test('UserService - getProfile not found', function () {
    $svc = new \Services\UserService(new \Models\User(), new \Models\Balance(), new \Models\Rate());
    $profile = $svc->getProfile(99999);
    assertEquals([], $profile);
});

echo "\nTesting atomic balance operations...\n";

test('Balance::deduct - exact amount succeeds', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 100);
    $result = $balance->deduct(1, 'EUR', 100);
    assertTrue($result, 'deduct should return true');
    $bal = $balance->findUserBalance(1, 'EUR');
    assertEquals(0.0, (float) $bal['amount'], 'Balance should be 0');
    $balance->ensureBalance(1, 'EUR', 1000);
});

test('Balance::deduct - insufficient returns false', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 10);
    $result = $balance->deduct(1, 'EUR', 20);
    assertTrue(!$result, 'deduct should return false');
    $bal = $balance->findUserBalance(1, 'EUR');
    assertEquals(10.0, (float) $bal['amount'], 'Balance should remain 10');
});

test('Balance::deduct - zero balance returns false', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 0);
    $result = $balance->deduct(1, 'EUR', 0.01);
    assertTrue(!$result, 'deduct should return false when balance is 0');
});

test('Balance::addBalance - fractional sum precision', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 0);
    $balance->addBalance(1, 'EUR', 0.07);
    $balance->addBalance(1, 'EUR', 0.07);
    $balance->addBalance(1, 'EUR', 0.07);
    $bal = $balance->findUserBalance(1, 'EUR');
    assertEquals(0.21, (float) $bal['amount'], '0.07+0.07+0.07 should equal 0.21');
    $balance->ensureBalance(1, 'EUR', 1000);
});

test('Balance::addBalance - new currency creates record', function () {
    $balance = new \Models\Balance();
    $balance->addBalance(1, 'RUB', 500);
    $bal = $balance->findUserBalance(1, 'RUB');
    assertEquals(500.0, (float) $bal['amount'], 'RUB balance should be 500');
    $balance->ensureBalance(1, 'RUB', 0);
});

function ensureJohnDoeBalance(float $amount): void
{
    $balance = new \Models\Balance();
    $balance->ensureBalance(getJohnDoeId(), 'EUR', $amount);
}

test('BetService - payout rounding (1.07 * 100 = 107.00)', function () {
    $svc = makeBetService();
    $balanceModel = new \Models\Balance();
    ensureJohnDoeBalance(500);

    $place = $svc->placeBet(getJohnDoeId(), 'Rounding Test Event', 'team1_win', 1.07, 100);
    assertTrue($place['success'], 'Bet placement should succeed');
    $settle = $svc->settleBet($place['bet_id'], 'won');
    assertTrue($settle['success'], 'Settlement should succeed');

    $bal = $balanceModel->findUserBalance(getJohnDoeId(), 'EUR');
    // 500 - 100 (stake) + 107.00 (payout) = 507.00
    assertEquals(507.0, (float) $bal['amount'], 'Balance should be exactly 507.00');
    ensureJohnDoeBalance(1000);
});

test('Balance::addBalance does not overwrite unrelated currencies', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 100);
    $balance->ensureBalance(1, 'USD', 50);
    $balance->addBalance(1, 'EUR', 25);
    $eur = $balance->findUserBalance(1, 'EUR');
    $usd = $balance->findUserBalance(1, 'USD');
    assertEquals(125.0, (float) $eur['amount'], 'EUR should be 125');
    assertEquals(50.0, (float) $usd['amount'], 'USD should remain 50');
    $balance->ensureBalance(1, 'EUR', 1000);
    $balance->ensureBalance(1, 'USD', 0);
});

test('Balance::addBalance with DECIMAL boundary value', function () {
    $balance = new \Models\Balance();
    $balance->ensureBalance(1, 'EUR', 9999999999900.00);
    $balance->addBalance(1, 'EUR', 99.99);
    $bal = $balance->findUserBalance(1, 'EUR');
    assertEquals(9999999999999.99, (float) $bal['amount'],
        'Large DECIMAL should not overflow');
    $balance->ensureBalance(1, 'EUR', 1000);
});

test('AdminService - updateUserBalance with negative amount', function () {
    $svc = new \Services\AdminService(
        new \Models\User(), new \Models\Balance(),
        new \Models\BalanceLog(), new \Models\Rate()
    );
    $balanceModel = new \Models\Balance();
    ensureJohnDoeBalance(100);
    $result = $svc->updateUserBalance(1, getJohnDoeId(), 'EUR', -50);
    assertTrue($result['success'], 'Negative adjustment should succeed');
    $bal = $balanceModel->findUserBalance(getJohnDoeId(), 'EUR');
    assertEquals(50.0, (float) $bal['amount'], 'Balance should be 100 - 50 = 50');
    ensureJohnDoeBalance(1000);
});

test('BalanceLog after deduct has correct balance_after', function () {
    $balance = new \Models\Balance();
    $log = new \Models\BalanceLog();
    ensureJohnDoeBalance(100);
    $balance->deduct(getJohnDoeId(), 'EUR', 30);
    $entries = $log->findByUser(getJohnDoeId());
    $bal = $balance->findUserBalance(getJohnDoeId(), 'EUR');
    assertEquals(70.0, (float) $bal['amount'], 'Balance should be 100 - 30 = 70');
    ensureJohnDoeBalance(1000);
});

echo "\nDone.\n";
