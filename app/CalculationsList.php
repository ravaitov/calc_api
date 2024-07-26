<?php

namespace App;

use PDO;

class CalculationsList extends AbstractApp
{
    public function run(): void
    {
        $condition =  ($this->url[1] ?? '') == 'resolved'
            ? "AND STATUS IN ('Cогласовано', 'Отказано')"
            : "AND STATUS NOT IN ('Cогласовано', 'Отказано', 'Удален', 'Архив')";
        $res = $this->query($condition);
        $this->result = array_map(fn($el) => ['title' => "$el[0] - $el[1] - $el[2]: $el[3]", 'id' => $el[1]], $res);
    }

    private function query(string $condition): array
    {
        $comp_id = $this->url[0];
        return
            $this->baseCalc->query(<<<SQL
            SELECT calculation_name, id, STATUS, updated_at 
            FROM calc_accounts 
            WHERE comp_id = $comp_id
            $condition
            ORDER BY updated_at DESC
        SQL)->fetchAll(PDO::FETCH_NUM);
    }
}

//            AND STATUS NOT IN ('Cогласовано', 'Отказано', 'Удален', 'Архив')