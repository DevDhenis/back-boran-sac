# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Qué es esto

Backend API-only en **Laravel 12 / PHP 8.2+** para un e-commerce (catálogo, carrito, ventas, inventario, empleados/roles). Sin vistas Blade de negocio: todo se consume vía `routes/api.php`. Existe un frontend separado que consume estas APIs — **cualquier cambio en la forma de request/response de un endpoint puede romper el front; avisar antes de cambiar contratos**.

## Comandos

```bash
composer dev          # levanta TODO: serve + queue listener + pail (logs) + vite (concurrently)
php artisan serve     # solo el servidor HTTP
composer test         # config:clear + php artisan test (suite completa)
php artisan test --filter=NombreDelTest   # un solo test
php artisan test tests/Feature/ExampleTest.php   # un archivo
./vendor/bin/pint     # formateo/lint (Laravel Pint)
php artisan migrate           # correr migraciones
php artisan migrate:fresh --seed   # recrear BD + seeders (resetea datos)
```

- BD por defecto en `.env.example`: **sqlite**. Los tests corren sobre sqlite `:memory:` (ver `phpunit.xml`).
- `.env` está en `.gitignore` — nunca commitear; solo `.env.example` es público.
- **El repo NO incluye `.env`** (solo `.env.example`). Antes de `serve`/`test` en una copia limpia hay que crearlo: `cp .env.example .env` → `php artisan key:generate` → `php artisan jwt:secret` (JWT requiere `JWT_SECRET`; auth = tymon/jwt-auth) → `touch database/database.sqlite` → `php artisan migrate`. Sin esto, `php artisan serve` y `php artisan test` fallan por falta de APP_KEY/secret.

## Commits

- **No agregar coautoría de Claude/IA** en los commits: nunca incluir líneas `Co-Authored-By: Claude ...` ni menciones de "Generated with Claude Code" en el mensaje. En los commits (el usuario figura como único autor).

## Estándares de Código y Comunicación

**Idiomas — regla estricta.** Todo identificador interno del proyecto va en inglés; solo lo que ve el usuario final va en español.

| Qué                                                                                         | Idioma             |
| ------------------------------------------------------------------------------------------- | ------------------ |
| Nombres de variables, funciones, métodos, clases, archivos                                  | **Inglés**         |
| Nombres de columnas de BD, tablas y valores de dominio                                      | **Inglés**         |
| Rutas, nombres de endpoints, ramas                                                          | **Inglés**         |
| Textos de UI / respuestas al usuario (labels, placeholders, `message`, validaciones, mails) | **Español**        |
| Comentarios en código (SOLO si explican un **PORQUÉ**, no un "qué")                         | **Inglés** técnico |

- Al **extender o crear** código, aplicar esto desde el día uno.
- El código **ya está migrado a inglés** en todos los identificadores internos: columnas, tablas, valores de dominio (estados de venta, tipos de movimiento, método/estado de pago), variables, métodos y clases. No debería quedar identificador interno en español; si aparece uno, migrarlo.
- **Excepción — datos/contenido en español**: los *valores* que son catálogo/contenido siguen en español (nombres de roles como `Cliente`/`Administrador`, categorías, unidades, tipos de documento como `DNI`, y los slugs de acceso usados en `access:<slug>`, ej. `access:ventas`). No son valores de dominio acotados (enums), son contenido; se mantienen en español.
- **Cuidado con el contrato del front**: renombrar columnas o valores de dominio cambia la forma del JSON que consume el frontend — coordinar/avisar antes (ver nota de contratos arriba).

## Arquitectura (lo que hay que entender leyendo varios archivos)

### Autenticación y autorización — JWT + accesos por rol

- Auth = **`tymon/jwt-auth`** (no Sanctum, aunque esté instalado). Guard `api` con driver `jwt` en `config/auth.php`. El `User` implementa `JWTSubject`.
- Dos middlewares custom, registrados como alias en `bootstrap/app.php`:
    - **`jwt`** (`JwtMiddleware`) — valida el token; devuelve 401 con `{success:false, message}` si es inválido/expirado/ausente.
    - **`access:<slug>`** (`CheckAccess`) — autorización por permiso. Compara (case-insensitive) `<slug>` contra `auth()->user()->role->accesses[].name`. Ej: `access:ventas`.
- El modelo de permisos es: `User → role_id → Role → (pivot access_roles) → Access`. Para dar/quitar permisos se sincroniza la pivote vía `RoleController@syncAccesses`.
- **Verificación de correo obligatoria para login**: `login` bloquea a usuarios con `email_verified_at` null y responde **403** `{ success:false, requires_verification:true }` (no entrega token). `register` **no devuelve token** (el usuario debe verificar primero). `auth/verify-email` y `auth/resend-code` son **públicos** (validan por email+código, sin JWT). El código de verificación vive en `users.verification_code`; el de recuperación en `users.recovery_code`.

### Modelo de dominio — `Person` es el eje

`Person` es la entidad central de identidad. Un `User` (credenciales/login) pertenece a un `Person` y a un `Role`. Tanto `Employee` como `Client` cuelgan de `Person` (por `person_id`). Por eso desde un usuario autenticado se resuelve su faceta con `auth()->user()->employee` o `auth()->user()->client` (ver `User::client()` que usa `hasOne(Client, person_id, person_id)`). Un mismo request puede representar a un empleado (staff) o a un cliente (comprador) según qué relación exista.

### Contrato de respuesta JSON (importante para no romper el front)

Todas las respuestas siguen la forma `{ "success": bool, "message": string, "data"|"errors": ... }`, construida **inline** en cada controlador (no hay trait/helper de respuesta). El manejo de errores está **centralizado en `bootstrap/app.php`** (`withExceptions`), que traduce excepciones a esta misma forma con status codes específicos:

- `ValidationException` → 422 con `errors: [{error: msg}]`
- `NotFound` / `ModelNotFound` → 404
- `AccessDenied` / `AuthorizationException` → 403
- `QueryException` code `23000` (FK) → 409 "recurso en uso"
- resto → 500

Al agregar/editar endpoints, mantener exactamente esta envoltura.

### Capas por convención

- **Validación**: FormRequests en `app/Http/Requests/<Dominio>/` (ej. `Sales/`, `Employee/`, `Auth/`). Un endpoint que valida input debe tener su FormRequest, no validar suelto (salvo casos puntuales como `SaleController@changeStatus`).
- **Serialización**: API Resources en `app/Http/Resources/` (`SaleResource`, `ProductResource`, etc.). Los controladores devuelven Resources, no modelos crudos, y hacen eager-load explícito de relaciones (`->with([...])`) para evitar N+1.
- **Endpoints públicos** (sin auth) viven en `app/Http/Controllers/Public/`.
- **Imágenes**: toda subida pasa por `App\Support\ImageUploader::upload($file, $folder, $maxSize?, $quality?)`, que **optimiza y convierte a WebP** en ambos drivers (local con Intervention/GD, Cloudinary con transformación entrante). Guarda `<folder>/<uuid>.webp` (local) o devuelve la URL segura (Cloudinary). Defaults en `config/images.php` (`max_size=1024`, `quality=80`); los avatares de perfil usan `512`. No agregar otra capa de optimización.
- `routes/api.php` es la fuente de verdad de qué está expuesto: hay controllers en el repo que aún no están enruteados.

### Convenciones de datos

- Columnas, tablas y valores de dominio están en **inglés** (ver _Estándares de Código y Comunicación_). Estados de venta: `pending_shipment`, `in_preparation`, `in_transit`, `delivered`, `cancelled`. Tipos de movimiento de inventario: `inbound`, `outbound`, `adjustment` (+ estado `active`/`voided`). Pago: método `cash`/`card`/`transfer`, estado `pending`/`confirmed`/`failed`. La bandera de registro es la columna `status` con código `A`/`I`.
- El historial de estados de venta se registra en `SaleStatusHistory` (quién cambió: empleado o cliente) cada vez que cambia el status de una `Sale`.

## Despliegue (producción) — Render + Aiven

La API se despliega en **Render** (Web Service, Docker, plan Free) contra una base **MySQL en Aiven** (plan Free, conexión TLS obligatoria). El frontend va aparte (Vercel). El desarrollo local sigue usando MySQL local (Laragon), independiente de esto.

- **Build/arranque**: `Dockerfile` en la raíz (PHP 8.3, `composer install --no-dev` → sin Faker). Al arrancar el contenedor corre: `config:clear` → `migrate --force` → `php artisan serve` en `$PORT`. Ver el `CMD` del `Dockerfile`.
- **Variables de entorno**: se configuran en el **panel de Render → Environment**, NO en `.env` (que no se commitea). Plantilla de referencia sin secretos: `.env.render.example`. Claves: `APP_KEY`, `JWT_SECRET`, credenciales de Aiven (`DB_*`) y `MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem`.
- **TLS a la BD**: Aiven exige SSL. El certificado CA (público, sin secretos) está en `certs/ca.pem` (commiteado) y `config/database.php` lo usa vía `PDO::MYSQL_ATTR_SSL_CA`.
- **Healthcheck**: `GET /api/health` (público, sin auth).
- **Límites del plan Free**: el servicio de Render se duerme tras ~15 min de inactividad (primer request lento) y su disco es efímero (no persistir archivos subidos ahí).

### Migraciones en producción (AUTOMÁTICO)

El `CMD` corre `migrate --force` en cada arranque. Para aplicar **nuevas migraciones** basta con hacer **push a `main`**: Render reconstruye y migra solo. No hay pasos manuales.

### Seeders en producción (MANUAL, desde tu PC)

El seeding **NO** corre en el arranque (varios seeders usan `insert()` y duplicarían al reiniciar), y el plan Free de Render **no tiene Shell**. Por eso los seeders se corren **desde tu PC apuntando a Aiven**, sobrescribiendo las variables `DB_*` por entorno:

```bash
DB_HOST=<host-aiven> DB_PORT=<puerto> DB_DATABASE=defaultdb \
DB_USERNAME=avnadmin DB_PASSWORD=<password> \
MYSQL_ATTR_SSL_CA="<ruta-abs>/certs/ca.pem" \
php artisan db:seed --force                 # todo el DatabaseSeeder
# o para uno solo:  php artisan db:seed --class=ProductCategorySeeder --force
```

(Los valores reales viven en el panel de Render/Aiven; nunca se commitean.) `DatabaseSeeder` siembra **datos base + 2 cuentas**: `admin` (rol Administrador General; **no** es colaborador, sin faceta `Employee`) y `cliente` (rol Cliente, con faceta `Client`), más tipos de documento, roles, accesos, unidades y categorías. Productos/ventas/inventario/colaboradores NO se siembran ahí (los colaboradores van en `DemoDataSeeder`). Nota: `auth()->user()->employee` y `->client` resuelven la faceta vía `hasOne(..., 'person_id', 'person_id')` en el modelo `User`.
