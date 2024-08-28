<?php

namespace App;

use PDO;

class GetMarkers extends UtilApp
{

    public function run(): void
    {
        $companyType = $this->companyType((int)$this->url[1]);
        $companyStatus = $this->getCompanyField('UF_CRM_1524464429', (int)$this->url[1]);
        $signNewProduct = (int)$this->isNewsProd($this->body['product']);
        $flash = $this->body['flash'];
        $priceByFact = $this->body['price_by_fact'];
        $product = $this->body['product'];
        $discount = $this->body['discount'];
        $sql = <<<SQL
            SELECT sf_calc_marker2 (
                $companyStatus,
                '$companyType',
                '$flash',
                $priceByFact,
                '$product',
                $discount,
                $signNewProduct
            ) as result
        SQL;
        $marker = $this->baseCalc->query($sql)->fetchAll(PDO::FETCH_NUM)[0][0] ?? '';
        $this->result = ['marker' => $marker];
    }
}