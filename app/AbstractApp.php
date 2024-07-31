<?php

namespace App;

use App\DataBase\DataBase;
use App\DataBase\DataBaseMs;
use App\Logger\Logger;
use App\Result\Result;
use GuzzleHttp\Client;
use PDO;
use stdClass;

class AbstractApp
{
    protected Config $config;
    protected DataBase $baseCalc;
    protected DataBase $baseZs;
    protected DataBase $baseMs;
    protected Logger $logger;
    protected string $appName;
    protected int $status = 400;
    protected ?array $body;
    protected array $url;
    protected string $method;
    protected string $endPoint;
    protected int $timeout = 10;
    public array $result = [];

    public function __construct(int $appId = Config::APP_ID)
    {
        $this->config = Config::instance();
        $this->config->setParam('app_id', $appId);
        $a = explode('\\', get_class($this));
        $this->appName = end($a);
        $this->config->setParam('app_name', $this->appName);
        $this->logger = Logger::instance();
        $this->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'));
        $this->baseCalc = new DataBase('db_calc');
        $this->baseZs = new DataBase('db_zs');
        $this->baseMs = new DataBaseMs('db_ms');
        $this->baseMs->handle()->setAttribute( PDO::SQLSRV_ATTR_DIRECT_QUERY, true );
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->url = array_slice(explode('/', $_SERVER['REQUEST_URI']), 3);
        $json = file_get_contents("php://input") ?? '{}';
        $this->body = json_decode($json, 1);
    }

    public function __destruct()
    {
        $this->log('<<< Завершение: ' . $this->appName . "\n");
    }

    public function log(string $log, int $level = 0): void
    {
        $this->logger->log($log, $level);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    protected function numRoundRows(array &$target, array $nums, $round = 2): void
    {
        foreach ($target as &$el) {
            $this->numRound($el, $nums, $round);
        }
    }

    protected function numRound(array &$target, array $nums, $round = 2): void
    {
        foreach ($nums as $num) {
            if (isset($target[$num])) {
                $target[$num] = round($target[$num], $round);
            }
        }
    }

    protected function httpClient(): Client
    {
        return new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->conf('access_token'),
            ],
            'base_uri' => $this->config->conf('base_uri'),
            'timeout' => $this->timeout,
            'http_errors' => false,
            'verify' => false
        ]);
    }
}