<?php

namespace App\DataBase;

use App\Config;
use PDO;

class DataBaseMs extends DataBase
{
    public function __construct(string $base)
    {
        $conf = Config::instance()->conf($base);
        $this->dbh = new PDO(
            sprintf('%s:Server=%s;Database=%s', $conf['type'], $conf['host'], $conf['name']),
            $conf['user'],
            $conf['password']
        );
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbh->setAttribute( PDO::SQLSRV_ATTR_DIRECT_QUERY, true );
    }

}