# Local Composer bootstrap for development

Created At: 2026-03-26 19:51:44
Last Updated At: 2026-03-26 19:55:21
Template Version: v1

## Context
El entorno actual tiene `php`, `curl` y `unzip`, pero no tiene `composer` instalado globalmente. Eso bloquea `composer install` y la ejecución normal de tests PHP del repo.

## Objective
Permitir preparar dependencias PHP del proyecto sin requerir una instalación global previa de Composer.

## Scope
- Definir una forma reproducible de obtener Composer localmente para este repo.
- Facilitar instalación de dependencias para `app/` y `builder/`.
- Documentar el flujo recomendado para desarrollo local.

## Out of Scope
- Gestionar instalación global del sistema operativo.
- Resolver dependencias externas de integración.
- Cambiar la estructura de paquetes PHP del proyecto.

## Success Criteria
- Existe un flujo documentado y ejecutable para obtener Composer localmente.
- Se pueden instalar dependencias de `app/` y `builder/` usando ese flujo.
- La solución no requiere `composer` global.

## Plan
- [x] Definir ubicación y comando de bootstrap para Composer local.
- [x] Implementar script reproducible para descarga/uso.
- [x] Documentar uso para desarrollo.
- [x] Validar que instala dependencias en `app/` y `builder/`.

## Validation
- `./actions/php_dependencies/01_bootstrap/01_install_local_composer_and_dependencies.sh`
- `php composer.phar --version`
- `./app/vendor/bin/phpunit --configuration app/phpunit.xml.dist app/tests/BootstrapGeneratorTest.php`
- `php -d phar.readonly=0 ./builder/vendor/bin/phpunit --configuration builder/phpunit.xml.dist builder/tests/PharBuilderTest.php`
- Confirmación manual de `app/vendor`, `builder/vendor`, `app/composer.lock` y `builder/composer.lock`.

## Result
El repo ahora incluye `actions/php_dependencies/01_bootstrap/01_install_local_composer_and_dependencies.sh`, que descarga `composer.phar` localmente, verifica la firma del instalador y ejecuta la instalación de dependencias en `app/` y `builder/` sin requerir Composer global. También se documentó el flujo en `README.md` y se dejó explícita en `AGENTS.md` la preferencia por acciones numeradas dentro de `actions/`.

## Change Log
- 2026-03-26 19:51:44: Spec created.
- 2026-03-26 19:51:44: Spec moved to active to implement local Composer bootstrap and dependency installation flow.
- 2026-03-26 19:53:32: Added local Composer bootstrap script, documented local development setup, validated dependency installation in `app/` and `builder`, and moved spec to done.
- 2026-03-26 19:54:32: Spec moved back to active to align the bootstrap with the repository action/script naming convention and document that convention in AGENTS.
- 2026-03-26 19:55:21: Moved the bootstrap into `actions/php_dependencies/01_bootstrap/`, updated docs and AGENTS conventions, revalidated execution, and moved spec back to done.
