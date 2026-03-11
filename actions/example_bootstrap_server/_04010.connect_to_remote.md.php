<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

$remoteIdentifier = "remote";

Escripta::callFunction("connect_to_$remoteIdentifier");


