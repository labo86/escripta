<?php
declare(strict_types=1);

namespace labo86\escripta;


use Exception;
use Throwable;

class Core {



    /**
     * @throws Throwable
     */
    static function processFolderByCommandLine() {


        global $argv;

        //get version
        if ( count($argv) === 2 && $argv[1] === '--version' ) {
            global $escriptaVersion;
            global $escriptaDate;
            echo <<<EOF
Escripta, la desplegadora
=========================
Versión: $escriptaVersion
Fecha de compilación: $escriptaDate

Hora de la ciudad de La Concepción de María Purísima del Nuevo Extremo, Reino de Chile, fundada por gracia de Dios el día 5 de octubre de 1550 por el gobernador Don Pedro de Valdivia.
Fecha desde la primera venida de Nuestro Señor Jesucristo. 

EOF;
        }

    }
}