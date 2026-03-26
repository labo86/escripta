# Add an agent-optimized Escripta guide next to AGENTS.md

Created At: 2026-03-26 19:39:18
Last Updated At: 2026-03-26 19:40:18
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
- [ ] Definir nombre final del documento (recomendado: `ESCRIPTA_AGENTS.md`).
- [ ] Redactar versión inicial optimizada para agentes.
- [ ] Agregar sección de referencia en `AGENTS.md`.
- [ ] Revisar consistencia con reglas actuales de `fetchConfig`.

## Validation
- Revisión manual del flujo con un caso de ejemplo.
- Verificar que un agente pueda inferir variables y uso leyendo solo `AGENTS.md` + guía.

## Result
Pendiente.

## Change Log
- 2026-03-26 19:39:18: Spec created.
- 2026-03-26 19:40:18: Added note that docs/integracion_escripta_para_otro_repo.md serves this goal but is manual.
