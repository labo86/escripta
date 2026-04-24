# Add an agent-optimized Escripta guide next to AGENTS.md

Created At: 2026-03-26 19:39:18
Last Updated At: 2026-04-24 18:50:30
Template Version: v1

## Context
Los agentes necesitan una guía directa y corta sobre Escripta. La documentación actual es completa, pero no siempre está optimizada para consumo rápido por agentes durante ejecución.
Existe `docs/integracion_escripta_para_otro_repo.md`, que fue hecho con este objetivo, pero su uso sigue siendo manual.

## Objective
Agregar un archivo junto a `AGENTS.md` (por ejemplo `ESCRIPTA_AGENTS.md`) que explique cómo funciona Escripta en formato optimizado para agentes, y enlazarlo explícitamente desde `AGENTS.md`.

## Scope
- Definir un archivo guía para agentes con:
  - flujo de `fetchConfig`
  - reglas de naming y contrato `ESCRIPTA_*`
  - reglas de multilinea (`*_FILENAME`)
  - ubicación de artefactos generados
  - checklist corto para generar scripts correctos
- Agregar referencia explícita en `AGENTS.md` para que los agentes lo consulten.
- Mantener contenido sin secretos ni valores sensibles.

## Out of Scope
- Reemplazar documentación extensa existente en `docs/`.
- Cambiar comportamiento funcional de Escripta.

## Success Criteria
- Existe archivo agent-optimized en root, junto a `AGENTS.md`.
- `AGENTS.md` referencia ese archivo claramente.
- El documento permite deducir variables y uso sin leer código fuente.
- Contenido breve, preciso y accionable para agentes.

## Plan
- [x] Definir nombre final del documento: `ESCRIPTA_AGENTS.md`.
- [x] Redactar versión inicial optimizada para agentes.
- [x] Agregar sección de referencia en `AGENTS.md`.
- [x] Revisar consistencia con reglas actuales de `fetchConfig`.

## Validation
- Revisión manual contra `app/src/Escripta.php`, `app/src/connectors/Config.php`, `app/src/BootstrapGenerator.php`, `docs/integracion_escripta_para_otro_repo.md` y el caso real `actions/build_and_deploy/config.php`.
- Ejecutado `./app/vendor/bin/phpunit --testdox app/tests/BootstrapGeneratorTest.php app/tests/connectors`: OK, 23 tests, 64 assertions.
- Caso real revisado: `actions/build_and_deploy/config.php` usa bloque `release`; `actions/build_and_deploy/escripta_env_vars.md` contiene `ESCRIPTA_RELEASE_BASE_URL`, `ESCRIPTA_RELEASE_PHAR_FILENAME`, `ESCRIPTA_RELEASE_SHA256_FILENAME`, `ESCRIPTA_RELEASE_GITHUB_REPOSITORY`, `ESCRIPTA_RELEASE_GITHUB_TOKEN`, `ESCRIPTA_CURRENT_DIR` y `ESCRIPTA_PROJECT_DIR`, consistente con la guía.

## Result
Entregado y validado. Se agregó `ESCRIPTA_AGENTS.md`, se enlazó desde `AGENTS.md`, y se verificó consistencia con el comportamiento actual de `fetchConfig`, flattening y generación de variables.

## Change Log
- 2026-03-26 19:39:18: Spec created.
- 2026-03-26 19:40:18: Added note that docs/integracion_escripta_para_otro_repo.md serves this goal but is manual.
- 2026-04-24 18:46:40: Spec moved from `spec/inbox/` to `spec/active/` to continue implementation.
- 2026-04-24 18:46:40: Added initial `ESCRIPTA_AGENTS.md` guide and linked it from `AGENTS.md`; validation remains pending.
- 2026-04-24 18:50:30: Validated guide against implementation and existing manifest; PHPUnit passed with 23 tests and 64 assertions.
- 2026-04-24 18:50:30: Spec moved from `spec/active/` to `spec/done/` after delivery and validation.
