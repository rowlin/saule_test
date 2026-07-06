<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;
use Core\Interfaces\DbInterface;
use Exceptions\PdoException;

class Db implements DbInterface
{
    private ?PDO $connection = null;

    public function __construct(
        string $dbhost = "localhost",
        string $dbname = "saule_betting",
        string $username = "root",
        string $password = ""
    ) {
        try {
            $this->connection = new PDO(
                "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4;",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\Exception $e) {
            throw new PdoException($e->getMessage());
        }
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function insert(string $query = "", array $params = []): int
    {
        $stmt = $this->executeStatement($query, $params);
        return (int) $this->connection->lastInsertId();
    }

    public function select(string $query = "", array $params = []): array
    {
        $stmt = $this->executeStatement($query, $params);
        return $stmt->fetchAll();
    }

    public function selectOne(string $query = "", array $params = []): ?array
    {
        $stmt = $this->executeStatement($query, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function update(string $query = "", array $params = []): int
    {
        $stmt = $this->executeStatement($query, $params);
        return $stmt->rowCount();
    }

    public function delete(string $query = "", array $params = []): int
    {
        $stmt = $this->executeStatement($query, $params);
        return $stmt->rowCount();
    }

    private function executeStatement(string $query = "", array $params = []): PDOStatement
    {
        $stmt = $this->connection->prepare($query);

        if ($stmt === false) {
            throw new PdoException("Unable to prepare statement: " . $query);
        }

        $stmt->execute($params);
        return $stmt;
    }
}
