<?php

namespace App;

use App\Rest\RestWH;

class UserName extends AbstractApp
{
    public function run(): void
    {
        $rest = new RestWH();
        $res = $rest->call(
            'user.get',
            [
                'id' => $this->url[0],
            ],
        );
        $this->result = [
            'name' => $res[0]['LAST_NAME'] . ' ' . $res[0]['NAME']
        ];
    }
}