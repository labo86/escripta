#!/usr/bin/php -d phar.readonly=0
<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../vendor/autoload.php');

use labo86\escripta\PharBuilder;

PharBuilder::build(__DIR__ . '/../../.escripta/escripta.phar', '3.6.0');