<?php

namespace App;

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
        $res = $this->baseMs->query('SELECT NamOrg FROM Org WHERE Num_1 = ' . $this->url[0]);
        return $res->fetchAll()[0][0] ?? '';
    }

    private function getB24(): string
    {
        return $this->getCompanyField('TITLE', $this->url[0]);
    }
}