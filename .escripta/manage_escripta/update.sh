SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

cd "$SCRIPT_DIR" || exit

rm -rf ../escripta.phar
git clone \
  git@github.com:labo86/escripta \
  --branch latest_release \
  --single-branch \
  --depth 1 \
  repo

mv repo/escripta.phar ../escripta.phar
rm -rf repo