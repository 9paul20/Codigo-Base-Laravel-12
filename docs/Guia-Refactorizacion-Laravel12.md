
# 🧱 Guía completa de herramientas para refactorizar en Laravel 12

---
## Índice

1. 🧱 [Modelos (Eloquent)](#modelos-eloquent)
2. 🧭 [Controladores](#controladores)
3. 🧩 [Middleware](#middleware)
4. 📋 [Form Request](#form-request)
5. 🛡️ [Policy](#policy)
6. 🧠 [Service](#service)
7. 🗂️ [Repository](#repository)
8. ⚡ [Action](#action)
9. 🧾 [Job / Queue](#job-queue)
10. 🔔 [Event + Listener](#event-listener)
11. 👁️ [Observer](#observer)
12. 🧬 [Trait](#trait)
13. 🔍 [Scopes (Local / Global)](#scopes-local-global)
14. 📦 [Resource / Resource Collections](#resource-resource-collections)
15. ⚗️ [Custom Casts / Value Objects / DTOs](#custom-casts-value-objects-dtos)
16. 🧩 [Macro](#macro)
17. 🧭 [Enum Route Bindings / Typed Routes](#enum-route-bindings-typed-routes)
18. 💤 [Lazy Service Providers / Deferred Providers](#lazy-service-providers-deferred-providers)
19. 🌱 [Seeders y Factories](#seeders-y-factories)
20. ✅ [Tests](#tests)
21. 📝 [Tips y convenciones](#tips-y-convenciones)

---
## 1. 🧱 Modelos (Eloquent)
**Propósito**: Representar la capa de datos y definiciones de relaciones entre tablas.  
**Ideal para**: Definir relaciones (hasMany, belongsTo, belongsToMany), scopes, casts y mutators.  
**Notas extras**: Ubícalos en **app/Models**. Evita lógica de negocio muy pesada; usa Observers o Services cuando sea necesario.  

### Ejemplo (Modelos)
```php
class User extends Authenticatable
{
    public function status() { return $this->belongsTo(Status::class); }
    // relaciones con Spatie
    public function roles() { return $this->belongsToMany(Role::class); }
}
```

---
## 2. 🧭 Controladores
**Propósito**: Orquestar peticiones, usar FormRequests para validar y Policies para autorizar.  
**Ideal para**: Delegar la lógica de negocio a Services o Actions y mantener controladores delgados.  
**Notas extras**: Ubícalos en **app/Http/Controllers**. Para APIs, retornar `JsonResource`.  

### Ejemplo (Controladores)
```php
class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::paginate(15);
        return RoleResource::collection($roles);
    }
}
```

---
### 3. 🧩 Middleware
**Propósito**: Ejecutar lógica transversal en el flujo HTTP (antes o después del controlador) como autenticación, roles, logging.  
**Ideal para**: Verificación global o específica de rutas, filtros que aplican en muchas rutas o grupos de rutas.  
**Notas extras**: Se ubica en **app/Http/Middleware**. Regístralo en **Kernel.php** como alias.  
```bash
php artisan make:middleware NombreDelMiddleware
```

### Ejemplo (Middleware)
```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->is_active) {
            abort(403, "Usuario no activo");
        }
        return $next($request);
    }
}
```

---
## 4. 📋 Form Request
**Propósito**: Validar y autorizar una petición HTTP antes de que llegue al controlador.  
**Ideal para**: Cuando tus controladores reciben datos del cliente con reglas claras de validación y autorización, evitar mezclar validación en el controlador.  
**Notas extras**: Ubícalo en **app/Http/Requests**. Define los métodos **authorize()** y **rules()**.  

### Comando (Form Request)
```bash
php artisan make:request NombreDelRequest
```

### Ejemplo (Form Request)
```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount'     => 'required|numeric|min:1',
            'product_id' => 'required|exists:products,id',
        ];
    }
}
```

---
## 5. 🛡️ Policy
**Propósito**: Centralizar reglas de autorización para modelos o acciones específicas.  
**Ideal para**: Cuando necesitas decidir si un usuario puede “ver”, “editar”, “eliminar” un modelo determinado, según roles/permisos u otras condiciones de negocio.  
**Notas extras**: Regístrala en **AuthServiceProvider** en la propiedad **\$policies**. Uso en controlador con **\$this->authorize('action', $model)**. También puedes usar **Gates** para reglas de autorización puntuales y sencillas.  

### Comando (Policy)
```bash
php artisan make:policy NombreDelPolicy --model=Modelo
```

### Ejemplo (Policy)
```php
<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->status === 'pending';
    }
}
```

---
## 6. 🧠 Service
**Propósito**: Extraer la lógica de negocio fuera del controlador.  
**Ideal para**: Procesos complejos o reutilizables (ej. cálculos, integraciones).  
**Notas extras**: Colócalo en **app/Services**. Inyecta el servicio en el controlador. Mejora la testabilidad.  

### Comando (Service)
```bash
php artisan make:class Services/NombreDelService
```

### Ejemplo (Service)
```php
<?php
namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createForUser(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $order = new Order();
            $order->user_id = $user->id;
            $order->amount = $data['amount'];
            $order->status = 'pending';
            $order->save();

            // otras operaciones…

            return $order;
        });
    }
}
```

---
## 7. 🗂️ Repository
**Propósito**: Abstraer el acceso a datos, separar lógica de persistencia de la lógica de negocio.  
**Ideal para**: Cuando tienes muchas consultas específicas, múltiples métodos de consulta, quieres facilitar pruebas o cambiar la fuente de datos (por ejemplo usar otro ORM).  
**Notas extras**: Ubícalo en **app/Repositories**. El servicio o controlador lo utiliza para obtener datos, en lugar de llamar directamente al modelo.  

### Comando (Repository)
```bash
php artisan make:class Repositories/NombreDelRepository
```

### Ejemplo (Repository)
```php
<?php
namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\Paginator;

class OrderRepository
{
    public function findByUser(int $userId, int $perPage = 15): Paginator
    {
        return Order::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    public function findPending(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('status', 'pending')->get();
    }
}
```

---
## 8. ⚡ Action
**Propósito**: Representar una operación de negocio única, atómica, reutilizable.  
**Ideal para**: Cuando tienes tareas “crear usuario”, “suspender cuenta”, “generar reporte” etc., que pueden encapsularse en una clase y reutilizarse desde distintos lugares.  
**Notas extras**: Ubicación **app/Actions**. Puedes inyectarla en controladores o servicios.  

### Comando (Action)
```bash
php artisan make:class Actions/NombreDelAction
```

### Ejemplo (Action)
```php
<?php
namespace App\Actions;

use App\Models\User;

class SuspendUserAction
{
    public function execute(User $user): User
    {
        $user->status = 'suspended';
        $user->save();
        return $user;
    }
}
```

---
## 9. 🧾 Job / Queue
**Propósito**: Ejecutar tareas en segundo plano, desacoplar lógica de larga ejecución de la petición HTTP principal.  
**Ideal para**: Envío de correos, procesamiento de archivos grandes, integraciones con APIs externas que tardan, tareas programadas.  
**Notas extras**: Ubicación **app/Jobs**. Implementa **ShouldQueue** si quieres que se encole. Despacha con **dispatch()**.  

### Comando (Job / Queue)
```bash
php artisan make:job NombreDelJob
```

### Ejemplo (Job / Queue)
```php
<?php
namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmail implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        Mail::to($this->order->user->email)
            ->send(new OrderCreatedMail($this->order));
    }
}
```

---
## 10. 🔔 Event + Listener
**Propósito**: Desacoplar reacciones a eventos de dominio. Cuando ocurre algo (“orden colocada”), múltiples acciones pueden responder sin acoplarse al código principal.  
**Ideal para**: Notificaciones, logs, auditoría, disparar distintos procesos en respuesta a un evento de negocio.  
**Notas extras**: Registra en **EventServiceProvider** la relación evento → listener. Dispara con **event(new NombreDelEvent(\$data))**.  

### Comando (Event + Listener)
```bash
php artisan make:event NombreDelEvent
php artisan make:listener NombreDelListener --event=NombreDelEvent
```

### Ejemplo (Event + Listener)

#### Evento
```php
<?php
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
```

#### Listener
```php
<?php
namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfNewOrder implements ShouldQueue
{
    public function handle(OrderPlaced $event)
    {
        Log::info("Nueva orden creada: " . $event->order->id);
    }
}
```

---
## 11. 👁️ Observer
**Propósito**: Escuchar eventos del ciclo de vida de un modelo (creado, actualizado, eliminado) y ejecutar lógica relacionada.  
**Ideal para**: Auditoría, sincronización, lógica tras guardar/eliminar, sin mezclar en el modelo directamente.  
**Notas extras**: Ubícalo en **app/Observers**. Regístralo en **boot()** (por ejemplo en **AppServiceProvider**) mediante **Modelo::observe(Observer::class)**.  

### Comando (Observer)
```bash
php artisan make:observer NombreDelObserver --model=Modelo
```

### Ejemplo (Observer)
```php
<?php
namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order)
    {
        Log::info("Orden creada con ID: {$order->id}");
    }
}
```

---
## 12. 🧬 Trait
**Propósito**: Compartir métodos/procedimientos entre clases (modelos, servicios, controladores) sin herencia múltiple.  
**Ideal para**: Funcionalidades reutilizables (logging, helpers, métodos comunes) que no corresponden a una clase concreta del dominio.  
**Notas extras**: Ubícalo en **app/Traits**. Luego usa **use NombreTrait** en la clase que lo requiera.  

### Comando (Trait)
```bash
php artisan make:trait NombreDelTrait
```

### Ejemplo (Trait)
```php
<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    public function log(string $message): void
    {
        Log::info("[" . static::class . "] " . $message);
    }
}
```

---
## 13. 🔍 Scopes (Local / Global)
**Propósito**: Reutilizar condiciones de consulta en los modelos, ya sea local (método) o global (aplicada a todas las consultas).  
**Ideal para**: Evitar repetir **->where('status','active')** muchas veces, o aplicar filtros permanentes.  
**Notas extras**: Local scopes con **scopeNombre()**, global scopes con **addGlobalScope()**.  

### Comando (Scopes)
```bash
php artisan make:class Models/Scopes/NombreScope
```

### Ejemplo (Scopes)
```php
// En el modelo User.php
public function scopePending($query)
{
    return $query->where('status', 'pending');
}
```

### Uso
```php
$pendingOrders = Order::pending()->get();
```

---
## 14. 📦 Resource / Resource Collections
**Propósito**: Transformar modelos o colecciones de modelos en estructuras JSON adecuadas para APIs.  
**Ideal para**: Cuando estás construyendo APIs (por ejemplo con Laravel + Vue) y quieres separar la transformación de datos de la lógica de negocio/controlador.  
**Notas extras**: Ubícalo en **app/Http/Resources**. Si necesitas colección: **NombreDelResource::collection(...)**.  

### Comando (Resource)
```bash
php artisan make:resource NombreDelResource
```

### Ejemplo (Resource)
```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'user'       => $this->user->only('id','name'),
            'amount'     => $this->amount,
            'status'     => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
```

---
## 15. ⚗️ Custom Casts / Value Objects / DTOs
**Propósito**: Transformar atributos de modelos automáticamente, usar objetos valor o DTOs para atributos complejos.  
**Ideal para**: Cuando un atributo requiere transformación, cifrado, conversión a objeto, etc. Mejora la claridad del dominio.  
**Notas extras**: Ubícalo en **app/Casts**. Usa la interfaz **CastsAttributes** (o **CastsInboundAttributes**). En Laravel 12 ya está documentado.  

### Comando (Custom Casts)
```bash
php artisan make:cast NombreDelCast
```

### Ejemplo (Custom Casts)
```php
<?php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Encrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return Crypt::decryptString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return Crypt::encryptString($value);
    }
}
```

---
## 16. 🧩 Macro
**Propósito**: Extender clases (colecciones, respuestas, query builder, etc.) con métodos personalizados reutilizables.  
**Ideal para**: Cuando repites una operación en muchos lugares y quieres un método “nuevo” para ello.  
**Notas extras**: Ubica la lógica en **AppServiceProvider@boot()** o en un proveedor dedicado. Usa **::macro()**.  

### Ejemplo (Macro)
```php
use Illuminate\Support\Collection;

public function boot()
{
    Collection::macro('toUpper', function () {
        return $this->map(fn($item) => strtoupper($item));
    });
}
```

### Uso
```php
collect(['hola','mundo'])->toUpper(); // ['HOLA','MUNDO']
```

---
## 17. 🧭 Enum Route Bindings / Typed Routes
**Propósito**: Usar enums o tipado fuerte en rutas, binding automático de parámetros, mejorar seguridad/claridad.  
**Ideal para**: Cuando tus rutas reciben parámetros definidos como enums, o quieres usar tipado más fuerte para rutas y controladores.  
**Notas extras**: Laravel 12 promueve rutas tipadas como parte de mejoras de PHP 8+. Facilita la limpieza del código en rutas y controladores.  

### Ejemplo (Enum Route Bindings)
```php
// Supón que tienes un enum:
enum OrderStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}

// Ruta en web.php:
Route::get('/orders/{status}', [OrderController::class, 'index'])
     ->whereEnum('status', OrderStatus::class);

// En controlador:
public function index(OrderStatus $status)
{
    $orders = Order::where('status', $status->value)->get();
    return OrderResource::collection($orders);
}
```

---
## 18. 💤 Lazy Service Providers / Deferred Providers
**Propósito**: Optimizar el arranque de la aplicación cargando proveedores de servicios solo cuando se necesitan; mejorar el rendimiento.  
**Ideal para**: Aplicaciones grandes o con muchos proveedores que no siempre se usan en cada petición.  
**Notas extras**: En Laravel 12 se menciona como mejora de performance. Considera revisar la configuración **defer** o registrar servicios como **when()** en **App\Providers**.  

### Comando (Lazy Service Providers)
No tiene comando “make:provider-lazy” específico, puedes usar
```bash
php artisan make:provider NombreDelProvider
```
y configurar como diferido (deferred) en **provides()** y **defer = true**.

### Ejemplo (Lazy Service Providers)
```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HeavyServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function provides()
    {
        return [\App\Services\HeavyService::class];
    }

    public function register()
    {
        $this->app->singleton(\App\Services\HeavyService::class, function ($app) {
            return new \App\Services\HeavyService();
        });
    }
}
```

---
## 19. 🌱 Seeders y Factories
**Propósito**: Poblar la base de datos con datos iniciales y crear fábricas para pruebas.  
**Ideal para**: Población de datos inicial (roles, permisos, estados) y pruebas/unitarias con `factories`.  
**Notas extras**: Ubícalos en **database/seeders** y **database/factories**. Despacha con `php artisan db:seed` o `--class=NombreSeeder`.  

### Comando (Seeders y Factories)
```bash
php artisan make:seeder NombreDelSeeder
php artisan make:factory NombreFactory
```

### Ejemplo (Seeders y Factories)
```php
// Seeder
public function run()
{
    Role::create(['name' => 'admin']);
}

// Factory
public function definition()
{
    return [
        'name' => fake()->name(),
        'email' => fake()->email(),
    ];
}
```

---
## 20. ✅ Tests
**Propósito**: Verificar la integridad de la aplicación con pruebas unitarias e integradas.  
**Ideal para**: Cubrir Policies, Requests, Services y API endpoints.  
**Notas extras**: Ubícalos en **tests/Feature** y **tests/Unit**. Usa Pest o PHPUnit; ejecutar con `vendor/bin/pest` o `php artisan test`.  

### Ejemplo (Tests)
```php
public function test_user_can_create_role()
{
    $this->actingAs($admin)
         ->postJson(route('roles.store'), $payload)
         ->assertStatus(201)
         ->assertJsonStructure(['role' => ['id','name','permissions']]);
}
```

---
## 21. 📝 Tips y convenciones
**Propósito**: Resumir convenciones de este repo para mantener coherencia.  
**Recomendaciones**:
- `role`: autorización de alto nivel; `permission`: control granular.
- `JsonResource`: usar en todas las respuestas de API.
- `FormRequest`: validar y autorizar entrada.
- `Policy`: políticas registradas en `AuthServiceProvider`.
- Centraliza strings de rol/permiso en `config/roles.php` para evitar hardcoding.
  
**Notas extras**: Mantén controladores delgados y extrae lógica compleja a Services/Actions para testabilidad.
