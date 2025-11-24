# Guía de Autorización - Middlewares en Laravel

## Resumen Ejecutivo

Este proyecto implementa un sistema de autorización robusto usando **Spatie Laravel Permission** con dos tipos de middlewares:

- **`CheckPermission`**: Para permisos específicos y granulares
- **`CheckRole`**: Para validación de roles completos

**Nota Importante:** Los IDs de usuario utilizan **ULID** (Universally Unique Lexicographically Sortable Identifier) en lugar de IDs secuenciales para mayor seguridad y escalabilidad.

## Arquitectura de Autorización

### 1. Componentes Principales

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Usuario       │────│     Roles       │────│   Permisos      │
│                 │    │                 │    │                 │
│ • ulid (id)     │    │ • id            │    │ • id            │
│ • name          │    │ • name          │    │ • name          │
│ • email         │    │ • guard_name    │    │ • guard_name    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 2. Relaciones

- **Usuario ↔ Roles**: Un usuario puede tener múltiples roles
- **Roles ↔ Permisos**: Un rol puede tener múltiples permisos
- **Usuario → Permisos**: Indirectamente a través de roles

### 3. IDs ULID

Los usuarios utilizan ULID como clave primaria:
- **Formato**: `01KASSGB071MCJW X0CADW8ZCBK` (26 caracteres alfanuméricos)
- **Ventajas**: Únicos globalmente, ordenables lexicográficamente, URL-safe
- **Uso en rutas**: `/users/{user}` donde `{user}` es el ULID

## Middlewares Disponibles

### CheckPermission

**Sintaxis:** `permission:name` o `permission:name1|name2`

**Uso:** Para acceso granular basado en permisos específicos.

```php
// Un permiso
Route::middleware('permission:view users')->get('/users', ...);

// Múltiples permisos (OR)
Route::middleware('permission:view users|create users')->get('/users', ...);
```

### CheckRole

**Sintaxis:** `role:name` o `role:name1|name2`

**Uso:** Para acceso completo basado en roles.

```php
// Un rol
Route::middleware('role:admin')->get('/admin', ...);

// Múltiples roles (OR)
Route::middleware('role:admin|moderator')->get('/manage', ...);
```

## Patrones de Autorización Recomendados

### Patrón 1: Solo Permisos (Granular)

**Cuándo usar:** Acceso específico y detallado.

```php
Route::middleware(['jwt.auth', 'permission:view roles'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
});
```

**Ventajas:**
- Control preciso
- Flexibilidad máxima
- Fácil de auditar

### Patrón 2: Solo Roles (Simple)

**Cuándo usar:** El rol determina completamente el acceso.

```php
Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/reports', [AdminController::class, 'reports']);
});
```

**Ventajas:**
- Simple de implementar
- Fácil de entender
- Menos middlewares por ruta

### Patrón 3: Roles + Permisos (Híbrido)

**Cuándo usar:** Máxima seguridad con verificación dual.

```php
Route::middleware(['jwt.auth', 'role:admin', 'permission:edit users'])->group(function () {
    Route::put('/users/{id}', [UserController::class, 'update']);
});
```

**Ventajas:**
- Doble validación
- Máxima seguridad
- Flexibilidad

### Patrón 4: Múltiples Condiciones

**Cuándo usar:** Acceso para diferentes perfiles de usuario.

```php
// Múltiples roles + múltiples permisos
Route::middleware([
    'jwt.auth',
    'role:admin|manager',
    'permission:edit users|delete users'
])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

## Casos de Uso Prácticos

### Sistema de Usuarios

```php
// Lectura - cualquier usuario con permiso
Route::middleware(['jwt.auth', 'permission:view users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});

// Creación - roles específicos
Route::middleware(['jwt.auth', 'permission:create users'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

// Edición - permiso específico
Route::middleware(['jwt.auth', 'permission:edit users'])->group(function () {
    Route::put('/users/{user}', [UserController::class, 'update']);
});

// Eliminación - permiso específico
Route::middleware(['jwt.auth', 'permission:delete users'])->group(function () {
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
```

### Sistema de Contenido

```php
// Ver contenido - público o con permiso básico
Route::middleware(['jwt.auth', 'permission:view content'])->group(function () {
    Route::get('/content', [ContentController::class, 'index']);
});

// Gestionar contenido - moderadores o admins
Route::middleware(['jwt.auth', 'role:admin|moderator'])->group(function () {
    Route::post('/content', [ContentController::class, 'store']);
    Route::put('/content/{id}', [ContentController::class, 'update']);
});

// Eliminar contenido - solo admins con permiso específico
Route::middleware(['jwt.auth', 'role:admin', 'permission:delete content'])->group(function () {
    Route::delete('/content/{id}', [ContentController::class, 'destroy']);
});
```

## Mejores Prácticas

### 1. Orden de Middlewares

```php
// ✅ Correcto: Autenticación primero
['jwt.auth', 'role:admin', 'permission:edit users']

// ❌ Incorrecto: Autenticación al final
['role:admin', 'permission:edit users', 'jwt.auth']
```

### 2. Nombres Consistentes

```php
// ✅ Consistente: usar guiones para roles con espacios
 'super admin', 'content moderator'

// ❌ Inconsistente: mezclar espacios y guiones
'super admin', 'content_moderator'
```

### 3. Principio de Menor Privilegio

```php
// ✅ Específico: solo lo necesario
'permission:view reports'

// ❌ Excesivo: más de lo necesario
'role:admin'
```

### 4. Agrupación Lógica

```php
// ✅ Agrupar por funcionalidad
Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', ...);
    Route::get('/users', ...);
    Route::get('/reports', ...);
});
```

## Respuestas de Error

### Sin Autenticación (401)

```json
{
    "severity": "error",
    "summary": "Unauthorized",
    "detail": "Authentication required"
}
```

### Sin Permiso (403)

```json
{
    "severity": "error",
    "summary": "Forbidden",
    "detail": "You do not have permission to access this resource"
}
```

### Sin Rol (403)

```json
{
    "severity": "error",
    "summary": "Forbidden",
    "detail": "You do not have the required role to access this resource"
}
```

## Testing

### Verificar Middlewares

```bash
# Ejecutar pruebas
php artisan test

# Verificar middlewares específicos
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->hasRole('admin'); // true/false
>>> $user->hasPermissionTo('view users'); // true/false
```

## Rutas Implementadas

Este proyecto incluye archivos de rutas específicos para cada módulo con middlewares de autorización aplicados:

### Usuarios (`routes/users.php`)
```php
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create users');
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view users');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:view users');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete users');
});
```

### Estados (`routes/statuses.php`)
```php
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/statuses', [StatusController::class, 'store'])->middleware('permission:create permissions');
    Route::get('/statuses', [StatusController::class, 'index'])->middleware('permission:view permissions');
    Route::get('/statuses/{status}', [StatusController::class, 'show'])->middleware('permission:view permissions');
    Route::put('/statuses/{status}', [StatusController::class, 'update'])->middleware('permission:edit permissions');
    Route::delete('/statuses/{status}', [StatusController::class, 'destroy'])->middleware('permission:delete permissions');
});
```

### Roles (`routes/roles.php`)
Contiene ejemplos avanzados de diferentes patrones de autorización con middlewares.

### Permisos (`routes/permissions.php`)
```php
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:create permissions');
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:view permissions');
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->middleware('permission:view permissions');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:edit permissions');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:delete permissions');
});
```

## Conclusión

La combinación de `CheckPermission` y `CheckRole` proporciona un sistema de autorización flexible y seguro que puede adaptarse a diferentes necesidades:

- **Permisos** para control granular
- **Roles** para simplicidad
- **Combinaciones** para máxima seguridad

Elegir el patrón correcto depende de los requisitos específicos de cada endpoint y el nivel de seguridad necesario.

**Nota:** Los administradores (`admin` y `super admin`) tienen bypass automático en el middleware `CheckPermission` para acceso completo al sistema.
