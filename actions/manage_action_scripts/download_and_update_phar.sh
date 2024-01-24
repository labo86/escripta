rm -rf ../action_scripts.phar
git clone git@github.com:labo86/action_scripts -b last_release repo
mv repo/action_scripts.phar ../action_scripts.phar
rm -rf repo