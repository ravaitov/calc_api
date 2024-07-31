<?php

namespace App\DataBase;

use App\Config;
use PDO;
use PDOStatement;

class DataBase
{
    protected PDO $dbh;

    public function __construct(string $base)
    {
        $conf = Config::instance()->conf($base);
        $this->dbh = new PDO(
            sprintf('%s:host=%s;dbname=%s', $conf['type'], $conf['host'], $conf['name']),
            $conf['user'],
            $conf['password']
        );
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function handle(): PDO
    {
        return $this->dbh;
    }

    public function query(string $query):  ?PDOStatement
    {
        return $this->dbh->query($query);
    }

    public function execute(string $queryString, array $params = []): array
    {
        $query = $this->dbh->prepare($queryString);
        $this->executeQuery($query, $params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function executeQuery($query, array $params): void
    {
        if ($query->execute($params) === false) {
            $error_message = print_r($query->errorInfo(), true);
            throw new \Exception("SQL error. PDO errorInfo is: "
                . $error_message . ". Query string was: '"
                . $query->queryString . "' Parameters was: " . print_r($params, true));
        }
    }
}