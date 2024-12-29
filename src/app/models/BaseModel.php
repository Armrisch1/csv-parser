<?php

namespace src\models;

use PDO;
use src\core\Db;

class BaseModel
{
    public PDO $connection;

    public function __construct()
    {
        $db = Db::getInstance();
        $this->connection = $db->getConnection();
    }
}