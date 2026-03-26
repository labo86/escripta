# fetchConfig ensures .gitignore entries for generated outputs

Created At: 2026-03-26 19:37:16
Last Updated At: 2026-03-26 19:37:16
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
- [ ] Diseñar formato de bloque en `.gitignore` (comentario inicio/fin + entradas).
- [ ] Implementar función de aseguramiento idempotente.
- [ ] Integrar llamada desde `fetchConfig`.
- [ ] Agregar pruebas unitarias/integración.
- [ ] Validar comportamiento con `.gitignore` preexistente.

## Validation
- Ejecutar suite de tests relevante.
- Verificar manualmente archivo `.gitignore` generado en un caso limpio y uno con contenido preexistente.

## Result
Pendiente.

## Change Log
- 2026-03-26 19:37:16: Spec created.
