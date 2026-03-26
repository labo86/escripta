# Functional Example Flows

## Status

Draft

## Summary

Convertir la carpeta `examples/` en una colección de flujos realmente funcionales y reutilizables, donde Escripta genere `escripta_env.sh` y los scripts derivados puedan copiarse o adaptarse en otros repositorios sin depender de scripts legacy desconectados.

## Problem

Hoy `examples/` contiene casos de uso valiosos, pero la experiencia está partida:

- Escripta genera un script de entorno (`escripta_env.sh`) pensado para ejecutarse con `source`.
- Los scripts internos dependen de nombres de variables de entorno generados a partir de configuraciones específicas.
- Existen carpetas y scripts legacy pensados para copiar y pegar en otros repositorios.
- No está claro cuáles de esos ejemplos están realmente funcionales de punta a punta.
- No hay una convención suficientemente fuerte para que un ejemplo sirva como plantilla viva y no solo como referencia parcial.

## Goals

- Hacer que los ejemplos principales de `examples/` sean funcionales de punta a punta.
- Asegurar que cada ejemplo pueda regenerar su `config.gen` y su `escripta_env.sh`.
- Hacer explícito qué variables de entorno expone cada ejemplo.
- Permitir que los scripts de ejemplo se puedan copiar y adaptar en otros repositorios con cambios mínimos.
- Reducir o reemplazar scripts legacy que ya no reflejan el flujo real de Escripta.

## Non-Goals

- Reescribir todos los ejemplos en una sola iteración.
- Eliminar inmediatamente todos los scripts legacy sin plan de migración.
- Convertir ejemplos en productos totalmente genéricos o framework-agnostic.
- Resolver distribución pública de estos ejemplos fuera del repo en esta fase.

## Scope

Áreas afectadas:

- `examples/`
- generadores de `config.gen`
- generación y uso de `escripta_env.sh`
- scripts de ejemplo materializados
- documentación de uso en los ejemplos

## Constraints

- Los ejemplos deben seguir siendo simples de entender.
- El flujo debe respetar cómo Escripta nombra variables de entorno a partir de la configuración.
- Los ejemplos deben poder ejecutarse con dependencias razonables y claramente documentadas.
- Si un ejemplo no puede ser totalmente funcional, debe declarar qué parte es demostrativa.

## Proposed Design

### Base idea

Tratar cada ejemplo como un flujo autocontenido con cuatro piezas claras:

- fuente de configuración
- generación de `config.gen`
- generación de `escripta_env.sh`
- scripts consumidores de esas variables

Cada ejemplo debería poder responder claramente:

- cómo se genera
- qué variables exporta
- qué scripts usan esas variables
- qué partes son seguras de copiar a otro repo

### Functional example contract

Cada ejemplo funcional debería incluir:

- un `config.php` o equivalente para generar configuración
- un `README.md` local con pasos de uso
- scripts que funcionen leyendo variables desde `source ./escripta_env.sh`
- nombres de variables previsibles y documentados

### Legacy migration idea

Los scripts legacy deberían pasar a una de estas categorías:

- migrados a un ejemplo funcional real
- marcados explícitamente como legacy/no mantenidos
- eliminados si ya no aportan valor

### Recommended approach

No intentar normalizar todo con demasiada abstracción desde el inicio. Es mejor:

1. elegir 1 o 2 ejemplos prioritarios
2. hacerlos totalmente funcionales
3. extraer el patrón común
4. aplicar ese patrón al resto

Los candidatos más claros parecen:

- `examples/script_examples/`
- `examples/vbox/`

porque ya muestran generación de config y scripts consumidores.

## Implementation Plan

### Step 1: inventory current example states

- Listar qué ejemplos existen.
- Identificar cuáles generan `config.gen` y `escripta_env.sh`.
- Identificar cuáles tienen scripts funcionales y cuáles son solo referencia.
- Identificar qué scripts legacy siguen siendo útiles.

Deliverable:

- matriz simple de ejemplos con estado actual y gaps

### Step 2: define a contract for a functional example

- Definir la estructura mínima de un ejemplo funcional.
- Definir cómo documentar variables exportadas.
- Definir cómo marcar scripts copiables versus scripts internos.

Deliverable:

- contrato documentado para ejemplos funcionales

### Step 3: upgrade one reference example end-to-end

- Elegir un ejemplo prioritario.
- Asegurar generación completa de `config.gen` y `escripta_env.sh`.
- Validar que los scripts realmente funcionen consumiendo variables generadas.
- Documentar pasos reales de ejecución.

Deliverable:

- un ejemplo de referencia completamente funcional

### Step 4: align or replace legacy scripts

- Mapear scripts legacy con el ejemplo funcional equivalente.
- Migrar, marcar o eliminar los que no representen el flujo actual.
- Evitar dejar duplicados que diverjan.

Deliverable:

- scripts legacy alineados con el flujo vigente

### Step 5: expand pattern to more examples

- Aplicar el patrón a los ejemplos restantes de mayor valor.
- Priorizar los ejemplos más reutilizables para otros repositorios.

Deliverable:

- al menos dos ejemplos funcionales con estructura coherente

### Step 6: document reuse in external repositories

- Explicar qué archivos copiar.
- Explicar qué variables deben redefinirse.
- Explicar cómo regenerar configuración en el repo consumidor.

Deliverable:

- guía clara de reutilización desde otros repos

## Acceptance Criteria

- [ ] Existe una definición explícita de qué es un ejemplo funcional.
- [ ] Al menos un ejemplo funciona de punta a punta con `config.php` y `escripta_env.sh`.
- [ ] Los scripts del ejemplo leen variables reales generadas por Escripta.
- [ ] Cada ejemplo funcional documenta cómo generar y usar su entorno.
- [ ] Los scripts legacy relevantes quedan migrados, marcados o eliminados.
- [ ] Queda claro qué partes pueden copiarse a otros repositorios como base.

## Test Plan

- Unit tests:
- cubrir generadores o helpers si se agregan utilidades comunes
- Integration tests:
- ejecutar generación de config y verificar que `escripta_env.sh` exporta las variables esperadas
- ejecutar al menos un script consumidor por ejemplo funcional
- Manual verification:
- seguir el README de un ejemplo desde cero
- copiar el ejemplo funcional a un repo sandbox y validar adaptación mínima

## Rollout Notes

- Conviene empezar por pocos ejemplos y dejar visible cuáles están oficialmente soportados.
- Los ejemplos no funcionales deberían quedar marcados para no confundir a consumidores nuevos.
- Puede ser útil distinguir entre `examples/official/` y legacy en una fase posterior, si la mezcla actual sigue creciendo.

## Open Questions

- Cuáles son hoy los scripts legacy que realmente vale la pena conservar?
- Qué ejemplos deben considerarse oficialmente soportados primero?
- Hace falta una convención para documentar variables exportadas por ejemplo?
- Conviene generar scripts materializados a partir de templates en todos los ejemplos o solo en algunos?
