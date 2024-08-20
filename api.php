<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;

$url2 = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$class = [
        'test' => 'TestApp',
        'calc_list' => 'CalculationsList',          // список расчетов, с /resolved - с решением, иначе черновики и тд
        'company_title' => 'CompanyTitle',
//        'calc_account' => 'CalcAccount',
        'user_name' => 'UserName',
        'current_situation' => 'CurrentSituation',  //Текущая ситуация
        'factor_es' => 'FactorEs',                  // ЕС
        'prod_list' => 'ProductList',               // список продуктов (с основными свойствами)
        'networking_list' => 'NetworkingList',      // сетевитость для продукта
        'get_price' => 'GetPrice',
        'get_price_vksp' => 'GetPriceVksp',         // Получить цену и ВКСП
        'calculation' => 'Calculation',             // CRUD
        'get_markers' => 'GetMarkers',              //
    ] [$url2] ?? 'ErrorApp';

try {
    $app = eval("return new App\\$class();");
} catch (Throwable $t) {
    terminateError($t);
}

try {
    header('Content-Type: application/json');
    $app->run();
    echo json_encode($app->result,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $t) {
    terminateError($t);
}

function terminateError(Throwable $t): void
{
    Logger::instance()->log("!!! Error: " . $t->getMessage());
    http_response_code(400);
    echo '{"error": "'. $t->getMessage().'"}';
    exit();
}