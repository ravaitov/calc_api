<?php

namespace App;

use PDO;

class NetworkingList extends AbstractApp
{
    public function run(): void
    {
        $where = empty($this->url[0]) ? '' : 'where product_name = \'' . rawurldecode($this->url[0]) . '\'';
        $this->result = $this->baseZs->query(<<<SQL
            SELECT distinct 
                spr_calc_ver_id, 
                spr_calc_ver_NamVer,
                spr_calc_ver_KodVer, 
                spr_calc_ver_KodTechType, 
                condition_name
            from vw_product_tech_condition
            $where
            ORDER BY spr_calc_ver_NamVer
        SQL
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}