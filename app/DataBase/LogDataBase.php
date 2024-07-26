<?php

namespace App\DataBase;

use App\Config;

class LogDataBase extends DataBase
{
    private int $appId;

    public function __construct(int $appId)
    {
        parent::__construct('db_log');
        $this->appId = $appId;
    }

    public function log(string $log, int $level = Config::DEBUG): void
    {
        $STH = $this->dbh->prepare(
            'INSERT INTO applogs (app_ID, appLog_Level, appLog_Log, appLog_DateTime, appLog_userId)'
            . 'values (:appId, :level, :log, :dateTime, 0)'
        );
        $STH->execute([
            'appId' => $this->appId,
            'level' => $level,
            'log' => $log,
            'dateTime' => date('Y-m-d H:i:s'),
        ]);
    }
}