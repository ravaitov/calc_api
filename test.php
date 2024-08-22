<?php
require_once __DIR__ . '/vendor/autoload.php';

print_r((new \App\AbstractApp())->getAddedDeleted(399, [['СвАС', 'ОВК-Ф'], ['СвАС', 'ОВМ1']]));