<?php

namespace App;

use App\Rest\RestWH;

class CompanyTitle extends AbstractApp
{
    public function run(): void
    {
        $rest = new RestWH();
        $res = $rest->call('crm.company.get', ['id' => $this->url[0]]);
        $this->result = ['title' => $res['TITLE']];
    }
}