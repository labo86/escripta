<?php
declare(strict_types=1);
# version 1.0.0


function getSqlStmtCreateDatabase(string $dbName) : string {
    $stmt = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
    return $stmt;
}

function getSqlStmtCreateUser(string $userName, string $password, string $dbName) : string {
    $stmt = <<<EOF
CREATE USER IF NOT EXISTS '$userName'@'%' IDENTIFIED BY '$password';
GRANT ALL PRIVILEGES ON $dbName.* TO '$userName'@'%';
FLUSH PRIVILEGES;

EOF;
    return $stmt;
}

function getSqlStmtInfoVersion(int $currentVersionNumber) : string {
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $stmt = "INSERT INTO info (id, value) VALUES ('version', '$currentVersionNumber') ON DUPLICATE KEY UPDATE value = '$currentVersionNumber';";
    return $stmt;
}



/**
 * Recorrea los archivos desde una version inicial hasta una version final, el primero no es inclusivo
 * Si se quiere incluir el primero, se debe pasar como parametro el numero de version anterior, se recomienda un numero inmediatamente menor.
 * Si se quiere incluir hasta el final poner el numero -1 al final
 * @param string $sqlDir
 * @param int $startNumber Non inclusive
 * @param int $endNumber  Inclusive
 * @return string
 */
function getSqlStmtBetweenVersions(string $sqlDir, int $startNumber = -1, int $endNumber = -1) : string {
    if ( $endNumber === -1 ) $endNumber = 9999999;

    /*
    $startNumberStr = str_pad((string)$startNumber, 7, '0', STR_PAD_LEFT);
    $endNumberStr = str_pad((string)$endNumber, 7, '0', STR_PAD_LEFT);
*/
    $outputStr = "BEGIN;\n";
    $maxNumber = -1;
    # list files in $sqlDir that their filename start with startNumber and end with endNumber
    foreach (glob($sqlDir . '/*.sql') as $file) {
        $fileName = basename($file);
        echo "Processing file: $fileName" . PHP_EOL;
        //get first 7 digits of filename
        $fileNumber = substr($fileName, 0, 7);

        if ($fileNumber > $startNumber && $fileNumber <= $endNumber) {
            $sql = file_get_contents($file);
            $maxNumber = max($maxNumber, $fileNumber);
            $outputStr .= <<<EOL
# BEGIN Filename : $fileName

$sql

EOL;
        }
    }

    if ( $maxNumber > -1 ) {
        $sql = "INSERT INTO info (id, value) VALUES ('version', '$maxNumber') ON DUPLICATE KEY UPDATE value = '$maxNumber';";

        $outputStr .= <<<EOL
    # BEGIN Update version
    
    $sql
    EOL;
    }

    $outputStr .= "COMMIT;\n";

    return $outputStr;


}

function getMysqlCliCommand(string $host, int $port, string $userName, string $userPassword, string $sqlCommand = "", string $dbName = "") {


    $command = <<<EOF
mysql \
    --host={$host} \
    --port={$port} \
    --user={$userName} \
    --password={$userPassword}
EOF;
    if ( !empty($sqlCommand) ) {
        $escapedSqlCommand = escapeshellarg($sqlCommand);
        $command .= "\\\n --execute={$escapedSqlCommand}";
    }

    if ( !empty($dbName) ) {
        $command .= "\\\n   {$dbName}";
    }

    return $command;
}