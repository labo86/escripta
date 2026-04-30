Escripta
===

Instalación y actualización
---

Artefacto principal de distribución:

- `escripta.phar`

Instalación inicial:

```bash
mkdir -p .escripta/bin
curl -fsSL https://github.com/labo86/escripta/releases/latest/download/escripta.phar -o .escripta/bin/escripta.phar
chmod +x .escripta/bin/escripta.phar
php .escripta/bin/escripta.phar --version
```

Actualización de una instalación existente:

```bash
php .escripta/bin/escripta.phar -U
```

Ayuda y opciones de línea de comando:

```bash
php .escripta/bin/escripta.phar --help
```

Si ejecutas Escripta sin argumentos, también muestra esta ayuda por defecto.

Opciones disponibles:

- `--version`: muestra versión y fecha de compilación.
- `-U`, `--self-update`: actualiza el archivo `escripta.phar` actual desde el release publicado.
- `--install-agent-guide`: instala `ESCRIPTA_AGENTS.md` y `AGENTS_HINT.md` en `.escripta/`.
- `--install-agent-guide=DIR`: instala la guía en un directorio destino explícito.
- `-h`, `--help`: muestra la ayuda de CLI.

Metadata de release publicada para instalación y self-update:

- `https://github.com/labo86/escripta/releases/latest/download/release.json`
- `https://github.com/labo86/escripta/releases/latest/download/escripta.phar.sha256`

Publicación de un nuevo release:

```bash
bash actions/build_and_deploy/03_release.sh 4.1.2
```

Desarrollo local
---

Si el entorno no tiene `composer` instalado globalmente, el repo incluye un bootstrap local:

```bash
bash actions/php_dependencies/01_bootstrap/01_install_local_composer_and_dependencies.sh
```

Ese script:

- descarga `composer.phar` en la raíz del repo si todavía no existe
- verifica la firma del instalador
- ejecuta `composer install` en `app/`
- ejecuta `composer install` en `builder/`

Luego puedes usar Composer local así:

```bash
php composer.phar --working-dir=app test
php composer.phar --working-dir=builder test
```

<img src="docs/images/escripta_01.webp" alt="Escripta" style="width:50%; max-width:400px"/>
 
 <table>
 <tr><td>Nombre completo</td><td>Esmeralda Inés de la Fuente Carrasco</td></tr>
 <tr><td>Sobrenombre</td><td>Escripta, la desplegadora</td></tr>
 <tr><td>Edad</td><td>34 años</td></tr>
 <tr><td>Lugar de nacimiento</td><td>La Concepción de María Purísima del Nuevo Extremo, Reino de Chile</td></tr>
 </table>



Biografía
---

Esmeralda Inés de la Fuente Carrasco vio la primera luz en Concepción, conocida también como "La Concepción de María Purísima del Nuevo Extremo", una ciudad de gran importancia estratégica y cultural en el Reino de Chile. Hija de un distinguido arquitecto y una maestra dedicada a la educación, Esmeralda se crió en un ambiente donde el conocimiento y la pasión por el descubrimiento eran valores fundamentales.

Desde niña, Esmeralda demostró una inteligencia y un interés excepcionales por las ciencias y las matemáticas, fascinada por los complejos mecanismos de los artefactos y las maravillas de la ingeniería que estudiaba en los libros de su padre. Esta temprana pasión la llevó a adentrarse en el estudio de la ingeniería y la navegación, disciplinas en las que sobresalió, ganándose la admiración y el respeto de sus maestros y colegas.

Movida por las historias de exploradores y conquistadores que expandieron los dominios del imperio español, Esmeralda decidió emprender su propio camino como líder y exploradora. Equipada con un profundo conocimiento en tecnología y estrategia, lideró varias expediciones hacia tierras desconocidas, donde sus habilidades para la planificación y la gestión de recursos resultaron en exitosas conquistas y descubrimientos.

Con el tiempo, Esmeralda Inés de la Fuente Carrasco se erigió como una figura emblemática, reconocida no solo por sus contribuciones como conquistadora, sino también por su profunda devoción católica, que la inspiró en cada una de sus expediciones. Su fe inquebrantable la llevó a integrar símbolos y valores religiosos en sus empresas y en la fundación de nuevas comunidades, dejando un legado de fervor y devoción que perdura hasta hoy.

A pesar de enfrentar numerosos desafíos y adversidades, Esmeralda nunca dejó de creer en la posibilidad de unir mundos y culturas a través de la exploración y el entendimiento mutuo. Su vida es un testimonio de valentía, sabiduría y fe, inspirando a generaciones futuras a soñar con lo desconocido y a dejar una huella imborrable en la historia.ralda nunca dejó de creer en la posibilidad de unir mundos y culturas a través de la exploración y el entendimiento mutuo. Su vida es un testimonio de valentía, sabiduría y fe, inspirando a generaciones futuras a soñar con lo desconocido y a dejar una huella imborrable en la historia.
