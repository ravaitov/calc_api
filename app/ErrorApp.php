<?php

namespace App;

class ErrorApp extends AbstractApp
{
    public function run(): void
    {
        throw new \Exception("Incorrect method");
    }
}