# 🗺️ Mapa Mental: Flujo de Implementación y Herramientas (Laravel 12)

Este documento es una representación visual y práctica del orden en el que se suele implementar y llamar las clases en un módulo CRUD (ej. Statuses, Roles, Permissions, Users) dentro de este repo. Está pensado como checklist para desarrolladores que implementan o refactorizan un módulo.

---

````md
┌─────────────────────────────────────────────────────────────┐
│                    CONFIGURACIÓN                            │
│  1. app.php (AppServiceProvider, AuthServiceProvider)      │
│     └─ Registro de Policies, Gates, Providers              │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    BASE DE DATOS                            │
│  2. Migraciones (create_statuses_table.php)                │
│  3. Seeders (StatusSeeder.php, RolePermissionSeeder.php)   │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    MODELO                                   │
│  4. Status.php (Eloquent Model)                             │
│     └─ Relaciones, Scopes, Accessors/Mutators              │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    LÓGICA DE NEGOCIO                        │
│  5. StatusService.php                                       │
│     └─ Métodos de negocio, validaciones complejas          │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    AUTORIZACIÓN                             │
│  6. StatusPolicy.php                                        │
│     └─ Reglas de acceso (view, create, update, delete)     │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    VALIDACIÓN                               │
│  7. Form Requests (StoreStatusRequest, UpdateStatusRequest)│
│     └─ Validación de entrada, sanitización                  │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    TRANSFORMACIÓN                           │
│  8. StatusResource.php                                      │
│     └─ Formato de respuesta JSON                            │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    CONTROLADOR                              │
│  9. StatusController.php                                    │
│     └─ Manejo de requests/responses, coordinación          │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    RUTAS                                    │
│ 10. statuses.php (Route definitions)                        │
│     └─ Definición de endpoints API                          │
└─────────────────────────────────────────────────────────────┘
````

---

## 🧩 Notas rápidas
- Sigue este orden cuando creas/entregas un nuevo módulo CRUD (o refactor): Configuración → DB → Model → Business → Auth → Validation → Resource → Controller → Routes.
- Usa `FormRequest` para validación, `Policy` para autorización y `Resource` para la salida JSON.
- Registra Policies en `AuthServiceProvider` y Middlewares en `Kernel.php`.

---

## 🔧 Herramientas y archivos que puedes usar en cada paso (ejemplos y comandos)

1) CONFIGURACIÓN
- Archivos relevantes: `app/Providers/AppServiceProvider.php`, `app/Providers/AuthServiceProvider.php`.
- Registro de `Policies`, `Gates` y `Providers`.
- Comando útil: `php artisan vendor:publish` para paquetes; `php artisan config:cache` para producción.

2) BASE DE DATOS
- Migraciones: `database/migrations/*` (e.g., `2025_10_14_043228_create_statuses_table.php`).
- Seeders / Factories: `database/seeders/`, `database/factories/`.
- Comandos útiles:
```bash
php artisan make:migration create_statuses_table
php artisan migrate
php artisan make:seeder StatusSeeder
php artisan db:seed --class=StatusSeeder
```

3) MODELO (Eloquent)
- Archivos: `app/Models/Status.php`.
- Incluir relaciones, casts, scopes y mutators/accessors.
- Comandos:
```bash
php artisan make:model Status -m
```

4) LÓGICA DE NEGOCIO
- Donde colocar: `app/Services/StatusService.php` o `app/Actions/` para operaciones pequeñas.
- Ejemplo: sincronización de permisos, cálculos, transacciones DB.
- Comando:
```bash
php artisan make:class Services/StatusService
```

5) AUTORIZACIÓN
- Archivo: `app/Policies/StatusPolicy.php`.
- Registrar en `AuthServiceProvider`.
- Integración: `CheckPermission` middleware y `CheckRole` cuando aplique.
- Comando:
```bash
php artisan make:policy StatusPolicy --model=Status
```

6) VALIDACIÓN
- Form Requests: `app/Http/Requests/StoreStatusRequest.php`, `UpdateStatusRequest.php`.
- Usar reglas `exists`, `unique:table,column,{ignoreId}` y `permissions.*` para arrays.
- Comando:
```bash
php artisan make:request StoreStatusRequest
```

7) TRANSFORMACIÓN (Resources)
- Archivo: `app/Http/Resources/StatusResource.php`.
- Usar `whenLoaded` o `relationLoaded` para Relaciones y soportar paginación.
- Comando:
```bash
php artisan make:resource StatusResource
```

8) CONTROLADOR
- Archivo: `app/Http/Controllers/StatusController.php`.
- Lógica: recibir `FormRequest`, autorizar (`$this->authorize()`), usar `StatusService`, `StatusResource` para respuestas.
- Comando:
```bash
php artisan make:controller StatusController --api
```

9) RUTAS
- Archivo: `routes/statuses.php` o `routes/api.php`.
- Definir rutas CRUD con `{id}` y aplicar middlewares `jwt.auth`, `permission`, `role`, etc.

---

## 🌐 Mapa Mental Expandido — Flujo y Patrones (detallado)
El objetivo aquí es listar tareas concretas y patrones de diseño que guíen la implementación de un módulo desde el diseño hasta la producción.

````md
┌────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    DISEÑO DE API / CONTRATO                                      │
│  A. Versión: /api/v1/…                                                                           │
│  B. Contratos de respuesta: {status, data, error, pagination}                                   │
│  C. JSON Error structure: { severity, summary, detail, code }                                    │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                   CONFIGURACIÓN & POLÍTICAS                                     │
│  1. app.php (AppServiceProvider, AuthServiceProvider)                                          │
│  1.1 Registering policies, gates, service providers, singletons                                  │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      BASE DE DATOS                                               │
│  2. Migrations (columns, nullable, index, foreign keys)                                         │
│  3. Seeders & Factories                                                                          │
│  4. Indexes & Constraints (FKs, unique constraints, composite indexes)                          │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                           MODELO                                                  │
│  5. Status Model                                                                                 │
│  5.1 Scopes, Relations, Casts, Accessors/Mutators, SoftDeletes, Global Scopes                    │
│  5.2 Factory for tests, default attributes                                                        │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      PATTERN: SERVICES & REPOS                                     │
│  6. Service (StatusService) — Business rules; move complex calculations/transactions here         │
│  7. Repository (optional) — Abstract DB access, DI via service container                         │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                   AUTORIZACIÓN & POLÍTICAS                                        │
│  8. Policies (StatusPolicy) — view, create, update, delete; admin bypass                          │
│  9. Middleware: jwt.auth, role, permission, throttle                                              │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      VALIDACIÓN & DTOs                                             │
│ 10. FormRequests (sanitize, prepareForValidation)                                                │
│ 11. DTOs (optional) for complex inputs — map to Service                                          │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      CONTROLLER & EXCEPCIONES                                     │
│ 12. Controller receives FormRequest, Service usage, Resource output                              │
│ 13. Exception handling: ModelNotFound, ValidationException mapped to JSON Error structure         │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      TRANSFORMACIÓN & RESOURCES                                   │
│ 14. StatusResource (item) and Resource Collections for paginated results                         │
│ 15. Response contract: {status, role|permission, message, data}                                   │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                                      BACKGROUND & EVENTS                                           │
│ 16. Jobs for async tasks; queues and workers                                                      │
│ 17. Events for domain notifications; listeners to trigger jobs or logs                            │
└─────────────────────────┬──────────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼──────────────────────────────────────────────────────────────────────┐
│                               TESTING, CI / CD, MONITORING & DEPLOYMENT                            │
│ 18. Tests: Unit, Feature, API contract, E2E                                                         │
│ 19. CI: static analysis (phpstan), lint, tests, code coverage                                      │
│ 20. Observability: Logging (channel), Monitoring (Prometheus), Errors (Sentry)                      │
│ 21. Deployment: docker, config:cache, optimize                                                     │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
````

---

## 🔎 Detalles por etapa (tareas concretas y patrones)
Voy a detallar la lista de pasos y acciones por cada etapa del mapa mental para que sea más útil como checklist.

### 1. API DESIGN & CONTRACT

- Diseñar rutas y version: `/api/v1/statuses`.
- Establecer formato de respuesta y error (ej: `severity, summary, detail, code`).
- Acordar headers CORS, ETag, y caches.

### 2. CONFIGURACIÓN

- Registrar `Policies` y `Providers` en `AuthServiceProvider` y `AppServiceProvider`.
- Cargar variables en `.env`, crear `config/roles.php` para centralizar roles.

### 3. MIGRACIONES & DB

- Añadir índices (index, unique), FK constraints y ON DELETE behaviors.
- Plan de migración segura: `php artisan migrate --path=...` y `migrate:rollback` para revertir.

### 4. MODELOS

- Evitar lógica de negocio pesada: solo relaciones, scopes, casts.
- Implementar `toArray` safe, hidden, appends, `HasFactory`.

### 5. SERVICIOS Y REPOS

- Crear Services que usan Repos (si los usas). Registrar bindings en `AppServiceProvider`.
- Agregar tests unitarios para Services y Repos.

### 6. POLICIES & MIDDLEWARE

- Policy con `before` que permite bypass admin/super-admin.
- Middlewares: `jwt.auth` -> `CheckRole` -> `CheckPermission`.

### 7. VALIDACIÓN

- Reglas semánticas: use `Rule::unique()` y `exists:table,id`.
- Sanitizar `prepareForValidation()` y `passedValidation()` para normalizar datos.

### 8. EXCEPTION HANDLING

- Mapear `ModelNotFoundException` y `NotFoundHttpException` a respuestas JSON con 404.
- `ValidationException` a 422 con `errors` y `message`.

### 9. RESOURCES & RESPONSES

- `StatusResource` con top-level `status` and `message`.
- Para colecciones, usa `StatusResource::collection($paginator)` e incluye `meta` y `links`.

### 10. JOBS, EVENTS, OBSERVERS

- Jobs: background tasks; set `queue` name and `tries`/`backoff`.
- Events: `StatusCreated` (listener `NotifyAdmin`), Observers for model lifecycle.

### 11. TESTING & QA

- Unit tests para Models/Services.
- Feature tests para Controllers/Endpoints.
- Contract tests para Estructura de respuestas JSON (keys y schema).
- CI runs: phpstan, composer test, pest.

### 12. CI / CD & DEPLOY

- GitHub Actions / Pipelines: run linter, phpstan, tests, push to production.
- Use `composer install --no-dev` y `php artisan optimize`.

### 13. MONITORING & LOGGING

- Integrar Sentry / Bugsnag para errores.
- Exponer health checks y métricas para Prometheus o similar.

### 14. SECURITY & BEST PRACTICES

- Mantener secretos en `.env`.
- CORS & CSP configurado correctamente para la API.
- Rate limiting (throttle) por endpoint.

---

## 🔁 Ejemplo de flujo (Request → Response)
````md
Client -> [HTTP Request] -> Nginx -> App (php-fpm) -> Middleware: jwt.auth -> CheckRole -> CheckPermission -> Controller -> (FormRequest validation) -> Service/Action -> Repository -> Model -> DB
                                                                                                        ↑
                                                                                                        |
                                                                                                  Events/Jobs (async)
                                                                     
Response: Object -> Resource -> JSON -> Client
````

---

## 💡 Ejemplos de patrones y snippets (rápidos)

### FormRequest (StoreStatusRequest)
```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255','unique:statuses,name'],
            'color' => ['nullable','string'],
        ];
    }
}
```

### Policy (StatusPolicy) — ejemplo `before` para bypass admin
```php
public function before($user, $ability)
{
    if ($user->hasRole('super admin') || $user->hasRole('admin')) {
        return true; // bypass full
    }
}
```

### Service (StatusService) — patrón sencillo
```php
<?php
namespace App\Services;

use App\Models\Status;

class StatusService
{
    public function create(array $data): Status
    {
        return Status::create($data);
    }
}
```

### Resource (StatusResource) — top-level key
```php
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'created_at' => $this->created_at->toDateTimeString(),
    ];
}
```

### Job (SendStatusNotification)
```php
class SendStatusNotification implements ShouldQueue
{
    public function __construct(public Status $status) {}
    public function handle() { /* send notification */ }
}
```

### Feature Test (create status)
```php
it('creates a status', function() {
    actingAsAdmin();
    $payload = ['name' => 'Active'];
    postJson(route('statuses.store'), $payload)->assertCreated();
});
```

---

## 🧭 Patrones de diseño recomendados (resumen rápido)
- Service: encapsula reglas de negocio, transacciones y orquestación.
- Repository (opcional): abstrae queries complejos; útil si se cambia la fuente de datos.
- Action: operación única y atómica, útil en lugares con responsibilities limitadas.
- Events/Jobs: desacoplan acciones de alta latencia o notificaciones.
- DTOs: cuando un input es complejo y necesita validación y mapping antes de llamar a Service.

---


---

## ✅ Checklist final por módulo (ej: `Statuses`)
- [ ] Definir API contract y rutas (versioned)
- [ ] Crear migración y modelo con índices
- [ ] Crear factories y seeders
- [ ] Implementar FormRequests con reglas y mensajes
- [ ] Implementar Policy y registrar en `AuthServiceProvider`
- [ ] Implementar Service/Repository si aplica
- [ ] Implementar Controller con authorizar y refresh/reload
- [ ] Implementar Resources y colección JSON
- [ ] Crear Jobs / Events / Observers si aplica
- [ ] Añadir tests unitarios & feature
- [ ] Añadir monitoreo básico y logging
- [ ] Agregar a CI y documentar endpoint en README

---

Si quieres, convierto este checklist en Issues/Plantillas de Issue para que cada module tenga automáticamente los pasos listos en GitHub. ¿Deseas que lo haga ahora?  


---

## 🧰 Otras herramientas y utilidades (referencias en `docs/Guia-Refactorizacion-Laravel12.md`)
- `Jobs / Queues`: `app/Jobs/*` para tareas asíncronas.
- `Events + Listeners`: desacoplar reacciones a eventos de dominio.
- `Observers`: para lógica del modelo (created, updated).
- `Traits`: funcionalidad reutilizable (`app/Traits`).
- `Scopes`: modelos con funciones de consulta reutilizadas.
- `Custom Casts`: `app/Casts` para transformar atributos automáticamente.
- `Macros`: extender clases (Collections u otras) desde `AppServiceProvider@boot()`.

Comandos rápidos (resumen):
```bash
php artisan make:job NombreDelJob
php artisan make:event NombreDelEvent
php artisan make:listener NombreDelListener --event=NombreDelEvent
php artisan make:observer NombreDelObserver --model=Status
php artisan make:trait LogsActivity
php artisan make:cast Encrypted
```

---

## ✅ Buenas prácticas y checklist de entrega
- Usar `FormRequest` y `Resource` por defecto.
- `Controller` delgado; toda lógica compleja en `Services/Actions`.
- `Policy` y `Middleware` claros (prueba con admin/super admin bypass).
- `Resource` en respuestas: incluir `permissions` cuando corresponda y usar `->relationLoaded()` en paginación.
- Añadir Tests: `tests/Feature` y `tests/Unit` con Pest (o PHPUnit). Ejecutar `php artisan test`.
- Documentar la dependencia del endpoint en `README.md` y `docs/*`.

---

## 📚 Referencias
- `docs/Guia-Refactorizacion-Laravel12.md` — Herramientas y patrones.
- `docs/Guia-Completa-para-Entorno-Laravel-12-en-Windows.md` — Configuración y entorno.
- `docs/herramientas-clases.md` — Atajos y ejemplos simples.

---

Si quieres, puedo generar un checklist editable compatible con `issues` o un archivo TODO con tareas específicas para cada módulo (Statuses, Roles, Permissions) basado en este mapa mental.
