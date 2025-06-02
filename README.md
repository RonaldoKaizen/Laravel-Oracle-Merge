<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Proyecto Laravel-Oracle: Sincronización de Alumnos con MERGE

Este repositorio contiene una pequeña aplicación Laravel 12 que se conecta a Oracle 21c XE (usuario `user01`) y permite:
- Registrar y autenticar usuarios (tabla `users`).
- Ejecutar, tras el login, un procedimiento PL/SQL que sincroniza 10 000 registros de la tabla `alumno` a la tabla `alumno_m1` mediante un `MERGE`.
- Mostrar un **progress bar** en el frontend mientras dura la ejecución del procedimiento.
- Devolver, al finalizar, el número total de filas insertadas/actualizadas (y opcionalmente el tiempo invertido).

---
## Índice

1. [Requisitos Previos](#requisitos-previos)  
2. [Clonar el Repositorio](#clonar-el-repositorio)  
3. [Instalar Dependencias de PHP/Laravel](#instalar-dependencias-de-phplaravel)  
4. [Configuración del Entorno (`.env`)](#configuracion-del-entorno-env)  
5. [Configurar Oracle (usuario, tablas y procedimiento)](#configurar-oracle-usuario-tablas-y-procedimiento)  
6. [Ejecutar Migraciones en Oracle](#ejecutar-migraciones-en-oracle)  
7. [Crear un Usuario de Prueba](#crear-un-usuario-de-prueba)  
8. [Cargar Tablas de Alumnos y el Procedimiento PL/SQL](#cargar-tablas-de-alumnos-y-el-procedimiento-plsql)  
9. [Probar la Aplicación](#probar-la-aplicacion)  
10. [Notas Adicionales](#notas-adicionales)  

---

## 1. Requisitos Previos

Antes de comenzar, asegúrate de tener instalado en tu equipo:

1. **PHP 8.1 o superior**  
   - Verifica ejecutando:  
     ```bash
     php -v
     ```

2. **Composer** (gestor de dependencias de PHP)  
   - Verifica:  
     ```bash
     composer --version
     ```

3. **Oracle 21c XE** (o superior)  
   - Debes contar con una instancia Oracle XE instalada y un usuario/schema llamado `user01` con privilegios de creación de tablas, secuencias y procedimientos.  
   - Asegúrate de que tu servicio Oracle esté corriendo y que puedas conectarte con `sqlplus user01/tu_password@XE` (o desde SQL Developer).

4. **Extensiones PHP necesarias**  
   - `oci8` (para conectarse a Oracle).  
   - `pdo_oci` (PDO driver para Oracle).  
   - `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json` (normalmente vienen habilitadas con Laravel).  

5. **Node.js y NPM (opcional)**  
   - Solo si planeas compilar activos frontend (por ejemplo, si agregas estilos personalizados). No es estrictamente obligatorio para este proyecto, que usa Bootstrap CDN.

6. **Git**  
   - Para clonar el repositorio:  
     ```bash
     git --version
     ```

---

## 2. Clonar el Repositorio

En tu terminal (PowerShell, Bash, Terminal, etc.), navega a la carpeta donde desees descargar el proyecto y ejecuta:

```bash
git clone https://github.com/tu-usuario/tu-repositorio.git

O, si prefieres descargarte el ZIP desde GitHub, haz clic en “Code → Download ZIP” y descomprime en la ubicación deseada.

Luego, entra en la carpeta:

bash
Copiar
Editar
cd tu-repositorio
3. Instalar Dependencias de PHP/Laravel
Dentro de la carpeta del proyecto, ejecuta:

bash
Copiar
Editar
composer install
Esto descargará todas las dependencias necesarias (incluido el paquete yajra/laravel-oci8 para conectar con Oracle).

Nota: Si aún no has publicado/configurado los proveedores de yajra/laravel-oci8, Laravel 12 lo detecta automáticamente. No es necesario hacer php artisan vendor:publish en este caso.

4. Configuración del Entorno (.env)
Copia el archivo de ejemplo .env.example a .env:

bash
Copiar
Editar
cp .env.example .env
Edita .env con tu editor de texto favorito y ajusta las siguientes variables:

ini
Copiar
Editar
APP_NAME="Laravel Oracle MERGE"
APP_ENV=local
APP_KEY=   # Se generará en el siguiente paso
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Configuración de la base de datos
DB_CONNECTION=oracle
DB_HOST=127.0.0.1
DB_PORT=1521
DB_DATABASE=XE
DB_USERNAME=user01
DB_PASSWORD=tu_password_oracle
DB_CHARSET=AL32UTF8
DB_PREFIX=
DB_PREFIX_SCHEMA=
DB_EDITION=ora$base
DB_SERVER_VERSION=21c

# Configuración adicional (correo, etc.) – opcional
MAIL_MAILER=smtp
MAIL_HOST=mail.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
Genera la clave de la aplicación:

bash
Copiar
Editar
php artisan key:generate
Verifica que APP_KEY en .env se haya completado automáticamente.

5. Configurar Oracle (usuario, tablas y procedimiento)
Abre tu cliente SQL (SQLPlus, SQLcl o SQL Developer) y conéctate con user01:

sql
Copiar
Editar
CONNECT user01/tu_password_oracle@XE
5.1 Eliminar tablas/residuos anteriores (si existen)
sql
Copiar
Editar
DROP TABLE usuario PURGE;
DROP TABLE alumno_m1 PURGE;
DROP TABLE alumno PURGE;
DROP TABLE users PURGE;
DROP TABLE password_reset_tokens PURGE;
DROP TABLE sessions PURGE;
Importante: Solo si existían versiones anteriores en tu esquema. Si no, ignora errores “table does not exist”.

5.2 Crear las tablas requeridas por Laravel
Laravel trae la migración create_users_table.php. Sin embargo, puedes crear manualmente las tablas en SQL si prefieres:

sql
Copiar
Editar
-- Tabla users
CREATE TABLE users (
  id                  NUMBER GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  name                VARCHAR2(255) NOT NULL,
  email               VARCHAR2(255) UNIQUE NOT NULL,
  email_verified_at   TIMESTAMP        NULL,
  password            VARCHAR2(255)    NOT NULL,
  remember_token      VARCHAR2(100)    NULL,
  created_at          DATE DEFAULT SYSDATE,
  updated_at          DATE DEFAULT SYSDATE
);

-- Tabla password_reset_tokens
CREATE TABLE password_reset_tokens (
  email       VARCHAR2(255) PRIMARY KEY,
  token       VARCHAR2(255) NOT NULL,
  created_at  TIMESTAMP      NULL
);

-- Tabla sessions
CREATE TABLE sessions (
  id            VARCHAR2(255) PRIMARY KEY,
  user_id       NUMBER        NULL,
  ip_address    VARCHAR2(45)  NULL,
  user_agent    CLOB          NULL,
  payload       CLOB          NOT NULL,
  last_activity NUMBER        NOT NULL
);

-- Crear índice sobre user_id en sessions
CREATE INDEX sessions_user_id_index ON sessions(user_id);
Nota: En el flujo normal se crean mediante php artisan migrate --database=oracle, pero aquí queda como ejemplo directo en SQL si necesitas inspeccionar o moldear las columnas.

6. Ejecutar Migraciones en Oracle
Si prefieres que Laravel cree automáticamente las tablas users, password_reset_tokens y sessions, simplemente ejecuta:

bash
Copiar
Editar
php artisan migrate --database=oracle
Verás en pantalla algo como:

makefile
Copiar
Editar
Migrating: 2023_XX_XX_XXXXXX_create_users_table
Migrated:  2023_XX_XX_XXXXXX_create_users_table (10.34ms)
Migrating: 2023_XX_XX_XXXXXX_create_password_reset_tokens_table
Migrated:  2023_XX_XX_XXXXXX_create_password_reset_tokens_table (6.12ms)
Migrating: 2023_XX_XX_XXXXXX_create_sessions_table
Migrated:  2023_XX_XX_XXXXXX_create_sessions_table (4.57ms)
Esto confirmará que Laravel generó las tres tablas en tu esquema user01.

7. Crear un Usuario de Prueba
Para poder entrar al sistema y ver el dashboard, necesitas al menos un usuario. Tienes dos opciones:

Seeder (opcional)

Puedes crear un seeder que inserte un usuario con contraseña.

Ejemplo rápido:

bash
Copiar
Editar
php artisan make:seeder UserSeeder
En database/seeders/UserSeeder.php:

php
Copiar
Editar
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@example.com',
            'password' => Hash::make('Secreto123'),
        ]);
    }
}
Luego edita DatabaseSeeder.php para llamar al UserSeeder:

php
Copiar
Editar
$this->call([
    UserSeeder::class,
]);
Y ejecuta:

bash
Copiar
Editar
php artisan db:seed --class=UserSeeder --database=oracle
Verifica que exista un usuario con email admin@example.com, contraseña Secreto123.

Usar Tinker

bash
Copiar
Editar
php artisan tinker --database=oracle
>>> use App\Models\User;
>>> User::create([
...     'name'     => 'Usuario Prueba',
...     'email'    => 'prueba@example.com',
...     'password' => bcrypt('clave123'),
... ]);
Luego sal de Tinker con exit.

En adelante, usarás prueba@example.com / clave123 (o el que hayas configurado) para hacer login.

8. Cargar Tablas de Alumnos y el Procedimiento PL/SQL
8.1 Crear la tabla alumno
En SQLPlus o SQL Developer, conectado como user01:

sql
Copiar
Editar
CREATE TABLE alumno (
  id_alumno   NUMBER PRIMARY KEY,
  nombre      VARCHAR2(100),
  apellido    VARCHAR2(100),
  email       VARCHAR2(100),
  fecha_nac   DATE,
  modalidad   VARCHAR2(50)
);
8.2 Poblar con 10 000 registros
Ejecuta este bloque PL/SQL (ajusta fechas o datos si lo deseas). Tardará unos segundos:

sql
Copiar
Editar
DECLARE
  v_id NUMBER := 1;
BEGIN
  WHILE v_id <= 10000 LOOP
    INSERT INTO alumno (id_alumno, nombre, apellido, email, fecha_nac, modalidad)
    VALUES (
      v_id,
      'Nombre' || v_id,
      'Apellido' || v_id,
      'alumno' || v_id || '@correo.com',
      ADD_MONTHS(DATE '1990-01-01', TRUNC(DBMS_RANDOM.VALUE(0, 400))),
      CASE WHEN MOD(v_id,2)=0 THEN 'Presencial' ELSE 'Virtual' END
    );
    v_id := v_id + 1;
  END LOOP;
  COMMIT;
END;
/
Confirma con:

sql
Copiar
Editar
SELECT COUNT(*) FROM alumno;
-- Debe devolver 10000
8.3 Crear la tabla alumno_m1
sql
Copiar
Editar
CREATE TABLE alumno_m1 (
  id_alumno   NUMBER PRIMARY KEY,
  nombre      VARCHAR2(100),
  apellido    VARCHAR2(100),
  email       VARCHAR2(100),
  fecha_nac   DATE,
  modalidad   VARCHAR2(50)
);
Por defecto queda vacía.

8.4 Crear el procedimiento PL/SQL con MERGE
sql
Copiar
Editar
CREATE OR REPLACE PROCEDURE sp_sync_alumno (
  p_rows_merged OUT NUMBER
) AS
  v_total NUMBER;
BEGIN
  MERGE INTO alumno_m1 tgt
  USING alumno src
  ON (src.id_alumno = tgt.id_alumno)
  WHEN MATCHED THEN
    UPDATE SET
      tgt.nombre    = src.nombre,
      tgt.apellido  = src.apellido,
      tgt.email     = src.email,
      tgt.fecha_nac = src.fecha_nac,
      tgt.modalidad = src.modalidad
  WHEN NOT MATCHED THEN
    INSERT (id_alumno, nombre, apellido, email, fecha_nac, modalidad)
    VALUES (src.id_alumno, src.nombre, src.apellido, src.email, src.fecha_nac, src.modalidad);

  v_total := SQL%ROWCOUNT;
  p_rows_merged := v_total;
  COMMIT;
EXCEPTION
  WHEN OTHERS THEN
    ROLLBACK;
    RAISE;
END sp_sync_alumno;
/
p_rows_merged será el número total (INSERT + UPDATE) que devolvemos a Laravel.

Puedes verificar su existencia con:

sql
Copiar
Editar
SELECT object_name 
FROM user_objects 
WHERE object_type = 'PROCEDURE' 
  AND object_name = 'SP_SYNC_ALUMNO';
9. Probar la Aplicación
Arranca el servidor integrado de Laravel:

bash
Copiar
Editar
php artisan serve
Normalmente se ejecuta en:

cpp
Copiar
Editar
http://127.0.0.1:8000
Registrar un usuario

Abre tu navegador en http://127.0.0.1:8000/register.

Llena el formulario (nombre, email, password).

Tras enviar, se crea el usuario en la tabla users (Oracle) y se autentica automáticamente.

Ingresar al Dashboard

Si ya tenías un usuario de prueba, entra a http://127.0.0.1:8000/login y pon tu email/contraseña.

Una vez logueado, se te mostrará la página principal (home.blade.php) con un botón “Actualizar Alumnos”.

Sincronizar alumnos

Haz clic en “Actualizar Alumnos”. Verás que aparece un progress bar animado que simula avance hasta 90 %.

Tras unos segundos (según la velocidad de tu máquina y de Oracle), el progress bar llegará al 100 % y mostrará un mensaje del tipo:

nginx
Copiar
Editar
Se sincronizaron 10000 registros.
En futuras ejecuciones, como la tabla alumno_m1 ya está poblada, solo hará UPDATE de filas previas (y INSERT si hubiera nuevos), pero el procedimiento siempre devuelve el conteo de registros procesados.

Cerrar sesión

En la parte superior derecha encontrarás un botón “Cerrar sesión” que envía un POST /logout y te redirige a login.

10. Notas Adicionales
Separar configuración Oracle vs. MySQL/PostgreSQL
Este proyecto está preparado exclusivamente para Oracle XE; si en un futuro quisieras cambiar a MySQL o PostgreSQL, tendrías que:

Ajustar DB_CONNECTION en .env.

Modificar la migración de users (Laravel la adapta automáticamente, pero podría requerir ajustes de tipo de dato).

Eliminar/ajustar todo lo relacionado con PL/SQL (MERGE, procedimientos, etc.).

Mostrar tiempo real de ejecución (opcional)
Si deseas medir cuántos milisegundos tarda exactamente el MERGE, puedes reemplazar sp_sync_alumno por el siguiente procedimiento que también calcula tiempo:

sql
Copiar
Editar
CREATE OR REPLACE PROCEDURE sp_sync_alumno_tiempo (
  p_rows_merged OUT NUMBER,
  p_time_ms     OUT NUMBER
) AS
  v_start_time NUMBER;
  v_end_time   NUMBER;
BEGIN
  v_start_time := DBMS_UTILITY.get_time;  -- Centésimas de segundo
  MERGE INTO alumno_m1 tgt
  USING alumno src
  ON (src.id_alumno = tgt.id_alumno)
  WHEN MATCHED THEN
    UPDATE SET
      tgt.nombre    = src.nombre,
      tgt.apellido  = src.apellido,
      tgt.email     = src.email,
      tgt.fecha_nac = src.fecha_nac,
      tgt.modalidad = src.modalidad
  WHEN NOT MATCHED THEN
    INSERT (id_alumno, nombre, apellido, email, fecha_nac, modalidad)
    VALUES (src.id_alumno, src.nombre, src.apellido, src.email, src.fecha_nac, src.modalidad);

  v_end_time := DBMS_UTILITY.get_time;
  p_rows_merged := SQL%ROWCOUNT;
  p_time_ms     := (v_end_time - v_start_time) * 10; -- Pasa de centésimas a milisegundos
  COMMIT;
EXCEPTION
  WHEN OTHERS THEN
    ROLLBACK;
    RAISE;
END sp_sync_alumno_tiempo;
/
Luego, en HomeController@syncAlumno, ajusta la invocación para recibir ambos parámetros y retornarlos en JSON.

Permisos y roles de Oracle
Asegúrate de que el usuario user01 tenga los permisos necesarios para:

Crear y borrar tablas (CREATE TABLE, DROP TABLE).

Crear y ejecutar procedimientos (CREATE PROCEDURE, EXECUTE).

Usar paquetes como DBMS_UTILITY.
Si usas Oracle XE con un usuario recién creado, es posible que necesites otorgarle permisos adicionales (por ejemplo, GRANT CREATE PROCEDURE TO user01;).

Variable DB_PREFIX en Oracle
Por defecto dejamos DB_PREFIX= vacío. Si en el futuro quisieras que todas las tablas lleven un prefijo (por ejemplo, app_users, app_alumno), puedes definirlo en .env y modificar migraciones en consecuencia.

Ambiente de Producción

En .env, coloca APP_ENV=production y APP_DEBUG=false.

Configura un servidor web (Nginx/Apache) que apunte a la carpeta public/ de Laravel.

Asegura que storage/ y bootstrap/cache/ sean escribibles.

Configura un usuario de base de datos con credenciales distintas de user01 en producción (por seguridad).

Estructura principal del repositorio
arduino
Copiar
Editar
├── app
│   ├── Console
│   ├── Exceptions
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── AuthController.php
│   │   │   └── HomeController.php
│   ├── Models
│   │   ├── User.php
│   │   ├── Alumno.php
│   │   └── AlumnoM1.php
│   └── ...
├── bootstrap
├── config
│   └── database.php         ← Asegúrate de que esté configurada la conexión `oracle`.
├── database
│   ├── migrations
│   │   ├── xxxx_xx_xx_xxxxxx_create_users_table.php
│   │   ├── xxxx_xx_xx_xxxxxx_create_password_reset_tokens_table.php
│   │   └── xxxx_xx_xx_xxxxxx_create_sessions_table.php
│   └── seeders
│       └── UserSeeder.php   ← (Opcional) ejemplo de seeder.
├── public
├── resources
│   └── views
│       ├── auth
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       └── home.blade.php
├── routes
│   └── web.php              ← Rutas de login, registro, home y sync-alumno.
├── .env.example
├── README.md               ← Este archivo.
└── composer.json
Contribuir o Reportar Problemas
Si encuentras algún error, bug o quieres proponer mejoras, siéntete libre de abrir un Issue o enviar un Pull Request. Agradezco cualquier contribución que haga este proyecto más robusto.

¡Gracias por tu interés en este proyecto! Si lo pruebas y te funciona correctamente, no olvides darle ⭐ al repositorio.
