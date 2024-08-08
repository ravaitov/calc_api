<?php

namespace App;

class FactorEs extends AbstractApp
{
    public function run(): void
    {
        $date = implode('-', $this->url) . '-01 00:00:00.000';
        $factor = $this
                ->baseMs
                ->query("select EC_value from VIEW_RIC037_Spr_ES where ES_month = '$date'")
                ->fetchAll()[0]['EC_value'] ?? '';
        $factor = !$factor ? 'NaN' : round($factor, 2);
        $this->result = ['factor_es' => $factor];
    }
}