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
                    	typeProdukt,
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
                    where Etap = [dbo].[sf_Ric037_2012_current_etap] ()
                        and ID_B24 = $company_id
                    union
                    select 'нет' as [Платный],
                    	'' as typeProdukt,
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
                    where Etap = [dbo].[sf_Ric037_2012_current_etap] ()
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
        $sumCatalogPrice = $sumCatalogPriceDeviation = $sumResultPrice = $vksp = $es = 0;
        foreach ($this->result['rows'] as $row) {
            $sumResultPrice += $row['Итоговая цена'];
            $sumCatalogPrice += $row['Цена по прейскуранту'];
            if ($row['Платный'] == 'нет') {
                continue;
            }
            if ($row['typeProdukt'] != 'НП') { // не новшество!
                $sumCatalogPriceDeviation += $row['Цена по прейскуранту'];
            }
            $vksp = $vksp ?: round($row['ВКСП'], 4);
            $es = $es ?: round($row['ЕС по текущему договору'], 4);
        }

        $fmt = new \NumberFormatter('ru_RU', \NumberFormatter::CURRENCY);
        $symbol = $fmt->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);

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