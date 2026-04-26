# fetchConfig ensures .gitignore entries for generated outputs

Created At: 2026-03-26 19:37:16
Last Updated At: 2026-04-24 18:58:10
Template Version: v1

## Context
`fetchConfig` genera `escripta_env.sh` y un directorio de configuración generado. Hoy no garantiza que esos artefactos queden ignorados por git en el lugar correcto.

## Objective
Hacer que `fetchConfig` cree o actualice un `.gitignore` en el directorio donde genera `escripta_env.sh` para ignorar:
- `escripta_env.sh`
- el directorio de config generado (actualmente `config.gen/`)

## Scope
- Implementar lógica en `fetchConfig` (o componente cercano) para asegurar `.gitignore`.
- Si `.gitignore` no existe, crearlo.
- Si existe, agregar entradas faltantes sin duplicar.
- Agregar una línea/comentario identificando que esa sección fue agregada por Escripta.
- Agregar/actualizar tests automatizados.

## Out of Scope
- Reescribir entradas existentes del usuario.
- Ordenamiento global completo de `.gitignore`.
- Manejo de `.gitignore` en otros directorios fuera del output de `fetchConfig`.

## Success Criteria
- Después de ejecutar `fetchConfig`, existe `.gitignore` en el output root.
- `.gitignore` contiene entradas para `escripta_env.sh` y `config.gen/`.
- Existe marcador claro de sección administrada por Escripta.
- Re-ejecutar `fetchConfig` no duplica líneas.
- Tests pasan y cubren creación + actualización idempotente.

## Plan
- [x] Diseñar formato de bloque en `.gitignore` (comentario inicio/fin + entradas).
- [x] Implementar función de aseguramiento idempotente.
- [x] Integrar llamada desde `fetchConfig`.
- [x] Agregar pruebas unitarias/integración.
- [x] Validar comportamiento con `.gitignore` preexistente.

## Validation
- Ejecutado `./app/vendor/bin/phpunit --testdox app/tests/BootstrapGeneratorTest.php app/tests/connectors/ConfigTest.php`: OK, 23 tests, 73 assertions.
- Ejecutado `./app/vendor/bin/phpunit --testdox app/tests`: OK, 49 tests, 115 assertions, 2 skipped.
- La suite cubre creación de `.gitignore` desde cero con bloque Escripta y actualización idempotente de `.gitignore` preexistente con `var/`, `escripta_env.sh` y `config.gen/`.
- La suite cubre también la normalización de una entrada preexistente `config.gen` hacia la entrada gestionada `config.gen/`.

## Result
Entregado y validado. `BootstrapGenerator` asegura un bloque administrado en `.gitignore` para `escripta_env.sh` y el directorio generado de config, preservando entradas no relacionadas y evitando duplicados en re-ejecuciones.

## Change Log
- 2026-03-26 19:37:16: Spec created.
- 2026-04-24 18:52:10: Spec moved from `spec/inbox/` to `spec/active/` to implement the gitignore guard.
- 2026-04-24 18:52:10: Chose managed block format with `# BEGIN Escripta generated outputs` and `# END Escripta generated outputs`.
- 2026-04-24 18:53:47: Implemented idempotent `.gitignore` guard in `BootstrapGenerator`, added tests for clean and preexisting `.gitignore`, and validated with PHPUnit.
- 2026-04-24 18:53:47: Spec moved from `spec/active/` to `spec/done/` after delivery and validation.
- 2026-04-24 18:54:20: Ran full app PHPUnit suite; 49 tests and 114 assertions passed, with 2 integration tests skipped by environment.
- 2026-04-24 18:58:10: Normalized preexisting `config.gen` entries to avoid semantic duplication with managed `config.gen/`; full app PHPUnit suite passed with 49 tests, 115 assertions, and 2 skipped.
