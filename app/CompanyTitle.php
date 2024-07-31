<?php

namespace App;

use App\Rest\RestWH;

class CompanyTitle extends AbstractApp
{
    public function run(): void
    {
        $this->result = [
            'title' => $this->getSql() ?: $this->getB24()
        ];
    }

    private function getSql(): string
    {
        $res = $this->baseMs->query('SELECT NamOrg FROM Org WHERE Num_1=' . $this->url[0]);
        return $res->fetchAll()[0][0] ?? '';
    }

    private function getB24(): string
    {
        $rest = new RestWH();
        $res = $rest->call('crm.company.get', ['id' => $this->url[0]]);
        return $res['TITLE'];
    }
}