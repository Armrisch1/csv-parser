<?php

namespace src\core;

use PDO;
use PDOException;
use RuntimeException;

class Db
{
    private static ?Db $instance = null;
    private PDO $connection;

    /**
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->connect($config);
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
    }

    /**
     * @param array $config
     * @return void
     */
    public static function initialize(array $config): void
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
    }

    public static function getInstance(): Db
    {
        return self::$instance;
    }

    /**
     * @param array $config Database configuration.
     */
    private function connect(array $config): void
    {
        try {
            $this->connection = new PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                $config['options'] ?? []
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * @return PDO The database connection instance.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}