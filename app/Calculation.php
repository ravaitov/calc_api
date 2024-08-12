<?php

namespace App;

use PDO;

class Calculation extends AbstractApp
{
    private int $calcId;

    public function run(): void
    {
        $this->calcId = intval($this->url[0] ?? 0);

        switch ($this->method) {
            case 'GET':
                $this->getCalculation();
                break;
            case 'POST':
                if ($this->calcId) {
                    $this->updateCalculation();
                } else {
                    $this->createCalculation();
                }
                break;
            case 'DELETE' :
                $this->deleteCalculation();
        }
    }

    private function getCalculation(): void
    {
        $calc = $this->baseCalc->query(<<<SQL
            select 
                comp_id,
                month_for_es,
                coeff_for_es,
                settlement_period,
                contract_type,
                contract_date,
                period,
                distr_amount,
                freemounth_amount,
                total_for_period,
                month_specification,
                type_fin_condition,
                mounth_count_avance,
                status,
                otklyuchenie,
                dopostavka,
                sumDopostavka,
                calculation_name,
                user_id,
                action_with_kit
                from calc_accounts
                where id = $this->calcId
                and status != 'Удален'
            SQL
        )->fetchAll(PDO::FETCH_ASSOC)[0];
        if (!$calc) {
            $this->result = ['calc' => 'not found'];
            return;
        }
        $models = $this->baseCalc->query(<<<SQL
            select 
                id,
                product,
                setevitost,
                price_by_price,
                deviation,
                price_by_fact,
                vksp,
                es,
                nameStatusOrg,
                typeKontr
                from calc_model
                where calc_account_id = $this->calcId
            SQL
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($models as $model) {
            $withId[$model['id']] = $model;
        }
        $this->result = [
            'calc' => $calc,
            'models' => $withId,
        ];
    }

    private function createCalculation(): void
    {
        foreach ($this->body['calc'] as $field => $value) {

        }
//        $res = $this->baseCalc->
    }

    private function updateCalculation(): void
    {

    }

    private function deleteCalculation(): void
    {
        $res = $this->baseCalc->handle()->exec(
            "update calc_accounts set status = 'Удален' where id = $this->calcId"
        );
        $this->result = ['result' => $res ? 'succest' : 'not found'];
    }
}