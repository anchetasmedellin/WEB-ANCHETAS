# WEB-ANCHETAS

Repositorio base del plugin de WordPress para montar la web de Anchetas Medellin sobre el sitio ya existente en `https://anchetasmedellin.online`.

## Que incluye

- Plugin `WEB ANCHETAS` listo para sincronizar desde GitHub.
- Creacion y actualizacion de paginas comerciales en espanol.
- Configuracion inicial de WooCommerce.
- Categorias y productos iniciales para la tienda.
- Ajuste de la portada principal del sitio.

## Archivos principales

- `web-anchetas.php`: archivo principal del plugin.
- `includes/class-web-anchetas-seeder.php`: logica para crear paginas, categorias y productos.

## Lo que hace el plugin

Cuando el plugin se activa o cuando se ejecuta manualmente desde el administrador de WordPress:

1. actualiza el nombre y descripcion del sitio,
2. reemplaza la portada generica por contenido de Anchetas Medellin,
3. crea paginas de negocio como `Nosotros`, `Contacto`, `Anchetas de cumpleanos`, `Desayunos sorpresa`, `Regalos empresariales` y `Anchetas romanticas`,
4. crea categorias de WooCommerce,
5. crea productos iniciales de ejemplo listos para editar.

## Como usarlo

1. Sincroniza este repositorio con tu plugin de GitHub en WordPress.
2. Asegurate de que WordPress lo reconozca como plugin.
3. Activa `WEB ANCHETAS`.
4. En el panel de WordPress entra a `WEB ANCHETAS` y pulsa `Crear o actualizar contenido inicial`.

## Siguiente paso recomendado

Despues de activar este plugin, el siguiente paso es conectar escritura autenticada a WordPress para que yo pueda crear y actualizar paginas, productos y publicaciones directamente por API sin entrar manualmente al panel.
