<?php

namespace App;

use App\DataBase\DataBase;
use App\DataBase\DataBaseMs;
use App\Logger\Logger;
use App\Rest\RestWH;
use PDO;

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
    protected array $necessaryGet = [];
    protected $latRu = [
        'kompl_type' => 'Тип дистрибутива',
        'product' => 'Продукт',
        'setevitost' => 'сетевитость',
        'price_by_price' => 'Цена по прейскуранту',
        'price_by_fact' => 'Цена по факту',
        'price_total' => 'Итоговая цена',
        'deviation' => 'Отклонение',
        'vksp' => 'ВКСП',
        'es' => 'ЕС',
        'komplekt' => 'Комплект',
        'paid' => 'Платный',
        'typeKontr' => 'Тип котрагента',
        'es_under_contract' => 'ЕС по текущему договору',
        'partner' => 'Контрагент',
        'product_type' => 'тип продукта',
        'type_company' => 'тип контрагента',
        'dopostavka' => 'Допоставка',
        'sumDopostavka' => 'Сумма допоставки',
        'calculation_name' => 'Название расчета',
        'month_for_es' => 'Месяц для ЕС',
        'coeff_for_es' => 'Коэффициент для расчета ЕС',
        'settlement_period' => 'Расчетный период',
        'contract_type' => 'Тип договора',
        'contract_date' => 'Дата договора',
        'period' => 'Период',
        'distr_amount' => 'Количество дистрибутивов',
        'freemounth_amount' => 'Количество бесплатных месяцев',
        'total_for_period' => 'Итого за период',
        'month_specification' => 'Месяц начала действия спецификации',
        'type_fin_condition' => 'Тип финансовых условий',
        'mounth_count_avance' => 'Количество месяцев аванса',
        'status' => 'Статус',
        'otklyuchenie' => 'Отключение',
        'action_with_kit' => 'Действие с комплектом К+',
        'statusOrg' => 'Статус компании',
    ];
    protected array $ruLat;

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
        $this->baseMs->handle()->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
        $this->rest = new RestWH();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->url = array_slice(explode('/', $_SERVER['REQUEST_URI']), 3);
        $json = file_get_contents("php://input") ?? '{}';
        $this->body = json_decode($json, 1);
        $this->validate();
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

    protected function validate(): void
    {
        foreach ($this->necessaryGet as $param) {
            if (empty($_GET[$param])) {
                throw new \Exception("Validate! Need param '$param'");
            }
        }
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
        $fields = implode('`,`', array_keys($insert));
        $values = implode("','", array_values($insert));

        return "(`$fields`) values ('$values')";
    }

    protected function assoc2Update(array $update): string
    {
        $result = 'SET ';
        foreach ($update as $field => $value) {
            $result .= "`$field`='$value',";
        }
        return substr($result, 0, -1);
    }

    protected function latRu(array $source): array
    {
        foreach ($source as $key => $item) {
            $out[$this->latRu[$key] ?? $key] = $item;
        }
        return $out;
    }

    protected function ruLat(array $source): array
    {
        $this->ruLat ??= array_flip(array_map(fn($el) => mb_strtoupper($el), $this->latRu));
        foreach ($source as $key => $item) {
            $out[$this->ruLat[mb_strtoupper($key)] ?? $key] = $item;
        }
        return $out;
    }
}