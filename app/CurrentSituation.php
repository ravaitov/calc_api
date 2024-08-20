<?php

namespace App;

use PDO;

class CurrentSituation extends CalcDeviation
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
               type_kontr [тип контрагента]
            FROM [RClient4].[dbo].[View_ric037_calc_tek_b24]
            where Etap=[dbo].[sf_Ric037_2012_current_etap] ()
                  and ID_B24=$company_id
        SQL;

        $this->result['rows'] = $this->baseMs->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (!$this->result['rows']) {
            $this->result['total'] = 'Нет данных';
            return;
        }
        $this->numRoundRows($this->result['rows'], ['Итоговая цена', 'ВКСП', 'Цена по прейскуранту', 'Отклонение']);
        $this->sum();
        if ($this->url[2] == 'lat') {
            $this->result['rows'] = array_map(fn($el) => $this->ruLat($el), $this->result['rows']);
            $this->result['total'] = $this->ruLat($this->result['total']);
        }
    }

}

//,
//sum(price_price) over (partition by ID_B24) as sum_price_price,
//               sum(price_Itog) over (partition by ID_B24) as sum_price_Itog,
//               abs(sum(price_Itog) over (partition by ID_B24)/sum(case when Pri_Bespl='да' and typeProdukt<>'НП'
//                  then price_price else 0 end) over (partition by ID_B24)-1)*100 as sum_otkl