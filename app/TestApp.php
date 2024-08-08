<?php

namespace App;

class TestApp extends AbstractApp
{
    public function run(): void
    {
        $this->log(print_r($_GET, 1));
        return;
        $this->result = ['price_cod' => $this->codPrice($this->url[0])];
        return;
        $this->result = $this->url;
        $this->log(rawurldecode(print_r($this->url, 1)));
    }
}