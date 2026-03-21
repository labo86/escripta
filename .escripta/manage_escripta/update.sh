SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PHAR_URL="https://github.com/labo86/escripta/releases/latest/download/escripta.phar"
TARGET_PHAR="$SCRIPT_DIR/../escripta.phar"

cd "$SCRIPT_DIR" || exit

rm -f "$TARGET_PHAR"
curl -fsSL "$PHAR_URL" -o "$TARGET_PHAR"
