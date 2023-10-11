<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../src/remoteServer.php');

$user = "test45";

//createUserWithNoPasswordInRemoteServer("127.0.0.1", "edwin", 3022, $user);
//addAuthorizedKeyToUserInRemoteServer("127.0.0.1", "edwin", 3022, $user, "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQDqukKpfFJ+wDeBYaUiH1Cp/5ovgJnXww7bFog6bR67OTyZ3nmqGCDEEYnB10hqa3pD2AM0TdxauYmbPYZVeWm9G/paozrslDAzoDYeLKH/XP4f7/fIh8lzd0CKd6re6dvhp5ozdePYXp0GUMqLoGVDGctHIw78rGiv7ZI4VDzWBKg3O92B9aytYlzcp2fqx3YLMpObZQh7hkTxpnthd5Q7evBt2v65FTusHvMLJirpmqwxPSTF7r4FijLE8VdRc8io8T6Zz87+GxyusRDVcljZ9RBRkR4DYuTeecDNrxMzZPjq7F1MEOBXDL7hFQrTXkkLloteWCvThB7S0M30VHkwalUxgNAhQaP/aoEv5zujflkhRGK4/Kdyeg1pE1u+yw08yezgcN4dy49lx1uSGtO5y4wZh7/GkOm4axBHsTD7ugfe3EjW9twncbDczqhrLXD1ZVnZliV8Lova5OkbaViXO+w8priZT08VF9Z3jt/UCQeEqa0ZEjWHY4a0IgdBGF0= edwin@edwin-pc-ubuntu");
//createSudoersServiceFileInRemoteServer("127.0.0.1", "edwin", 3022, $user, "test_service");

//$content = createSystemdServiceFileContent("test_service", "test45", "test45", "app", "php --version");
//createFileOnRemoteServer("127.0.0.1", "edwin", 3022, "/etc/systemd/system/test_service.service", $content);

$r = systemCtlOnRemoteServer("127.0.0.1", "test45", 3022, "/home/edwin/.ssh/id_rsa", "test_service", "status");
echo $r;
