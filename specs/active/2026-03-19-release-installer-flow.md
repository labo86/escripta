# Release Installer Flow

## Status

Active

## Summary

Unificar la forma en que Escripta se publica y se instala para otros repositorios. El objetivo es que `.escripta/manage_escripta/update.sh` deje de ser un script aislado y pase a formar parte del flujo oficial de release, usando la misma configuración que `actions/build_and_deploy` y quedando disponible para descarga simple desde el README.

## Problem

Hoy el flujo está partido:

- `.escripta/manage_escripta/update.sh` clona `latest_release` directamente por `git@github.com:labo86/escripta`, lo que lo acopla a SSH y a una rama concreta.
- `actions/build_and_deploy` construye y publica `escripta.phar`, pero `update.sh` no se genera ni se valida desde ese mismo flujo.
- No hay una fuente única de verdad para decidir qué artefactos se publican en `latest_release`.
- El README no ofrece un comando simple para obtener `escripta.phar` o `update.sh`.
- Otros repositorios no tienen una experiencia clara de bootstrap o actualización.

## Goals

- Hacer que `update.sh` y `escripta.phar` se publiquen desde el mismo flujo de release.
- Evitar duplicar configuración entre `.escripta/manage_escripta/update.sh` y `actions/build_and_deploy`.
- Permitir un flujo de descarga simple para consumidores externos.
- Dejar documentado en el README cómo instalar o actualizar Escripta.
- Hacer posible regenerar `update.sh` desde datos de release y no editarlo manualmente.

## Non-Goals

- Rediseñar toda la arquitectura de deployment del proyecto.
- Cambiar el formato de `escripta.phar`.
- Introducir un package manager nuevo.
- Resolver distribución multiplataforma más allá de `bash` en esta iteración.

## Scope

Archivos y flujos afectados:

- `.escripta/manage_escripta/update.sh`
- `actions/build_and_deploy/`
- proceso que publica `latest_release`
- README principal
- artefactos incluidos en el release

## Constraints

- El flujo debe seguir siendo simple de usar desde otros repositorios.
- El script de bootstrap debe ser copiable y ejecutable con pocas dependencias.
- La publicación debe salir de un único origen de configuración.
- El resultado debe ser compatible con el flujo actual basado en `latest_release`, al menos durante transición.

## Proposed Design

### Base proposal

Hacer que `actions/build_and_deploy` produzca ambos artefactos de distribución:

- `escripta.phar`
- `update.sh`

`update.sh` no debería mantenerse a mano como archivo independiente de la release. Debería generarse desde una plantilla o desde un script generador dentro del flujo de build/deploy, usando la misma configuración usada para publicar el release.

La branch o destino `latest_release` debería contener al menos:

- `escripta.phar`
- `update.sh`
- opcionalmente un `checksums.txt` o archivo equivalente de integridad

`.escripta/manage_escripta/update.sh` dentro de este repo debería pasar a ser:

- o bien un archivo generado
- o bien una copia del artefacto oficial de release

En cualquier caso, no debería divergir del script realmente publicado.

### Installer behavior

`update.sh` debería:

- descargar o copiar `escripta.phar` desde el release oficial
- funcionar sin requerir `git clone` del repo completo
- usar HTTPS por defecto para facilitar bootstrap en repositorios consumidores
- opcionalmente aceptar parámetros o variables para branch/version/canal

### README behavior

El README debería incluir comandos listos para usar, por ejemplo:

- descargar solo `escripta.phar`
- descargar `update.sh` y ejecutarlo

### GitHub Actions migration

El directorio actual `actions/build_and_deploy/` no corresponde a un workflow estándar de GitHub Actions. Hoy contiene scripts locales para construir `escripta.phar` y luego clonar, copiar, commitear y pushear el contenido publicado hacia `latest_release`.

Se considera válido y deseable migrar esa orquestación a un workflow real en `.github/workflows/`, por ejemplo `release.yml`, siempre que se mantenga el contrato de artefactos definido en esta spec.

La migración propuesta sería:

- reutilizar el build actual del phar
- generar `update.sh` dentro del workflow
- publicar `escripta.phar` y `update.sh` como artefactos explícitos del release
- opcionalmente seguir actualizando `latest_release` durante una etapa de transición

Esto permite separar mejor build y publicación, reducir dependencia de claves SSH locales y dejar el proceso reproducible desde GitHub.

## Recommended Alternative

Un flujo mejor que depender de `git clone --branch latest_release` es publicar artefactos explícitos de release y usar `update.sh` solo como bootstrap downloader.

Recomendación:

- mantener `latest_release` solo si ya aporta compatibilidad o simplicidad
- pero tratar el release como publicación de artefactos, no como clonación de repo

Ventajas:

- menos tráfico y menos acoplamiento a git
- menos fragilidad por estructura interna del repo
- instalación más clara para consumidores externos
- mejor trazabilidad si luego se quiere versionar por tag

Flujo recomendado:

1. Un workflow de GitHub Actions construye `escripta.phar`.
2. El workflow genera `update.sh` desde una plantilla con variables centralizadas.
3. El workflow publica ambos artefactos como release assets o en un destino equivalente.
4. Si sigue siendo necesario, el workflow sincroniza también `latest_release`.
5. El README expone una línea para bajar `update.sh`.
6. `update.sh` descarga `escripta.phar` al directorio esperado en el repo consumidor.

## Implementation Plan

### Step 1: define release contract

- Confirmar que `latest_release` publicará como mínimo `escripta.phar` y `update.sh`.
- Definir la URL canónica de descarga que usará `update.sh`.
- Definir el directorio destino esperado en repos consumidores.
- Decidir si `update.sh` soportará solo `latest` o también versión/tag explícito.

Deliverable:

- contrato escrito de artefactos, nombres y URLs dentro de esta spec

### Step 2: centralize release configuration

- Identificar en `actions/build_and_deploy` la configuración usada para publicar el release.
- Extraer URL, branch, artefactos y rutas a una fuente única.
- Diseñar esa fuente para que también pueda alimentar la generación de `update.sh`.
- Diseñar esa configuración para que pueda ser consumida por scripts locales y por un workflow en `.github/workflows`.

Deliverable:

- configuración compartida definida y referenciada desde deploy y script generator

### Step 3: generate installer script

- Crear una plantilla o generador para `update.sh`.
- Hacer que el script final se produzca durante build/deploy.
- Hacer que `.escripta/manage_escripta/update.sh` se sincronice desde ese artefacto generado.

Deliverable:

- `update.sh` generado y no mantenido manualmente

### Step 4: publish both artifacts

- Implementar o ajustar un workflow de GitHub Actions para publicar `escripta.phar` y `update.sh`.
- Validar que ambos quedan presentes en `latest_release`.
- Validar también que ambos queden presentes como artefactos explícitos del workflow o del release.
- Hacer fallar el pipeline si falta alguno de los artefactos esperados.

Deliverable:

- release consistente con ambos artefactos

### Step 5: document consumer flow

- Agregar al README un comando para bajar `update.sh`.
- Agregar al README un comando para bajar `escripta.phar`.
- Documentar el flujo recomendado de instalación inicial y actualización.

Deliverable:

- README con comandos copy/paste para consumidores externos

### Step 6: validate end-to-end

- Probar generación local del instalador.
- Probar el flujo de release en un entorno limpio.
- Probar bootstrap desde un repo consumidor de ejemplo.
- Agregar pruebas automatizadas a generador y release flow donde sea razonable.

Deliverable:

- verificación manual y automatizada del flujo completo

## Acceptance Criteria

- [ ] Existe una única fuente de configuración para publicar `escripta.phar` y `update.sh`.
- [ ] `update.sh` se genera o sincroniza automáticamente desde el flujo de release.
- [ ] El artefacto publicado en `latest_release` incluye `update.sh` y `escripta.phar`.
- [ ] El README documenta al menos un comando para bajar `update.sh`.
- [ ] El README documenta al menos un comando para bajar `escripta.phar`.
- [ ] Un repo consumidor puede copiar/pegar un comando de instalación o actualización sin clonar el repo completo.
- [ ] El flujo sigue funcionando aunque `update.sh` se ejecute fuera de este repo.

## Test Plan

- Unit tests:
- si se introduce generador para `update.sh`, cubrir generación de contenido y variables resultantes
- Integration tests:
- validar que `build_and_deploy` deja ambos artefactos en el destino esperado
- validar que `update.sh` descarga o instala `escripta.phar` correctamente en un repo de prueba
- Manual verification:
- ejecutar el comando documentado en README desde un repo consumidor limpio
- verificar que una actualización posterior reemplaza el phar correctamente

## Rollout Notes

- Puede ser necesario mantener compatibilidad temporal con el flujo actual basado en `latest_release`.
- Si el script cambia de `git clone` a descarga por HTTPS, conviene mantener claro el canal por defecto.
- Si se agrega versionado futuro por tags, `update.sh` debería admitir `latest` y una versión explícita.

## Open Questions

- `latest_release` seguirá siendo branch publicada, o pasará a ser un concepto equivalente sobre artefactos versionados?
- `update.sh` debe instalar siempre `latest`, o permitir fijar una versión?
- El artefacto oficial debe incluir checksum o validación de integridad?
- El repo consumidor debe bajar solo el phar o también otros archivos auxiliares?
- El workflow de GitHub Actions publicará solo release assets, o también mantendrá sincronizada la branch `latest_release` por compatibilidad?
