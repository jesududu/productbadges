# Gestor de Etiquetas de Producto (productbadges) - PrestaShop 1.7

Módulo nativo desarrollado para PrestaShop 1.7.8.x que permite la gestión avanzada, reutilización y asignación multilenguaje de etiquetas visuales personalizadas (badges) sobre las imágenes de los productos del catálogo (enfocado a tiendas de mascotas).

## 🛠️ Requisitos del Entorno
- **PrestaShop**: 1.7.8.11 (referencia de desarrollo)
- **PHP**: 7.4 / 8.1 (desarrollado de forma estable en PHP 7.4.x bajo entorno local MAMP en macOS)
- **Bootstrap**: Habilitado nativamente (`$this->bootstrap = true`)

## 📦 Instrucciones de Instalación
1. Descarga o clona este repositorio dentro del directorio `/modules/` de tu instalación de PrestaShop.
2. Asegúrate de que el nombre de la carpeta sea exactamente `productbadges`.
3. Accede al Back Office de PrestaShop, navega a **Módulos > Gestor de Módulos**.
4. Busca "Gestor de Etiquetas de Producto" en la sección de módulos de terceros e haz clic en **Instalar**.
5. Tras la instalación, aparecerá un nuevo acceso en el menú lateral bajo **Catálogo > Etiquetas de Productos**.

## 🧠 Decisiones Técnicas Relevantes
- **Persistencia limpia via ObjectModel**: En lugar de inyectar consultas crudas en los controladores, se ha optado por implementar la API nativa `ObjectModel` de PrestaShop. Esto centraliza las validaciones de tipo en el servidor (`isColor`, `isCleanHtml`) y delega el mapeo de tablas de forma segura.
- **Resolución de Conflicto Estructural (Singularización)**: Durante el desarrollo se detectó que el motor interno de PrestaShop aplicaba reglas automáticas de pluralización sobre la tabla principal (`productbadges`), inyectando de forma errónea una "s" en las cláusulas `ON` de los *joins* automáticos. Se tomó la decisión arquitectónica de refactorizar las tablas a singular (`productbadge`), solucionando el bug del núcleo de raíz.
- **Relación Muchos a Muchos Optimizada**: La vinculación entre productos y etiquetas se gestiona mediante una tabla intermedia indexada de forma masiva en el método `postProcess()`, limpiando registros antiguos mediante transacciones atómicas para prevenir la corrupción de datos.
- **Seguridad y Mitigación XSS/SQLi**: Todo parámetro numérico procesado en los controladores o hooks aplica tipado estricto (*casting* a `(int)`). Asimismo, todas las variables renderizadas en las vistas Smarty (`.tpl`) implementan de forma rigurosa el modificador de escape `|escape:'html':'UTF-8'`.

## ⚙️ Qué se ha dejado fuera y por qué
- **Paginación avanzada en listas muy extensas de productos**: Debido a que es un entorno controlado de prueba, el formulario carga el listado total de productos mediante `Product::getProducts`. En una tienda en producción real con más de 10.000 referencias, se habría optado por una inyección asíncrona mediante un buscador AJAX (`HelperForm` con componente autocompletado) para mitigar el consumo de memoria del servidor.
