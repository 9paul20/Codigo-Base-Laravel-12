# Guía de Consumo de API - Laravel 12

Esta guía proporciona instrucciones completas para consumir la API de Laravel 12 utilizando herramientas como Postman, ApiDog, Hoppscotch, Insomnia y otras plataformas de testing de APIs.

## 📁 Estructura de Archivos

Los archivos de configuración y documentación se encuentran en la carpeta `docs/api-testing/`:

```
docs/
├── api-testing/
│   ├── Laravel_API_Permisos_Roles.postman_collection.json
│   ├── Laravel_API_Environment.postman_environment.json
│   ├── Codigo_Base_Laravel_12.postman_collection.json
│   └── Codigo_Base_Laravel_12.openapi.json
└── API_Consumption_README.md (este archivo)
```

## 🚀 Configuración Inicial

### 1. Preparación del Entorno

Asegúrate de que tu servidor Laravel esté ejecutándose:

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api/v1`

### 2. Variables de Entorno

Las siguientes variables son utilizadas en las colecciones:

- `base_url`: `http://localhost:8000/api/v1`
- `token`: Token JWT válido para autenticación
- `user_email`: `admin@example.com`
- `user_password`: `password`

## 📋 Colecciones Disponibles

### 1. Colección Principal: `Laravel_API_Permisos_Roles.postman_collection.json`

Esta colección contiene todos los endpoints para gestión de permisos y roles.

**Endpoints incluidos:**
- **Usuarios**: CRUD completo con gestión de roles y permisos
- **Permisos**: Gestión de permisos del sistema
- **Roles**: Gestión de roles con asignación de permisos

### 2. Colección General: `Codigo_Base_Laravel_12.postman_collection.json`

Colección completa con todos los endpoints de la API, incluyendo:
- **Autenticación**: Login, registro, logout
- **Usuarios**: Gestión completa
- **Estados (Statuses)**: CRUD de estados de usuario
- **Permisos**: Gestión de permisos
- **Roles**: Gestión de roles

### 3. Especificación OpenAPI: `Codigo_Base_Laravel_12.openapi.json`

Archivo OpenAPI 3.1.0 que puede ser importado en herramientas que soporten este formato.

## 🛠️ Configuración por Plataforma

### Postman

#### Importación
1. Abre Postman
2. Ve a **File > Import**
3. Selecciona **File** y elige los archivos:
   - `Laravel_API_Environment.postman_environment.json`
   - `Laravel_API_Permisos_Roles.postman_collection.json` (o la colección general)
4. Selecciona el entorno "Laravel API Environment" en la esquina superior derecha

#### Configuración
- Actualiza la variable `base_url` si tu servidor corre en un puerto diferente
- El token JWT se actualiza automáticamente en algunas requests

### ApiDog

#### Importación
1. Abre ApiDog
2. Ve a **Import > Postman Collection**
3. Selecciona el archivo `.postman_collection.json`
4. Importa también el archivo de entorno si es necesario

#### Configuración
- Configura las variables de entorno en ApiDog
- Actualiza las URLs base según tu configuración

### Hoppscotch

#### Importación
1. Abre Hoppscotch
2. Ve a **Import** (icono de flecha hacia arriba)
3. Selecciona **Postman Collection**
4. Carga el archivo `.postman_collection.json`

#### Configuración
- Configura las variables en la sección de Environment
- Actualiza las URLs y tokens según sea necesario

### Insomnia

#### Importación
1. Abre Insomnia
2. Ve a **Application > Preferences > Data > Import Data > From File**
3. Selecciona el archivo `.postman_collection.json`

#### Configuración
- Crea un nuevo Environment con las variables necesarias
- Actualiza las URLs base

### Otras Herramientas

Para herramientas que soporten OpenAPI:
1. Importa el archivo `Codigo_Base_Laravel_12.openapi.json`
2. Configura la URL base: `http://localhost:8000/api/v1`
3. Configura la autenticación Bearer Token

## 🔐 Autenticación

### Obtener Token JWT

1. Ejecuta el request de **Login** en la colección
2. El token se guardará automáticamente en la variable `token`
3. Los requests subsiguientes usarán este token

**Ejemplo de login:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Respuesta esperada:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1Qi...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

## 📊 Endpoints Principales

### 👤 Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/users` | Listar usuarios (paginado) |
| GET | `/users/{id}` | Obtener usuario específico |
| POST | `/users` | Crear usuario con roles |
| PUT | `/users/{id}` | Actualizar usuario |
| DELETE | `/users/{id}` | Eliminar usuario |

**Nota:** Los IDs de usuario ahora usan ULID (ejemplo: `01kassgb071mcjwx0cadw8zcbk`)

### 🔑 Permisos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/permissions` | Listar permisos |
| GET | `/permissions/{id}` | Obtener permiso específico |
| POST | `/permissions` | Crear permiso |
| PUT | `/permissions/{id}` | Actualizar permiso |
| DELETE | `/permissions/{id}` | Eliminar permiso |

### 👤 Roles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/roles` | Listar roles con permisos |
| GET | `/roles/{id}` | Obtener rol específico |
| POST | `/roles` | Crear rol con permisos |
| PUT | `/roles/{id}` | Actualizar rol |
| DELETE | `/roles/{id}` | Eliminar rol |

### 🔐 Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/auth/register` | Registrar nuevo usuario |
| POST | `/auth/login` | Iniciar sesión |
| POST | `/auth/logout` | Cerrar sesión |

### 📊 Estados

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/statuses` | Listar estados |
| GET | `/statuses/{id}` | Obtener estado específico |
| POST | `/statuses` | Crear estado |
| PUT | `/statuses/{id}` | Actualizar estado |
| DELETE | `/statuses/{id}` | Eliminar estado |

## 📝 Ejemplos de Uso

### Crear un Usuario con Roles

```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "status_id": 1,
  "roles": [1, 2],
  "permissions": [1, 2, 3]
}
```

### Crear un Rol con Permisos

```json
{
  "name": "editor",
  "guard_name": "web",
  "permissions": [1, 3, 9, 10, 11]
}
```

### Crear un Permiso

```json
{
  "name": "manage reports",
  "guard_name": "web"
}
```

## ⚠️ Consideraciones Importantes

### IDs Dinámicos
- Los IDs de usuario ahora usan ULID (26 caracteres alfanuméricos)
- Actualiza los IDs en los requests de ejemplo según los recursos creados
- Ejemplo de ULID: `01kassgb071mcjwx0cadw8zcbk`

### Validaciones de Seguridad
- **Jerarquía de roles**: Solo usuarios con roles superiores pueden gestionar usuarios inferiores
- **Auto-eliminación**: Los usuarios no pueden eliminarse a sí mismos
- **Auto-estatus**: Los usuarios no pueden cambiar su propio estatus
- **Roles mínimos**: Los usuarios deben mantener al menos un rol

### Estados de Respuesta
- **200/201**: Éxito
- **401**: No autorizado (token inválido)
- **403**: Prohibido (permisos insuficientes)
- **404**: Recurso no encontrado
- **422**: Error de validación

## 🔄 Actualización de Token

Si el token JWT expira (3600 segundos por defecto):

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

Actualiza la variable `token` en tu herramienta de testing.

## 🧪 Testing Automatizado

### En Postman
- Utiliza el Runner para ejecutar colecciones completas
- Configura tests en la pestaña "Tests" de cada request
- Revisa los resultados en la pestaña "Test Results"

### Scripts de Testing
Ejemplo de test en Postman:

```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has user data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('user');
});
```

## 📚 Recursos Adicionales

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de JWT Auth](https://jwt-auth.readthedocs.io/)
- [Documentación de Spatie Permission](https://spatie.be/docs/laravel-permission)
- [OpenAPI Specification](https://swagger.io/specification/)

## 🤝 Contribución

Para contribuir con mejoras a esta documentación:

1. Actualiza los archivos JSON en `docs/api-testing/`
2. Modifica este README según los cambios
3. Asegúrate de que los ejemplos funcionen correctamente

¡Listo para consumir tu API de Laravel 12! 🚀
