# 🚀 Guía completa: crear y configurar un proyecto Laravel 12 (API REST)

Este documento muestra, paso a paso y con ejemplos, cómo crear un proyecto Laravel 12 orientado a APIs (REST) similar a este repositorio. Cubre:

- Requisitos e instalación básica en Windows / WSL
- Flujo de trabajo para crear, restaurar y preparar el proyecto
- Autenticación con JWT usando `tymon/jwt-auth` (instalado en este repo)
- Roles y permisos con `spatie/laravel-permission`
- Buenas prácticas: Form Requests, Resources, Policies y middlewares
- Ejemplos de `AuthController` y comandos para probar con cURL / Postman
- Testeo básico (Pest/PHPUnit)

El objetivo es que con esta guía puedas replicar el entorno y las convenciones usadas aquí y que puedas exportar las pruebas de Postman/JSON que ya tienes.

---
 
## Índice

- ⚙️ [Requisitos e instalación de dependencias base](#requisitos-e-instalacion-de-dependencias-base)
- 🧱 [Crear un nuevo proyecto Laravel](#crear-un-nuevo-proyecto-laravel)
- ♻️ [Restaurar un proyecto existente](#restaurar-un-proyecto-existente)
- 🧩 [Configuraciones extras](#configuraciones-extras)
- 🐘 [Bases de datos y drivers](#bases-de-datos-y-drivers)
- 🔐 [Autenticación con JWT (tymon/jwt-auth)](#autenticacion-con-jwt-tymonjwt-auth)
- 🛡️ [Roles y permisos (Spatie)](#roles-y-permisos-spatie)
- 🔁 [Controllers, Requests y Resources (buenas prácticas)](#controllers-requests-y-resources-(buenas-practicas))
- 🧪 [Testing (Pest/PHPUnit)](#testing-pestphpunit)
- 🧯 [Excepciones JWT y Troubleshooting](#excepciones-jwt-y-troubleshooting)
- 🧩 [Security & Production Checklist](#security--production-checklist)
- 🗂️ [Postman y colecciones](#postman-y-colecciones)
- 🔎 [Endpoints (ejemplos reales del proyecto)](#endpoints-ejemplos-reales-del-proyecto)
- 🔁 [Flujo (request → response)](#flujo-request-→-response)
- ✅ [Mejores prácticas del repo](#mejores-practicas-del-repo)
- 🚀 [Preparar para Producción](#preparar-para-produccion)
- 💾 [Uso con Docker (opcional)](#uso-con-docker-(opcional))
- 📚 [Recursos y lectura adicional](#recursos-y-lectura-adicional)
- 🧠 [Recursos recomendados](#recursos-recomendados)

---

## 1. ⚙️ Requisitos e instalación de dependencias base

Recomendación: usa WSL2 o Windows con PowerShell actualizado. WSL facilita el manejo de dependencias del sistema y la compatibilidad con Linux cuando trabajas con Docker.

### 🧰 PHP + Composer (via Chocolatey)
```powershell
choco install php
choco install composer
```

#### 📌 Verifica la instalación:
```powershell
php -v
composer -V
```

### 🌿 1.1. Node con NVM
- Descargar en **[NVM Windows](https://github.com/coreybutler/nvm-windows)**
- O por chocolate
```powershell
choco install nvm
nvm list available # Enlistar Todas las Versiones Node Disponibles
nvm install 20   # Instala Node LTS (ejemplo)
nvm list # Enlistar todas las versiones Node Instaladas Localmente
nvm use 20
nvm uninstall 18.X.X # Desinstalar Node 18.X.X LTS (ejemplo)
```

#### 📌 Verifica la instalación:
```powershell
node -v
npm -v
```

#### 🧅 (Opcional) Bun (como alternativa a npm)
```powershell
powershell -c "irm bun.sh/install.ps1 | iex"
```

#### 📌 Verifica la instalación:
```powershell
bun -v
```

### 🐳 1.2. Docker Desktop
- Descargar en **[Docker Windows](https://www.docker.com)**
- O por chocolate
```powershell
choco install docker-desktop
```

- Asegúrate de que Docker esté corriendo antes de continuar.

- 💡 Consejo: Puedes usar contenedores Docker para todo (PHP, DB, Redis, etc.) o solo para bases de datos.

## 🧱 2. Crear un Nuevo Proyecto Laravel

### 🆕 Crear proyecto
```powershell
laravel new nombre-proyecto
# o con Composer
composer create-project laravel/laravel nombre-proyecto
```

### 🚶‍♂️ Acceder al proyecto
```powershell
cd nombre-proyecto
```

### 🔑 Generar clave de aplicación
```powershell
php artisan key:generate
```

### 📦 Instalar dependencias
```powershell
composer install
npm install     # o bun install
```

### 🔄 Actualizar dependencias
```powershell
composer update
npm update     # o bun update
```

### 🗑️ Eliminar dependencias
```powershell
rm -r vendor        # para dependencias composer
rm -r node_modules  # para dependencias nodejs
```

### ⚙️ Compilar assets frontend
```powershell
npm run dev     # o bun run dev
# Para producción:
npm run build   # o bun run build
```

### ▶️ Levantar servidor local
```powershell
php artisan serve
```

## ♻️ 3. Restaurar un Proyecto Existente (Clonado o Copiado)

### 📁 Entrar al proyecto
```powershell
cd nombre-proyecto
```

### 🔧 Instalar dependencias PHP y JS
```powershell
composer install
npm install     # o bun install
```

### 🧬 Copiar variables de entorno
```powershell
cp .env.example .env
```

### 🔑 Generar clave de aplicación
```powershell
php artisan key:generate
```

### 🗄️ Migraciones y Seeders
```powershell
php artisan migrate                 # Generar base de datos
php artisan db:seed                 # Poblar base de datos de Seeders
php artisan migrate --seed          # Generar base de datos con Seeders
php artisan migrate:refresh         # Reiniciar base de datos
php artisan migrate:refresh --seed  # Reiniciar base de datos completa con Seeders

# Reiniciar para una clase en especifico
php artisan migrate:refresh --path=database/migrations/0001_01_01_000000_create_users_table.php
php artisan db:seed --class=DatabaseSeeder
```

#### *📝NOTA IMPORTATE*: Cada vez que se modifica una clase de **migrations**, se tiene que refrescar la migración de la(s) clase(s) correspondiente(s) o bien refrescar todas las clases del proyecto.

### ▶️ Levantar el servidor
```powershell
php artisan serve
```

## 🧩 4. Configuraciones Extras
 
### 🧹 Limpieza y caché de configuración
```powershell
php artisan config:clear
php artisan cache:clear
php artisan clear-compiled
php artisan event:clear
php artisan route:clear
php artisan view:clear
# O para ejecutar todos con un solo comando
php artisan optimize:clear
```

 
 
### 🔧 Volver a generar caché optimizada
```powershell
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
# O para ejecutar todos con un solo comando
php artisan optimize
```

 
 
### ➕ Comandos extras para caché
```powershell
php artisan make:cache-table
php artisan schedule:clear-cache
```

 
 
### ⚙️ Comprobación del entorno
```powershell
php artisan --version
php artisan about
```

## 🐘 5. Bases de Datos y Drivers

Laravel 12 soporta de forma nativa:
 
- PostgreSQL
- SQLite
 
📌 En .env define la conexión, por ejemplo:
```powershell
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_db
DB_USERNAME=root
DB_PASSWORD=
```

### Lista de todos los comandos de php artisan
```powershell
php artisan list
```
#### 1. Instalación de paquetes
```powershell
# Instalación por composer
composer require laravel/breeze
composer require laravel/sanctum

# Instalación por php artisan
php artisan breeze:install
php artisan jetstream:install
php artisan install:api
```
#### 2. Creación de migración(migration)
```powershell
php artisan make:migration NombreMigracion
```
#### 3. Creación de model(modelo)
```powershell
php artisan make:model NombreModelo
php artisan make:model NombreModelo -m      #-m es para crear la migración
```
#### 4. Creación de factoria(factory)
```powershell
php artisan make:factory NombreFactory --model=Modelo
```
#### 5. Creación de semilla(seeder)
```powershell
php artisan make:seeder NombreSeeder
```
#### 6. Creación de proveedor(vendor)
```powershell
php artisan vendor:...
# ejemplos del uso de vendor:
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=migrations
```
#### 7. Creación de controlador(controller)
```powershell
php artisan make:controller NombreController --api
```
#### 8. Creación de proveedor(provider)
```powershell
php artisan make:provider NombreServiceProvider
```
#### 9. Creación de ruta(router)
```powershell
# No existe comando php artisan directo, solamente se crea la clase php en routes/ y se edita bootstrap/app.php para incluir la clase
php artisan route:list      #Enlistar rutas existentes del proyecto
```
#### 10. Creación de vista(view)
```powershell
php artisan make:view Modelo.NombreView        #ejemplo: php artisan make:view users.index
```
#### 11. Creación de pedido(request)
```powershell
php artisan make:request NombreRequest
```

### 🧪 Debug con Xdebug

En tu php.ini:
```powershell
zend_extension="xdebug"
xdebug.mode=develop,debug
xdebug.start_with_request=yes
```
Verifica con:
```powershell
php -m | findstr xdebug
```

### 🧭 Comandos Útiles de Laravel
---

## 6. Autenticación con JWT (tymon/jwt-auth)

Este proyecto utiliza `tymon/jwt-auth`. Si recién armas un proyecto, sigue estos pasos para configurar JWT correctamente.

### 1. Instala el paquete (si no está instalado):

```pwsh
composer require tymon/jwt-auth
```

### 2. Publica configuración si quieres personalizar `config/jwt.php`:

```pwsh
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### 3. Genera la clave JWT y agrega a `.env`:

```pwsh
php artisan jwt:secret
```

### 4. Comprueba que `User` implemente `Tymon\JWTAuth\Contracts\JWTSubject`. Ejemplo mínimo (puede ya venir en el user model):

```php
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
	public function getJWTIdentifier() { return $this->getKey(); }
	public function getJWTCustomClaims() { return []; }
}
```

### 5. Protege las rutas con middleware `jwt.auth` o `auth:api` según tu configuración. Ejemplo de AuthController (login / logout / refresh)

Un `AuthController` mínimo para `tymon/jwt-auth`:

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
	public function register(Request $request) {
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6',
		]);

		$user = User::create([ 'name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']) ]);
		$token = auth()->login($user);
		return $this->respondWithToken($token);
	}

	public function login(Request $request) {
		$credentials = $request->only(['email', 'password']);
		if (! $token = auth()->attempt($credentials)) {
			return response()->json(['severity' => 'error','summary' => 'Unauthorized','detail' => 'Invalid credentials'], 401);
		}
		return $this->respondWithToken($token);
	}

	public function logout() {
		auth()->logout();
		return response()->json(['message' => 'Successfully logged out']);
	}

	public function refresh() {
		return $this->respondWithToken(auth()->refresh());
	}

	protected function respondWithToken($token) {
		return response()->json(['access_token' => $token,'token_type' => 'bearer','expires_in' => auth()->factory()->getTTL() * 60]);
	}
}
```

### 6. Pruebas con cURL y Postman

Login con cURL:

```bash
curl -X POST http://localhost:8000/api/v1/auth/login -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"password"}'
```

Respuesta (ejemplo):

```json
{ "access_token": "eyJ...", "token_type": "bearer", "expires_in": 3600 }
```

Usa `Authorization: Bearer <token>` para las peticiones protegidas. También puedes importar la colección Postman adjunta en este repo y ejecutar con la `environment` exportada.

---

## 🛡️ 7. Roles y permisos (Spatie)

Instala `spatie/laravel-permission` para manejar roles y permisos:

```pwsh
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

En el `seeder` puedes crear roles y permisos y asignarlos:

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

Permission::firstOrCreate(['name' => 'view users']);
Role::firstOrCreate(['name' => 'admin'])->givePermissionTo(Permission::all());
```

Las `Policies` y middlewares del proyecto se integran con Spatie. Ejemplos:

- Middleware: `permission:view users` — valida permisos
- Middleware: `role:admin|super admin` — valida roles
- Policy: `UserPolicy` tiene validaciones que permiten a `admin`/`super admin` pasar.

> Nota: en este repo el Seeder crea `super admin` con espacio (`'super admin'`) — mantén consistencia en strings o usa `config/roles.php`.

---

## 🔁 8. Controllers, Requests y Resources (buenas prácticas)

- `Form Requests` (`php artisan make:request`) para centralizar validación y preparación de datos.
- `Resources` (`php artisan make:resource`) para transformar output JSON y ocultar campos sensibles.
- Usa `Policy` para autorización y `Gate` si es necesario.

Ejemplo de `RoleRequest` (validación):

```php
public function rules(): array {
	return [
		'name' => ['required','string','max:255','unique:roles,name,'.request()->route('id')],
		'permissions' => ['nullable','array'],
		'permissions.*' => ['integer','exists:permissions,id'],
	];
}
```

Para respuestas, `RoleResource` y `PermissionResource` devuelven la estructura deseada. En este proyecto usamos `role` y `permission` en lugar de `data` en respuestas para claridad.

---

## 🧪 9. Testing (Pest/PHPUnit)

Recomendado: usar Pest para tests legibles y rápidos. Ejemplo:

```php
it('create role with permissions', function () {
	actingAsAdmin(); // helper que crea un user con role=admin
	$payload = ['name' => 'moderator','guard_name' => 'web','permissions' => [1,2,3]];
	postJson('/api/v1/roles', $payload)->assertStatus(201)
		->assertJsonStructure(['role' => ['id','permissions']]);
});
```

Ejecuta tests:

```pwsh
php artisan test
```

---

## 🧯 10. Excepciones JWT y Troubleshooting

Comunes:

- Token not provided: envía Authorization: Bearer <token>
- Token expired: usar endpoint `refresh` para renovar
- Token invalid/blacklisted: eliminar cache o re-emitir

Si ocurren errores en JWT, revisa `app/Exceptions/Handler.php` y `bootstrap/app.php` (aquí mapeamos excepciones JWT a JSON responses).

---

## 🧩 11. Security & Production Checklist

- Establecer `APP_ENV=production` y `APP_DEBUG=false`.
- Asegura `DB_USERNAME` y `DB_PASSWORD` correctos y que no se suban al repo.
- Configura HTTPS y CORS apropiado.
- Revoca tokens y rota `JWT_SECRET` cuando sea necesario.

---

## 🗂️ 12. Postman y colecciones

Importa `Laravel_API_Permisos_Roles.postman_collection.json` y `Laravel_API_Environment.postman_environment.json` para pruebas rápidas. Asegúrate de configurar el `{{baseUrl}}` y añadir token en Authorization.

---

## 🔎 13. Endpoints (ejemplos reales del proyecto)

A continuación se listan los endpoints principales que se usan en este repositorio y el middleware asociado:

 - Roles
	 - GET /api/v1/roles — permiso: `view roles`
	 - GET /api/v1/roles/{id} — permiso: `view roles`
	 - POST /api/v1/roles — permiso: `create roles|update roles`
	 - PUT /api/v1/roles/{id} — permisos: `role:admin + permission:edit roles`
	 - DELETE /api/v1/roles/{id} — permisos: `role:admin|super admin + permission:edit roles|delete roles`

 - Permissions
	 - GET /api/v1/permissions — permiso: `view permissions`
	 - GET /api/v1/permissions/{id} — permiso: `view permissions`
	 - POST /api/v1/permissions — permiso: `create permissions`
	 - PUT /api/v1/permissions/{id} — permiso: `edit permissions`
	 - DELETE /api/v1/permissions/{id} — permiso: `delete permissions`

 - Users
	 - GET /api/v1/users — permiso: `view users`
	 - POST /api/v1/users — permiso: `create users`
	 - PUT /api/v1/users/{user} — permiso: `edit users`
	 - DELETE /api/v1/users/{user} — permiso: `delete users`

 - Statuses
	 - GET /api/v1/statuses — permiso: `view permissions`
	 - POST /api/v1/statuses — permiso: `create permissions`
	 - PUT /api/v1/statuses/{status} — permiso: `edit permissions`
	 - DELETE /api/v1/statuses/{status} — permiso: `delete permissions`

> Nota: Este repositorio usa convenciones híbridas: `RoleResource` y `PermissionResource` forman la salida para páginas y objetos. Para crear roles o permisos, el body debe validar con `RoleRequest` o `PermissionRequest`.

### Ejemplo: crear rol (request)

POST /api/v1/roles

Headers:

```
Content-Type: application/json
Authorization: Bearer <token>
```

Body:

```json
{
	"name": "moderator",
	"guard_name": "web",
	"permissions": [1,2,3]
}
```

Ejemplo de respuesta (201):

```json
{
	"role": {
		"id": 5,
		"name": "moderator",
		"guard_name": "web",
		"permissions": [
			{"id": 1, "name": "view statuses"},
			{"id": 2, "name": "create statuses"}
		],
		"created_at": "2025-11-03T04:39:16.000000Z"
	},
	"message": "Role created successfully"
}
```

### Ejemplo: crear permiso (request)

POST /api/v1/permissions

Body:

```json
{
	"name": "manage reports",
	"guard_name": "web"
}
```

Respuesta (201):

```json
{
	"permission": {
		"id": 18,
		"name": "manage reports",
		"created_at": "2025-11-03T04:20:15.000000Z"
	},
	"message": "Permission created successfully"
}
```

---

## 🔁 14. Flujo (request → response)

Resumen de pasos en el pipeline del framework:

- El `Request` entra por la ruta en `routes/*`.
- Middleware `jwt.auth` valida el token JWT.
- Middleware `permission` o `role` verifica autorización (o permite bypass si es admin/super admin).
- `Controller` recibe request y delega validación a `FormRequest` (ej. `RoleRequest`).
- Se ejecuta la acción sobre el `Model` (crear, actualizar, eliminar, sync permissions).
- `Resource` (ej. `RoleResource`) transforma la respuesta a JSON (collection / item handlers).
- Respuesta JSON estandarizada es devuelta al cliente.

Este flujo se repite para la mayoría de endpoints — policies se ejecutan cuando el código usa `authorize` o `Gate::allows`.

---

## ✅ 15. Mejores prácticas del repo

- Evitar lógica de negocio excesiva en controllers: delega a `Models`, `Observers`, `Jobs` o `Services` si escala.
- Usar `FormRequest` para validar y sanear request data.
- Usar `Resources` para normalizar json output.
- Escribir `Policy` por cada `Model` para separar autorización.


## 🚀 16. Preparar para Producción

### 📦 Instalar dependencias sin dev
```powershell
composer install --no-dev --optimize-autoloader
```

### ⚙️ Optimizar proyecto
```powershell
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 🧱 Compilar assets para producción
```powershell
npm run build
```

### 📡 Servidor final

Puedes usar:
- Apache / Nginx
- Docker (imagen Laravel oficial)
- Forge / Vapor (si usas servicios de Laravel)

## 💾 17. Uso con Docker (opcional)

### 🐳 Iniciar contenedores
```powershell
docker compose up -d
```

### 🔍 Ver logs
```powershell
docker compose logs -f
```

### 🧹 Detener contenedores
```powershell
docker compose down
```

💡 Tip: Puedes usar tu propio stack como ols-docker-9paul2 para un entorno completo de PHP, DB y herramientas.

---

## 📚 18. Recursos y lectura adicional

- Documentación JWT: https://jwt-auth.readthedocs.io/
- Spatie Permissions: https://spatie.be/docs/laravel-permission
- Laravel API recommendations: https://laravel.com/docs/api-resources
- Pest: https://pestphp.com/

---

Si quieres, ahora avanzo con el segundo README (herramientas y clases) o con mejoras/ejemplos en este mismo archivo (ej. `AuthController` completo con control de roles/permissions).

---

## 🧠 19. Recursos Recomendados

- 📘  **[Documentación oficial de Laravel](https://laravel.com/docs)**
- 🧩  **[Composer Packages](https://packagist.org/)**
- 🐳  **[Docker Hub Laravel](https://hub.docker.com/r/bitnami/laravel)**
- 🧰  **[NVM Windows Docs](https://github.com/coreybutler/nvm-windows)**
- 🧅  **[Bun.sh Docs](https://bun.sh/docs)**
- 🧑‍💻  **[Xdebug Setup](https://xdebug.org/docs/install)**
