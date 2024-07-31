<?php

namespace App;

use PDO;

class CurrentSituation extends AbstractApp
{
    public function run(): void
    {
        $company_id = $this->url[1];
        $sql = <<<SQL
                    select 'да' as [Платный],
                        numorg_kontr as [Контрагент],
                        ComplREG AS [Комплект],
                        otklonenie as [Отклонение],
                        VKSP as [ВКСП],
                        NamProdukt as [Продукт],
                        price_price as [Цена по прейскуранту],
                        price_Itog as [Итоговая цена],
                        case when flash = 'ОВМ-Ф' then flash+IdeVer
                             when flash = 'ОВМ' then IdeVer
                             when flash = 'ОВС' then IdeVer
                             when flash = 'И-В' then IdeVer
                        else flash
                        end as [сетевитость],
                        IdeTyp as [Тип дистрибутива],
                        type_kontr [Тип КА],
                        DOkon [по этап],
                        ES_dog as [ЕС по текущему договору]
                    from VIEW_RIC037_otklonenia_v_schetah_po_distr
                    where Etap=[dbo].[sf_Ric037_2012_current_etap] ()
                        and ID_B24 = $company_id
                    union
                    select 'нет' as [Платный],
                        numorg_kontr as [Контрагент],
                        ComplREG AS [Комплект],
                        otklonenie as [Отклонение],
                        VKSP as [ВКСП],
                        NamProdukt as [Продукт],
                        price_price as [Цена по прейскуранту],
                        0 as [Итоговая цена],
                        flash as [сетевитость],
                        IdeTyp as [Тип дистрибутива],
                        type_kontr as [Тип КА],
                        DOkon as [по этап],
                        ES_dog as [ЕС по текущему договору]
                    from VIEW_RIC037_otklonenia_v_schetah_po_distr_b24_freeDog
                    where Etap=[dbo].[sf_Ric037_2012_current_etap] ()
                        and ID_B24 = $company_id
                    order by ComplREG,[Платный],NamProdukt            
                 SQL;
        $this->result['rows'] = $this->baseMs->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->numRoundRows($this->result['rows'], ['Итоговая цена', 'ВКСП', 'Цена по прейскуранту', 'Отклонение']);
        $this->sum();
//        $this->log(print_r($this->result, 1));
    }

    private function sum(): void
    {
        $cnt = $sumInitPrice = $sumResultPrice = $averageDeviation = $vksp = $es =0;
        foreach ($this->result['rows'] as $row) {
            $sumInitPrice += $row['Цена по прейскуранту'];
            $sumResultPrice += $row['Итоговая цена'];
            if ($row['Платный'] == 'нет') {
                continue;
            }
            $cnt++;
            $averageDeviation += $row['Отклонение'];
            $vksp = $vksp ?: $row['ВКСП'];
            $es = $es ?: $row['ЕС по текущему договору'];
        }
        $averageDeviation /= $cnt;

        $fmt = new \NumberFormatter( 'ru_RU', \NumberFormatter::CURRENCY );
        $symbol = $fmt->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);

        $sumInitPrice = $fmt->formatCurrency($sumInitPrice,  $symbol);
        $sumResultPrice = $fmt->formatCurrency($sumResultPrice,  $symbol);
        $averageDeviation = round($averageDeviation, 2) . ' %';
        $this->result['result'] = [
            'Цена по прейскуранту' => $sumInitPrice,
            'Итоговая цена' => $sumResultPrice,
            'Отклонение' => $averageDeviation,
            'ВКСП' => $vksp,
            'ЕС по текущему договору' => $es,
        ];
    }
}