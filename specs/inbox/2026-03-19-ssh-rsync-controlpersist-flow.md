# SSH Rsync ControlPersist Flow

## Status

Draft

## Summary

Agregar un flujo nuevo para ejecutar muchos comandos remotos por SSH de forma eficiente, subiendo scripts con `rsync` y reutilizando conexiones con `ControlPersist`, `ControlPath` y `ControlMaster`.

## Problem

Cuando hay que ejecutar muchos pasos remotos, abrir una conexión SSH nueva por cada comando es lento y frágil. El repo ya tiene ejemplos de administración remota, pero falta un flujo explícito y mantenido que combine:

- subida de scripts por `rsync`
- ejecución remota repetida
- multiplexación de SSH

## Goals

- Crear un flujo de ejemplo para operaciones remotas repetidas por SSH.
- Reducir overhead reutilizando la misma conexión SSH.
- Usar Escripta para generar variables de conexión, rutas y credenciales.
- Dejar scripts reutilizables para otros repositorios.

## Non-Goals

- Reemplazar herramientas completas de orquestación.
- Resolver paralelismo distribuido en esta iteración.
- Cubrir todos los sistemas remotos posibles.

## Scope

Áreas potenciales:

- `examples/script_examples/` o un nuevo ejemplo dedicado
- scripts de `rsync`
- scripts de ejecución remota
- configuración SSH multiplexed

## Constraints

- El flujo debe ser entendible y operativo con herramientas estándar.
- Debe quedar claro dónde vive `ControlPath`.
- Debe evitar configuraciones frágiles o inseguras por defecto.
- Debe ser fácil de adaptar por proyecto.

## Proposed Design

### Base flow

El ejemplo debería cubrir:

- preparar una conexión multiplexada
- subir scripts a un directorio remoto con `rsync`
- ejecutar varios scripts remotos sobre la misma sesión lógica
- cerrar la conexión multiplexada al final

### Escripta integration

Escripta debería generar variables como:

- host remoto
- usuario remoto
- puerto SSH
- ruta de clave privada
- directorio remoto de trabajo
- ruta local de scripts
- ruta del `ControlPath`

### Candidate scripts

- `open_connection.sh`
- `upload_scripts.sh`
- `run_remote_step.sh`
- `run_remote_all.sh`
- `close_connection.sh`

## Implementation Plan

### Step 1: define the remote execution contract

- Definir los parámetros mínimos de conexión y despliegue.
- Definir qué opciones SSH serán obligatorias.

### Step 2: model the Escripta-generated environment

- Diseñar las variables exportadas por Escripta para el flujo remoto.
- Asegurar nombres claros y reutilizables.

### Step 3: implement multiplexed SSH helpers

- Crear los helpers para abrir, reutilizar y cerrar la conexión.
- Encapsular opciones `ControlPersist`, `ControlPath` y `ControlMaster`.

### Step 4: implement rsync + remote script execution

- Subir scripts o assets al host remoto.
- Ejecutar múltiples pasos remotos usando la conexión ya abierta.

### Step 5: document consumer usage

- Explicar cómo adaptar host, clave, rutas y scripts.
- Documentar el flujo recomendado para otros repositorios.

## Acceptance Criteria

- [ ] Existe un flujo de ejemplo para subir scripts por `rsync` y ejecutarlos remotamente por SSH.
- [ ] El flujo usa multiplexación con `ControlPersist`, `ControlPath` y `ControlMaster`.
- [ ] La configuración necesaria se obtiene desde variables generadas por Escripta.
- [ ] El ejemplo documenta cómo abrir, usar y cerrar la conexión.
- [ ] El flujo puede reutilizarse en otros repositorios con cambios mínimos.

## Test Plan

- Integration tests:
- validar construcción de comandos SSH y `rsync`
- validar que varios comandos remotos reutilizan el mismo socket de control
- Manual verification:
- subir scripts a un host de prueba y ejecutar múltiples pasos remotos con una sola conexión persistente

## Open Questions

- Este flujo vive como ejemplo nuevo o extiende `examples/script_examples/`?
- Conviene agrupar todos los comandos remotos en un solo wrapper o mantener scripts pequeños?
- Qué defaults de seguridad deben venir activados por defecto?
