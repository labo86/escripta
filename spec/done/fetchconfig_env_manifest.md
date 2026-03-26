# fetchConfig generates a readable environment variables manifest

Created At: 2026-03-26 19:37:16
Last Updated At: 2026-03-26 19:48:45
Template Version: v1

## Context
Hoy para conocer variables disponibles se inspecciona `escripta_env.sh` y se infiere manualmente. Esto agrega fricción para agentes y personas.

## Objective
Hacer que `fetchConfig` genere un archivo de documentación legible, sin valores sensibles, con la lista de variables de entorno creadas.

## Scope
- Definir y generar un archivo de salida en texto plano o markdown (por ejemplo `escripta_env_vars.md` o `escripta_env_vars.txt`) junto a `escripta_env.sh`.
- Incluir todas las variables `ESCRIPTA_*` que se exportan.
- Diferenciar variables normales y variables `*_FILENAME` (multilínea).
- Mantener el archivo sin valores secretos para permitir commit.
- Agregar/actualizar tests.

## Out of Scope
- Incluir valores reales de config.
- Reemplazar `escripta_env.sh`.
- Diseñar un formato complejo con metadatos avanzados.

## Success Criteria
- Tras ejecutar `fetchConfig`, existe el archivo de manifiesto de variables.
- El archivo enumera variables en formato legible y estable.
- No contiene valores sensibles.
- El contenido refleja exactamente las variables exportadas por `escripta_env.sh`.
- Tests cubren consistencia y ausencia de valores.

## Plan
- [x] Definir nombre y formato final del archivo de manifiesto.
- [x] Implementar generación desde el mismo flujo de bootstrap.
- [x] Asegurar sincronía 1:1 con variables exportadas.
- [x] Agregar pruebas automáticas.
- [x] Documentar uso esperado para agentes.

## Validation
- `php -l app/src/BootstrapGenerator.php`
- `php -l app/tests/BootstrapGeneratorTest.php`
- `php -r 'require "app/src/BootstrapGenerator.php"; ...'` para generar archivos temporales, comparar variables exportadas vs manifest y verificar ausencia de secretos.

## Result
`fetchConfig` ahora genera `escripta_env_vars.md` junto a `escripta_env.sh`, agrupando variables normales y `*_FILENAME` sin incluir valores. La generación usa la misma colección de variables que el script shell para mantener sincronía exacta.

## Change Log
- 2026-03-26 19:37:16: Spec created.
- 2026-03-26 19:43:02: Spec moved to active to implement generated environment manifest from BootstrapGenerator.
- 2026-03-26 19:44:26: Implemented `escripta_env_vars.md`, added manifest consistency test coverage, documented agent usage, and validated generation with PHP syntax checks plus manual runtime verification.
- 2026-03-26 19:48:45: Spec moved to done after recording validation evidence and close-out result.
