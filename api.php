<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;

$url2 = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$class = [
        'test' => 'TestApp',
        'calc_list' => 'CalculationsList',
        'company_title' => 'CompanyTitle',
        'calc_account' => 'CalcAccount',
        'user_name' => 'UserName',
        'current_situation' => 'CurrentSituation',
        'factor_es' => 'FactorEs',
        'prod_list' => 'ProductList',
        'networking_list' => 'NetworkingList',
        'get_price' => 'GetPrice',
        'get_price_vksp' => 'GetPriceVksp',
    ] [$url2] ?? 'ErrorApp';

try {
    $app = eval("return new App\\$class();");
} catch (Throwable $t) {
    Logger::instance()->log("!!!Fatal\n" . $t->getMessage());
    http_response_code(400);
    echo "error=" . $t->getMessage();
    exit();
}

try {
    header('Content-Type: application/json');
    $app->run();
    echo json_encode($app->result,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $t) {
    Logger::instance()->log("!!! Error: " . $t->getMessage());
    http_response_code(400);
    echo '{"error": "'. $t->getMessage().'"}';
}
