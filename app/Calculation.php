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
                $this->body['models'] ??= [];
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
                action_with_kit,
                statusOrg,
                typeKontr
                from calc_accounts
                where id = $this->calcId
                and ifnull(status, 0) != 'Удален'
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
                es
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
        if ($this->url[1]  == 'ru') {
            $this->result['models'] = array_map(fn($el) => $this->latRu($el), $this->result['models']);
            $this->result['calc'] = $this->latRu($this->result['calc']);
        }
    }

    private function createCalculation(): void
    {
        $calc = $this->assoc2Insert($this->body['calc']);
//        $this->log(print_r($this->body, 1));
        $this->baseCalc->exec("INSERT INTO calc_accounts $calc");
        $calcId = $this->baseCalc->lastInsertId();
        if (!$calcId) {
            throw new \Exception("error calc_accounts insert!");
        }
//        $this->log("Id=$calcId");
        foreach ($this->body['models'] as $model) {
            $this->insertModel($calcId, $model);
        }
        $this->result = ['calc_id' => $calcId];
//        $res = $this->baseCalc->
    }

    private function insertModel(int $calcId, array $model): void
    {
        $model = $this->assoc2Insert($model + ['calc_account_id' => $calcId]);
        if (!$this->baseCalc->exec("INSERT INTO calc_model $model")) {
            throw new \Exception("error calc_model insert!");
        }
    }

    private function updateCalculation(): void
    {
        $calc = $this->assoc2Update($this->body['calc']);
        $this->baseCalc->exec("UPDATE  calc_accounts $calc where id = $this->calcId");
        foreach ($this->body['models'] as $model) {
            if (!isset($model['id'])) { //insert
                $this->insertModel($this->calcId, $model);
                continue;
            }
            $modelId = $model['id'];
            unset($model['id']);
            if (!$model) { // delete
                $this->baseCalc->handle()->exec("DELETE from calc_model where id = $modelId");
                continue;
            }
            $model = $this->assoc2Update($model);
            $this->baseCalc->exec("UPDATE  calc_model $model where id = $modelId");
        }
    }

    private function deleteCalculation(): void
    {
        $res = $this->baseCalc->handle()->exec(
            "update calc_accounts set status = 'Удален' where id = $this->calcId"
        );
        $this->result = ['result' => $res ? 'success' : 'not found'];
    }
}