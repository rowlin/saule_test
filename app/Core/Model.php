<?php

declare(strict_types=1);

namespace Core;

class Model
{
    protected Db $pdo;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $db = $config['db'];
        $this->pdo = new Db(dbhost: $db['host'], dbname: $db['name'], username: $db['user'], password: $db['password']);
    }
}
