<?php

namespace App;

use PDO;

class CurrentSituation extends AbstractApp
{
    public function run(): void
    {
        $company_id = $this->url[1];
        $sql = <<<SQL
            SELECT Pri_Bespl [Платный],
               numorg_kontr [Контрагент],
               ComplREG [Комплект],
               otklonenie [Отклонение],
               VKSP [ВКСП],
               NamProdukt [Продукт],
               price_price [Цена по прейскуранту],
               price_Itog [Итоговая цена],
               flash [сетевитость],
               IdeTyp [Тип дистрибутива],
               DOkon [по этап],
               ES_dog as [ЕС по текущему договору],
               typeProdukt  [тип продукта],
               sum(price_price) over (partition by ID_B24) as sum_price_price,  
               sum(price_Itog) over (partition by ID_B24) as sum_price_Itog,
               abs(sum(price_Itog) over (partition by ID_B24)/sum(case when Pri_Bespl='да' and typeProdukt<>'НП'
                  then price_price else 0 end) over (partition by ID_B24)-1)*100 as sum_otkl
            FROM [RClient4].[dbo].[View_ric037_calc_tek_b24]
            where Etap=[dbo].[sf_Ric037_2012_current_etap] ()
                  and ID_B24=399
        SQL;

        $this->result['rows'] = $this->baseMs->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->numRoundRows($this->result['rows'], ['Итоговая цена', 'ВКСП', 'Цена по прейскуранту', 'Отклонение']);
        $this->sum();
//        $this->log(print_r($this->result, 1));
    }

    private function sum(): void
    {
        $sumCatalogPrice = $sumCatalogPriceDeviation = $sumResultPrice = $vksp = $es = 0;
        foreach ($this->result['rows'] as $row) {
            $sumResultPrice += $row['Итоговая цена'];
            $sumCatalogPrice += $row['Цена по прейскуранту'];
            if ($row['Платный'] == 'нет') {
                continue;
            }
            if ($row['тип продукта'] != 'НП') { // не новшество!
                $sumCatalogPriceDeviation += $row['Цена по прейскуранту'];
            }
            $vksp = $vksp ?: round($row['ВКСП'], 4);
            $es = $es ?: round($row['ЕС по текущему договору'], 4);
        }

        $fmt = new \NumberFormatter('ru_RU', \NumberFormatter::CURRENCY);
        $symbol = $fmt->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);
        $this->log("sumResultPrice=$sumResultPrice; sumCatalogPriceDeviation=$sumCatalogPriceDeviation");
        $percentDeviation = round(($sumResultPrice / $sumCatalogPriceDeviation - 1) * 100, 2) . ' %';
        $this->result['total'] = [
            'Цена по прейскуранту' => $fmt->formatCurrency($sumCatalogPrice, $symbol),
            'Итоговая цена' => $fmt->formatCurrency($sumResultPrice, $symbol),
            'Отклонение' => $percentDeviation,
            'ВКСП' => $vksp,
            'ЕС по текущему договору' => $es,
        ];
    }
}