<?php

require_once(__DIR__ . '/escripta.php');

use labo86\escripta\Escripta;

$remoteIdentifier = "remote_admin";

Escripta::callFunction("connect_to_$remoteIdentifier");





