<?php

namespace App;

use App\Traits\SingletonTrait;
use App\DataBase\DataBase;

class Config
{
    use SingletonTrait;

    /**
     * from table zsmicroapp.applog_levels
     */
    const ERROR = 1;
    const WARNING = 2;
    const IMPORTANT = 3;
    const EVENT = 4;
    const DEBUG = 5;

    const LOG_DB = [self::ERROR, self::WARNING, self::IMPORTANT, self::EVENT];

    const APP_ID = 0;

    public array $level_names = [
        0 => '',
        self::ERROR => 'Ошибка: ',
        self::WARNING => 'Предупреждение: ',
        self::IMPORTANT => 'Важно! ',
        self::EVENT => '',
        self::DEBUG => 'Отладка: ',
    ];

    private DataBase $dataBase;

    private array $conf = [
        'version' => '0.1.1',
        'comment' => '',
        'access_token' => '',
        'refresh_token' => '',
        'log_file' => '??', // auto init
        'log_limit' => 90, // log files count limit
        'app_id' => self::APP_ID, // !!!
        'db_calc' =>
            [
                'comment' => '',
                'type' => 'mysql',
                'host' => '192.168.100.170:3306',
                'name' => 'calc',
                'user' => 'calc',
                'password' => '3141592',
            ],
        'db_ms' =>
            [
                'comment' => '',
                'type' => 'sqlsrv',
                'host' => '192.168.5.18',
                'name' => 'Rclient4',
                'user' => 'ZSapp',
                'password' => 'ZSapp!@#pwd',
            ],
        'db_zs' =>
            [
                'comment' => '',
                'type' => 'mysql',
                'host' => '192.168.100.170:3306',
                'name' => 'zsmicroapp',
                'user' => 'ZSapp',
                'password' => 'o_2FbRkAM1F2',
            ],

        'db_log' => [],
    ];

    public function dataBase(): DataBase
    {
        $this->dataBase ??= new DataBase('database');
        return $this->dataBase;
    }

    public function conf(string $key): array|string|int
    {
        if (!isset($this->conf[$key])) {
            throw new \Exception("Config! Unknown key='$key'");
        }

        return $this->conf[$key];
    }


    public function setParam(string $key, $param): void
    {
        $this->conf[$key] = $param;
    }

    public function appName(): string
    {
        return $this->conf['app_names'][$this->conf['app_id'] ?? 0] ?? '';
    }

    protected function init(): void
    {
        date_default_timezone_set('Europe/Moscow');
        try {
            $this->conf['log_dir'] = realpath(__DIR__ . '/../log/') . DIRECTORY_SEPARATOR;
            $this->conf['log_file'] = $this->conf['log_dir'] . 'log_%s.txt';
            $this->conf['stor_dir'] = realpath(__DIR__ . '/../storage/') . DIRECTORY_SEPARATOR;
        } catch (\Throwable $t) {
            throw new \Exception("Config init error! " . $t->getMessage());
        }
//        print_r($this->conf);
    }
}