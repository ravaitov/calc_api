<?php

namespace App;

class TestApp extends AbstractApp
{
    public function run(): void
    {
        $this->result = $this->url;
    }
}