<?php

namespace App;

class GetAddDel extends UtilApp
{
    public function run(): void
    {
        if (!$companyId = $this->url[1]) {
            throw new \Exception("Validate! Need company/{id}");
        }
        $this->body['prod_net'] ??= [[]];
        foreach ($this->body['prod_net'] as $item) {
            if (count($item) != 2) {
                throw new \Exception("Validate! incorrect prod_net");
            }
        }
        $this->result = $this->getAddedDeleted($companyId, $this->body['prod_net']);
    }
}