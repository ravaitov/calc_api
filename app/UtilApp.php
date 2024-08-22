<?php

namespace App;

use PDO;
use App\Traits\SingletonTrait;

class UtilApp extends AbstractApp
{
    use SingletonTrait;

    public function getAddedDeleted(int $companyId, array $productNetwork = []): array // [['СвАС', 'ОВК-Ф']...]
    {
        $res = $this->baseMs->query(<<<SQL
            SELECT 
                concat(NamProdukt, '#', flash) unic 
                FROM [RClient4].[dbo].[View_ric037_calc_tek_b24]
                where Etap=[dbo].[sf_Ric037_2012_current_etap] ()
                AND ID_B24=$companyId    
        SQL
        )->fetchAll(PDO::FETCH_NUM);
        foreach ($res as $el) {
            $current[$el[0]] = true;
        }
        foreach ($productNetwork as $el) {
            $model[implode('#', $el)] = true;
        }

        return [
            'dopostavka' =>  count(array_diff_key($model, $current)),
            'otklyuchenie' => count(array_diff_key($current, $model)),
        ];
    }
}