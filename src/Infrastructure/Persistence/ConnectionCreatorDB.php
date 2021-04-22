<?php

namespace Alura\Infrastructure\Persistence;

use PDO;

class ConnectionCreatorDB
{
    public static function createConnection(string $dsn, ?string $user, ?string $pass): PDO
    {
        $connection = new PDO($dsn, $user, $pass);

        return $connection;
    }
}