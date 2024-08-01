<?php

namespace App;

use PDO;

class ProductList extends AbstractApp
{
    public function run():void
    {
        $this->result = $this->baseZs->query(<<<SQL
            SELECT distinct 
                products_id, 
                product_name, 
                kodProdukt, 
                isNews
            from vw_product_tech_condition
            ORDER BY product_name
        SQL
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}