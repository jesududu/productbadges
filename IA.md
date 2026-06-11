# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |
|---|---|---|---|
| ChatGPT web | GPT-4o / Claude 3.5 Sonnet | Consultas interactivas, depuración de arquitectura y generación de esqueletos base. | 70% |
| Ninguna | — | Resolución manual de bloqueos de caché, configuración de puertos de MAMP e inspección de base de datos. | 30% |

## 2. Configuración del proyecto

### CLAUDE.md / AGENTS.md
Ninguno. Al realizarse mediante la interfaz web interactiva tradicional, no se han acoplado archivos de instrucciones a nivel de agente local de terminal.

### settings.json u otra configuración equivalente
Ninguna. Se han utilizado las extensiones nativas por defecto de VS Code para el resaltado de sintaxis PHP y Smarty.

## 3. Skills personalizadas
Ninguna.

## 4. Slash commands personalizados
Ninguna.

## 5. Sub-agentes invocados
Ninguna.

## 6. MCPs (Model Context Protocol)
Ninguno. Con más tiempo de desarrollo, se habría integrado el servidor filesystem para automatizar las escrituras, pero se ha preferido un flujo de control manual y consciente de cada línea.

## 7. Prompts importantes

### Prompt 1
- **Herramienta:** ChatGPT
- **Prompt:** Genera la estructura de base de datos relacional para un módulo de PrestaShop 1.7 llamado productbadges que guarde texto, color_bg, color_text, posición y que sea multilenguaje con una relación muchos a muchos con productos.
- **Qué generó (resumen):** Los esquemas SQL para install.php e uninstall.php.
- **Qué hice con el output:** Lo acepté en su mayoría pero cambié la estructura de los prefijos para forzar la compatibilidad nativa mediante `_DB_PREFIX_`.

### Prompt 2
- **Herramienta:** ChatGPT
- **Prompt:** Necesito el código de un AdminProductBadgesController para PrestaShop 1.7 que use HelperList y HelperForm para gestionar un modelo que tiene campos de color y relación múltiple de productos.
- **Qué generó (resumen):** El esqueleto del controlador administrativo con `renderForm()`.
- **Qué hice con el output:** Modifiqué sustancialmente el método `postProcess` y añadí `updateProductRelations` debido a que el output inicial olvidaba vaciar las relaciones antiguas en la base de datos antes de guardar las nuevas, duplicando las filas.

## 8. Errores de la IA que detecté

- **Qué generó la IA (mal):** Rutas obsoletas e inexistentes en PrestaShop 1.7.8 para saltar el bloqueo de versión del instalador con PHP 8.1 (sugirió modificar `ShopRequirements.php` o `system_requirements.json` que pertenecen a otras versiones de la plataforma).
- **Por qué estaba mal:** El instalador clásico de la 1.7 lee directamente las constantes del archivo centralizado `install/install_version.php`. Si no se modifican los IDs numéricos de ahí, el instalador provoca un fallo 500 fatal por incompatibilidad.
- **Cómo lo corrigiste:** Realicé una inspección manual del árbol de archivos nativo, localicé `install_version.php` y redefiní las constantes `_PS_INSTALL_MAXIMUM_PHP_VERSION_ID_` y `_PS_INSTALL_MAXIMUM_PHP_VERSION_` para admitir el entorno del servidor o derivar el motor limpiamente a PHP 7.4 bajo MAMP.

- **Qué generó la IA (mal):** Inyección automática de nombres en plural basados en el identificador inicial del módulo (`productbadges`), generando el error `Unknown column 'b.id_productbadges' in 'on clause'`.
- **Por qué estaba mal:** La IA no tuvo en cuenta la automatización de persistencia y pluralización forzada que aplica el núcleo de PrestaShop 1.7 en sus joins internos sobre el modelo, provocando una rotura del mapeo relacional de base de datos.
- **Cómo lo corrigiste:** Analicé el origen de la inyección del alias, refactoricé toda la arquitectura del módulo a singular (`productbadge`) en el script SQL, ObjectModel y controladores, forzando al motor a mapear la clave primaria limpia `id_productbadge` en singular.

## 9. Partes que NO usé IA
La resolución y configuración del servidor local bajo macOS. El paso de XAMPP a MAMP para gestionar de forma limpia el puerto `8889` y la versión estricta de PHP 7.4 fue una decisión y ejecución 100% manual al detectar los bloqueos nativos del core.

## 10. Reflexión final
- **¿Qué te ahorró la IA en este ejercicio?** Una velocidad tremenda para estructurar los arrays de configuración visual de `HelperForm` y `HelperList`, ahorrándome tener que consultar las especificaciones de marcado HTML de PrestaShop en la documentación oficial.
- **¿En qué te entorpeció o te llevó por mal camino?** En la gestión técnica de las versiones internas de PHP del núcleo de PrestaShop y en el control estricto del comportamiento del ObjectModel frente a los nombres en plural, sufriendo alucinaciones sobre la ubicación de ficheros inexistentes.
- **Qué cambiarías de tu flujo con IA si lo repitieras?** Acoplaría un motor MCP indexando las DevDocs oficiales de PrestaShop 1.7 desde el primer minuto para erradicar las alucinaciones del modelo en la lógica de hooks.
