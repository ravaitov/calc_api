<?php

namespace App;

use PDO;

class GetPriceVksp extends AbstractApp
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

        $param = in_array($prodId, [251, 252, 253, 254, 255]) ? 52 : 3;

        $vksp = $this->baseMs->query(
            "select dbo.uf_Get_VKSP([dbo].[sf_Ric037_2012_current_etap] (), $prodId, $netId, $techTypeId, $param)"
        )->fetchAll(PDO::FETCH_NUM)[0][0];

        $this->result = [
            'price' => round($price, 2),
            'vksp' => round($vksp, 4),
        ];
    }
}