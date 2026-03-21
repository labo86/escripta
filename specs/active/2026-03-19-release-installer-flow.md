# Release Installer Flow

## Status

Done

## Summary

Unificar la forma en que Escripta se publica, se instala y se actualiza para otros repositorios. El objetivo es que `escripta.phar` sea el artefacto principal de distribución y que pueda actualizarse a sí mismo con un comando como `php escripta.phar -U`, usando la misma configuración de release que hoy vive en `actions/build_and_deploy`.

## Problem

Hoy el flujo está partido:

- `.escripta/manage_escripta/update.sh` clona `latest_release` directamente por `git@github.com:labo86/escripta`, lo que lo acopla a SSH y a una rama concreta en vez de usar releases versionados.
- `actions/build_and_deploy` construye y publica `escripta.phar`, pero el mecanismo de instalación y actualización no está centrado en ese artefacto.
- No hay una fuente única de verdad para decidir qué artefactos se publican y desde qué URL se descargan.
- El README no ofrece un flujo simple y principal basado solo en `escripta.phar`.
- Otros repositorios no tienen una experiencia clara donde instalar sea descargar el phar y actualizar sea ejecutar `-U`.

## Goals

- Hacer que `escripta.phar` sea el artefacto principal de distribución.
- Permitir instalación inicial por descarga directa de `escripta.phar`.
- Permitir actualización desde el propio phar con `php escripta.phar -U`.
- Evitar duplicar configuración entre build, publicación y actualización.
- Dejar documentado en el README cómo instalar y actualizar Escripta.

## Non-Goals

- Rediseñar toda la arquitectura de deployment del proyecto.
- Cambiar el formato de `escripta.phar`.
- Introducir un package manager nuevo.
- Resolver distribución multiplataforma más allá de `bash` y `php` en esta iteración.

## Scope

Archivos y flujos afectados:

- `.escripta/manage_escripta/update.sh`
- `actions/build_and_deploy/`
- proceso que publica el release
- README principal
- artefactos incluidos en el release
- CLI de `escripta.phar`

## Constraints

- El flujo debe seguir siendo simple de usar desde otros repositorios.
- La publicación debe salir de un único origen de configuración.
- La actualización debe funcionar sin requerir `git clone` del repo completo.
- El resultado puede mantener compatibilidad temporal con `latest_release`, pero el flujo objetivo debe centrarse en GitHub Releases por tag.
- El self-update debe reemplazar el phar de forma segura, validando integridad antes de dejarlo activo.

## Proposed Design

### Base proposal

Hacer que `actions/build_and_deploy` produzca como artefacto principal de distribución:

- `escripta.phar`

Y además publique metadata suficiente para que el propio phar pueda actualizarse:

- URL canónica de descarga de `escripta.phar`
- opcionalmente `checksums.txt` o manifiesto equivalente
- opcionalmente metadata de versión o canal

`update.sh` deja de ser el mecanismo principal. Puede mantenerse solo como compatibilidad o conveniencia, pero el contrato principal de instalación y actualización pasa a ser:

- instalación: descargar `escripta.phar`
- actualización: ejecutar `php escripta.phar -U`

El release publicado por tag debería contener al menos:

- `escripta.phar`
- `escripta.phar.sha256`
- `release.json` como metadata consumible por el self-update e instalación

### Installer behavior

La instalación principal debería requerir solo descargar `escripta.phar` al directorio esperado por el repo consumidor.

Ejemplo conceptual:

- crear el directorio destino
- descargar `escripta.phar` desde la URL oficial por HTTPS
- ejecutar el phar normalmente

Si `update.sh` sigue existiendo, debe considerarse accesorio y no obligatorio.

### Self-update behavior

`php escripta.phar -U` debería:

- determinar la ruta del phar que se está ejecutando
- consultar la URL o metadata oficial del release
- descargar una nueva versión a un archivo temporal
- validar integridad o checksum antes de reemplazar
- reemplazar el phar actual de forma segura
- fallar sin corromper la instalación previa si la descarga o validación no son correctas

Opcionalmente puede aceptar:

- versión o tag explícito
- canal como `latest` o equivalente
- modo `check` sin reemplazo

### README behavior

El README debería incluir comandos listos para usar, priorizando:

- descargar solo `escripta.phar`
- actualizar una instalación existente con `php escripta.phar -U`

Si se mantiene `update.sh`, su uso debería quedar documentado como compatibilidad o conveniencia, no como camino principal.

### Publishing model

El flujo debe centrarse en GitHub Releases publicados por tag en este repositorio.

El modelo aceptado por ahora es:

- build local o desde cualquier automatización externa
- creación de un tag de release en este repo
- publicación de assets en GitHub Releases

URLs de consumo esperadas:

- latest estable: `https://github.com/labo86/escripta/releases/latest/download/<asset>`
- release versionado: `https://github.com/labo86/escripta/releases/download/<tag>/<asset>`

El self-update puede consumir `releases/latest/download/release.json` como canal principal sin depender de `raw.githubusercontent.com`.

## Recommended Alternative

Un flujo mejor que depender de `git clone --branch latest_release` o de un bootstrap script separado es publicar `escripta.phar` como asset explícito de GitHub Release y dejar que el propio phar se actualice.

Recomendación:

- tratar `escripta.phar` como unidad principal de distribución
- mantener `latest_release` solo si aporta compatibilidad temporal
- evitar que `update.sh` sea una pieza necesaria del flujo normal

Ventajas:

- menos pasos para instalación inicial
- menos duplicación entre instalador y CLI
- menos fragilidad por estructura interna del repo
- instalación y actualización más claras para consumidores externos
- mejor trazabilidad si luego se quiere versionar por tag

Flujo recomendado:

1. Un script local construye `escripta.phar`.
2. El proceso de publicación crea o actualiza un release asociado a un tag y sube `escripta.phar`, su checksum y `release.json`, por ejemplo con `actions/build_and_deploy/02_github_deploy/01_publish_tagged_release.php`.
3. El usuario instala descargando `escripta.phar`.
4. El usuario actualiza ejecutando `php escripta.phar -U`.
5. Si sigue siendo necesario, `latest_release` puede mantenerse solo como compatibilidad.

## Implementation Plan

### Step 1: define release contract

- Confirmar que cada GitHub Release publicará como mínimo `escripta.phar`.
- Definir la URL canónica de descarga que usará el self-update, preferentemente `releases/latest/download`.
- Definir si habrá `checksums.txt`, manifiesto o metadata equivalente.
- Definir el directorio destino esperado en repos consumidores.
- Decidir si `-U` soportará solo `latest` o también versión/tag explícito.

Deliverable:

- contrato escrito de artefactos, nombres y URLs dentro de esta spec

Contrato actual definido:

- artefacto principal: `escripta.phar`
- checksum: `escripta.phar.sha256`
- manifiesto: `release.json`
- URL base canónica: `https://github.com/labo86/escripta/releases/latest/download`
- URLs canónicas:
- `https://github.com/labo86/escripta/releases/latest/download/escripta.phar`
- `https://github.com/labo86/escripta/releases/latest/download/escripta.phar.sha256`
- `https://github.com/labo86/escripta/releases/latest/download/release.json`

### Step 2: centralize release configuration

- Identificar en `actions/build_and_deploy` la configuración usada para publicar el release.
- Extraer URL, tag, artefactos y rutas a una fuente única.
- Diseñar esa fuente para que también pueda alimentar la lógica de self-update.
- Diseñar esa configuración para que scripts locales puedan publicar assets en GitHub Releases sin duplicar datos.

Deliverable:

- configuración compartida definida y referenciada desde build, deploy y self-update

### Step 3: implement self-update in the phar

- Agregar una opción de CLI como `-U` o `--self-update`.
- Resolver la ruta del phar actualmente en ejecución.
- Descargar la nueva versión a un archivo temporal.
- Validar checksum o metadata antes de reemplazar.
- Reemplazar el archivo actual de forma segura.
- Definir mensajes y códigos de salida del flujo de actualización.

Deliverable:

- `escripta.phar` capaz de actualizarse a sí mismo

### Step 4: publish release artifacts

- Ajustar los scripts de publicación para publicar `escripta.phar` como asset del release taggeado.
- Publicar también checksum y `release.json`.
- Validar que el release publicado contiene los artefactos esperados.
- Hacer fallar el proceso de publicación si falta alguno de los artefactos requeridos.

Deliverable:

- release consistente con artefactos suficientes para instalación y self-update

### Step 5: document consumer flow

- Agregar al README un comando para bajar `escripta.phar`.
- Documentar el flujo recomendado de instalación inicial.
- Documentar el flujo recomendado de actualización con `php escripta.phar -U`.
- Si se mantiene `update.sh`, documentarlo solo como compatibilidad o atajo opcional.

Deliverable:

- README con comandos copy/paste para instalación y actualización

### Step 6: validate end-to-end

- Probar generación local del phar.
- Probar el flujo de release en un entorno limpio.
- Probar instalación desde un repo consumidor de ejemplo.
- Probar una actualización posterior que reemplace el phar correctamente.
- Agregar pruebas automatizadas al self-update donde sea razonable.

Deliverable:

- verificación manual y automatizada del flujo completo

## Acceptance Criteria

- [x] Existe una única fuente de configuración para publicar `escripta.phar` y su metadata de actualización.
- [x] `escripta.phar` puede actualizarse a sí mismo con una opción de CLI como `-U`.
- [x] El artefacto publicado en `latest_release` o destino equivalente incluye `escripta.phar`.
- [x] El release publica la metadata necesaria para validar actualización e integridad.
- [x] El README documenta al menos un comando para bajar `escripta.phar`.
- [x] El README documenta el comando de actualización con `php escripta.phar -U`.
- [x] Un repo consumidor puede instalar y actualizar sin clonar el repo completo.

## Test Plan

- Unit tests:
- si se introduce lógica para resolver ruta, versionado o checksums, cubrir esos componentes
- Integration tests:
- validar que `build_and_deploy` deja `escripta.phar` y su metadata en el release esperado
- validar que `php escripta.phar -U` descarga y reemplaza el phar correctamente
- validar que un checksum inválido o una descarga rota no pisan la instalación existente
- Manual verification:
- ejecutar el comando documentado en README desde un repo consumidor limpio
- verificar que una actualización posterior reemplaza el phar correctamente

## Validation Notes

- Se agregaron tests automatizados para `SelfUpdate` y para el contrato de metadata embebida en el phar generado por `builder`.
- Se validó localmente la construcción del phar y la resolución de metadata de release privada.
- Se ejecutó validación manual real del flujo y funcionó correctamente en uso del usuario.

## Rollout Notes

- Puede ser necesario mantener compatibilidad temporal con el flujo actual basado en `latest_release`.
- Si se mantiene `update.sh`, debería quedar fuera del camino principal y existir solo por compatibilidad o conveniencia.
- Si se agrega versionado futuro por tags, `-U` debería admitir `latest` y una versión explícita.
- El contrato principal de instalación y self-update debe usar GitHub Releases por tag y `latest` como alias de consumo.

## Open Questions

- `latest_release` seguirá existiendo solo por compatibilidad, o puede eliminarse cuando GitHub Releases quede operativo?
- `-U` debe instalar siempre `latest`, o permitir fijar una versión?
- El artefacto oficial debe incluir checksum o validación de integridad obligatoria?
- Hace falta mantener `update.sh` por compatibilidad, o puede eliminarse del flujo principal desde ya?
