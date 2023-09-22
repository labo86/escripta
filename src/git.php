<?php
declare(strict_types=1);
# version 1.0.0


function gitClone($sshKeyFile, $repository, $branch, $targetDir) : void {

    //clean targetDir
    passthru("rm -rf {$targetDir}");


    $command = <<<EOF
GIT_SSH_COMMAND="ssh -i {$sshKeyFile}" \
git clone \
     {$repository} \
     --branch {$branch} \
     {$targetDir}
EOF;

    passthru($command);
}

function gitCopyInto($sourceDir, $targetDir) : void {

    //if targetDir does nto have a / then add
    if (!str_ends_with($targetDir, '/')) {
        $targetDir .= '/';
    }
    //the same for sourceDir
    if (!str_ends_with($sourceDir, '/')) {
        $sourceDir .= '/';
    }


    $command = <<<EOF
rsync \
    --recursive \
    --links \
    --perms \
    --times \
    --devices \
    --specials \
    --verbose \
    --compress \
    --delete \
    --exclude='.git' \
    {$sourceDir} {$targetDir}
EOF;

        passthru($command);
}

function gitPush($sshKeyFile, $sourceDir) : void {

    $command = <<<EOF
cd $sourceDir;
GIT_SSH_COMMAND="ssh -i {$sshKeyFile}";
git add -A;
git commit -m "commit";
git push;
EOF;

    passthru($command);
}

