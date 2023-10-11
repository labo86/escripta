<?php
declare(strict_types=1);
# version 1.0.0

/**
 * @param string $serviceName
 * @param string $user
 * @param string $group
 * @param string $workingDirectory from the user home directory
 * @param string $execStart like /usr/bin/npm start
 * @return string
 */
function createSystemdServiceFileContent(string $serviceName, string $user, string $group, string $workingDirectory, string $execStart) : string {
    $serviceFileContent = <<<EOF
[Unit]
Description={$serviceName}
After=network.target

[Service]
Type=simple
User={$user}
Group={$group}
LimitNOFILE=65536

Restart=on-failure
RestartSec=5

WorkingDirectory=/home/{$user}/{$workingDirectory}
ExecStart={$execStart}

StandardOutput=append:/home/{$user}/{$serviceName}_stdout.log
StandardError=append:/home/{$user}/{$serviceName}_stderr.log
SyslogIdentifier={$serviceName}

[Install]
WantedBy=multi-user.target
EOF;

    return $serviceFileContent;
}