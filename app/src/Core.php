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
        $args = $argv ?? [];

        if (AgentGuideInstaller::isRequested($args)) {
            AgentGuideInstaller::run($args);
            return;
        }

        if (SelfUpdate::isRequested($args)) {
            SelfUpdate::run($args);
            return;
        }

        //get version
        if ( count($args) === 2 && $args[1] === '--version' ) {
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
            return;
        }

        if (self::isHelpRequested($args) || count($args) === 1) {
            echo self::getHelpText();
        }
    }

    public static function isHelpRequested(array $argv): bool
    {
        if (count($argv) !== 2) {
            return false;
        }

        return in_array($argv[1], ['--help', '-h'], true);
    }

    public static function getHelpText(): string
    {
        return <<<EOF
Escripta CLI
============
Uso:
  php .escripta/bin/escripta.phar [opción]

Opciones:
  --version                 Muestra versión y fecha de compilación.
  -U, --self-update         Actualiza el archivo escripta.phar actual desde el release publicado.
  --install-agent-guide     Instala ESCRIPTA_AGENTS.md y AGENTS_HINT.md en .escripta/.
  --install-agent-guide=DIR Instala la guía en un directorio destino explícito.
  -h, --help                Muestra esta ayuda.

EOF;
    }
}
