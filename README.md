# QSUL Backend – Laravel

Este repositorio contiene el backend de la aplicación QSUL (Quality System Universidad Libre), desarrollado en Laravel. Puede ser ejecutado en un entorno con Docker (recomendado para producción) o en un entorno local tradicional (recomendado para desarrollo).

---

## 🚀 Requisitos

### Opción A – Docker

* [Docker](https://www.docker.com/get-started)
* [Docker Compose](https://docs.docker.com/compose/install/)

### Opción B – Sin Docker (Ubuntu)

* PHP 8.2
* Composer
* MySQL
* Extensiones PHP necesarias:

  * `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `session`, `tokenizer`, `xml`, `zip`, `pdo_mysql`, `mysqli`, `pcntl`, `opcache`

---

## 📦 Instalación (Opción A – Usando Docker)

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/esteban389/qsul-backend.git
   cd qsul-backend 
   ```

2. **Crear el archivo `.env`**

   ```bash
   cp .env.example .env
   ```

   Ajusta las siguientes variables clave:

   ```env
   APP_URL=http://localhost
   FRONTEND_URL=http://localhost:3000

   NATIONAL_COORDINATOR_NAME=Alejandro
   NATIONAL_COORDINATOR_EMAIL=alejandro@mail.com
   NATIONAL_COORDINATOR_PASSWORD=12345678
   ```

3. **Levantar los contenedores**

   ```bash
   docker compose up -d --build
   ```

   Esto iniciará los servicios:

   * `web`: servidor PHP + NGINX
   * `worker`: procesador de colas
   * `task`: ejecutor de tareas programadas
   * `mysql`: base de datos

   El seeder del coordinador nacional se ejecutará automáticamente.

4. **Verificar el estado**

   Accede al backend en [http://localhost](http://localhost).

---

## 🧑‍💻 Instalación (Opción B – Sin Docker)

Esta guía asume que ya se tiene un servidor web instalado y configurado, ya sea apache o nginx + php-fpm
1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/esteban389/qsul-backend.git
   cd qsul-backend
   ```

   El contenido de la carpeta qsul-backend es lo que se expone en el servidor, si está usando nginx el 
   contenido debería quedar en la carpeta /var/www/html

2. **Instalar dependencias**

   ```bash
   composer install
   ```

3. **Crear el archivo `.env`**

   ```bash
   cp .env.example .env
   ```

   Edita las variables según tu entorno:

   ```env
   APP_URL=http://localhost:8000
   FRONTEND_URL=http://localhost:3000
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=qsul-db
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

4. **Generar clave de la aplicación**

   ```bash
   php artisan key:generate
   ```

5. **Migrar y sembrar la base de datos**

   ```bash
   php artisan migrate --seed
   ```

6. **Optimizar archivos de laravel**

   ```bash
   php artisan optimize
   ```

7. **Vincular carpeta pública con carpeta de archivos**

   ```bash
   php artisan storage:link
   ```

8. **Iniciar el ejecutor de tareas y procesador de colas**

   ```bash
    nohup php artisan queue:work > storage/logs/queue.log 2>&1 &
   ```

   ```bash
    nohup php artisan schedule:work > storage/logs/schedule.log 2>&1 &
   ```
---

## 🍪 Consideraciones sobre Cookies y Sanctum

Laravel Sanctum usa autenticación basada en cookies. Para que funcione:

* El backend (`APP_URL`) y frontend (`FRONTEND_URL`) deben estar bajo el **mismo dominio** (ej. `app.com` y `api.app.com`, o `app.com:80` y `app.com:81`).
* Variables importantes:

  ```env
  SESSION_DOMAIN=localhost
  SANCTUM_STATEFUL_DOMAINS=localhost:3000
  ```

---

## 📤 Envío de correos

Configura SMTP en el `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@unilibre.edu.co
MAIL_PASSWORD=tu_contraseña_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_correo@unilibre.edu.co
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🛠 Servicios en producción

* `web`: NGINX + PHP-FPM
* `worker`: `php artisan queue:work`
* `task`: `php artisan schedule:work`
* `mysql`: base de datos con persistencia en `.database/`

---
## 👤 Usuario inicial

Durante el proceso de instalación se crea un usuario tipo "Coordinador Nacional" a través del seeder (esto se ejecuta de manera automática si se usa la infraestructura docker que hay por defecto en el proyecto), quien tiene permisos administrativos en el sistema. Este usuario es necesario para acceder inicialmente y no puede crearse desde la interfaz de usuario, por lo que sus credenciales deben estar correctamente definidas en las variables:

```env
NATIONAL_COORDINATOR_NAME=...
NATIONAL_COORDINATOR_EMAIL=...
NATIONAL_COORDINATOR_PASSWORD=...
```
---

## ✅ Endpoint de prueba

Prueba el endpoint (requiere autenticación):

```http
GET /api/user
```

---

## 📄 Licencia

MIT © Universidad Libre
