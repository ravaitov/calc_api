<?php

namespace App;

use PDO;

class ProductList extends AbstractApp
{
    public function run():void
    {
        $this->result = $this->baseZs->query(<<<SQL
            SELECT distinct 
                products_id id, 
                product_name name, 
                kodProdukt i_s_id, 
                isNews is_news,
                prOsnSystem base_system
            from vw_product_tech_condition
            ORDER BY product_name
        SQL
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}