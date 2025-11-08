# Backend - Gestión de Canchas Sintéticas

Backend en PHP + MySQL para la gestión de alquiler de canchas sintéticas, siguiendo el patrón MVC.

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7+ o 8+
- Composer
- Extensiones PHP: PDO, PDO_MySQL, mbstring

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd <nombre-del-proyecto>
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

Copia el archivo de ejemplo y edítalo con tus credenciales:

```bash
cp env.example .env
```

Edita el archivo `.env` con tus datos de conexión:

```env
DB_HOST=localhost
DB_NAME=canchas_db
DB_USER=root
DB_PASS=tu_contraseña
APP_ENV=local
BASE_URL=http://localhost:8000
```

### 4. Configurar la base de datos

Crea la base de datos en MySQL:

```sql
CREATE DATABASE canchas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ejecuta las migraciones (cuando estén disponibles):

```bash
# Las migraciones estarán en la carpeta migrations/
```

### 5. Iniciar el servidor de desarrollo

```bash
php -S localhost:8000 -t public
```

La aplicación estará disponible en: `http://localhost:8000`

## Estructura del Proyecto

```
project/
├─ public/              # Front Controller y archivos públicos
│  └─ index.php
├─ app/                 # Lógica de la aplicación
│  ├─ Controllers/      # Controladores MVC
│  ├─ Models/           # Modelos y acceso a BD
│  ├─ Views/            # Vistas (opcional)
│  ├─ Services/         # Lógica de negocio
│  ├─ Middlewares/      # Autenticación, permisos
│  └─ Helpers/          # Funciones auxiliares
├─ config/              # Archivos de configuración
│  ├─ database.php
│  └─ app.php
├─ migrations/          # Scripts de migración SQL
├─ seeds/               # Datos iniciales
├─ tests/               # Pruebas unitarias
├─ storage/             # Archivos de almacenamiento
│  └─ logs/             # Logs de la aplicación
└─ vendor/              # Dependencias de Composer
```

## Características

- ✅ Arquitectura MVC
- ✅ PDO para acceso a base de datos
- ✅ Autoload PSR-4 con Composer
- ✅ Variables de entorno con Dotenv
- ✅ BaseController y BaseModel para reutilización
- ✅ Helpers para respuestas JSON y validaciones
- ✅ Estructura preparada para API REST
- ✅ Middlewares para autenticación
- ✅ Servicios para lógica de negocio

## Desarrollo

### Crear un nuevo controlador

Crea un archivo en `app/Controllers/` extendiendo `BaseController`:

```php
<?php

namespace App\Controllers;

class MiController extends BaseController
{
    public function index()
    {
        $this->successResponse(['mensaje' => 'Hola mundo']);
    }
}
```

### Crear un nuevo modelo

Crea un archivo en `app/Models/` extendiendo `BaseModel`:

```php
<?php

namespace App\Models;

class MiModelo extends BaseModel
{
    protected string $table = 'mi_tabla';

    public function getAll()
    {
        return $this->query("SELECT * FROM {$this->table}");
    }
}
```

## Testing

Ejecutar las pruebas:

```bash
composer test
```

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

