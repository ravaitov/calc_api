<?php

namespace App;

use App\DataBase\DataBase;
use App\DataBase\DataBaseMs;
use App\Logger\Logger;
use App\Rest\RestWH;
use GuzzleHttp\Client;
use PDO;
use stdClass;

class AbstractApp
{
    protected Config $config;
    protected DataBase $baseCalc;
    protected DataBase $baseZs;
    protected DataBase $baseMs;
    protected RestWH $rest;
    protected Logger $logger;
    protected string $appName;
    protected int $status = 400;
    protected ?array $body;
    protected array $url;
    protected string $method;
    protected string $endPoint;
    protected int $timeout = 10;
    public array $result = [];

    public function __construct()
    {
        $this->config = Config::instance();
        $a = explode('\\', get_class($this));
        $this->appName = end($a);
        $this->config->setParam('app_name', $this->appName);
        $this->logger = Logger::instance();
        $this->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'));
        $this->baseCalc = new DataBase('db_calc');
        $this->baseZs = new DataBase('db_zs');
        $this->baseMs = new DataBaseMs('db_ms');
        $this->baseMs->handle()->setAttribute( PDO::SQLSRV_ATTR_DIRECT_QUERY, true );
        $this->rest = new RestWH();
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

    protected function numRoundRows(array &$target, array $nums, $round = 4): void
    {
        foreach ($target as &$el) {
            $this->numRound($el, $nums, $round);
        }
    }

    protected function numRound(array &$target, array $nums, $round = 4): void
    {
        foreach ($nums as $num) {
            if (isset($target[$num])) {
                $target[$num] = round($target[$num], $round);
            }
        }
    }

    protected function codPrice(int $companyId): int
    {
        $reg = 0;
        $res = $this->rest->call(
            'crm.address.list',
            ['FILTER' => ['ENTITY_TYPE_ID' => 4, 'ENTITY_ID' => $companyId]]
        );
        foreach ($res as $item) {
            $reg = mb_stristr($item['PROVINCE'], 'калужск') !== false ? 15 : $reg;
            $reg = mb_stristr($item['PROVINCE'], 'московск') !== false ? 18 : $reg;
            if ($reg)
                break;
        }
        return $reg;
    }

    protected function assoc2Insert(array $insert): string
    {
        $fields =  implode('`,`' , array_keys($insert));
        $values =  implode("','", array_values($insert));

        return "(`$fields`) values ('$values')";
    }

    protected function assocUpdate(array $update): string
    {

    }

}