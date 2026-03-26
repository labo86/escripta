# QEMU Ubuntu Server Flow

## Status

Draft

## Summary

Agregar un flujo nuevo en Escripta para controlar una máquina Linux Ubuntu Server sobre QEMU, como alternativa al flujo existente basado en VirtualBox.

## Problem

Actualmente el repo tiene ejemplos y flujos para VirtualBox, pero no un flujo equivalente y mantenido para QEMU con Ubuntu Server. Eso limita el uso de Escripta en entornos donde QEMU es preferible por portabilidad, automatización o cercanía a despliegues reales.

## Goals

- Crear un flujo funcional de ejemplo para Ubuntu Server sobre QEMU.
- Reutilizar el patrón de Escripta basado en `config.php`, `config.gen` y `escripta_env.sh`.
- Cubrir operaciones básicas de provisión y control de la VM.
- Dejarlo como base reutilizable para otros repositorios.

## Non-Goals

- Soportar todas las variantes posibles de imágenes o distros en la primera iteración.
- Reemplazar el flujo de VirtualBox existente.
- Resolver automatización avanzada de cloud-init si no es necesaria en la primera versión.

## Scope

Áreas potenciales:

- `examples/qemu/`
- scripts de creación, arranque, apagado y conexión
- configuración de imagen Ubuntu Server
- variables de entorno generadas por Escripta

## Constraints

- El flujo debe ser usable desde Linux con dependencias razonables.
- Debe quedar claro qué partes son locales y cuáles se ejecutan dentro de la VM.
- Debe ser simple de adaptar a otros repositorios.

## Proposed Design

### Base flow

El ejemplo debería cubrir como mínimo:

- crear disco o imagen de trabajo
- arrancar una VM Ubuntu Server con QEMU
- exponer SSH al host
- conectarse por SSH
- detener y limpiar la VM

### Escripta integration

El flujo debería usar Escripta para generar:

- rutas de imagen
- puertos
- credenciales o rutas de claves
- parámetros relevantes de la VM

### Candidate scripts

- `create_image.sh`
- `create_vm.sh` o equivalente
- `start_vm.sh`
- `stop_vm.sh`
- `connect_vm.sh`
- `delete_vm.sh`

## Implementation Plan

### Step 1: define the VM contract

- Elegir versión objetivo de Ubuntu Server.
- Definir imagen base, puertos, CPU, RAM y disco.
- Definir cómo se accede por SSH.

### Step 2: define Escripta config model

- Definir las configuraciones que generarán variables para el flujo QEMU.
- Nombrar variables de entorno de manera estable y documentada.

### Step 3: implement the local control scripts

- Crear scripts de creación, inicio, parada, conexión y limpieza.
- Asegurar que todos usen `source ./escripta_env.sh`.

### Step 4: validate the example end-to-end

- Generar config.
- Crear imagen.
- Arrancar la VM.
- Probar acceso SSH.
- Detener y limpiar.

### Step 5: document reuse

- Agregar README local explicando cómo correr y adaptar el flujo.

## Acceptance Criteria

- [ ] Existe un ejemplo nuevo para Ubuntu Server sobre QEMU.
- [ ] El ejemplo genera `escripta_env.sh` y lo usa en sus scripts.
- [ ] El flujo permite crear, iniciar, conectar y detener la VM.
- [ ] El README del ejemplo documenta dependencias y pasos.
- [ ] El ejemplo puede servir como base para otro repositorio.

## Test Plan

- Integration tests:
- validar generación de entorno
- validar que los scripts resuelven rutas y parámetros esperados
- Manual verification:
- levantar una VM Ubuntu Server en QEMU y conectarse por SSH

## Open Questions

- Se usará imagen cloud, ISO o disco prearmado?
- Conviene incluir cloud-init en la primera versión?
- El flujo debe apuntar a Linux host solamente o también contemplar macOS?
