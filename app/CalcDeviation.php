<?php

namespace App;

class CalcDeviation extends AbstractApp
{
    protected function sum(): void
    {
        $sumCatalogPrice = $sumCatalogPriceDeviation = $sumResultPrice = $vksp = $es = 0;
        foreach ($this->result['rows'] as $row) {
            $sumResultPrice += $row['Итоговая цена'];
            $sumCatalogPrice += $row['Цена по прейскуранту'];
            if (($row['Платный'] ?? '') == 'нет') {
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