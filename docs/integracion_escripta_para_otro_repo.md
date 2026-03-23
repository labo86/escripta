# Integración de Escripta en otro repositorio

Este documento está pensado para copiarse en otro repo que use **Escripta**, de modo que cualquier agente o persona entienda:

- cómo se carga la configuración,
- cómo se generan los nombres,
- qué variables aparecen en shell,
- y cómo usar esa información para construir scripts.

Nota de vigencia:

- La lógica de nombres y variables descrita aquí sigue vigente tras los cambios recientes del repo.
- El flujo actual de distribución de Escripta usa `escripta.phar`, metadata de release y self-update con `-U`.
- Por eso, cuando se necesiten ejemplos "actuales", conviene pensar en bloques tipo `release` más que en ejemplos legacy eliminados.

## Qué hace Escripta

Escripta toma configuración desde una carpeta `.escripta/configs`, la aplana a archivos dentro de `config.gen/`, y además genera un archivo `escripta_env.sh` con variables de entorno `ESCRIPTA_*`.

Flujo general:

1. Se define qué configuraciones cargar en un `config.php`.
2. `Escripta::fetchConfig(...)` lee esas fuentes.
3. Cada clave termina escrita como un archivo en `config.gen/`.
4. Escripta genera `escripta_env.sh` para exportar esas claves como variables de entorno.

## Estructura mínima esperada

```text
mi-repo/
├── .escripta/
│   └── configs/
│       ├── git.ini
│       ├── server_app.ini
│       └── service/
│           ├── _.ini
│           └── app.ini
└── accion/
    └── config.php
```

## Ejemplo mínimo de `config.php`

```php
#!/usr/bin/php
<?php
declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');

use labo86\escripta\Escripta;

Escripta::fetchConfig([
    'git' => [
        Escripta::getConfigLocal('git'),
    ],
    'server_app' => [
        Escripta::getConfigLocal('server_app'),
    ],
    'service' => [
        Escripta::getConfigLocal('service'),
    ],
]);
```

Al ejecutar ese archivo, Escripta genera:

- `config.gen/`
- `escripta_env.sh`

## Instalación y actualización de Escripta

En el estado actual del proyecto, Escripta se distribuye como `escripta.phar`.

Instalación típica:

```bash
mkdir -p .escripta/bin
curl -fsSL https://github.com/labo86/escripta/releases/latest/download/escripta.phar -o .escripta/bin/escripta.phar
chmod +x .escripta/bin/escripta.phar
php .escripta/bin/escripta.phar --version
```

Actualización:

```bash
php .escripta/bin/escripta.phar -U
```

Metadata pública de release:

```text
https://github.com/labo86/escripta/releases/latest/download/release.json
https://github.com/labo86/escripta/releases/latest/download/escripta.phar.sha256
```

## Regla principal: de config a nombre final

Hay **dos transformaciones**:

1. La configuración se aplana a claves con `_`.
2. Cada clave se exporta como variable `ESCRIPTA_*`.

### Ejemplo simple

Archivo:

```ini
# .escripta/configs/git.ini
repo_url=git@github.com:example/deploy.git
repo_branch=main
```

Llamada:

```php
Escripta::fetchConfig([
    'git' => [
        Escripta::getConfigLocal('git'),
    ],
]);
```

Resultado en `config.gen/`:

```text
git_repo_url
git_repo_branch
```

Resultado en `escripta_env.sh`:

```bash
export ESCRIPTA_GIT_REPO_URL="..."
export ESCRIPTA_GIT_REPO_BRANCH="..."
```

## Cómo se generan las claves intermedias

## 1. Archivos `.ini`

Si se carga un archivo `.ini`, Escripta:

- usa el nombre del archivo como prefijo,
- convierte secciones en prefijos extra,
- y une todo con `_`.

Ejemplo:

```ini
# .escripta/configs/app.ini
host=localhost

[db]
port=3306
```

Produce:

```text
app_host
app_db_port
```

Pero cuando se usa `Escripta::getConfigLocal('app')`, ese método ya carga el archivo `app.ini` sin volver a prefijar con `app`, así que el resultado que devuelve es:

```text
host
db_port
```

Y si luego en `fetchConfig` lo montas bajo:

```php
'backend' => [
    Escripta::getConfigLocal('app'),
]
```

los nombres finales quedan:

```text
backend_host
backend_db_port
```

Variables exportadas:

```text
ESCRIPTA_BACKEND_HOST
ESCRIPTA_BACKEND_DB_PORT
```

## 2. Carpetas de config

Si en vez de un solo `.ini` hay una carpeta, Escripta recorre subdirectorios y archivos.

Ejemplo:

```text
.escripta/configs/service/
├── _.ini
└── app.ini
```

Con:

```ini
# _.ini
ssh_user=deploy
public_host=example.test
```

```ini
# app.ini
service_name=example-app
```

Resultado de `Escripta::getConfigLocal('service')`:

```text
ssh_user
public_host
app_service_name
```

Si eso se publica bajo `'service'` en `fetchConfig`, entonces los archivos finales serán:

```text
service_ssh_user
service_public_host
service_app_service_name
```

Y las variables:

```text
ESCRIPTA_SERVICE_SSH_USER
ESCRIPTA_SERVICE_PUBLIC_HOST
ESCRIPTA_SERVICE_APP_SERVICE_NAME
```

## Reglas importantes de nombres

### Regla A: el nombre del bloque en `fetchConfig` sí importa

Este nombre es el prefijo final del archivo generado.

```php
Escripta::fetchConfig([
    'release' => [
        Escripta::getConfigLocal('release'),
    ],
]);
```

Si la config devuelve `token`, el archivo final será:

```text
release_token
```

Y la variable:

```text
ESCRIPTA_RELEASE_TOKEN
```

### Regla B: espacios se convierten en `_`

Si una clave final contiene espacios, Escripta los reemplaza por `_`.

Ejemplo:

```text
public key -> public_key
```

### Regla C: caracteres no alfanuméricos se limpian al exportar variables

Para pasar de nombre de archivo a variable de entorno:

- cualquier grupo de caracteres no alfanuméricos pasa a `_`,
- múltiples `_` se colapsan,
- se hace `UPPERCASE`,
- y se antepone `ESCRIPTA_`.

Ejemplo:

```text
service.app-key -> ESCRIPTA_SERVICE_APP_KEY
```

### Regla D: si el nombre empieza con número, se antepone `N_`

Ejemplo:

```text
123_token -> ESCRIPTA_N_123_TOKEN
```

### Regla E: si el contenido es multilinea, la variable termina en `_FILENAME`

Esto es muy importante para llaves privadas, certificados o textos largos.

Si el archivo generado contiene saltos de línea, Escripta **no** exporta el contenido directo, sino la ruta al archivo.

Ejemplo:

```text
config.gen/app_private_key
```

Si ese archivo tiene varias líneas, la variable exportada será:

```text
ESCRIPTA_APP_PRIVATE_KEY_FILENAME
```

Y su valor será algo como:

```text
$ESCRIPTA_CURRENT_DIR/app_private_key
```

En cambio, si el valor es de una sola línea:

```text
config.gen/app_public_key
```

la variable será:

```text
ESCRIPTA_APP_PUBLIC_KEY
```

## Reglas especiales con `_` en nombres de archivos o carpetas

El prefijo `_` se usa para **evitar agregar un segmento al nombre**.

### Archivo `_algo.ini`

Si un archivo `.ini` empieza con `_`, su nombre **no** se usa como prefijo.

Ejemplo:

```text
_config.ini
```

Contenido:

```ini
a=1

[db]
host=localhost
```

Resultado:

```text
a
db_host
```

No:

```text
config_a
config_db_host
```

### Archivo `_.ini`

Sirve para poner claves "en la raíz" de esa carpeta.

Ejemplo:

```text
service/_.ini
```

permite producir:

```text
ssh_user
public_host
```

sin agregar un prefijo extra como `__`.

### Carpeta `_private/`

Si una carpeta empieza con `_`, su nombre no se agrega al prefijo.

Eso sirve para organizar archivos sin contaminar el nombre final de las claves.

## Variables extra que siempre genera Escripta

Además de las variables de config, `escripta_env.sh` exporta:

- `ESCRIPTA_CURRENT_DIR`: directorio donde está `escripta_env.sh`
- `ESCRIPTA_PROJECT_DIR`: ruta relativa al directorio base del proyecto

## Cómo consumir las variables en shell

Patrón típico:

```bash
source ./escripta_env.sh

echo "$ESCRIPTA_GIT_REPO_URL"
echo "$ESCRIPTA_SERVER_APP_SSH_HOST"
echo "$ESCRIPTA_SERVICE_APP_SERVICE_NAME"
```

Para secretos multilinea:

```bash
source ./escripta_env.sh

ssh -i "$ESCRIPTA_SERVER_APP_PRIVATE_KEY_FILENAME" user@host
```

## Guía práctica para construir scripts

Cuando un script necesite datos de Escripta:

1. Busca el nombre del bloque declarado en `Escripta::fetchConfig(...)`.
2. Toma la clave aplanada proveniente del `.ini` o de la carpeta.
3. Une ambas partes con `_`.
4. Convierte eso a `ESCRIPTA_...` en mayúsculas.
5. Si el valor original puede ser multilinea, usa `..._FILENAME`.

### Ejemplos directos

Config:

```php
'release' => [
    Escripta::getConfigLocal('release'),
],
```

Archivos:

```ini
# release.ini
base_url=https://github.com/acme/tool/releases/latest/download
phar_filename=tool.phar
```

Variables esperadas:

```text
ESCRIPTA_RELEASE_BASE_URL
ESCRIPTA_RELEASE_PHAR_FILENAME
```

### Ejemplo con llave privada

Si existe un archivo no-ini como:

```text
.escripta/configs/app_private/private_key
```

y se publica así:

```php
'app' => [
    Escripta::getConfigLocal('app_private'),
],
```

entonces el archivo generado será algo equivalente a:

```text
app_private_key
```

y como el contenido es multilinea, la variable utilizable en shell será:

```text
ESCRIPTA_APP_PRIVATE_KEY_FILENAME
```

## Recomendaciones para agentes que generen scripts

- Preferir siempre variables `ESCRIPTA_*` en vez de hardcodear rutas, hosts o usuarios.
- Si el dato representa una llave, certificado o bloque multilinea, asumir que debe usarse `*_FILENAME`.
- Si la config viene de una carpeta, revisar si existe `_.ini`, porque esas claves quedan al nivel raíz del bloque.
- Si una carpeta o archivo empieza con `_`, no usar ese segmento en el nombre final.
- El nombre más confiable para deducir una variable es: `prefijo de fetchConfig + clave aplanada`.

## Regla corta para recordar

```text
variable_final = ESCRIPTA_ + UPPERCASE(nombre_archivo_generado)
```

Donde:

```text
nombre_archivo_generado = <bloque_fetchConfig> + "_" + <clave_aplanada>
```

Y si el valor tiene varias líneas:

```text
variable_final = ESCRIPTA_<NOMBRE>_FILENAME
```

## Resumen ejecutivo

- `fetchConfig` define el prefijo final.
- Los `.ini` se aplanan uniendo archivo, secciones y claves con `_`.
- Los directorios permiten composición recursiva.
- Los nombres con `_` inicial evitan agregar prefijos.
- Los valores de una línea salen como `ESCRIPTA_*`.
- Los valores multilinea salen como `ESCRIPTA_*_FILENAME`.
