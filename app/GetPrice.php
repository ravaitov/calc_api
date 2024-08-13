<?php

namespace App;

use PDO;

class GetPrice extends AbstractApp
{
    public function run(): void
    {
        $region = $_GET['region'] == 18 ? 18 : 15; // 15 - Калужская, 18 - Московская
        $source = substr($_GET['month'], 0, 7) == date('Y-m')
            ? 'sf_Ric037_2012_current_etap'
            : 'sf_Ric037_2023_max_etap';
        $techTypeId = $_GET['tech_type_id'];
        $prodId = $_GET['prod_id'];
        $netId = $_GET['net_id'];

        $price = $this->baseMs->query(
            "SELECT dbo.uf_Get_Price2($region, [dbo].[$source] (), 3, $techTypeId, $prodId, $netId)"
        )->fetchAll(PDO::FETCH_NUM)[0][0];
        $this->result = [
            'price' => round($price, 2),
            'debug' => $source,
        ];
    }
}