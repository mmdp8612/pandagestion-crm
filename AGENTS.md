# PandaGestion - contexto persistente del proyecto

Ultima actualizacion: 2026-09-22

Este archivo es la referencia principal para retomar el desarrollo de PandaGestion en futuras sesiones. Debe leerse completo antes de modificar el proyecto y actualizarse despues de cada etapa terminada y verificada.

## Protocolo para futuras sesiones

1. Leer este archivo antes de proponer o implementar cambios.
2. Revisar el codigo afectado y mantener el desarrollo incremental: una funcionalidad pequena por etapa.
3. No implementar automaticamente el siguiente paso sugerido; esperar confirmacion del usuario.
4. Al finalizar una etapa, actualizar como minimo: estado implementado, rutas, migraciones, tests, decisiones nuevas y proximo paso sugerido.
5. No marcar como implementada una funcionalidad solamente planificada.
6. No guardar en este archivo contrasenas, tokens, claves, valores de `APP_KEY` ni otras credenciales.
7. No modificar estructuras historicas de `material/database.sql` sin informar inconsistencias y obtener una decision del usuario cuando el cambio sea estructural.

## Objetivo del producto

PandaGestion es un CRM inmobiliario construido como monolito Laravel. El administrador funciona bajo `/admin` y el portal publico comenzo su desarrollo incremental bajo `/`.

Principios acordados:

- Priorizar simplicidad, claridad, seguridad y mantenibilidad.
- Desarrollar modulo por modulo y evitar funcionalidades futuras anticipadas.
- Usar Laravel Query Builder para los datos de negocio.
- Eloquent esta permitido para usuarios, autenticacion y modelos requeridos por Spatie Permission.
- Usar Blade, Tailwind CSS y JavaScript; jQuery esta permitido cuando exista una necesidad concreta.
- No incorporar React, Vue, Alpine ni capas arquitectonicas innecesarias.
- Validar siempre en backend, usar consultas parametrizadas y proteger rutas en backend.
- Agregar tests junto con cada funcionalidad critica.

## Entorno tecnico actual

- PHP: 8.4.25.
- Laravel Framework: 12.69.2.
- Spatie Laravel Permission: 8.3.0, restriccion Composer `^8.0`.
- Frontend: Tailwind CSS 4, Vite 7, JavaScript y SweetAlert2 11.26.25.
- Testing: PHPUnit mediante `php artisan test`.
- Nombre de aplicacion: `PandaGestion`.
- Base local activa: MariaDB 10.4.32 mediante la conexion Laravel `mysql`, con la base `pandagestion_crm`.
- La base anterior `database/database.sqlite` se conserva intacta como respaldo de la migracion del 2026-09-21, pero ya no es la fuente activa de la aplicacion.
- URL local: `http://localhost:8000`; `APP_URL` y `.env.example` incluyen el puerto para que Laravel Storage genere enlaces publicos correctos.
- `.env.example` refleja MySQL como motor esperado y no contiene credenciales privadas; los valores reales permanecen exclusivamente en `.env`.
- El entorno local tiene un transporte SMTP configurado mediante `MAIL_*`; sus credenciales permanecen exclusivamente en `.env` y no se documentan. `.env.example` conserva valores seguros de ejemplo y un timeout SMTP de 10 segundos.
- Dependencias npm instaladas y `package-lock.json` generado.
- La carpeta es un repositorio Git local sobre la rama `main`; `origin` apunta a `https://github.com/mmdp8612/pandagestion-crm.git`.

No cambiar ni documentar credenciales de base de datos sin indicacion del usuario.

## Estado implementado

### 1. Autenticacion administrativa

- Formulario de login en `/login`.
- Login mediante `Auth::attempt`.
- El login solo acepta usuarios con `is_active = true` y mantiene el mismo mensaje generico para credenciales invalidas o cuentas inactivas.
- Regeneracion de sesion despues de autenticar.
- Limite de cinco intentos fallidos por correo e IP.
- Logout solamente mediante `POST /logout` con CSRF.
- Al salir se invalida la sesion y se regenera el token CSRF.
- No existe registro publico ni recuperacion de contrasena.
- El middleware `EnsureUserIsActive` protege todas las rutas autenticadas y cierra la sesion si una cuenta fue desactivada.
- El login usa un fondo oscuro con degradados y un mosaico vectorial local de casas, edificios, mapas, ubicaciones y llaves. El patron es decorativo, no depende de servicios externos y permanece separado del contenido accesible.
- La tarjeta mantiene alto contraste, foco visible y un ancho responsive. Se verifico sin desplazamiento horizontal en escritorio y en un viewport movil real de 390 px.
- Este ajuste visual no modifico autenticacion, rutas, validaciones, migraciones ni dependencias.

### 2. Roles y permisos

- Spatie Laravel Permission instalado y publicado.
- `User` usa el trait `HasRoles`.
- Permisos por modulo:
  - `dashboard`
  - `bienesraices`
  - `consultas`
  - `catalogo`
  - `configuracion`
  - `usuarios`
  - `roles`
- Rol inicial: `administrador`, con los siete permisos.
- Seeder repetible: `RolesAndPermissionsSeeder`.
- El usuario `test@pandagestion.com.ar` tiene asignado el rol `administrador` en la base local actual.
- Las verificaciones de una sola capacidad usan el middleware nativo `can`, integrado con Laravel Gate por Spatie.

### 3. Layout administrativo

- Sidebar responsive y menu movil.
- Header con usuario y menu de cuenta.
- Breadcrumb.
- Mensajes flash de exito, error, advertencia e informacion.
- Confirmaciones sensibles centralizadas con SweetAlert2 mediante formularios `data-confirm`.
- Logout desde sidebar y menu de usuario.
- El menu se renderiza segun permisos reales.
- Dashboard es funcional.
- Bienes Raices es un enlace funcional cuando el usuario tiene su permiso; resalta cualquiera de sus rutas y ya no muestra el indicador `Proximamente`.
- Consultas es un enlace funcional independiente visible con el permiso `consultas`; resalta el listado y el detalle de la bandeja y muestra un contador discreto cuando existen mensajes nuevos.
- Catalogo es un menu desplegable visible con el permiso `catalogo`; contiene Antiguedades, Cocheras, Comercializacion, Orientaciones, Tipologias, Tipos de moneda, Usos y Vistas, permanece abierto dentro de sus rutas y resalta el catalogo activo.
- Catalogo comparte el mismo tratamiento visual que Configuracion, sin fondo sobre todo el desplegable; el enlace funcional de Bienes Raices se mantiene en una sola linea.

### 3.1 Dashboard operativo

- Disponible en `/admin` para usuarios con el permiso `dashboard`; la ruta ahora utiliza `DashboardController` en lugar de renderizar una vista estatica.
- Cuando el usuario tambien posee `bienesraices`, muestra totales de propiedades, habilitadas, deshabilitadas, destacadas, con fotos y sin fotos mediante una unica consulta agregada.
- Los indicadores de total, estado y destacadas enlazan al listado general o a sus filtros existentes. Los indicadores de fotos son informativos porque ese criterio aun no forma parte de los filtros del listado.
- Muestra las cinco propiedades cargadas mas recientemente, ordenadas por fecha de alta e ID descendentes, con codigo, domicilio, clasificacion resumida, estado, destacada y portada habilitada.
- La portada se obtiene mediante la misma subconsulta correlacionada utilizada por el listado: prioriza portada, orden e ID y excluye imagenes deshabilitadas.
- Incluye accesos rapidos a propiedades, consultas, catalogos, inmobiliaria, usuarios y roles solo cuando el usuario posee el permiso correspondiente; el cambio de contrasena personal permanece disponible para toda cuenta autenticada.
- Un usuario con `dashboard` pero sin `bienesraices` no ejecuta consultas sobre propiedades ni recibe sus indicadores, ultimas altas o enlaces operativos.
- Cuando el usuario posee `consultas`, muestra los totales de consultas nuevas y en proceso mediante una unica consulta agregada, junto con los cinco contactos mas recientes ordenados por fecha e ID descendentes.
- Los indicadores de consultas enlazan a los filtros existentes de la bandeja; cada contacto reciente abre su detalle y el acceso rapido lleva al listado completo.
- Un usuario sin `consultas` no ejecuta consultas SQL sobre esa tabla ni recibe indicadores, contactos, contador de menu o accesos relacionados, aunque pueda entrar al Dashboard.
- El contador del sidebar refleja solamente consultas con estado `nueva`; se comparte con el layout mediante un View Composer condicionado por permisos y se limita visualmente a `99+`.
- El Dashboard es responsive, contempla el estado vacio y no incorpora graficos ni estadisticas historicas en esta etapa.
- No requirio migraciones, permisos ni dependencias nuevas.

### 4. Gestion de usuarios

Ruta base: `/admin/configuracion/usuarios`.

Implementado:

- Listado con nombre, correo, roles, fecha de alta y paginacion de 15 registros.
- Busqueda combinable por nombre o correo y filtro por rol mediante parametros GET validados.
- Los filtros activos se conservan durante la paginacion y pueden limpiarse desde el listado.
- Alta de usuario con nombre, correo, contrasena confirmada y rol inicial.
- Edicion de nombre, correo y rol.
- Estado activo/inactivo visible y modificable desde el listado mediante confirmacion SweetAlert2.
- Los usuarios nuevos quedan activos por defecto.
- Al desactivar una cuenta se renueva su `remember_token`; no puede volver a iniciar sesion y cualquier sesion existente se cierra en la siguiente solicitud.
- Un administrador no puede desactivar su propia cuenta, tanto por interfaz como por backend.
- Un administrador puede restablecer la contrasena de otro usuario desde una pantalla separada, con confirmacion SweetAlert2 y la politica de seguridad vigente.
- El restablecimiento administrativo no permite operar sobre la propia cuenta, exige una contrasena distinta de la actual, renueva el `remember_token` y elimina las sesiones persistidas del usuario afectado.
- Las acciones del listado se muestran como iconos compactos en una sola fila, con leyendas al pasar el puntero y etiquetas accesibles; esta mejora no agrego rutas ni migraciones.
- Permite eliminar definitivamente otros usuarios mediante confirmacion SweetAlert2; no admite la propia cuenta ni al ultimo administrador activo.
- La eliminacion se comprueba dentro de una transaccion con bloqueo, revoca sesiones, elimina tokens de restablecimiento y limpia las relaciones de roles y permisos de Spatie. No requirio una migracion nueva.
- Correos normalizados a minusculas y validados como unicos.
- Contrasenas nuevas con minimo de 8 caracteres, mayusculas, minusculas y numeros.
- La creacion y actualizacion de rol se ejecutan dentro de transacciones.
- La interfaz actual mantiene un unico rol por usuario mediante `syncRoles`.
- Todas estas rutas requieren el permiso de modulo `usuarios`.

### 5. Cambio de contrasena personal

- Disponible en `/admin/configuracion/contrasena`.
- Accesible para cualquier usuario autenticado; no requiere permiso de modulo porque es una operacion de seguridad sobre la propia cuenta.
- Exige la contrasena actual mediante `current_password:web`.
- La nueva contrasena debe ser distinta, confirmada y cumplir la politica de seguridad.
- Se renueva `remember_token` y se regenera la sesion actual.
- No modifica contrasenas desde el formulario de edicion de usuarios; el restablecimiento administrativo utiliza un flujo separado y no admite la propia cuenta.

### 6. Gestion de roles y permisos

- Disponible en `/admin/configuracion/roles`.
- Lista los roles del guard `web`, los permisos de modulo asociados y la cantidad de usuarios asignados.
- Los permisos se muestran con etiquetas legibles sin modificar sus nombres tecnicos.
- El listado esta ordenado por nombre y paginado de a 15 roles.
- Permite crear roles desde `/admin/configuracion/roles/nuevo` y seleccionar uno o mas permisos existentes del guard `web`.
- Permite editar el nombre y los permisos de los roles comunes desde `/admin/configuracion/roles/{role}/editar`.
- Los nombres de rol se recortan, se compactan los espacios y se normalizan a minusculas; deben ser unicos dentro del guard `web`.
- La creacion y actualizacion del rol junto con la asignacion de permisos se ejecutan dentro de transacciones.
- Permite eliminar roles comunes solamente cuando no tienen usuarios asignados, mediante una confirmacion SweetAlert2 y una comprobacion transaccional en backend.
- El rol `administrador` es inmutable: se muestra como protegido y los intentos directos de abrirlo, actualizarlo o eliminarlo reciben respuesta `403`.
- Las acciones del listado usan iconos compactos alineados en una sola fila, con leyendas y etiquetas accesibles; `Protegido` y `En uso` permanecen como indicadores visibles. Este ajuste no agrego rutas ni migraciones.
- Todas las rutas y el enlace de menu requieren el permiso de modulo `roles`.

### 7. Inmobiliaria

- Disponible en `/admin/configuracion/inmobiliaria` y enlazada desde el menu de Configuracion.
- Administra un unico registro de empresa identificado internamente con `id = 1` mediante Query Builder.
- Permite crear o actualizar razon social, telefonos, WhatsApp, correo, domicilio, codigo postal, provincia, partido, localidad, barrio, coordenadas, web, matricula y estado habilitado.
- Los campos opcionales vacios se guardan como `null` y el correo se normaliza a minusculas.
- Valida longitudes, formato de correo, URL HTTP/HTTPS y rangos de latitud y longitud en un Form Request.
- Permite cargar, previsualizar, reemplazar y eliminar el logo mediante el disco publico de Laravel Storage.
- Los logos se guardan con nombres generados dentro de `inmobiliaria/logos`; solo se aceptan JPG, PNG y WebP de hasta 2 MB y la base conserva unicamente la ruta relativa.
- Al reemplazar un logo se elimina el archivo anterior despues de confirmar la actualizacion de la base; la eliminacion explicita usa SweetAlert2.
- El enlace `public/storage` esta creado y apunta a `storage/app/public`.
- Incluye una busqueda explicita de direcciones mediante OpenStreetMap Nominatim: solo consulta al presionar `Buscar` o Enter, muestra hasta cinco resultados y completa domicilio, codigo postal, provincia, partido, localidad, barrio y coordenadas con los datos disponibles.
- Los campos completados por la busqueda permanecen editables y los valores ausentes en la respuesta no borran informacion ya ingresada.
- El navegador consulta un endpoint interno; la aplicacion actua como proxy, normaliza la respuesta, mantiene cache por consulta durante 24 horas y limita globalmente las consultas externas sin cache a una por segundo.
- La interfaz muestra la atribucion requerida a OpenStreetMap y devuelve mensajes controlados ante limites o indisponibilidad del proveedor.
- Al seleccionar una direccion con coordenadas se muestra un mapa incrustado de OpenStreetMap con un marcador y un enlace para abrir la ubicacion en el mapa completo.
- El mapa tambien se reconstruye al abrir un registro con coordenadas guardadas y se actualiza con una breve espera si latitud o longitud se corrigen manualmente; se oculta cuando las coordenadas estan incompletas o fuera de rango.
- Las rutas requieren el permiso de modulo `configuracion`.

### 8. Catalogo de antiguedades

- Disponible en `/admin/catalogos/antiguedades` y enlazado desde el menu principal de Catalogo.
- Usa Query Builder sobre la tabla historica `tip_antiguedad` y conserva los campos `IdAntiguedad`, `Descrip`, `Orden` y `Hab`.
- La migracion conserva el codigo alfanumerico de hasta 3 caracteres, la descripcion de hasta 15 caracteres y el orden entero; `Hab` se almacena como booleano.
- El seeder repetible `AntiquitiesSeeder` carga los 14 registros historicos con sus codigos, descripciones y orden originales. Usa insercion no destructiva para no sobrescribir cambios administrativos al volver a ejecutarse.
- El listado respeta `Orden`, usa la descripcion como segundo criterio, pagina de a 15 registros y muestra codigo, descripcion, orden y estado.
- Permite crear registros con codigo normalizado a mayusculas, descripcion sin espacios redundantes, orden y estado inicial.
- Permite editar descripcion, orden y estado. El codigo queda inmutable despues del alta para conservar una referencia estable para futuras propiedades.
- Permite habilitar y deshabilitar registros desde acciones compactas con iconos y confirmacion SweetAlert2; no permite eliminarlos.
- Todas las rutas y el enlace de menu requieren el permiso de modulo `catalogo`.

### 9. Catalogo de comercializacion

- Disponible en `/admin/catalogos/comercializaciones` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_comercializacion` y conserva `IdComercializacion`, `Descrip` y `Hab` con sus longitudes historicas.
- El seeder repetible `CommercializationsSeeder` carga los cinco valores historicos sin sobrescribir cambios administrativos.
- Los valores historicos con `Hab = -1` se normalizan a `true`; `FON` (`Fondo de Comerc`) conserva su estado historico `Hab = 0` como `false`.
- El listado se ordena por descripcion, pagina de a 15 registros y muestra codigo, descripcion y estado.
- Permite crear y editar descripcion y estado; acepta codigos de hasta 3 caracteres con letras, numeros o guiones y los normaliza a mayusculas.
- El codigo queda inmutable despues del alta. No existe eliminacion fisica; los registros se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 10. Catalogo de tipologias

- Disponible en `/admin/catalogos/tipologias` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_tipologia` y conserva los campos historicos `IdTipologia`, `Descrip`, `TipoGral`, `OrdTipoGral` y `Hab` con sus longitudes originales.
- El seeder repetible `TypologiesSeeder` carga las 27 tipologias historicas sin sobrescribir cambios administrativos y normaliza `Hab = -1` a booleano `true`.
- El listado se ordena por `OrdTipoGral`, tipo general, descripcion y codigo; pagina de a 15 registros y muestra agrupacion, orden y estado.
- Permite crear y editar descripcion, tipo general, orden del grupo y estado; el tipo general puede quedar vacio porque el campo historico admite `null`.
- Los codigos aceptan hasta 4 letras, numeros o guiones, se normalizan a mayusculas y quedan inmutables despues del alta.
- No existe eliminacion fisica; las tipologias se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 11. Catalogo de cocheras

- Disponible en `/admin/catalogos/cocheras` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_cochera` y conserva los campos historicos `IdCochera`, `Descrip` y `Hab` con sus longitudes originales.
- El seeder repetible `GaragesSeeder` carga las 13 cocheras historicas sin sobrescribir cambios administrativos y normaliza `Hab = -1` a booleano `true`.
- El listado se ordena por descripcion y codigo, pagina de a 15 registros y muestra codigo, descripcion y estado.
- Permite crear y editar descripcion y estado; los codigos aceptan hasta 3 letras, numeros o guiones, se normalizan a mayusculas y quedan inmutables despues del alta.
- No existe eliminacion fisica; las cocheras se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 12. Catalogo de orientaciones

- Disponible en `/admin/catalogos/orientaciones` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_orientacion` y conserva los campos historicos `IdOrientacion`, `Descrip` y `Hab` con sus longitudes originales.
- La migracion Laravel explicita evita importar el `CREATE TABLE` incompleto del SQL historico, que carece de punto y coma, sin modificar el archivo de referencia.
- El seeder repetible `OrientationsSeeder` carga las nueve orientaciones historicas sin sobrescribir cambios administrativos y normaliza `Hab = -1` a booleano `true`.
- El listado se ordena por descripcion y codigo, pagina de a 15 registros y muestra codigo, descripcion y estado.
- Permite crear y editar descripcion y estado; los codigos aceptan hasta 2 letras, numeros o guiones, se normalizan a mayusculas y quedan inmutables despues del alta.
- No existe eliminacion fisica; las orientaciones se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 13. Catalogo de usos

- Disponible en `/admin/catalogos/usos` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_uso` y conserva los campos historicos `IdUso`, `Descrip` y `Hab` con sus longitudes originales.
- El seeder repetible `PropertyUsesSeeder` carga los cinco usos historicos sin sobrescribir cambios administrativos y normaliza `Hab = -1` a booleano `true`.
- El listado se ordena por descripcion y codigo, pagina de a 15 registros y muestra codigo, descripcion y estado.
- Permite crear y editar descripcion y estado; los codigos aceptan hasta 4 letras, numeros o guiones, se normalizan a mayusculas y quedan inmutables despues del alta.
- No existe eliminacion fisica; los usos se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 14. Catalogo de vistas

- Disponible en `/admin/catalogos/vistas` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_vista` y conserva los campos historicos `IdVista`, `Descrip` y `Hab` con sus longitudes originales.
- El seeder repetible `PropertyViewsSeeder` carga las cuatro vistas historicas sin sobrescribir cambios administrativos y normaliza `Hab = -1` a booleano `true`.
- El listado se ordena por descripcion y codigo, pagina de a 15 registros y muestra codigo, descripcion y estado.
- Permite crear y editar descripcion y estado; los codigos aceptan hasta 7 letras, numeros, guiones o barras, se normalizan a mayusculas y quedan inmutables despues del alta.
- Se conserva el codigo historico `C/FTE`. Sus rutas de detalle ubican el parametro al final y admiten barras para poder editar y cambiar el estado de ese registro sin transformar su identificador.
- No existe eliminacion fisica; las vistas se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 15. Catalogo de tipos de moneda

- Disponible en `/admin/catalogos/tipos-moneda` dentro del menu desplegable de Catalogo.
- Usa Query Builder sobre `tip_tipomoneda` y conserva los campos historicos `idTipoMoneda`, `Descrip`, `Simbolo` y `Hab` con sus tipos y longitudes originales.
- El seeder repetible `CurrencyTypesSeeder` carga los dos valores historicos sin sobrescribir cambios administrativos: ID `0` para `Dolares` con simbolo `u$s` e ID `1` para `Pesos` con simbolo `$`; ambos estados `Hab = -1` se normalizan a booleano `true`.
- El listado se ordena por descripcion e identificador, pagina de a 15 registros y muestra ID, descripcion, simbolo y estado.
- Permite crear y editar descripcion, simbolo opcional y estado. El identificador admite enteros entre `0` y `32767`, queda inmutable despues del alta y conserva correctamente el valor valido `0`.
- No existe eliminacion fisica; los tipos de moneda se habilitan o deshabilitan mediante acciones compactas y confirmacion SweetAlert2.
- Todas sus rutas requieren `auth`, usuario activo y permiso `catalogo`.

### 16. Bienes Raices - identificacion, clasificacion, caracteristicas, comercializacion, ubicacion, multimedia, publicacion e imagenes

- Disponible en `/admin/bienesraices` y enlazado desde el menu principal con el permiso `bienesraices`.
- Usa Query Builder sobre la tabla incremental `bienesraices`; actualmente contiene `Codigo`, `Descrip`, `IdTipologia`, `IdUso`, `Antiguedad`, `IdOrientacion`, `IdCochera`, `IdVista`, `SupCubiertaPropia`, `SupTerreno`, `Frente`, `Fondo`, `MtsFondo`, `Luminosidad`, `Plantas`, `Ambientes`, `Sanitarios`, `Suite`, `Dormitorios`, `LineasTel`, `IdComercializacion`, `ImporteVta`, `ImporteAlq`, `idTipoMonedaVta`, `idTipoMonedaAlq`, `Calle`, `Numero`, `Piso`, `Torre`, `Provincia`, `Partido`, `Localidad`, `Barrio`, `CodigoPostal`, `Latitud`, `Longitud`, `Destacada`, `Slug`, `TieneFoto`, `TieneVideo`, `VideoUrl`, `Hab` y timestamps.
- El listado se ordena por fecha de alta e ID descendentes, pagina de a 15 propiedades y usa una grilla compacta de seis columnas logicas: imagen, propiedad, caracteristicas, comercializacion, estado y acciones. Ya no muestra slug, antiguedad, orientacion, cochera, vista, dimensiones secundarias, luminosidad ni lineas telefonicas; esos datos permanecen disponibles en la edicion.
- El listado permite buscar por codigo, calle, numero, barrio o localidad. Las consultas de varias palabras exigen que cada termino aparezca en alguno de esos campos, por lo que un domicilio completo funciona aunque calle y numero esten almacenados por separado.
- La busqueda puede combinarse con filtros por Tipologia, Comercializacion, estado habilitado/deshabilitado y solo propiedades destacadas. Los criterios se validan y normalizan en backend, se conservan durante la paginacion y pueden limpiarse desde el listado.
- Los selectores de filtro incluyen tambien valores de catalogo deshabilitados, identificados visualmente, para poder localizar propiedades historicas que aun los tengan asignados.
- La primera columna muestra la portada habilitada de `bienesraices_imagenes` y usa la primera imagen ordenada como respaldo si el registro quedara sin una portada explicita. Cuando no hay imagen muestra un placeholder y la miniatura enlaza a la galeria.
- La grilla usa tabla de ancho fijo sin contenedor de desplazamiento horizontal. Caracteristicas y comercializacion se ocultan progresivamente en resoluciones menores, conservando siempre imagen, resumen principal y acciones; estado se oculta solamente en pantallas pequenas.
- El resumen principal agrupa codigo, destacada, domicilio, ubicacion, Tipologia y Uso. Las columnas opcionales conservan las superficies y cantidades principales, modalidad, importes, estado, video y fecha de actualizacion. Solo se consultan los catalogos necesarios para esos datos compactos.
- Permite crear y editar la identificacion, clasificacion, superficies, distribucion de ambientes, comercializacion, ubicacion, URL de video y publicacion. `Codigo` admite hasta 30 letras o numeros, se normaliza a mayusculas y es unico, pero puede modificarse mientras conserve esa unicidad.
- Tipologia, Uso, Antiguedad, Orientacion, Cochera y Vista son opcionales para permitir una carga gradual y conservar propiedades preexistentes incompletas. Los selectores ofrecen solamente valores habilitados; Tipologia se agrupa mediante `TipoGral` y respeta `OrdTipoGral`.
- Si un valor se deshabilita despues de haber sido asignado, la edicion lo muestra identificado como deshabilitado y permite conservarlo o quitarlo, pero no permite asignar otro valor deshabilitado.
- Superficie cubierta propia y superficie de terreno son decimales opcionales, no negativos y con hasta dos posiciones decimales. Plantas, ambientes, sanitarios, dormitorios y dormitorios en suite son cantidades enteras opcionales entre `0` y `65535`.
- Frente y Fondo representan superficies en metros cuadrados; MtsFondo representa una longitud en metros. Los tres son decimales opcionales, no negativos y con hasta dos posiciones. Luminosidad es una descripcion breve opcional de hasta 30 caracteres, y LineasTel es una cantidad entera opcional entre `0` y `65535`.
- La modalidad comercial es opcional. Los importes de venta y alquiler son decimales opcionales, no negativos y con hasta dos posiciones; cada importe debe proporcionarse junto con su tipo de moneda y viceversa. El identificador de moneda `0` se conserva como valor valido.
- Los selectores comerciales ofrecen solamente valores habilitados. Si una modalidad o moneda se deshabilita despues de asignarla, la edicion permite conservarla o quitarla, pero no reemplazarla por otro valor deshabilitado.
- `Destacada` es un booleano con valor inicial `false`. `Slug` es obligatorio, unico y de hasta 190 caracteres; se genera automaticamente con tipologia, ambientes, barrio o localidad y codigo, y los conflictos reciben un sufijo numerico.
- El slug se conserva durante las ediciones comunes, aunque cambien los datos usados para generarlo. Solo se regenera al marcar la opcion explicita del formulario de edicion, que solicita confirmacion mediante SweetAlert2 porque puede cambiar la futura URL publica.
- `TieneFoto` y `TieneVideo` son booleanos derivados que no se editan manualmente. `TieneFoto` permanece activo solamente cuando existe al menos una imagen habilitada; `TieneVideo` permanece activo solamente cuando `VideoUrl` contiene una URL valida de YouTube o Vimeo.
- `VideoUrl` es opcional, admite hasta 500 caracteres y acepta enlaces HTTP/HTTPS habituales de YouTube, `youtu.be`, Shorts, embeds de YouTube, Vimeo y el reproductor de Vimeo. La URL se normaliza a una forma canonica HTTPS antes de guardarse; HTML, proveedores distintos, dominios imitadores e identificadores invalidos se rechazan.
- Los datos opcionales vacios se guardan como `null`; calle es obligatoria y latitud/longitud deben proporcionarse juntas y respetar sus rangos geograficos.
- Permite habilitar y deshabilitar propiedades desde acciones compactas con confirmacion SweetAlert2; no existe eliminacion fisica en esta etapa.
- Reutiliza el proxy y la interfaz de busqueda explicita de OpenStreetMap. La respuesta normalizada ahora incluye `calle` y `numero`, ademas de los campos ya utilizados por Inmobiliaria.
- Al seleccionar una direccion completa calle, numero, codigo postal, provincia, partido, localidad, barrio y coordenadas cuando el proveedor los informa; todos permanecen editables.
- Muestra el mapa con marcador al seleccionar una direccion o abrir una propiedad con coordenadas guardadas.
- La busqueda de direcciones tiene un endpoint propio protegido por `bienesraices`; el endpoint de Inmobiliaria continua protegido por `configuracion`.
- La galeria de cada propiedad permite cargar hasta 10 imagenes JPG, PNG o WebP por solicitud, con un maximo de 5 MB por archivo. Laravel genera nombres seguros y las guarda en el disco `public` bajo `bienesraices/{id}/imagenes`.
- Las imagenes se registran mediante Query Builder en `bienesraices_imagenes` con propiedad, archivo, orden, portada, estado y timestamps. La primera imagen queda como portada; puede elegirse otra desde la galeria.
- La galeria permite reordenar manualmente mediante arrastre o botones accesibles para mover hacia atras y adelante. El boton para guardar se habilita solo cuando cambia el orden; la portada se conserva aunque cambie de posicion.
- El backend exige que el nuevo orden incluya exactamente una vez todas las imagenes de la propiedad, bloquea los registros durante la transaccion y persiste posiciones consecutivas desde `1`. Rechaza duplicados, omisiones e IDs pertenecientes a otras propiedades.
- Cada imagen puede habilitarse o deshabilitarse desde la galeria con confirmacion SweetAlert2. Una imagen deshabilitada conserva archivo y posicion, permanece visible para la administracion, pero se oculta de la ficha y de los futuros consumidores publicos.
- Una imagen deshabilitada no puede elegirse como portada. Si se deshabilita o elimina la portada, el backend asigna la primera imagen habilitada segun orden e ID; si no quedan imagenes habilitadas, ninguna conserva `Portada` y `TieneFoto` pasa a `false`.
- La carga, el cambio de portada, el cambio de estado y la eliminacion normalizan transaccionalmente la portada y `TieneFoto`. La eliminacion solicita confirmacion SweetAlert2, elimina tambien el archivo fisico y renumera todas las posiciones restantes.
- La ficha de solo lectura esta disponible en `/admin/bienesraices/{property}`. Reune portada, galeria, descripcion, clasificacion, comercializacion, caracteristicas, ubicacion, mapa, publicacion y multimedia sin exponer campos editables.
- La ficha muestra solamente imagenes habilitadas, conserva su orden administrativo y usa la portada habilitada como imagen principal, con la primera imagen ordenada como respaldo. Las imagenes completas se abren en una pestaña nueva.
- Cuando existe una URL de video valida, la ficha administrativa muestra un reproductor responsive generado desde el identificador validado y un enlace a la URL canonica del proveedor.
- El mapa de la ficha se genera en backend con las coordenadas persistidas y usa el mapa incrustable oficial de OpenStreetMap, su atribucion y el enlace al mapa completo; no requiere JavaScript adicional.
- El listado incorpora una accion compacta y accesible para abrir la ficha, y el codigo de la propiedad tambien enlaza al detalle. El listado y la edicion continúan enlazando la galeria, que ahora vuelve a la ficha de consulta.
- El listado representa las fotografias mediante su portada o el placeholder, sin repetir un indicador textual `Fotos`.
- La compactacion del listado y la incorporacion de la portada no agregaron rutas ni migraciones.
- La busqueda y los filtros reutilizan la ruta de listado existente mediante parametros GET y no agregaron rutas ni migraciones.
- La ficha agrego una ruta GET protegida, pero no requirio migraciones, dependencias frontend ni cambios en los datos existentes.
- El estado de imagenes reutiliza la columna `Hab`, agrego una ruta PATCH protegida y no requirio migraciones ni dependencias frontend nuevas.
- El reordenamiento reutiliza la columna `Orden` existente y no requirio una migracion ni dependencias frontend nuevas.
- Todas sus rutas requieren `auth`, usuario activo y permiso `bienesraices`.

### 17. Portal publico - portada, inventario, busqueda y ficha

- La ruta publica `/`, nombrada `home`, utiliza `PublicHomeController` y funciona exclusivamente como portada del portal. Conserva el hero, el buscador principal, beneficios de uso, pasos de consulta, contacto y pie de pagina.
- La portada muestra solamente las seis propiedades habilitadas cargadas mas recientemente, ordenadas por fecha de alta e ID descendentes. No pagina ni aplica filtros sobre ese bloque; el buscador envia sus criterios al catalogo dedicado.
- El inventario completo esta disponible en la pagina publica independiente `/propiedades`, nombrada `public.properties.index`. Usa una composicion responsive con encabezado propio, panel lateral de filtros, resumen de resultados, tarjetas en dos columnas y paginacion de nueve publicaciones.
- Usa la razon social, logo, matricula, domicilio, telefonos, WhatsApp, correo y web de la configuracion de Inmobiliaria solamente cuando el registro unico `id = 1` esta habilitado. Si no existe o esta deshabilitado, utiliza una identidad generica segura sin exponer sus datos.
- Los enlaces de contacto priorizan WhatsApp normalizado a digitos; si no esta disponible usan el correo configurado y finalmente la seccion de contacto como respaldo. Cada tarjeta prepara una consulta que identifica el codigo de la propiedad.
- El buscador publico combina ubicacion libre con filtros multiples por Operacion, Tipo de propiedad, Ambientes, Provincia, Partido, Localidad, Cochera, Antiguedad, Orientacion, Vista, Moneda de venta y Moneda de alquiler. La ubicacion admite varias palabras y exige que cada una aparezca en calle, numero, barrio, localidad, partido o provincia.
- El panel lateral representa cada opcion mediante un checkbox y muestra su cantidad real de propiedades habilitadas. Los grupos y opciones sin publicaciones no se renderizan; los catalogos ofrecen solamente valores habilitados y el backend rechaza valores inexistentes, deshabilitados o sin publicaciones habilitadas.
- La lista de opciones de cada grupo tiene una altura maxima de `18rem` y muestra un scroll vertical interno solamente cuando la cantidad de valores la supera. El titulo del filtro permanece fuera del area desplazable y el scrollbar usa un tratamiento visual discreto compatible con Firefox y navegadores WebKit.
- En resoluciones menores a `lg`, el panel completo comienza contraido detras de un boton `Mostrar filtros` que informa el total de resultados y la cantidad de criterios activos. El boton permanece accesible mientras se recorre el panel abierto; al cerrarlo desplaza el foco a los resultados respetando el encabezado fijo. En escritorio el panel continua siempre visible y el boton movil no se renderiza visualmente.
- Cuando un checkbox u ordenamiento provoca el envio automatico, `public.js` conserva temporalmente en `sessionStorage` si el panel estaba abierto, la posicion vertical de la pagina y el scroll de cada grupo identificado por su clave. El estado se consume una sola vez, caduca a los dos minutos y no contiene valores de filtros ni datos personales.
- El panel movil tambien puede cerrarse con `Escape`; el cambio de foco y el desplazamiento hacia resultados respetan la preferencia de movimiento reducido del navegador.
- Los valores de un mismo grupo se combinan con criterio OR y los grupos diferentes con criterio AND. Cada cambio de checkbox aplica inmediatamente los filtros mediante un JavaScript publico minimo, sin boton `Aplicar`; el ordenamiento tambien se envia al cambiar y la busqueda de ubicacion se ejecuta al presionar Enter.
- Los criterios aplicados se representan como etiquetas removibles de manera individual, pueden limpiarse en conjunto y se conservan durante la paginacion. La portada continua enviando filtros escalares compatibles, que el Form Request normaliza al mismo formato multiple del catalogo.
- El catalogo permite ordenar por publicaciones mas recientes, mas antiguas, mayor superficie cubierta o menor superficie cubierta. Los registros sin superficie informada se mantienen al final de ambos ordenes por superficie. No se ordena por precio porque distintas monedas no son comparables de forma segura.
- El inventario incluye todas las propiedades habilitadas y pagina de a nueve publicaciones. El orden predeterminado usa fecha de alta e ID descendentes; el estado destacada se conserva como indicador visual y no altera el orden elegido por el visitante.
- Cada tarjeta resume codigo, Tipologia, Uso, Comercializacion, ubicacion, ambientes, dormitorios, superficie cubierta, descripcion e importes de venta o alquiler cuando esos datos existen.
- Las tarjetas enlazan a la ficha publica estable `/propiedades/{slug}`, nombrada `public.properties.show`. La ruta usa el slug persistido y devuelve `404` tanto para slugs inexistentes como para propiedades deshabilitadas.
- La ficha publica muestra titulo, referencia, modalidad, importes, domicilio, caracteristicas principales, descripcion, clasificacion, datos fisicos, galeria y mapa cuando existen coordenadas validas.
- La ficha publica incorpora un reproductor responsive cuando la propiedad tiene una URL valida. YouTube utiliza el dominio de insercion con privacidad mejorada `youtube-nocookie.com` y Vimeo su reproductor oficial; ambos cargan de forma diferida, no reproducen automaticamente y ofrecen un enlace al proveedor.
- La galeria publica carga exclusivamente imagenes habilitadas, conserva `Orden`, usa la portada habilitada como imagen principal con la primera imagen como respaldo y permite abrir cada archivo completo en una pestana nueva. Cuando no hay imagenes muestra un placeholder.
- El bloque de consulta identifica automaticamente el codigo y la URL de la propiedad. Prioriza WhatsApp, usa correo como respaldo y finalmente dirige al contacto general; en escritorio permanece visible junto al contenido y en pantallas pequenas aparece inmediatamente despues de la galeria principal.
- El encabezado y el pie del portal se reutilizan mediante parciales Blade para mantener la misma navegacion e identidad entre portada, catalogo y ficha. Los enlaces y botones de busqueda dirigen al catalogo dedicado, y la ficha vuelve a esa pagina en lugar de regresar a una seccion de la home.
- Las portadas se obtienen mediante una subconsulta correlacionada que considera exclusivamente imagenes habilitadas y prioriza `Portada`, `Orden` e ID. Las propiedades sin imagen usan un placeholder consistente con el diseno.
- Existen estados vacios diferenciados para ausencia total de publicaciones y para una busqueda sin coincidencias, con acciones para contactar o limpiar filtros.
- El catalogo dedicado se verifico visualmente con los assets compilados en escritorio de 1440 px; el panel, resultados y tarjetas no generan desplazamiento horizontal.
- La separacion del catalogo agrego una ruta GET publica y una vista Blade. El filtrado por checkbox incorporo una entrada JavaScript publica pequena y separada del administrador; no requirio migraciones, permisos ni dependencias nuevas.

### 18. Consultas publicas y bandeja administrativa

- Cada ficha publica incorpora un formulario asociado a la propiedad con nombre, correo, telefono y mensaje. Se exige al menos correo o telefono y todos los datos se validan y normalizan en backend.
- El formulario esta protegido con CSRF, un campo trampa invisible y un limite de cinco intentos por minuto e IP mediante el middleware `throttle`; no incorpora CAPTCHA ni servicios externos.
- Solo las propiedades habilitadas aceptan consultas. Un slug inexistente o correspondiente a una propiedad deshabilitada devuelve `404` y no crea registros.
- Las consultas se guardan mediante Query Builder en `consultas` con la propiedad relacionada, una copia del codigo, datos de contacto, mensaje, estado y timestamps. El estado inicial es `nueva`.
- Despues de persistir el mensaje se intenta notificar al correo del registro habilitado de Inmobiliaria. El transporte y sus credenciales se mantienen en la configuracion `MAIL_*` del entorno; no existe una pantalla administrativa para credenciales SMTP.
- Si el envio de correo falla, la excepcion se reporta pero la consulta ya guardada no se elimina ni se presenta como perdida al visitante. Si Inmobiliaria no esta habilitada o no tiene correo, la consulta igualmente queda registrada.
- La notificacion usa `PropertyInquiryReceived`, identifica la propiedad y el contacto e incluye un enlace al detalle administrativo.
- Cuando el visitante informo correo y existe una Inmobiliaria habilitada con destinatario, se envia ademas una confirmacion independiente mediante `PropertyInquiryConfirmation`. Resume la propiedad y el mensaje, enlaza solamente a la ficha publica y nunca expone el enlace administrativo.
- Los dos correos se envian como mensajes separados, sin CC ni BCC. `MAIL_USERNAME` se usa solo para autenticar el transporte y no se incorpora como destinatario; la notificacion interna permite responder al visitante y la confirmacion permite responder a la Inmobiliaria mediante cabeceras `Reply-To`.
- Cada intento de envio se captura por separado: el fallo de uno no impide intentar el otro ni revierte la consulta persistida. Las consultas que informan solo telefono notifican unicamente a la Inmobiliaria.
- La bandeja esta disponible en `/admin/consultas` con el permiso independiente `consultas`. Lista por fecha descendente, pagina de a 15 y permite buscar por nombre, correo, telefono o codigo de propiedad y filtrar por estado, conservando los criterios durante la paginacion.
- El detalle muestra el mensaje completo, los datos de contacto, la propiedad relacionada y enlaces a sus fichas segun permisos y estado de publicacion.
- Los estados disponibles son `nueva`, `en_proceso`, `respondida` y `descartada`. El cambio es reversible, se valida en backend y no requiere confirmacion destructiva.
- El rol `administrador` fue resincronizado en la base local y posee el nuevo permiso `consultas`; el usuario `test@pandagestion.com.ar` lo hereda mediante ese rol.
- El formulario publico y la bandeja son responsive. El formulario se verifico visualmente con los assets compilados en escritorio y no genera desplazamiento horizontal.

### 19. Datos demostrativos de propiedades

- `DemoPropertiesSeeder` carga un conjunto determinista de 100 propiedades derivado del SQL de referencia recibido para esta etapa, adaptado a la estructura actual de `bienesraices` y a los catalogos habilitados.
- Los codigos se generan dentro del dataset como `DEM001` a `DEM100`: son unicos, alfanumericos, de seis caracteres y no colisionan con la propiedad local existente `COD001`.
- El dataset no incorpora cuentas, credenciales ni identificadores de brokers. Tambien excluye descripciones que contengan correos o patrones evidentes de telefonos de contacto.
- Todas las propiedades demostrativas quedan habilitadas y sin imagenes: `TieneFoto` se guarda en `false` y el seeder no inserta filas en `bienesraices_imagenes` ni crea archivos en Storage.
- Las URLs de video aprovechables se reducen a URLs canonicas de YouTube y vuelven a validarse mediante `PropertyVideo`; `TieneVideo` se deriva de esa validacion. Los valores de video no validos se descartan.
- Las fechas de alta son deterministas entre enero y abril de 2025 para evitar que los datos demostrativos desplacen artificialmente a las altas reales mas recientes de la portada.
- El seeder usa Query Builder dentro de una transaccion, omite cualquier codigo que ya exista y puede ejecutarse nuevamente sin duplicar ni sobrescribir propiedades. Antes asegura de forma no destructiva los catalogos requeridos mediante sus seeders existentes.
- Su ejecucion es deliberadamente explicita mediante `php artisan db:seed --class=DemoPropertiesSeeder`; no forma parte de `DatabaseSeeder`, por lo que una carga comun de permisos y catalogos no agrega inventario de demostracion.
- La base local fue poblada y verificada dos veces: contiene `COD001` intacta y las 100 propiedades `DEM`, con 100 codigos y slugs unicos, cero indicadores de foto activos y cero imagenes asociadas a esos registros.
- Esta etapa no modifico controladores, rutas, vistas, migraciones ni el comportamiento del portal o del administrador.

### 20. SEO tecnico del portal publico

- El layout publico centraliza titulo, descripcion, directiva para robots, URL canonica, Open Graph y Twitter Cards. La portada, el catalogo y cada ficha aportan valores propios sin duplicar la estructura del `<head>`.
- La portada publica declara la identidad habilitada como `RealEstateAgent` y el portal como `WebSite`, con una accion de busqueda por ubicacion. Cuando existe logo habilitado se utiliza como imagen social.
- El catalogo sin filtros publica datos estructurados `CollectionPage` e `ItemList` con las posiciones y URLs absolutas de la pagina actual. Cada pagina sin filtros conserva una URL canonica propia; las combinaciones de filtros u ordenamiento usan `noindex,follow`, apuntan la canonica al catalogo base y no emiten el `ItemList` filtrado.
- Cada ficha publica genera metadatos dinamicos con tipo, ubicacion, operacion, importes y codigo. La imagen social prioriza la portada habilitada y usa el logo de Inmobiliaria como respaldo; nunca consume imagenes deshabilitadas.
- Las fichas emiten JSON-LD `RealEstateListing` con `Place`, ofertas de venta o alquiler, monedas ISO conocidas (`USD` y `ARS`), organizacion responsable y migas `BreadcrumbList`. No se infiere un pais porque la estructura actual no almacena ese dato.
- `PublicUrl` construye las URLs SEO absolutas desde `APP_URL`, incluidas las rutas publicas y los archivos del disco `public`. El dominio definitivo debera configurarse en `APP_URL` antes de publicar para reemplazar `localhost` en canonicas, imagenes y sitemap.
- El sitemap dinamico esta disponible en `/sitemap.xml`; incluye portada, catalogo y exclusivamente propiedades habilitadas, con `lastmod` para cada ficha. No incorpora filtros, consultas, autenticacion ni rutas administrativas.
- `robots.txt` se sirve dinamicamente desde `/robots.txt`, permite el portal, bloquea `/admin` y `/login`, y referencia el sitemap absoluto. El archivo estatico vacio del esqueleto fue retirado para que la ruta sea la unica fuente.
- Esta etapa agrego dos rutas GET publicas, un controlador, un soporte pequeno de URLs, una vista XML y pruebas funcionales. No requirio migraciones, dependencias ni cambios visuales.

### 21. Migracion del entorno local a MySQL/MariaDB

- La aplicacion local usa `mysql` como conexion predeterminada contra la base `pandagestion_crm` de MariaDB 10.4.32. La configuracion efectiva se activo en `.env` y la cache de configuracion fue limpiada.
- Las 25 migraciones del proyecto se ejecutaron desde cero y quedaron registradas en un unico lote exitoso sobre la base MySQL inicialmente vacia.
- Se copiaron transaccionalmente desde SQLite las 18 tablas persistentes de negocio y autorizacion: 3 usuarios, 7 permisos, 2 roles, sus asignaciones, el registro de Inmobiliaria, todos los catalogos, 101 propiedades, 6 imagenes y 3 consultas.
- La comparacion posterior reviso fila por fila todos los valores de esas tablas, normalizando solamente la representacion equivalente de tipos numericos. Los datos, IDs, hashes de contrasena, textos, importes, coordenadas, timestamps y relaciones coincidieron entre ambos motores.
- Se verificaron la moneda con identificador `0`, los 101 codigos y slugs unicos y la ausencia de imagenes, consultas o asignaciones de permisos huerfanas.
- No se trasladaron sesiones, cache, bloqueos de cache, colas, lotes, trabajos fallidos ni tokens de restablecimiento. Son datos efimeros o de seguridad que las migraciones recrearon con sus tablas vacias; por eso las sesiones anteriores pueden requerir un nuevo login.
- `database/database.sqlite` permanece sin modificaciones como respaldo local previo al cambio. No debe eliminarse hasta que exista una politica de copias de seguridad acordada.
- Se verificaron sobre la conexion MySQL activa la portada, el catalogo, una ficha publica con imagen, el login, la redireccion de `/admin`, sitemap y robots. Esta etapa no cambio rutas, controladores, vistas ni reglas de negocio.
- PHPUnit continua forzando SQLite en memoria mediante `phpunit.xml`; esto mantiene la suite aislada de la base poblada. Una ejecucion integral contra MySQL requiere crear primero una base de pruebas separada y descartable.

### 22. Repositorio Git remoto

- El proyecto fue inicializado como repositorio Git sobre la rama `main` y publicado en `https://github.com/mmdp8612/pandagestion-crm.git`.
- El commit raiz `3b36789` (`feat: initial PandaGestion CRM`) contiene el estado funcional completo acumulado hasta esta etapa. El remoto estaba vacio y la publicacion creo `origin/main` sin integrar ni sobrescribir historial previo y sin usar `force`.
- Antes del commit se auditaron los 267 archivos candidatos. No se detectaron claves privadas, tokens ni secretos de alta confianza, y `composer.json` supero `composer validate --no-check-publish`.
- `.env`, la base SQLite local, `vendor`, `node_modules`, el build de Vite, el enlace `public/storage`, sesiones, cache, logs, logos e imagenes cargadas quedaron excluidos mediante las reglas de Git existentes.
- El archivo accidental `count())`, que contenia unicamente una salida fallida de Tinker, fue retirado antes de crear el historial.
- `README.md` dejo de ser el texto generico de Laravel y ahora documenta funcionalidades, instalacion con MySQL/MariaDB, Storage, primer usuario administrador, datos demostrativos, pruebas y archivos locales excluidos.
- Los archivos multimedia persistidos en Storage no forman parte del repositorio y requieren una estrategia de respaldo separada de Git.

## Rutas actuales

Todas las rutas con `auth` tambien ejecutan `EnsureUserIsActive`; una cuenta desactivada pierde la sesion antes de llegar al controlador.

| Metodo | URI | Nombre | Proteccion |
|---|---|---|---|
| GET | `/` | `home` | Publica; portada, buscador GET hacia el catalogo y las seis propiedades habilitadas mas recientes |
| GET | `/sitemap.xml` | `public.sitemap` | Publica; portada, catalogo y fichas habilitadas con URLs absolutas y fecha de modificacion |
| GET | `/robots.txt` | `public.robots` | Publica; permite el portal, bloquea administracion y login, y referencia el sitemap |
| GET | `/propiedades` | `public.properties.index` | Publica; inventario paginado, filtros removibles y ordenamiento validado |
| GET | `/propiedades/{slug}` | `public.properties.show` | Publica; solo propiedades habilitadas e imagenes habilitadas; slug alfanumerico con guiones |
| POST | `/propiedades/{slug}/consultas` | `public.properties.inquiries.store` | Publica; CSRF, validacion, campo trampa, limite de cinco intentos por minuto y propiedad habilitada |
| GET | `/login` | `login` | `guest` |
| POST | `/login` | `login.store` | `guest` |
| POST | `/logout` | `logout` | `auth` |
| GET | `/admin` | `admin.dashboard` | `auth`, `can:dashboard`; datos inmobiliarios condicionados ademas por `bienesraices` |
| GET | `/admin/bienesraices` | `admin.properties.index` | `auth`, `can:bienesraices` |
| GET | `/admin/bienesraices/nuevo` | `admin.properties.create` | `auth`, `can:bienesraices` |
| GET | `/admin/bienesraices/direcciones/buscar` | `admin.properties.addresses.search` | `auth`, `can:bienesraices`; consulta explicita validada |
| POST | `/admin/bienesraices` | `admin.properties.store` | `auth`, `can:bienesraices` |
| GET | `/admin/bienesraices/{property}` | `admin.properties.show` | `auth`, `can:bienesraices`; parametro numerico y ficha de solo lectura |
| GET | `/admin/bienesraices/{property}/editar` | `admin.properties.edit` | `auth`, `can:bienesraices`; parametro numerico |
| PUT | `/admin/bienesraices/{property}` | `admin.properties.update` | `auth`, `can:bienesraices` |
| PATCH | `/admin/bienesraices/{property}/estado` | `admin.properties.status.update` | `auth`, `can:bienesraices` |
| GET | `/admin/bienesraices/{property}/imagenes` | `admin.properties.images.index` | `auth`, `can:bienesraices`; parametros numericos |
| POST | `/admin/bienesraices/{property}/imagenes` | `admin.properties.images.store` | `auth`, `can:bienesraices`; carga multiple validada |
| PATCH | `/admin/bienesraices/{property}/imagenes/orden` | `admin.properties.images.order.update` | `auth`, `can:bienesraices`; conjunto completo y sin duplicados |
| PATCH | `/admin/bienesraices/{property}/imagenes/{image}/portada` | `admin.properties.images.cover.update` | `auth`, `can:bienesraices`; la imagen debe pertenecer a la propiedad |
| PATCH | `/admin/bienesraices/{property}/imagenes/{image}/estado` | `admin.properties.images.status.update` | `auth`, `can:bienesraices`; pertenencia y estado booleano validados |
| DELETE | `/admin/bienesraices/{property}/imagenes/{image}` | `admin.properties.images.destroy` | `auth`, `can:bienesraices`; confirmacion SweetAlert2 y pertenencia validada |
| GET | `/admin/consultas` | `admin.inquiries.index` | `auth`, `can:consultas`; busqueda, filtro por estado y paginacion |
| GET | `/admin/consultas/{inquiry}` | `admin.inquiries.show` | `auth`, `can:consultas`; parametro numerico y detalle de solo lectura |
| PATCH | `/admin/consultas/{inquiry}/estado` | `admin.inquiries.status.update` | `auth`, `can:consultas`; estado validado |
| GET | `/admin/catalogos/antiguedades` | `admin.catalogs.antiquities.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/antiguedades/nueva` | `admin.catalogs.antiquities.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/antiguedades` | `admin.catalogs.antiquities.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/antiguedades/{antiquity}/editar` | `admin.catalogs.antiquities.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/antiguedades/{antiquity}` | `admin.catalogs.antiquities.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/antiguedades/{antiquity}/estado` | `admin.catalogs.antiquities.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/comercializaciones` | `admin.catalogs.commercializations.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/comercializaciones/nueva` | `admin.catalogs.commercializations.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/comercializaciones` | `admin.catalogs.commercializations.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/comercializaciones/{commercialization}/editar` | `admin.catalogs.commercializations.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/comercializaciones/{commercialization}` | `admin.catalogs.commercializations.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/comercializaciones/{commercialization}/estado` | `admin.catalogs.commercializations.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/tipologias` | `admin.catalogs.typologies.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/tipologias/nueva` | `admin.catalogs.typologies.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/tipologias` | `admin.catalogs.typologies.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/tipologias/{typology}/editar` | `admin.catalogs.typologies.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/tipologias/{typology}` | `admin.catalogs.typologies.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/tipologias/{typology}/estado` | `admin.catalogs.typologies.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/cocheras` | `admin.catalogs.garages.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/cocheras/nueva` | `admin.catalogs.garages.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/cocheras` | `admin.catalogs.garages.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/cocheras/{garage}/editar` | `admin.catalogs.garages.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/cocheras/{garage}` | `admin.catalogs.garages.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/cocheras/{garage}/estado` | `admin.catalogs.garages.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/orientaciones` | `admin.catalogs.orientations.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/orientaciones/nueva` | `admin.catalogs.orientations.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/orientaciones` | `admin.catalogs.orientations.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/orientaciones/{orientation}/editar` | `admin.catalogs.orientations.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/orientaciones/{orientation}` | `admin.catalogs.orientations.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/orientaciones/{orientation}/estado` | `admin.catalogs.orientations.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/usos` | `admin.catalogs.uses.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/usos/nuevo` | `admin.catalogs.uses.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/usos` | `admin.catalogs.uses.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/usos/{propertyUse}/editar` | `admin.catalogs.uses.edit` | `auth`, `can:catalogo` |
| PUT | `/admin/catalogos/usos/{propertyUse}` | `admin.catalogs.uses.update` | `auth`, `can:catalogo`; codigo inmutable |
| PATCH | `/admin/catalogos/usos/{propertyUse}/estado` | `admin.catalogs.uses.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/vistas` | `admin.catalogs.views.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/vistas/nueva` | `admin.catalogs.views.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/vistas` | `admin.catalogs.views.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/vistas/editar/{propertyView}` | `admin.catalogs.views.edit` | `auth`, `can:catalogo`; admite `/` en el codigo |
| PUT | `/admin/catalogos/vistas/{propertyView}` | `admin.catalogs.views.update` | `auth`, `can:catalogo`; codigo inmutable y admite `/` |
| PATCH | `/admin/catalogos/vistas/estado/{propertyView}` | `admin.catalogs.views.status.update` | `auth`, `can:catalogo`; admite `/` en el codigo |
| GET | `/admin/catalogos/tipos-moneda` | `admin.catalogs.currency-types.index` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/tipos-moneda/nuevo` | `admin.catalogs.currency-types.create` | `auth`, `can:catalogo` |
| POST | `/admin/catalogos/tipos-moneda` | `admin.catalogs.currency-types.store` | `auth`, `can:catalogo` |
| GET | `/admin/catalogos/tipos-moneda/{currencyType}/editar` | `admin.catalogs.currency-types.edit` | `auth`, `can:catalogo`; parametro numerico, incluido `0` |
| PUT | `/admin/catalogos/tipos-moneda/{currencyType}` | `admin.catalogs.currency-types.update` | `auth`, `can:catalogo`; identificador inmutable |
| PATCH | `/admin/catalogos/tipos-moneda/{currencyType}/estado` | `admin.catalogs.currency-types.status.update` | `auth`, `can:catalogo` |
| GET | `/admin/configuracion/inmobiliaria` | `admin.real-estate-agency.edit` | `auth`, `can:configuracion` |
| GET | `/admin/configuracion/inmobiliaria/direcciones/buscar` | `admin.real-estate-agency.addresses.search` | `auth`, `can:configuracion`; consulta explicita validada |
| PUT | `/admin/configuracion/inmobiliaria` | `admin.real-estate-agency.update` | `auth`, `can:configuracion` |
| DELETE | `/admin/configuracion/inmobiliaria/logo` | `admin.real-estate-agency.logo.destroy` | `auth`, `can:configuracion` |
| GET | `/admin/configuracion/roles` | `admin.roles.index` | `auth`, `can:roles` |
| GET | `/admin/configuracion/roles/nuevo` | `admin.roles.create` | `auth`, `can:roles` |
| POST | `/admin/configuracion/roles` | `admin.roles.store` | `auth`, `can:roles` |
| GET | `/admin/configuracion/roles/{role}/editar` | `admin.roles.edit` | `auth`, `can:roles`; `administrador` bloqueado |
| PUT | `/admin/configuracion/roles/{role}` | `admin.roles.update` | `auth`, `can:roles`; `administrador` bloqueado |
| DELETE | `/admin/configuracion/roles/{role}` | `admin.roles.destroy` | `auth`, `can:roles`; solo roles comunes sin usuarios |
| GET | `/admin/configuracion/usuarios` | `admin.users.index` | `auth`, `can:usuarios` |
| GET | `/admin/configuracion/usuarios/nuevo` | `admin.users.create` | `auth`, `can:usuarios` |
| POST | `/admin/configuracion/usuarios` | `admin.users.store` | `auth`, `can:usuarios` |
| GET | `/admin/configuracion/usuarios/{user}/contrasena` | `admin.users.password.edit` | `auth`, `can:usuarios`; propia cuenta bloqueada |
| PUT | `/admin/configuracion/usuarios/{user}/contrasena` | `admin.users.password.update` | `auth`, `can:usuarios`; propia cuenta bloqueada |
| GET | `/admin/configuracion/usuarios/{user}/editar` | `admin.users.edit` | `auth`, `can:usuarios` |
| PUT | `/admin/configuracion/usuarios/{user}` | `admin.users.update` | `auth`, `can:usuarios` |
| DELETE | `/admin/configuracion/usuarios/{user}` | `admin.users.destroy` | `auth`, `can:usuarios`; propia cuenta y ultimo administrador activo bloqueados |
| PATCH | `/admin/configuracion/usuarios/{user}/estado` | `admin.users.status.update` | `auth`, usuario activo, `can:usuarios`; autodesactivacion bloqueada |
| GET | `/admin/configuracion/contrasena` | `admin.password.edit` | `auth` |
| PUT | `/admin/configuracion/contrasena` | `admin.password.update` | `auth` |

## Migraciones aplicadas en la base local activa

- `0001_01_01_000000_create_users_table`
- `0001_01_01_000001_create_cache_table`
- `0001_01_01_000002_create_jobs_table`
- `2026_09_17_144908_create_permission_tables`
- `2026_09_17_164215_add_is_active_to_users_table`
- `2026_09_17_180000_create_inmobiliaria_table`
- `2026_09_17_190000_create_tip_antiguedad_table`
- `2026_09_17_200000_create_tip_comercializacion_table`
- `2026_09_17_210000_create_tip_tipologia_table`
- `2026_09_17_220000_create_tip_cochera_table`
- `2026_09_17_230000_create_tip_orientacion_table`
- `2026_09_18_000000_create_tip_uso_table`
- `2026_09_18_010000_create_tip_vista_table`
- `2026_09_18_020000_create_tip_tipomoneda_table`
- `2026_09_18_030000_create_bienesraices_table`
- `2026_09_18_040000_add_basic_classification_to_bienesraices_table`
- `2026_09_18_050000_add_physical_characteristics_to_bienesraices_table`
- `2026_09_18_060000_add_complementary_classification_to_bienesraices_table`
- `2026_09_18_070000_add_commercialization_to_bienesraices_table`
- `2026_09_18_080000_add_remaining_physical_characteristics_to_bienesraices_table`
- `2026_09_18_090000_add_publication_fields_to_bienesraices_table`
- `2026_09_18_100000_add_multimedia_indicators_to_bienesraices_table`
- `2026_09_18_110000_create_bienesraices_imagenes_table`
- `2026_09_19_000000_create_consultas_table`
- `2026_09_21_000000_add_video_url_to_bienesraices_table`

Las 25 migraciones figuran como ejecutadas en `pandagestion_crm`. La base SQLite de respaldo conserva el mismo historial previo a la transferencia.

La migracion de Spatie crea `permissions`, `roles`, `model_has_permissions`, `model_has_roles` y `role_has_permissions`.

La migracion de estado agrega `users.is_active` como booleano indexado con valor predeterminado `true`.

La migracion de Inmobiliaria crea el registro unico de configuracion con los campos definidos en el alcance original: `RazonSocial`, `Telefonos`, `Whatsapp`, `Email`, `Domicilio`, `CodigoPostal`, `Provincia`, `Partido`, `Localidad`, `Barrio`, `Latitud`, `Longitud`, `Logo`, `Web`, `Matricula` y `Hab`, ademas de timestamps.

La gestion del logo reutiliza el campo `Logo` existente y no requirio una migracion adicional.

La busqueda de direcciones reutiliza los campos de ubicacion existentes y no requirio una migracion adicional.

La visualizacion del mapa reutiliza las coordenadas existentes y no requirio migraciones ni dependencias frontend adicionales.

La migracion de Antiguedades crea `tip_antiguedad` preservando sus cuatro campos historicos. La base local contiene los 14 registros iniciales y normaliza su `Hab = 1` a booleano `true`.

La migracion de Comercializacion crea `tip_comercializacion` preservando sus tres campos historicos. La base local contiene cinco registros: cuatro habilitados y `FON` deshabilitado.

La migracion de Tipologias crea `tip_tipologia` preservando sus cinco campos historicos. La base local contiene las 27 tipologias iniciales habilitadas, con sus grupos y ordenes originales.

La migracion de Cocheras crea `tip_cochera` preservando sus tres campos historicos. La base local contiene los 13 valores iniciales habilitados.

La migracion de Orientaciones crea `tip_orientacion` preservando sus tres campos historicos. La base local contiene los nueve valores iniciales habilitados.

La migracion de Usos crea `tip_uso` preservando sus tres campos historicos. La base local contiene los cinco valores iniciales habilitados.

La migracion de Vistas crea `tip_vista` preservando sus tres campos historicos. La base local contiene los cuatro valores iniciales habilitados, incluido el codigo `C/FTE` sin modificaciones.

La migracion de Tipos de moneda crea `tip_tipomoneda` preservando sus cuatro campos historicos, incluido `Simbolo` nullable. La base local contiene los dos valores iniciales habilitados con sus IDs `0` y `1` y simbolos originales.

La primera migracion de Bienes Raices crea `bienesraices` con ID interno autoincremental, codigo unico, descripcion opcional, domicilio desglosado, coordenadas decimales, estado indexado y timestamps. `Localidad` tambien queda indexada para los futuros filtros. No agrega aun columnas de caracteristicas, comercializacion, publicacion o multimedia.

La segunda migracion de Bienes Raices agrega `IdTipologia`, `IdUso` y `Antiguedad` como referencias opcionales e indexadas a los catalogos existentes. No incorpora Foreign Keys mientras siga vigente la decision de compatibilidad con la estructura historica.

La tercera migracion de Bienes Raices agrega `SupCubiertaPropia` y `SupTerreno` como decimales opcionales de precision `12,2`, junto con `Plantas`, `Ambientes`, `Sanitarios`, `Suite` y `Dormitorios` como enteros pequenos sin signo y opcionales. No agrega indices porque estos campos aun no se usan como filtros.

La cuarta migracion de Bienes Raices agrega `IdOrientacion`, `IdCochera` e `IdVista` como referencias opcionales e indexadas. Conserva las longitudes historicas de 2, 3 y 7 caracteres respectivamente, incluido el codigo de vista `C/FTE`, y no incorpora Foreign Keys por la decision vigente de compatibilidad historica.

La quinta migracion de Bienes Raices agrega `IdComercializacion`, `ImporteVta`, `ImporteAlq`, `idTipoMonedaVta` e `idTipoMonedaAlq`. La modalidad y las monedas son referencias opcionales e indexadas; los importes usan precision `15,2`. No incorpora Foreign Keys por la decision vigente de compatibilidad historica.

La sexta migracion de Bienes Raices agrega `Frente`, `Fondo` y `MtsFondo` como decimales opcionales de precision `12,2`, `Luminosidad` como texto opcional de hasta 30 caracteres y `LineasTel` como entero pequeno sin signo y opcional. No agrega indices porque estos campos aun no se utilizan como filtros.

La septima migracion de Bienes Raices agrega `Destacada` como booleano indexado con valor predeterminado `false` y `Slug` como texto obligatorio de hasta 190 caracteres con indice unico. Antes de imponer la restriccion, genera un slug para cada propiedad preexistente y resuelve posibles colisiones con sufijos numericos.

La octava migracion de Bienes Raices agrega `TieneFoto` y `TieneVideo` como booleanos obligatorios con valor predeterminado `false`. No agrega indices porque actualmente son indicadores informativos y no se usan como filtros.

La migracion de imagenes crea `bienesraices_imagenes` con `idBienRaiz`, `Archivo`, `Orden`, `Portada`, `Hab` y timestamps. La relacion usa Foreign Key con eliminacion en cascada porque ambas tablas pertenecen al nuevo modulo Laravel, e indexa propiedad y orden. Al aplicarse, restablece los `TieneFoto` preexistentes a `false`, ya que el indicador pasa a depender exclusivamente de las imagenes relacionadas.

La migracion de Consultas crea `consultas` con Foreign Key hacia `bienesraices`, copia del codigo de propiedad, nombre, correo y telefono opcionales segun la regla de contacto, mensaje, estado y timestamps. Indexa estado y propiedad junto con la fecha de alta, y elimina las consultas en cascada si en el futuro se elimina fisicamente su propiedad.

La novena migracion de Bienes Raices agrega `VideoUrl` como texto opcional de hasta 500 caracteres. Al aplicarse desactiva los indicadores `TieneVideo` preexistentes porque eran marcas manuales sin una URL verificable; desde entonces el indicador se deriva exclusivamente de `VideoUrl`.

## Seeders

- `DatabaseSeeder` llama a `RolesAndPermissionsSeeder`, `AntiquitiesSeeder`, `CommercializationsSeeder`, `TypologiesSeeder`, `GaragesSeeder`, `OrientationsSeeder`, `PropertyUsesSeeder`, `PropertyViewsSeeder` y `CurrencyTypesSeeder`.
- El seeder crea o reutiliza los siete permisos y el rol `administrador`.
- El seeder sincroniza todos los permisos con `administrador` y limpia la cache de permisos.
- Ya no se genera automaticamente el usuario inseguro `test@example.com` del esqueleto de Laravel.
- Ningun seeder contiene credenciales de acceso.
- `AntiquitiesSeeder` inserta los 14 valores historicos faltantes sin sobrescribir registros existentes, por lo que puede ejecutarse mas de una vez y conserva las ediciones administrativas.
- `CommercializationsSeeder` aplica el mismo criterio no destructivo para los cinco valores historicos de Comercializacion.
- `TypologiesSeeder` inserta de forma no destructiva las 27 tipologias historicas con `TipoGral` y `OrdTipoGral`.
- `GaragesSeeder` inserta de forma no destructiva los 13 valores historicos de Cocheras.
- `OrientationsSeeder` inserta de forma no destructiva las nueve orientaciones historicas.
- `PropertyUsesSeeder` inserta de forma no destructiva los cinco usos historicos.
- `PropertyViewsSeeder` inserta de forma no destructiva las cuatro vistas historicas.
- `CurrencyTypesSeeder` inserta de forma no destructiva los dos tipos de moneda historicos, conservando el ID `0`.
- `DemoPropertiesSeeder` es un seeder opcional y explicito que carga `database/seeders/data/demo_properties.json`. Inserta solamente codigos `DEM` ausentes, no sobrescribe propiedades existentes y nunca crea imagenes.

## Archivos importantes

- Rutas: `routes/web.php`.
- Login y fondo vectorial: `resources/views/layouts/guest.blade.php`, `resources/views/auth/login.blade.php`, `resources/css/app.css` y `public/images/auth/real-estate-pattern.svg`.
- Portal publico: `app/Http/Controllers/PublicHomeController.php`, `app/Http/Controllers/PublicPropertyController.php`, `app/Http/Requests/PublicPropertySearchRequest.php`, `resources/js/public.js`, `resources/views/layouts/public.blade.php`, `resources/views/public/home.blade.php`, `resources/views/public/properties/index.blade.php`, `resources/views/public/properties/_card.blade.php`, `resources/views/public/properties/show.blade.php`, `resources/views/public/partials/`, `tests/Feature/PublicPortal/HomeTest.php`, `tests/Feature/PublicPortal/PropertyIndexTest.php` y `tests/Feature/PublicPortal/PropertyShowTest.php`.
- SEO publico: `app/Http/Controllers/PublicSeoController.php`, `app/Support/PublicUrl.php`, `resources/views/public/seo/sitemap.blade.php`, las secciones SEO de las vistas publicas y `tests/Feature/PublicPortal/SeoTest.php`.
- Consultas: `app/Http/Controllers/PublicInquiryController.php`, `app/Http/Controllers/Admin/InquiryController.php`, `app/Http/Requests/StorePublicInquiryRequest.php`, `app/Http/Requests/Admin/InquiryIndexRequest.php`, `app/Http/Requests/Admin/UpdateInquiryStatusRequest.php`, `app/Mail/PropertyInquiryReceived.php`, `app/Mail/PropertyInquiryConfirmation.php`, `resources/views/admin/inquiries/`, `resources/views/emails/property-inquiry-received.blade.php`, `resources/views/emails/property-inquiry-confirmation.blade.php`, `config/mail.php`, `database/migrations/2026_09_19_000000_create_consultas_table.php`, `tests/Feature/PublicPortal/PropertyInquiryTest.php` y `tests/Feature/Admin/InquiryTest.php`.
- Dashboard: `app/Http/Controllers/Admin/DashboardController.php`, `app/Providers/AppServiceProvider.php`, `resources/views/admin/dashboard.blade.php` y `tests/Feature/Admin/DashboardTest.php`.
- Autenticacion: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`.
- Validacion de login: `app/Http/Requests/Auth/LoginRequest.php`.
- Usuario: `app/Models/User.php`.
- Proteccion de cuentas inactivas: `app/Http/Middleware/EnsureUserIsActive.php`.
- Usuarios admin: `app/Http/Controllers/Admin/UserController.php`.
- Filtros de usuarios: `app/Http/Requests/Admin/UserIndexRequest.php`.
- Alta de usuario: `app/Http/Requests/Admin/StoreUserRequest.php`.
- Edicion de usuario: `app/Http/Requests/Admin/UpdateUserRequest.php`.
- Estado de usuario: `app/Http/Requests/Admin/UpdateUserStatusRequest.php`.
- Cambio de contrasena: `app/Http/Controllers/Admin/PasswordController.php` y `app/Http/Requests/Admin/UpdatePasswordRequest.php`.
- Restablecimiento administrativo de contrasena: `app/Http/Controllers/Admin/UserPasswordController.php` y `app/Http/Requests/Admin/AdminUpdateUserPasswordRequest.php`.
- Inmobiliaria: `app/Http/Controllers/Admin/RealEstateAgencyController.php`, `app/Http/Requests/Admin/UpdateRealEstateAgencyRequest.php` y `resources/views/admin/real-estate-agency/edit.blade.php`.
- Busqueda de direcciones: `app/Http/Controllers/Admin/AddressSearchController.php`, `app/Http/Requests/Admin/SearchAddressRequest.php` y `app/Services/Geocoding/NominatimGeocoder.php`.
- Bienes Raices: `app/Http/Controllers/Admin/PropertyController.php`, `app/Http/Requests/Admin/PropertyIndexRequest.php`, `app/Http/Requests/Admin/SavePropertyRequest.php`, `app/Http/Requests/Admin/StorePropertyRequest.php`, `app/Http/Requests/Admin/UpdatePropertyRequest.php`, `app/Http/Requests/Admin/UpdatePropertyStatusRequest.php`, `app/Support/PropertyVideo.php` y `resources/views/admin/properties/`.
- Video de propiedades: `app/Support/PropertyVideo.php`, `resources/views/admin/properties/_form.blade.php`, `resources/views/admin/properties/show.blade.php`, `resources/views/public/properties/show.blade.php`, `tests/Feature/Admin/PropertyTest.php`, `tests/Feature/PublicPortal/PropertyShowTest.php` y `tests/Unit/Support/PropertyVideoTest.php`.
- Imagenes de propiedades: `app/Http/Controllers/Admin/PropertyImageController.php`, `app/Http/Requests/Admin/StorePropertyImagesRequest.php`, `app/Http/Requests/Admin/UpdatePropertyImageOrderRequest.php`, `app/Http/Requests/Admin/UpdatePropertyImageStatusRequest.php`, `resources/views/admin/properties/images/index.blade.php` y `tests/Feature/Admin/PropertyImageTest.php`.
- Migraciones de Bienes Raices: `database/migrations/2026_09_18_030000_create_bienesraices_table.php`, `database/migrations/2026_09_18_040000_add_basic_classification_to_bienesraices_table.php`, `database/migrations/2026_09_18_050000_add_physical_characteristics_to_bienesraices_table.php`, `database/migrations/2026_09_18_060000_add_complementary_classification_to_bienesraices_table.php`, `database/migrations/2026_09_18_070000_add_commercialization_to_bienesraices_table.php`, `database/migrations/2026_09_18_080000_add_remaining_physical_characteristics_to_bienesraices_table.php`, `database/migrations/2026_09_18_090000_add_publication_fields_to_bienesraices_table.php`, `database/migrations/2026_09_18_100000_add_multimedia_indicators_to_bienesraices_table.php`, `database/migrations/2026_09_18_110000_create_bienesraices_imagenes_table.php` y `database/migrations/2026_09_21_000000_add_video_url_to_bienesraices_table.php`.
- Antiguedades: `app/Http/Controllers/Admin/AntiquityController.php`, `app/Http/Requests/Admin/StoreAntiquityRequest.php`, `app/Http/Requests/Admin/UpdateAntiquityRequest.php`, `app/Http/Requests/Admin/UpdateAntiquityStatusRequest.php` y `resources/views/admin/catalogs/antiquities/`.
- Comercializacion: `app/Http/Controllers/Admin/CommercializationController.php`, `app/Http/Requests/Admin/StoreCommercializationRequest.php`, `app/Http/Requests/Admin/UpdateCommercializationRequest.php`, `app/Http/Requests/Admin/UpdateCommercializationStatusRequest.php` y `resources/views/admin/catalogs/commercializations/`.
- Tipologias: `app/Http/Controllers/Admin/TypologyController.php`, `app/Http/Requests/Admin/StoreTypologyRequest.php`, `app/Http/Requests/Admin/UpdateTypologyRequest.php`, `app/Http/Requests/Admin/UpdateTypologyStatusRequest.php` y `resources/views/admin/catalogs/typologies/`.
- Cocheras: `app/Http/Controllers/Admin/GarageController.php`, `app/Http/Requests/Admin/StoreGarageRequest.php`, `app/Http/Requests/Admin/UpdateGarageRequest.php`, `app/Http/Requests/Admin/UpdateGarageStatusRequest.php` y `resources/views/admin/catalogs/garages/`.
- Orientaciones: `app/Http/Controllers/Admin/OrientationController.php`, `app/Http/Requests/Admin/StoreOrientationRequest.php`, `app/Http/Requests/Admin/UpdateOrientationRequest.php`, `app/Http/Requests/Admin/UpdateOrientationStatusRequest.php` y `resources/views/admin/catalogs/orientations/`.
- Usos: `app/Http/Controllers/Admin/PropertyUseController.php`, `app/Http/Requests/Admin/StorePropertyUseRequest.php`, `app/Http/Requests/Admin/UpdatePropertyUseRequest.php`, `app/Http/Requests/Admin/UpdatePropertyUseStatusRequest.php` y `resources/views/admin/catalogs/uses/`.
- Vistas: `app/Http/Controllers/Admin/PropertyViewController.php`, `app/Http/Requests/Admin/StorePropertyViewRequest.php`, `app/Http/Requests/Admin/UpdatePropertyViewRequest.php`, `app/Http/Requests/Admin/UpdatePropertyViewStatusRequest.php` y `resources/views/admin/catalogs/views/`.
- Tipos de moneda: `app/Http/Controllers/Admin/CurrencyTypeController.php`, `app/Http/Requests/Admin/StoreCurrencyTypeRequest.php`, `app/Http/Requests/Admin/UpdateCurrencyTypeRequest.php`, `app/Http/Requests/Admin/UpdateCurrencyTypeStatusRequest.php` y `resources/views/admin/catalogs/currency-types/`.
- Gestion de roles: `app/Http/Controllers/Admin/RoleController.php`.
- Alta de roles: `app/Http/Requests/Admin/StoreRoleRequest.php`.
- Edicion de roles: `app/Http/Requests/Admin/UpdateRoleRequest.php`.
- Layout admin: `resources/views/layouts/admin.blade.php`.
- Mensajes flash: `resources/views/components/admin/flash-messages.blade.php`.
- Vistas de usuarios: `resources/views/admin/users/`.
- Vistas de roles: `resources/views/admin/roles/`.
- JavaScript del sidebar, alertas, confirmaciones SweetAlert2, busqueda de direcciones y mapa de ubicacion: `resources/js/app.js`.
- Configuracion de servicios externos y ejemplo de variables de Nominatim: `config/services.php` y `.env.example`.
- Dependencias frontend: `package.json` y `package-lock.json`.
- Seeder de permisos: `database/seeders/RolesAndPermissionsSeeder.php`.
- Seeder de Antiguedades: `database/seeders/AntiquitiesSeeder.php`.
- Seeder de Comercializacion: `database/seeders/CommercializationsSeeder.php`.
- Seeder de Tipologias: `database/seeders/TypologiesSeeder.php`.
- Seeder de Cocheras: `database/seeders/GaragesSeeder.php`.
- Seeder de Orientaciones: `database/seeders/OrientationsSeeder.php`.
- Seeder de Usos: `database/seeders/PropertyUsesSeeder.php`.
- Seeder de Vistas: `database/seeders/PropertyViewsSeeder.php`.
- Seeder de Tipos de moneda: `database/seeders/CurrencyTypesSeeder.php`.
- Datos demostrativos: `database/seeders/DemoPropertiesSeeder.php`, `database/seeders/data/demo_properties.json` y `tests/Feature/Database/DemoPropertiesSeederTest.php`.
- Configuracion de Spatie: `config/permission.php`.
- Repositorio e instalacion: `README.md`, `.gitignore`, `.gitattributes` y `.env.example`.

## Base historica `database.sql`

El archivo de referencia esta en `material/database.sql`, no en la raiz. Contiene:

- `tip_antiguedad`
- `tip_cochera`
- `tip_comercializacion`
- `tip_localidad`
- `tip_orientacion`
- `tip_provincia`
- `tip_tipologia`
- `tip_tipomoneda`
- `tip_uso`
- `tip_vista`

Inconsistencias detectadas y decisiones relacionadas:

1. `Hab` usa `1` en antiguedad, `-1` en muchos catalogos y `0` para algunos registros deshabilitados. Decision aprobada: al migrar catalogos, `1` y `-1` se convierten a booleano `true`, mientras que `0` se convierte a `false`; el SQL historico no se modifica.
2. `tip_localidad` tiene `idPartido`, pero el archivo no contiene `tip_partido` ni datos de localidades.
3. Algunas tablas usan `MyISAM`; no agregar Foreign Keys hasta resolver motor y compatibilidad de tipos.
4. Falta el punto y coma despues del `CREATE TABLE tip_orientacion`, por lo que el SQL completo no debe importarse directamente.
5. Los codigos de pais usan tanto `URU` como `URY`.
6. `tip_tipomoneda` usa IDs numericos historicos: `0` para Dolares y `1` para Pesos. Deben conservarse mientras no se acuerde otra cosa.
7. Deben conservarse IDs alfanumericos, nombres y longitudes historicas hasta que el usuario apruebe un cambio.

Estrategia recomendada cuando se aborden catalogos: migraciones Laravel explicitas y seeders repetibles que preserven IDs y valores; no ejecutar el SQL historico sin revision.

## Tests y verificacion

Ultima suite completa ejecutada el 2026-09-22:

```text
272 tests aprobados
1904 aserciones
```

La suite automatizada usa SQLite en memoria segun `phpunit.xml`; no se ejecuto `RefreshDatabase` contra la base MySQL poblada. La compatibilidad del entorno activo con MySQL se verifico mediante migraciones completas, transferencia transaccional, comparacion fila por fila y solicitudes HTTP reales.

Revision visual del login ejecutada el 2026-09-22:

- Se compilaron correctamente los assets de produccion mediante Vite y la vista Blade se almaceno en cache sin errores.
- Las siete pruebas especificas de autenticacion aprobaron sus 25 aserciones antes de ejecutar la suite completa.
- Se reviso el login en Chrome headless a `1440x900` y mediante emulacion movil exacta a `390x844`. En movil, el documento, el viewport y el ancho desplazable coincidieron en 390 px; la tarjeta quedo contenida entre 16 px y 374 px.
- El mosaico vectorial se mantuvo visible sin reducir el contraste de campos, textos, boton ni estados de foco.

Revision interactiva del portal ejecutada el 2026-09-21 con la base local poblada:

- Se verificaron portada, catalogo, pagina 2, ultima pagina, combinaciones reales de filtros, ficha sin imagenes y ficha con video en viewport de escritorio y movil mediante Chrome headless.
- Portada, catalogo, filtros y fichas respondieron `200`; un slug inexistente respondio `404`. La portada mostro seis novedades, el catalogo nueve tarjetas por pagina y la pagina 12 mostro los dos registros restantes de las 101 propiedades locales.
- El filtro de Alquiler devolvio 40 propiedades y la combinacion Casa + Buenos Aires devolvio 11. La aplicacion automatica por checkbox actualizo la URL y conservo el valor seleccionado.
- El catalogo renderizo 12 grupos de filtros; cuatro superaron la altura maxima y activaron correctamente su scroll interno. No se detectaron errores de consola, excepciones JavaScript, fallos de red propios ni desplazamiento horizontal en 1440 px o 390 px.
- Las 100 propiedades `DEM` mantienen descripcion, ubicacion y coordenadas completas, pares importe/moneda consistentes, 100 codigos y slugs unicos, 19 videos validos y cero imagenes o indicadores de foto activos.
- La mejora responsive posterior redujo el panel contraido a unos 101 px y coloco los resultados aproximadamente a 678 px desde el inicio, frente a los casi 3971 px anteriores. Al abrirlo, aplicar la ultima Tipologia y recargar, se conservaron el panel abierto, la posicion de pagina y los `392 px` de scroll interno; el checkbox seleccionado permanecio visible.
- Al cerrar los filtros moviles, el bloque de resultados recibio el foco y quedo a `96 px` del borde superior para no ocultarse bajo el encabezado fijo. En escritorio el panel continuo visible, el boton movil permanecio oculto y el ancho de documento coincidio con el viewport.
- Se comprobaron en el servidor local las canonicas y directivas para robots de portada, catalogo filtrado y ficha; las tres rutas respondieron `200` y emitieron JSON-LD solamente donde corresponde.
- `/sitemap.xml` respondio como XML con 103 URLs: portada, catalogo y las 101 propiedades habilitadas de la base local. No incluyo rutas administrativas. `/robots.txt` respondio como texto, bloqueo `/admin` y `/login` y enlazo el sitemap.

Prueba SMTP real iniciada el 2026-09-21:

- Se envio una consulta controlada desde el formulario publico de `COD001` hacia el correo configurado de Inmobiliaria y una casilla controlada del visitante. La solicitud HTTP finalizo correctamente, creo la consulta local `id = 3` con estado `nueva` y ambos intentos sincronicos terminaron sin excepciones ni nuevas entradas de error en el log de Laravel.
- La configuracion efectiva se reviso sin exponer valores: el transporte activo es SMTP, host, puerto, usuario, contrasena, remitente y nombre estan presentes y validos; Inmobiliaria esta habilitada, tiene un correo valido y este es distinto de `MAIL_USERNAME`.
- Continuan pendientes la confirmacion humana de recepcion o carpeta de spam en ambas casillas y la comprobacion de las cabeceras `Reply-To`. No se documento la casilla personal utilizada ni ninguna credencial.

Cobertura funcional actual:

- Login, credenciales invalidas, limitacion de acceso y logout por POST.
- Permisos por modulo y rol administrador.
- Visibilidad del menu segun permisos.
- Resumen operativo del Dashboard con indicadores reales de propiedades, enlaces a filtros existentes, ultimas cinco altas y portada habilitada.
- Ocultamiento integral de datos y consultas inmobiliarias en el Dashboard para usuarios sin `bienesraices`, junto con accesos rapidos condicionados por cada permiso.
- Indicadores de consultas nuevas y en proceso, ultimos cinco contactos, enlaces a filtros y detalles, acceso rapido y contador de mensajes nuevos en el sidebar.
- Ocultamiento integral de indicadores, contactos, accesos y consultas SQL de la bandeja para usuarios sin el permiso `consultas`.
- Acceso anonimo a la portada publica, estado vacio, seis altas mas recientes y uso exclusivo de la identidad de una Inmobiliaria habilitada.
- Catalogo publico independiente paginado de todas las propiedades habilitadas, con exclusion de propiedades e imagenes deshabilitadas y navegacion consistente desde la portada y la ficha.
- Busqueda publica combinable por ubicacion y filtros multiples de operacion, tipologia, ambientes, provincia, partido, localidad, cochera, antiguedad, orientacion, vista y monedas de venta o alquiler, con normalizacion, validacion, etiquetas removibles y persistencia durante la paginacion.
- Renderizado de filtros como checkboxes con conteos basados exclusivamente en propiedades habilitadas, exclusion de opciones sin resultados y aplicacion automatica al cambiar cada opcion.
- Altura limitada y scroll interno condicional en los grupos extensos de filtros, sin afectar sus titulos, conteos ni controles interactivos.
- Panel de filtros contraible en movil, contador de criterios activos, restauracion de posiciones tras el envio automatico, cierre con `Escape` y traslado accesible hacia los resultados; el panel de escritorio permanece siempre visible.
- Ordenamiento publico por fecha ascendente o descendente y superficie cubierta ascendente o descendente, manteniendo las superficies no informadas al final.
- Acceso publico por slug a la ficha completa, enlace desde cada tarjeta, consulta directa por referencia, galeria ordenada, mapa y respuestas `404` para propiedades inexistentes o deshabilitadas.
- Exclusion integral de imagenes deshabilitadas en la ficha publica y estados seguros cuando faltan fotografias, coordenadas, precios o una Inmobiliaria habilitada.
- Metadatos dinamicos, canonicas absolutas, Open Graph, Twitter Cards y JSON-LD para portada, catalogo y fichas, con exclusiones de imagenes y propiedades deshabilitadas.
- Canonica independiente para cada pagina no filtrada, `noindex,follow` para combinaciones de filtros, sitemap publico limitado a propiedades habilitadas y `robots.txt` dinamico sin rutas privadas.
- Formulario publico de consultas, normalizacion, exigencia de al menos un medio de contacto, almacenamiento asociado a la propiedad, notificacion al correo habilitado de Inmobiliaria y confirmacion separada al visitante cuando informa email.
- Destinatarios de consultas sin CC ni BCC hacia `MAIL_USERNAME`, cabeceras `Reply-To` utiles, plantilla publica sin enlaces administrativos y envio exclusivo a la Inmobiliaria cuando el contacto informa solo telefono.
- Rechazo del honeypot, bloqueo de consultas sobre propiedades deshabilitadas o inexistentes y ausencia de registros o correos ante solicitudes invalidas.
- Autorizacion independiente de la bandeja de consultas, listado, busqueda, filtro por estado, persistencia durante la paginacion, detalle y actualizacion validada de estados.
- Listado y paginacion de usuarios.
- Renderizado compacto y accesible de las acciones del listado de usuarios.
- Busqueda de usuarios por nombre o correo, filtro por rol y persistencia de filtros durante la paginacion.
- Alta de usuarios, hash de contrasena y asignacion de rol.
- Edicion de usuario sin alterar su contrasena.
- Activacion y desactivacion de usuarios, bloqueo de autodesactivacion y cierre de acceso para cuentas inactivas.
- Cambio de contrasena personal y validaciones de seguridad.
- Restablecimiento administrativo de la contrasena de otro usuario, cierre de sus sesiones, renovacion de `remember_token` y bloqueo sobre la propia cuenta.
- Eliminacion transaccional de usuarios, limpieza de datos de acceso y protecciones sobre la propia cuenta y el ultimo administrador activo.
- Autorizacion, contenido y paginacion del listado de roles y permisos.
- Renderizado compacto y accesible de las acciones del listado de roles, conservando los indicadores de roles protegidos o en uso.
- Alta transaccional de roles, normalizacion del nombre, validacion de unicidad y asignacion de permisos validos.
- Edicion transaccional de roles comunes y bloqueo integral de modificaciones sobre `administrador`.
- Conteo de usuarios por rol y eliminacion segura de roles comunes sin usuarios asignados.
- Autorizacion, alta, actualizacion de registro unico y validaciones de los datos generales de la inmobiliaria.
- Carga, visualizacion, conservacion, reemplazo y eliminacion segura del logo, incluyendo formato, tamano, archivos fisicos y permisos.
- Busqueda autorizada y validada de direcciones, normalizacion de campos de Nominatim, cache de consultas identicas, limite global de solicitudes externas y manejo controlado de fallos del proveedor.
- Renderizado de los elementos necesarios para mostrar el mapa, su marcador y el enlace al mapa completo cuando existen coordenadas validas.
- Seeder repetible de los 14 valores historicos de Antiguedades sin sobrescritura de cambios existentes.
- Autorizacion, orden, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con controles SweetAlert2 para Antiguedades.
- Normalizacion de estados y seeder no destructivo de los cinco valores historicos de Comercializacion, incluyendo `FON` deshabilitado.
- Autorizacion, menu desplegable, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Comercializacion.
- Seeder no destructivo de las 27 Tipologias y conservacion de los campos historicos de agrupacion y orden.
- Autorizacion, menu desplegable, orden por grupo, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Tipologias.
- Seeder no destructivo de las 13 Cocheras y normalizacion de sus estados historicos.
- Autorizacion, menu desplegable, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Cocheras.
- Seeder no destructivo de las nueve Orientaciones y normalizacion de sus estados historicos.
- Autorizacion, menu desplegable, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Orientaciones.
- Seeder no destructivo de los cinco Usos y normalizacion de sus estados historicos.
- Autorizacion, menu desplegable, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Usos.
- Seeder no destructivo de las cuatro Vistas y normalizacion de sus estados historicos.
- Autorizacion, menu desplegable, paginacion, alta, edicion con codigo inmutable, validaciones y activacion o desactivacion con SweetAlert2 para Vistas, incluyendo el codigo historico `C/FTE` en las rutas de detalle.
- Seeder no destructivo de los dos Tipos de moneda con conservacion del ID numerico `0`, simbolos originales y normalizacion de estados.
- Autorizacion, menu desplegable, paginacion, alta, edicion con identificador inmutable, simbolo nullable, validaciones y activacion o desactivacion con SweetAlert2 para Tipos de moneda.
- Autorizacion, menu principal, listado, paginacion, alta, edicion, normalizacion y unicidad del codigo, validacion de ubicacion y activacion o desactivacion con SweetAlert2 para la primera etapa de Bienes Raices.
- Clasificacion opcional de propiedades mediante Tipologia, Uso y Antiguedad, normalizacion de sus codigos y visualizacion de las descripciones de catalogo en el listado.
- Validacion de clasificaciones habilitadas al crear o cambiar una propiedad, junto con la conservacion controlada de valores que fueron deshabilitados despues de su asignacion.
- Clasificacion complementaria mediante Orientacion, Cochera y Vista, incluida la normalizacion y persistencia del codigo historico de vista `C/FTE` y sus descripciones en el listado.
- Carga, edicion, limpieza y resumen en el listado de superficies y distribucion de ambientes, con validaciones de precision decimal, valores no negativos y cantidades enteras.
- Carga, normalizacion, edicion, limpieza y resumen en el listado de Frente, Fondo, metros de fondo, Luminosidad y lineas telefonicas, con limites acordes a sus tipos y unidades.
- Carga, edicion, limpieza y resumen en el listado de la modalidad e importes comerciales de venta y alquiler, incluida la conservacion del identificador valido de moneda `0`.
- Validacion conjunta de cada importe con su moneda, uso exclusivo de opciones comerciales habilitadas para nuevas asignaciones y conservacion controlada de valores deshabilitados ya asociados.
- Generacion automatica, normalizacion, unicidad y resolucion de colisiones del slug; conservacion durante ediciones comunes y regeneracion solamente bajo solicitud explicita.
- Estado destacado, visualizacion del slug en la edicion y confirmacion SweetAlert2 condicional al regenerar una futura URL publica.
- Listado compacto de propiedades sin desplazamiento horizontal, ocultamiento responsivo de datos secundarios, placeholder sin imagen y seleccion de la portada relacionada para la primera columna.
- Busqueda de propiedades por codigo o ubicacion, filtros combinables por Tipologia, Comercializacion, estado y destacada, validacion de parametros y conservacion de criterios durante la paginacion.
- Autorizacion y contenido integral de la ficha de propiedad, incluidos catalogos historicos, formatos de superficies e importes, enlaces administrativos, mapa, portada, orden de galeria y exclusion de imagenes deshabilitadas.
- Alta, edicion, limpieza y normalizacion de URLs de YouTube y Vimeo, rechazo de proveedores o dominios no admitidos, sincronizacion derivada de `TieneVideo` y reproductores seguros en las fichas administrativa y publica.
- Exclusion de `TieneFoto` y `TieneVideo` del formulario general para impedir que sus indicadores derivados sean manipulados directamente.
- Autorizacion de la galeria, carga multiple de imagenes seguras, validacion de formato y tamano, almacenamiento fisico, orden inicial y seleccion automatica de la primera portada.
- Cambio de portada, validacion de pertenencia de cada imagen, eliminacion fisica con reasignacion de portada y sincronizacion de `TieneFoto` al cargar y eliminar imagenes.
- Habilitacion y deshabilitacion de imagenes sin eliminar archivos, confirmaciones SweetAlert2, bloqueo de portada sobre imagenes deshabilitadas, reasignacion automatica de portada y sincronizacion de `TieneFoto` segun imagenes habilitadas.
- Reordenamiento transaccional con posiciones consecutivas, conservacion de portada, controles de arrastre y flechas, autorizacion y rechazo de duplicados, omisiones o imagenes ajenas.
- Generacion de URLs publicas de Storage con `localhost:8000`; se comprobo que la junction `public/storage` y los archivos existentes son accesibles desde ese origen.
- Reutilizacion autorizada del buscador de direcciones desde Bienes Raices sin abrir el endpoint de Inmobiliaria a usuarios que solo tienen el permiso `bienesraices`.
- Normalizacion adicional de `calle` y `numero` en las respuestas del geocodificador sin alterar los campos consumidos por Inmobiliaria.
- Carga repetible de 100 propiedades demostrativas con codigos `DEM001` a `DEM100`, referencias a catalogos existentes, slugs unicos, videos derivados, ausencia total de imagenes y preservacion de propiedades preexistentes.

Comandos de verificacion habituales:

```bash
vendor/bin/pint --test
php artisan view:cache
php artisan test
npm run build
php artisan route:list --except-vendor -v
php artisan migrate:status
```

En Windows puede ser necesario usar `vendor\\bin\\pint`.

## Decisiones de arquitectura vigentes

- MySQL/MariaDB es el motor activo del entorno local. SQLite queda unicamente como respaldo historico de esta migracion y como motor aislado de la suite automatizada mientras no exista una base MySQL exclusiva para tests.
- Nunca se debe ejecutar una suite con `RefreshDatabase`, `migrate:fresh` o una operacion destructiva sobre `pandagestion_crm`; cualquier prueba integral de MySQL debe apuntar a una base separada y descartable.
- El fondo del login se resuelve con un SVG local repetible y CSS propio. No usa fotografias, JavaScript, peticiones externas ni una dependencia grafica adicional; la capa completa se marca como decorativa para tecnologias de asistencia.
- `origin/main` es la referencia remota del codigo fuente. Los commits nunca deben incluir `.env`, bases pobladas, credenciales, sesiones, cache, logs ni archivos multimedia cargados por usuarios.
- Git versiona codigo, migraciones, seeders y documentacion; los logos e imagenes de propiedades requieren copias de seguridad independientes junto con la base MySQL/MariaDB.
- No se uso Breeze, Jetstream ni Laravel UI; la autenticacion es pequena y explicita.
- No se agregaron repositorios, DTOs ni servicios sin una necesidad concreta.
- Los controladores actuales son pequenos; las validaciones viven en Form Requests.
- Las operaciones usuario/rol que deben ser atomicas usan transacciones.
- No hay logout por GET.
- Los permisos son por modulo, no por accion individual.
- El permiso `dashboard` habilita el acceso al panel, pero no concede acceso implicito a datos de otros modulos. Las consultas y componentes inmobiliarios del Dashboard solo se construyen cuando el usuario posee tambien `bienesraices`.
- Los indicadores del Dashboard se resuelven con una consulta agregada y las ultimas propiedades con una consulta limitada a cinco filas; no se incorporan graficos ni agregaciones temporales sin una necesidad operativa concreta.
- Los datos de Consultas del Dashboard siguen el mismo aislamiento: una consulta agregada para estados pendientes y otra limitada a cinco contactos, ejecutadas exclusivamente con el permiso `consultas`. El contador del menu consulta solamente el total `nueva` cuando ese permiso existe.
- El portal publico usa un layout separado del administrador y no carga el JavaScript administrativo. Su interactividad propia se mantiene en `resources/js/public.js`: envio automatico de filtros y ordenamiento, apertura del panel movil y restauracion temporal de sus posiciones.
- La identidad publica solo consume el registro habilitado de Inmobiliaria. La portada no revela los datos de una configuracion deshabilitada y utiliza `PandaGestion` como respaldo.
- La home y el catalogo publico consultan exclusivamente propiedades con `Hab = true` y nunca seleccionan imagenes con `Hab = false`. La home limita por consulta las seis altas mas recientes; el catalogo pagina de a nueve y respeta el orden solicitado sin anteponer automaticamente las destacadas.
- Los filtros publicos se validan en `PublicPropertySearchRequest` y se normalizan como arreglos incluso cuando la portada envia un unico valor. Operacion, Tipologia, Cochera, Antiguedad, Orientacion, Vista y monedas deben pertenecer a catalogos habilitados; Ambientes y las ubicaciones deben existir en una propiedad habilitada. Solo los valores validados se aplican y agregan a la paginacion.
- Los conteos del panel se calculan sobre el inventario habilitado completo y no dependen de los otros filtros activos. Dentro de cada grupo se aplica OR mediante `whereIn`; entre grupos y con la ubicacion libre se aplica AND. Pais no se ofrece porque `bienesraices` no almacena ese dato y no se debe inferir de Provincia.
- El desplazamiento de filtros extensos se limita al contenedor de opciones de cada grupo; no se fija ni se recorta el panel lateral completo, para mantener siempre legibles el encabezado y la separacion entre filtros.
- El panel completo permanece visible desde `lg`; por debajo de ese ancho empieza contraido para priorizar resultados. Su estado efimero de interfaz se guarda solamente en `sessionStorage`, se elimina al restaurarlo y nunca modifica los parametros validados ni la persistencia del backend.
- Las URLs SEO no dependen del host recibido por la solicitud: `PublicUrl` combina las rutas relativas de Laravel con `APP_URL`. El entorno productivo debe definir ese valor con el dominio y esquema definitivos antes de generar canonicas, imagenes sociales, JSON-LD, robots o sitemap.
- Los filtros publicos no se consideran paginas indexables: emiten `noindex,follow`, canonica al inventario base y omiten el listado estructurado. La paginacion sin filtros conserva su propia canonica porque cada pagina contiene publicaciones diferentes.
- El sitemap se genera desde la base en cada solicitud y selecciona solamente `Slug` y `updated_at` de propiedades habilitadas. No se mantiene un archivo que pueda quedar desactualizado ni se incluyen combinaciones de filtros.
- La ficha publica resuelve propiedades mediante el slug estable y exige `bienesraices.Hab = true` en la propia consulta. No expone una propiedad deshabilitada aunque se conozca su URL.
- La ficha publica carga la galeria en una consulta separada limitada a `bienesraices_imagenes.Hab = true`; no confia solamente en `TieneFoto` para decidir que archivos mostrar.
- Las consultas publicas se persisten antes de intentar la notificacion por correo. Una indisponibilidad SMTP no debe provocar la perdida del contacto ni revertir el alta.
- El destinatario de la notificacion interna es `inmobiliaria.Email` solo cuando el registro unico esta habilitado. Si la consulta contiene email, ese correo recibe una confirmacion publica independiente; no se usa CC o BCC y `MAIL_USERNAME` nunca se interpreta como destinatario. Las credenciales y el transporte pertenecen a `MAIL_*` y no se almacenan en tablas de negocio.
- Los correos se envian despues de persistir la consulta y cada intento se aisla en su propio manejo de errores. La notificacion interna incluye el detalle administrativo y usa el email del visitante como `Reply-To`; la confirmacion excluye informacion interna y usa el email de Inmobiliaria como `Reply-To`.
- `MAIL_TIMEOUT` se consume desde `config/mail.php` con un valor predeterminado de 10 segundos para evitar esperas SMTP indefinidas.
- La proteccion antispam inicial combina CSRF, validacion, honeypot y `throttle:5,1`; no se almacena la IP del visitante ni se incorpora un CAPTCHA mientras el volumen no lo justifique.
- La bandeja usa un permiso `consultas` separado de `bienesraices` y `configuracion`; los enlaces hacia la ficha administrativa de una propiedad se muestran solamente si el usuario posee tambien `bienesraices`.
- El listado de roles consulta solamente el guard `web`; los nombres tecnicos de los permisos permanecen sin cambios y la interfaz aplica etiquetas legibles.
- Los roles creados desde el administrador pertenecen siempre al guard `web`, requieren al menos un permiso y usan nombres normalizados en minusculas.
- El rol `administrador` es un rol de sistema inmutable y conserva siempre los siete permisos definidos por el seeder.
- La eliminacion de roles requiere ausencia de usuarios asociados; la condicion se vuelve a comprobar con bloqueo dentro de la transaccion.
- Toda accion de interfaz que requiera confirmacion debe usar el manejador SweetAlert2 reutilizable mediante un formulario `data-confirm`; la confirmacion visual nunca reemplaza las validaciones de backend.
- Los filtros del listado de usuarios se validan en backend, se aplican de forma combinable y solo sus valores validados se agregan a los enlaces de paginacion.
- Los filtros de Bienes Raices siguen el mismo criterio: se validan y normalizan mediante `PropertyIndexRequest`, se aplican de forma combinable y solamente los valores validados se agregan a la paginacion. Los catalogos deshabilitados permanecen disponibles como criterio de consulta para no ocultar asociaciones historicas.
- Las cuentas inactivas no pueden autenticarse; el estado tambien se verifica en cada solicitud autenticada y la propia cuenta no puede desactivarse.
- El restablecimiento administrativo de contrasena es un flujo separado del cambio personal, no permite seleccionar la propia cuenta y revoca sesiones persistidas y tokens de recuerdo del usuario afectado.
- La eliminacion de usuarios es definitiva mientras no existan datos de negocio asociados; se bloquean la propia cuenta y el ultimo administrador activo, y la condicion critica se vuelve a comprobar con bloqueo dentro de la transaccion.
- Inmobiliaria es una configuracion de registro unico con `id = 1`; se accede mediante Query Builder y conserva los nombres de campos definidos en el alcance original.
- Los logos de Inmobiliaria se almacenan en el disco `public` con nombres seguros generados por Laravel; la base no guarda BLOB ni nombres originales y el archivo anterior solo se elimina despues de persistir correctamente su reemplazo.
- La busqueda de direcciones usa el servicio publico Nominatim exclusivamente mediante una accion explicita; no implementa autocompletado mientras el proveedor publico lo prohiba.
- Nominatim permanece desacoplado del navegador mediante un proxy interno. Su URL, identificacion de la aplicacion y tiempo de cache son configurables con `NOMINATIM_BASE_URL`, `NOMINATIM_USER_AGENT` y `NOMINATIM_CACHE_TTL`.
- Las consultas externas a Nominatim incluyen `User-Agent`, `Referer`, idioma espanol y atribucion visible; las busquedas identicas se sirven desde cache y las nuevas se limitan globalmente a una por segundo.
- La verificacion visual usa el mapa HTML incrustable oficial de OpenStreetMap con un unico marcador, carga diferida y enlace al mapa completo; no incorpora Leaflet ni solicita teselas directamente desde codigo propio.
- Las propiedades guardan una copia editable de los textos de ubicacion y las coordenadas devueltas por OpenStreetMap; no dependen de consultar nuevamente al proveedor para mostrar o filtrar datos persistidos.
- Provincias y Localidades no se implementan por ahora como ABM: se evaluaran cuando los filtros y el modelo de propiedades permitan determinar si hace falta una normalizacion interna adicional.
- Los catalogos normalizan `Hab` a booleano: los valores historicos `1` y `-1` representan habilitado y `0` representa deshabilitado. Los codigos, descripciones y longitudes historicas se conservan.
- Los codigos de catalogo quedan inmutables despues del alta y los registros se deshabilitan en lugar de eliminarse, para mantener referencias estables cuando se relacionen con propiedades.
- Vistas admite la barra `/` en sus codigos para conservar `C/FTE`; los parametros de sus rutas de detalle son el ultimo segmento y usan una restriccion amplia, mientras que la busqueda exacta en base y la validacion del alta limitan los identificadores permitidos.
- Tipos de moneda conserva identificadores `smallint` no negativos, considera `0` una clave valida e inmutable y mantiene `Simbolo` como dato opcional de hasta tres caracteres.
- Los seeders de catalogos usan insercion no destructiva: agregan valores historicos faltantes sin restablecer cambios hechos desde el administrador.
- El inventario demostrativo permanece separado de `DatabaseSeeder`: se carga solo bajo solicitud explicita, usa codigos reservados `DEM001` a `DEM100`, conserva cualquier registro ya existente y no simula fotografias que no estan disponibles fisicamente.
- El menu Catalogo se vuelve desplegable desde el segundo catalogo implementado; solo contiene catalogos funcionales, se abre automaticamente en sus rutas y marca el elemento activo.
- Las Tipologias conservan `TipoGral` como agrupador opcional y `OrdTipoGral` como entero no negativo; el listado prioriza ese orden antes del nombre del grupo y la descripcion.
- Las acciones de tablas que se representen solo con iconos deben conservar una leyenda `title`, un nombre accesible mediante `aria-label` y texto `sr-only`, ademas de estados visibles de foco.
- El listado de propiedades prioriza reconocimiento y operacion rapida: la portada, el resumen principal y las acciones permanecen visibles; las caracteristicas y la comercializacion se ocultan primero cuando disminuye el ancho. Los detalles secundarios se consultan desde la edicion en lugar de forzar desplazamiento horizontal.
- La tabla `bienesraices` se completa mediante migraciones incrementales: la primera etapa incorporo identificacion, descripcion, ubicacion y estado; la segunda agrego la clasificacion basica; la tercera incorporo superficies y distribucion de ambientes; la cuarta completo la clasificacion con Orientacion, Cochera y Vista; la quinta agrego modalidad, importes y monedas de comercializacion; la sexta completo las caracteristicas fisicas restantes; la septima incorporo publicacion destacada y slug; la octava agrego los indicadores multimedia iniciales; la novena incorporo la URL real de video. Las imagenes se almacenan en una tabla relacionada separada.
- Las referencias de una propiedad a Tipologia, Uso, Antiguedad, Orientacion, Cochera y Vista son opcionales e indexadas, sin Foreign Keys por compatibilidad historica. Solo pueden realizarse asignaciones nuevas con valores habilitados; un valor deshabilitado ya asignado puede conservarse o quitarse para no invalidar datos existentes.
- Las superficies se almacenan como `decimal(12,2)` y las cantidades fisicas como `unsignedSmallInteger`; todos estos datos son opcionales para distinguir un valor desconocido de un cero informado. No se aplican relaciones entre superficies o cantidades porque el alcance original no define esas reglas de negocio.
- Frente y Fondo son superficies expresadas en metros cuadrados, mientras que MtsFondo es una longitud en metros; los tres usan `decimal(12,2)`. Luminosidad se normaliza compactando espacios y admite hasta 30 caracteres. LineasTel usa `unsignedSmallInteger`. Todos son opcionales.
- La comercializacion es opcional. Modalidad y monedas se referencian sin Foreign Keys por compatibilidad historica; los importes usan `decimal(15,2)` y cada importe forma una pareja inseparable con su moneda. Las nuevas asignaciones requieren catalogos habilitados, pero los valores deshabilitados ya asociados pueden conservarse o quitarse.
- Los slugs se generan en backend con tipologia, ambientes, barrio o localidad y codigo, tienen un maximo de 190 caracteres y cuentan con indice unico. Se preservan por defecto para mantener URLs estables; su regeneracion es una accion administrativa explicita y confirmada. La migracion completa los registros preexistentes antes de volver `Slug` obligatorio.
- `TieneFoto` se conserva como booleano explicito por compatibilidad, pero es un dato derivado: solamente `PropertyImageController` lo sincroniza con la existencia de imagenes habilitadas y no se acepta desde el formulario general. `TieneVideo` tambien es derivado y `PropertyController` lo sincroniza con la presencia de una `VideoUrl` validada.
- Los reproductores nunca usan directamente la URL ingresada como `src`: `PropertyVideo` admite una lista cerrada de hosts, extrae y valida el identificador, genera la URL canonica y construye el endpoint de insercion seguro. No se consulta la API de los proveedores ni se garantiza que el video exista o permita insercion hasta que el navegador lo carga.
- Las imagenes usan el disco publico de Laravel y nombres generados; la base conserva solo la ruta relativa. La carga guarda primero los archivos y los elimina si falla la transaccion. La eliminacion modifica primero la base y luego retira el archivo fisico.
- `Hab` controla la visibilidad de cada imagen sin eliminarla. Toda imagen deshabilitada pierde `Portada`; entre las habilitadas debe existir una unica portada cuando haya al menos una. La normalizacion de portada y `TieneFoto` se centraliza y se ejecuta dentro de las transacciones que alteran la galeria.
- `Orden` siempre representa posiciones consecutivas desde `1`; se normaliza al guardar un reordenamiento y despues de eliminar una imagen. La portada es independiente de la posicion y no cambia al reordenar.
- El arrastre usa APIs nativas del navegador y se complementa con botones para ofrecer una alternativa accesible y compatible con pantallas tactiles; no se incorporo una biblioteca de ordenamiento.
- La portada del listado se obtiene con una subconsulta correlacionada que prioriza `Portada`, `Orden` e ID entre imagenes habilitadas, evitando duplicar filas de propiedades por una union uno-a-muchos.
- La ficha administrativa es una vista de solo lectura. Obtiene los datos de la propiedad y las descripciones de catalogo mediante una consulta Query Builder, y carga por separado las imagenes habilitadas para no duplicar la fila principal.
- `bienesraices_imagenes` usa Foreign Key hacia `bienesraices` porque ambas tablas fueron creadas dentro del modulo nuevo; esta decision no modifica la politica de compatibilidad de los catalogos historicos.
- El codigo de propiedad se normaliza a mayusculas, admite letras y numeros, debe ser unico y puede editarse mientras la nueva version mantenga esa unicidad.
- Ocultar un menu nunca reemplaza la autorizacion de backend.
- La portada con seis novedades, el catalogo independiente con filtros y ordenamiento, la ficha publica, el video por URL, el flujo de consultas y el SEO tecnico estan implementados; los catalogos de ubicacion aun no fueron desarrollados.

## Pendientes y proximo paso sugerido

Proximo paso sugerido, aun no implementado:

- Crear una base MySQL/MariaDB separada y descartable para testing, configurar un entorno de prueba dedicado y ejecutar alli la suite completa. No reutilizar `pandagestion_crm`, porque `RefreshDatabase` elimina y reconstruye sus tablas.

Pendientes posteriores, sin orden definitivo:

- Completar, si aun no se hizo, la verificacion humana de la prueba SMTP ya aceptada por el servidor: recepcion o spam y comportamiento de `Reply-To` en ambas casillas.
- Configurar el dominio definitivo en `APP_URL` antes de publicar para que canonicas, imagenes sociales, JSON-LD, robots y sitemap dejen de usar `localhost`.
- Resolver las inconsistencias restantes del SQL historico si mas adelante se decide crear catalogos de ubicacion, especialmente la ausencia de partidos para Localidades y los codigos de pais de Provincias.

## Como retomar el proyecto

1. Leer este archivo completo.
2. Revisar la solicitud mas reciente del usuario y no asumir que el proximo paso sugerido ya fue aprobado.
3. Ejecutar `php artisan test` para confirmar la linea base.
4. Revisar `php artisan route:list --except-vendor -v` y `php artisan migrate:status` si la tarea afecta rutas o base de datos.
5. Implementar una sola etapa pequena, ejecutar Pint, tests y build cuando corresponda.
6. Actualizar este archivo antes de entregar el resultado.
