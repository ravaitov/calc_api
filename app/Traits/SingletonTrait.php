<?php

namespace App\Traits;

trait SingletonTrait
{
    private static $instance;

    /**
     * @return self
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
            self::$instance->init();
        }

        return self::$instance;
    }

    protected function init(): void {}

    private function __construct() {}

    private function __clone() {}
}