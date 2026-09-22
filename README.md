# PandaGestion

CRM inmobiliario desarrollado como monolito Laravel. Incluye un administrador protegido por roles y permisos, gestión integral de propiedades y un portal público para publicar, buscar y consultar inmuebles.

## Funcionalidades principales

- Autenticación administrativa y control de cuentas activas.
- Usuarios, roles y permisos por módulo mediante Spatie Permission.
- Configuración de la inmobiliaria, logo, ubicación y mapa.
- Catálogos inmobiliarios administrables.
- Gestión de propiedades, publicación, video y galería ordenable.
- Portal público con buscador, filtros, ficha, consultas y SEO técnico.
- Bandeja administrativa de consultas y notificaciones por correo.
- Dashboard operativo con indicadores y accesos condicionados por permisos.

## Tecnología

- PHP 8.4 y Laravel 12.
- MySQL o MariaDB.
- Blade, Tailwind CSS 4, Vite y JavaScript.
- SweetAlert2 para confirmaciones sensibles.
- PHPUnit para pruebas automatizadas.

## Instalación local

Clonar el repositorio e instalar las dependencias:

```bash
git clone https://github.com/mmdp8612/pandagestion-crm.git
cd pandagestion-crm
composer install
npm install
```

Crear el archivo de entorno y generar la clave de Laravel:

```bash
cp .env.example .env
php artisan key:generate
```

En Windows PowerShell puede reemplazarse `cp` por:

```powershell
Copy-Item .env.example .env
```

Crear una base vacía y completar en `.env` la conexión correspondiente:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pandagestion_crm
DB_USERNAME=usuario_local
DB_PASSWORD=contraseña_local
```

Aplicar las migraciones, cargar los roles y catálogos iniciales, y crear el enlace de Storage:

```bash
php artisan migrate --seed
php artisan storage:link
```

Compilar los recursos y levantar el servidor local:

```bash
npm run build
php artisan serve
```

La aplicación estará disponible por defecto en `http://localhost:8000`.

## Primer usuario administrador

Los seeders no crean usuarios ni contienen credenciales. El primer usuario debe generarse deliberadamente en el entorno correspondiente y luego recibir el rol `administrador`.

Una opción para un entorno local es utilizar Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'administrador@example.com',
    'password' => 'ReemplazarPorUnaClaveSegura123',
]);

$user->assignRole('administrador');
```

El modelo aplica automáticamente el hash seguro a la contraseña. No se debe incorporar una contraseña real al código ni a los seeders.

## Datos demostrativos

La carga de 100 propiedades de demostración es opcional y no forma parte del seeder general:

```bash
php artisan db:seed --class=DemoPropertiesSeeder
```

## Pruebas y calidad

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

En Windows puede ser necesario ejecutar `vendor\\bin\\pint --test`.

La suite utiliza SQLite en memoria y nunca debe apuntarse a una base MySQL poblada cuando ejecute `RefreshDatabase`.

## Archivos locales excluidos

El repositorio no incluye:

- `.env` ni credenciales reales.
- Bases SQLite locales.
- Dependencias de Composer o npm.
- Assets compilados por Vite.
- Sesiones, caché y logs.
- Logos o imágenes de propiedades cargadas en Storage.

Las imágenes y el logo deberán respaldarse y restaurarse por separado cuando se despliegue la aplicación.

## Contexto de desarrollo

El estado funcional, las decisiones de arquitectura, rutas, migraciones y próximos pasos se documentan en [`AGENTS.md`](AGENTS.md).
