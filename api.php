<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$url2 = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$class = [
        'test' => 'TestApp',
        'calc_list' => 'CalculationsList',
        'company_title' => 'CompanyTitle',
    ] [$url2] ?? 'ErrorApp';

try {
//    Logger::instance()->log("--- Event=$event -> $class REMOTE=" . $_SERVER['REMOTE_ADDR']);
    $app = eval("return new App\\$class();");
} catch (Throwable $t) {
    Logger::instance()->log("!!!Fatal\n" . $t->getMessage());
    http_response_code(400);
    echo "error=" . $t->getMessage();
    exit();
}

try {
    $app->run();
    echo json_encode($app->result,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $t) {
    Logger::instance()->log("!!! Error: " . $t->getMessage());
    http_response_code(400);
    echo "error=" . $t->getMessage();
}
