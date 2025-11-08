# Backend - Gestión de Canchas Sintéticas

Backend completo en PHP + MySQL para la gestión de alquiler de canchas sintéticas, siguiendo arquitectura MVC y RESTful API.

## 🚀 Características

- ✅ Arquitectura MVC limpia y modular
- ✅ API RESTful con JSON
- ✅ Autenticación JWT y sesiones
- ✅ PDO con prepared statements (seguridad)
- ✅ Validaciones y sanitización de datos
- ✅ Rate limiting para protección
- ✅ CORS configurado
- ✅ Logging de auditoría
- ✅ Tests unitarios con PHPUnit
- ✅ Documentación OpenAPI/Swagger
- ✅ Colección de Postman

## 📋 Requisitos

- PHP 8.1 o superior
- MySQL 5.7+ o 8+
- Composer
- Extensiones PHP: PDO, PDO_MySQL, mbstring, json

## 🔧 Instalación

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

Edita el archivo `.env` con tus datos:

```env
# Base de Datos
DB_HOST=localhost
DB_NAME=canchas_db
DB_USER=root
DB_PASS=tu_contraseña

# Aplicación
APP_ENV=local
BASE_URL=http://localhost:8000

# JWT Secret (generar una clave segura)
JWT_SECRET=tu-clave-secreta-muy-segura-aqui

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW=3600
```

### 4. Configurar la base de datos

#### Para XAMPP:

1. **Inicia MySQL desde el panel de control de XAMPP**

2. **Opción A: Usando phpMyAdmin (Más fácil)**
   
   - Abre `http://localhost/phpmyadmin`
   - Crea una nueva base de datos llamada `canchas_db` con collation `utf8mb4_unicode_ci`
   - Selecciona la base de datos
   - Ve a la pestaña "Importar"
   - Selecciona el archivo `migrations/001_init.sql` y ejecuta
   - Repite para `seeds/001_seed_data.sql`

3. **Opción B: Usando los scripts PHP**

   Desde la terminal (CMD o PowerShell) en la carpeta del proyecto:

   ```bash
   # Ejecutar migraciones
   C:\xampp\php\php.exe migrations/migrate.php

   # Poblar datos de ejemplo
   C:\xampp\php\php.exe migrations/seed.php
   ```

4. **Opción C: Usando línea de comandos MySQL**

   Abre una terminal y ejecuta (ajusta la ruta si es necesario):

   ```bash
   # Crear base de datos
   C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE canchas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Ejecutar migraciones
   C:\xampp\mysql\bin\mysql.exe -u root canchas_db < migrations/001_init.sql

   # Poblar datos
   C:\xampp\mysql\bin\mysql.exe -u root canchas_db < seeds/001_seed_data.sql
   ```

   **Nota:** Si MySQL tiene contraseña, agrega `-p` y ingresa la contraseña cuando se solicite.

5. **Configurar variables de entorno para XAMPP:**

   En el archivo `.env`, configura:
   ```env
   DB_HOST=localhost
   DB_NAME=canchas_db
   DB_USER=root
   DB_PASS=                    # Deja vacío si no tiene contraseña, o ingresa tu contraseña
   ```

### 5. Iniciar el servidor de desarrollo

#### Opción A: Usando XAMPP Apache (Recomendado)

1. **Configurar VirtualHost en Apache:**
   
   Edita el archivo `C:\xampp\apache\conf\extra\httpd-vhosts.conf` y agrega:

   ```apache
   <VirtualHost *:80>
       ServerName canchas-api.local
       DocumentRoot "D:/Stefania/xd/public"
       
       <Directory "D:/Stefania/xd/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   **Nota:** Cambia `D:/Stefania/xd` por la ruta real de tu proyecto.

2. **Editar el archivo hosts:**
   
   Edita `C:\Windows\System32\drivers\etc\hosts` (como administrador) y agrega:
   
   ```
   127.0.0.1 canchas-api.local
   ```

3. **Reiniciar Apache desde el panel de control de XAMPP**

4. **Acceder a la API:**
   
   `http://canchas-api.local` o `http://localhost`

#### Opción B: Usando el servidor PHP embebido (Alternativa)

Si prefieres usar el servidor PHP embebido de XAMPP:

```bash
# Desde la carpeta del proyecto
C:\xampp\php\php.exe -S localhost:8000 -t public
```

O si PHP está en el PATH:

```bash
php -S localhost:8000 -t public
```

La API estará disponible en: `http://localhost:8000`

#### Opción C: Usando XAMPP sin configuración adicional

1. Coloca el proyecto en `C:\xampp\htdocs\canchas-api\`
2. Accede a: `http://localhost/canchas-api/public/`
3. Actualiza `BASE_URL` en `.env` a: `http://localhost/canchas-api/public`

## 📚 Endpoints de la API

### Autenticación

- `POST /api/auth/register` - Registrar nuevo usuario
- `POST /api/auth/login` - Iniciar sesión (retorna JWT)
- `POST /api/auth/forgot-password` - Solicitar recuperación de contraseña
- `POST /api/auth/reset-password` - Resetear contraseña con token
- `GET /api/auth/profile` - Obtener perfil del usuario autenticado
- `PUT /api/auth/profile` - Actualizar perfil

### Canchas

- `GET /api/fields` - Listar canchas (filtros: status, ubicacion, tipo, min_price, max_price)
- `GET /api/fields/{id}` - Obtener cancha por ID
- `GET /api/fields/{id}/availability?date=YYYY-MM-DD` - Consultar disponibilidad
- `POST /api/fields` - Crear cancha (Admin)
- `PUT /api/fields/{id}` - Actualizar cancha (Admin)
- `DELETE /api/fields/{id}` - Eliminar cancha (Admin)

### Reservas

- `GET /api/bookings` - Listar reservas (filtros: field_id, status, date_from, date_to)
- `GET /api/bookings/{id}` - Obtener reserva por ID
- `POST /api/bookings` - Crear reserva
- `PUT /api/bookings/{id}/cancel` - Cancelar reserva

### Pagos

- `POST /api/payments` - Procesar pago (mock)
- `GET /api/payments/{id}` - Obtener pago por ID
- `GET /api/payments/booking/{booking_id}` - Obtener pagos de una reserva

### Administración

- `GET /api/admin/reports/bookings` - Reporte de reservas
- `GET /api/admin/reports/revenue` - Reporte de ingresos

## 🔐 Autenticación

La API utiliza JWT (JSON Web Tokens) para autenticación. Después de hacer login, incluye el token en el header:

```
Authorization: Bearer {token}
```

### Usuarios de prueba

Después de ejecutar los seeds, puedes usar:

- **Admin:**
  - Email: `admin@canchas.com`
  - Password: `admin123`

- **Cliente:**
  - Email: `juan@example.com`
  - Password: `cliente123`

## 📝 Ejemplos de Uso

### Registrar usuario

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Pérez",
    "email": "juan@example.com",
    "telefono": "987654321",
    "password": "cliente123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@canchas.com",
    "password": "admin123"
  }'
```

### Crear reserva

```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "field_id": 1,
    "date": "2024-12-25",
    "start_time": "10:00:00",
    "duration_minutes": 60
  }'
```

### Consultar disponibilidad

```bash
curl http://localhost:8000/api/fields/1/availability?date=2024-12-25
```

## 🧪 Testing

Ejecutar tests unitarios:

```bash
./vendor/bin/phpunit
```

O usando Composer:

```bash
composer test
```

## 📖 Documentación

### OpenAPI/Swagger

La documentación OpenAPI está en `docs/openapi.yaml`. Puedes visualizarla usando:

- [Swagger Editor](https://editor.swagger.io/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)

### Postman

Importa la colección desde `docs/postman_collection.json` en Postman.

Configura la variable de entorno `base_url` en Postman:
- `base_url`: `http://localhost:8000`

## 🏗️ Estructura del Proyecto

```
project/
├─ public/
│  └─ index.php              # Front Controller
├─ app/
│  ├─ Controllers/           # Controladores MVC
│  │  ├─ AuthController.php
│  │  ├─ FieldController.php
│  │  ├─ BookingController.php
│  │  ├─ PaymentController.php
│  │  └─ AdminController.php
│  ├─ Models/                # Modelos y acceso a BD
│  │  ├─ User.php
│  │  ├─ Field.php
│  │  ├─ Booking.php
│  │  ├─ Payment.php
│  │  └─ ...
│  ├─ Services/              # Lógica de negocio
│  │  ├─ AuthService.php
│  │  ├─ BookingService.php
│  │  └─ PaymentService.php
│  ├─ Middlewares/           # Autenticación, CORS, Rate Limit
│  │  ├─ JWTAuthMiddleware.php
│  │  ├─ AdminMiddleware.php
│  │  ├─ RateLimitMiddleware.php
│  │  └─ CORSMiddleware.php
│  ├─ Helpers/               # Funciones auxiliares
│  │  ├─ ResponseHelper.php
│  │  ├─ ValidationHelper.php
│  │  ├─ SanitizeHelper.php
│  │  └─ LogHelper.php
│  └─ Core/
│     └─ Router.php          # Router simple
├─ config/
│  ├─ database.php           # Configuración DB
│  ├─ app.php                # Configuración general
│  └─ routes.php             # Definición de rutas
├─ migrations/               # Scripts SQL
│  ├─ 001_init.sql
│  └─ migrate.php
├─ seeds/                    # Datos de ejemplo
│  ├─ 001_seed_data.sql
│  └─ seed.php
├─ tests/                    # Tests unitarios
│  ├─ Models/
│  ├─ Services/
│  └─ Controllers/
├─ storage/
│  ├─ logs/                  # Logs de la aplicación
│  └─ cache/                 # Cache (rate limiting)
├─ docs/                     # Documentación
│  ├─ openapi.yaml
│  └─ postman_collection.json
├─ composer.json
├─ phpunit.xml
└─ README.md
```

## 🔒 Seguridad

- **Prepared Statements**: Todas las consultas usan PDO prepared statements
- **Password Hashing**: Contraseñas hasheadas con `password_hash()`
- **JWT**: Tokens JWT para autenticación stateless
- **Sanitización**: Todos los inputs son sanitizados
- **Validación**: Validaciones estrictas en todos los endpoints
- **Rate Limiting**: Protección contra ataques de fuerza bruta
- **CORS**: Configuración de CORS para el frontend
- **Audit Logs**: Registro de todas las acciones importantes

## 🚢 Despliegue

### Apache

Configura el VirtualHost apuntando a `public/`:

```apache
<VirtualHost *:80>
    ServerName api.tudominio.com
    DocumentRoot /ruta/al/proyecto/public
    
    <Directory /ruta/al/proyecto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name api.tudominio.com;
    root /ruta/al/proyecto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Variables de entorno en producción

Asegúrate de:
1. Cambiar `APP_ENV=production`
2. Generar un `JWT_SECRET` seguro y único
3. Configurar credenciales de BD seguras
4. Habilitar HTTPS
5. Configurar backups de BD

## 🔧 Configuración Avanzada

### Integración de Pagos

El servicio de pagos (`PaymentService`) actualmente simula el procesamiento. Para integrar un gateway real:

1. Edita `app/Services/PaymentService.php`
2. Reemplaza la lógica de simulación en `processPayment()`
3. Integra con Stripe, PayPal, o tu gateway preferido
4. Actualiza las variables de entorno con las credenciales

### Políticas de Cancelación

Las políticas de cancelación están en `app/Services/BookingService.php`. Actualmente:
- Cancelación permitida hasta 24 horas antes
- Modifica `canCancelBooking()` para cambiar las reglas

### Logs

Los logs se guardan en:
- Base de datos: tabla `audit_logs`
- Archivo: `storage/logs/app.log`

## 📞 Soporte

Para preguntas o problemas, abre un issue en el repositorio.

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

---

**Desarrollado con ❤️ para la gestión de canchas sintéticas**
