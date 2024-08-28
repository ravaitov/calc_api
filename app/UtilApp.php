<?php

namespace App;

use PDO;

class UtilApp extends AbstractApp
{
    private array $prodNews;
    private array $isBaseProd;

    /**
     * получить "отключение" и "допоставка"
     */
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
        $add = count(array_diff_key($model, $current));

        return [
            'otklyuchenie' => count(array_diff_key($current, $model)),
            'dopostavka' =>  $add,
            'sum' => $add * self::SUMM_FACTOR,
        ];
    }

    public function isNewsProd(string $prod): bool
    {
        $this->fillNewsBaseProds();

        return $this->isNewsProds[$prod] ?? false;
    }

    public function isBaseProd(string $prod): bool
    {
        $this->fillNewsBaseProds();

        return $this->isBaseProd[$prod] ?? false;
    }

    public function companyType(int $companyId): string
    {
        $res = $this->baseMs->query(<<<SQL
           SELECT IDEOrgAll_S1 
           FROM SprOrgAll_S1 
           WHERE KodOrgAll_S1 = 
                 (SELECT KodOrgAll_S1 FROM Org WHERE Num_1 = $companyId) 
        SQL
        )->fetchAll(PDO::FETCH_NUM);

        return $res[0][0] ?? '';
    }

    private function fillNewsBaseProds(): void
    {
        if (empty($this->isNewsProds)) {
            $res = $this->baseZs->query(<<<SQL
                SELECT distinct 
                    product_name, 
                    prOsnSystem,
                    isNews,
                    prOsnSystem
                from vw_product_tech_condition 
                WHERE isNews = 1 or prOsnSystem = 1
            SQL
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($res as $el) {
                $this->isNewsProds[$el['product_name']] = $el['isNews'] ?: false;
                $this->isBaseProd[$el['product_name']] = $el['prOsnSystem'] ?: false;
            }
        }
    }
}