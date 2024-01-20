<?php
declare(strict_types=1);

namespace labo86\action_scripts;


class Escript {

  const LANG = 'lang';
  const PARAM = 'param';

/**
 * si la linea hace match con una linea que empieza con tres backtics y despues solo espacios
 * @param string $line
 * @return bool
 */
    static function isLineCodeEnd(string $line): bool
    {
        return preg_match('/^```\s*$/', $line) === 1;
    }


    /**
     * si la linea coincides con tres backticks seguida por una palabra despues la palabra escripts y despues parametros de la forma name=value separador por espacios
     * @param string $line
     * @return array|bool
     */
    static function isLineCodeStart(string $line): array|bool
    {
        if ( preg_match('/^```([a-zA-Z0-9_]+)\s+escript(\s+.*)?$/', $line, $matches) === 1 ) {

            $parameters = $matches[2];
            if ( is_null($parameters) ) {
                $parameters = '';
            } else {
                $parameters = trim($parameters);
            }
            if ( $parameters === '' ) {
                $parameters = [];
            } else {
                $parameters = explode(' ', $parameters);
                $parameters = array_map(function($item) {
                    $item = explode('=', $item);
                    if ( count($item) === 1 ) {
                        $item = [$item[0], true];
                    }
                    return $item;
                }, $parameters);
                $parameters = array_combine(array_column($parameters, 0), array_column($parameters, 1));
            }

            return [
                self::LANG => $matches[1],
                self::PARAM => $parameters
            ];
        } else {
            return false;
        }
    }
}