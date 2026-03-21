# Release Installer Flow

## Status

Active

## Summary

Unificar la forma en que Escripta se publica, se instala y se actualiza para otros repositorios. El objetivo es que `escripta.phar` sea el artefacto principal de distribución y que pueda actualizarse a sí mismo con un comando como `php escripta.phar -U`, usando la misma configuración de release que hoy vive en `actions/build_and_deploy`.

## Problem

Hoy el flujo está partido:

- `.escripta/manage_escripta/update.sh` clona `latest_release` directamente por `git@github.com:labo86/escripta`, lo que lo acopla a SSH y a una rama concreta.
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
- proceso que publica `latest_release`
- README principal
- artefactos incluidos en el release
- CLI de `escripta.phar`

## Constraints

- El flujo debe seguir siendo simple de usar desde otros repositorios.
- La publicación debe salir de un único origen de configuración.
- La actualización debe funcionar sin requerir `git clone` del repo completo.
- El resultado debe ser compatible con el flujo actual basado en `latest_release`, al menos durante transición.
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

La branch o destino `latest_release` debería contener al menos:

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

### GitHub Actions migration

El directorio actual `actions/build_and_deploy/` no corresponde a un workflow estándar de GitHub Actions. Hoy contiene scripts locales para construir `escripta.phar` y luego clonar, copiar, commitear y pushear el contenido publicado hacia `latest_release`.

Se considera válido y deseable migrar esa orquestación a un workflow real en `.github/workflows/`, por ejemplo `release.yml`, siempre que se mantenga el contrato de artefactos definido en esta spec.

La migración propuesta sería:

- reutilizar el build actual del phar
- publicar `escripta.phar` como artefacto explícito del release
- publicar checksum o metadata consumible por el self-update
- opcionalmente seguir actualizando `latest_release` durante una etapa de transición

Esto permite separar mejor build y publicación, reducir dependencia de claves SSH locales y dejar el proceso reproducible desde GitHub.

## Recommended Alternative

Un flujo mejor que depender de `git clone --branch latest_release` o de un bootstrap script separado es publicar `escripta.phar` como artefacto explícito de release y dejar que el propio phar se actualice.

Recomendación:

- tratar `escripta.phar` como unidad principal de distribución
- mantener `latest_release` solo si ya aporta compatibilidad o simplicidad
- evitar que `update.sh` sea una pieza necesaria del flujo normal

Ventajas:

- menos pasos para instalación inicial
- menos duplicación entre instalador y CLI
- menos fragilidad por estructura interna del repo
- instalación y actualización más claras para consumidores externos
- mejor trazabilidad si luego se quiere versionar por tag

Flujo recomendado:

1. Un workflow de GitHub Actions construye `escripta.phar`.
2. El workflow publica `escripta.phar` y su metadata de integridad.
3. El usuario instala descargando `escripta.phar`.
4. El usuario actualiza ejecutando `php escripta.phar -U`.
5. Si sigue siendo necesario, el workflow sincroniza también `latest_release`.

## Implementation Plan

### Step 1: define release contract

- Confirmar que `latest_release` publicará como mínimo `escripta.phar`.
- Definir la URL canónica de descarga que usará el self-update.
- Definir si habrá `checksums.txt`, manifiesto o metadata equivalente.
- Definir el directorio destino esperado en repos consumidores.
- Decidir si `-U` soportará solo `latest` o también versión/tag explícito.

Deliverable:

- contrato escrito de artefactos, nombres y URLs dentro de esta spec

Contrato actual definido:

- artefacto principal: `escripta.phar`
- checksum: `escripta.phar.sha256`
- manifiesto: `release.json`
- URL base canónica: `https://raw.githubusercontent.com/labo86/escripta/latest_release`
- URLs canónicas:
- `https://raw.githubusercontent.com/labo86/escripta/latest_release/escripta.phar`
- `https://raw.githubusercontent.com/labo86/escripta/latest_release/escripta.phar.sha256`
- `https://raw.githubusercontent.com/labo86/escripta/latest_release/release.json`

### Step 2: centralize release configuration

- Identificar en `actions/build_and_deploy` la configuración usada para publicar el release.
- Extraer URL, branch, artefactos y rutas a una fuente única.
- Diseñar esa fuente para que también pueda alimentar la lógica de self-update.
- Diseñar esa configuración para que pueda ser consumida por scripts locales y por un workflow en `.github/workflows`.

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

- Implementar o ajustar un workflow de GitHub Actions para publicar `escripta.phar`.
- Publicar también checksum o metadata requeridos por el self-update.
- Validar que el release y, si aplica, `latest_release`, contienen los artefactos esperados.
- Hacer fallar el pipeline si falta alguno de los artefactos requeridos.

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

- [ ] Existe una única fuente de configuración para publicar `escripta.phar` y su metadata de actualización.
- [ ] `escripta.phar` puede actualizarse a sí mismo con una opción de CLI como `-U`.
- [ ] El artefacto publicado en `latest_release` o destino equivalente incluye `escripta.phar`.
- [ ] El release publica la metadata necesaria para validar actualización e integridad.
- [ ] El README documenta al menos un comando para bajar `escripta.phar`.
- [ ] El README documenta el comando de actualización con `php escripta.phar -U`.
- [ ] Un repo consumidor puede instalar y actualizar sin clonar el repo completo.

## Test Plan

- Unit tests:
- si se introduce lógica para resolver ruta, versionado o checksums, cubrir esos componentes
- Integration tests:
- validar que `build_and_deploy` deja `escripta.phar` y su metadata en el destino esperado
- validar que `php escripta.phar -U` descarga y reemplaza el phar correctamente
- validar que un checksum inválido o una descarga rota no pisan la instalación existente
- Manual verification:
- ejecutar el comando documentado en README desde un repo consumidor limpio
- verificar que una actualización posterior reemplaza el phar correctamente

## Rollout Notes

- Puede ser necesario mantener compatibilidad temporal con el flujo actual basado en `latest_release`.
- Si se mantiene `update.sh`, debería quedar fuera del camino principal y existir solo por compatibilidad o conveniencia.
- Si se agrega versionado futuro por tags, `-U` debería admitir `latest` y una versión explícita.

## Open Questions

- `latest_release` seguirá siendo branch publicada, o pasará a ser un concepto equivalente sobre artefactos versionados?
- `-U` debe instalar siempre `latest`, o permitir fijar una versión?
- El artefacto oficial debe incluir checksum o validación de integridad obligatoria?
- Hace falta mantener `update.sh` por compatibilidad, o puede eliminarse del flujo principal desde ya?
- El workflow de GitHub Actions publicará solo release assets, o también mantendrá sincronizada la branch `latest_release` por compatibilidad?
