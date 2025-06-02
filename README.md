<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Proyecto Laravel + Oracle: Sincronización de Alumnos

Este repositorio contiene una pequeña aplicación en **Laravel 12** que utiliza **Oracle 21c XE** (usuario `user01`) para:
- Registro/login de usuarios (`users`).
- Sincronizar (INSERT/UPDATE) 10 000 registros de la tabla `alumno` hacia `alumno_m1` mediante un procedimiento PL/SQL con `MERGE`.
- Mostrar un progress bar al invocar dicha sincronización desde el front-end.

---

## Requisitos

Antes de instalar y ejecutar el proyecto, asegúrate de tener:

PHP 8.1 o mayor.

Composer (administrador de dependencias PHP)

Laravel 12 (se instalará vía Composer)

Oracle 21c XE

    Para este ejemplo se usó:
    - Esquema/Usuario: user01
    - Contraseña: tecsup
    Se puede verificar dentro del archivo .env y config/database.php

Oracle Instant Client y extensión PHP OCI8 configurada (php_oci8)

Extensión OCI8 habilitada en PHP (para conectarse a Oracle) añadido al PATH

Nota: No se utiliza Node.js ni npm para este proyecto.

---

## Configuración del entorno

Clonar el repositorio

git clone [https://github.com/RonaldoKaizen/Laravel-Oracle-Merge.git]
cd Laravel-Oracle-Merge

Instalar dependencias PHP con Composer

    composer install


Luego edita .env y ajusta las siguientes variables (ejemplo):

---

# Conexión Oracle
    DB_CONNECTION=oracle
    DB_HOST=127.0.0.1
    DB_PORT=1521
    DB_DATABASE=XE
    DB_USERNAME=user01
    DB_PASSWORD=tecsup
    DB_CHARSET=AL32UTF8
    DB_SERVER_VERSION=21c

Generar la clave de aplicación

    php artisan key:generate

Esto completará APP_KEY en tu .env.

---

Crear las tablas en OracleLaravel tiene migraciones configuradas para Oracle. Solo debes ejecutar:

    php artisan migrate --database=oracle

Esto creará las tablas users, password_reset_tokens y sessions en el esquema user01.

Luego, en Oracle (SQLPlus o SQL Developer) deberás crear las tablas alumno y alumno_m1 y poblar alumno con 10,000 filas.

Ejemplo de SQL para crear alumno:

    CREATE TABLE alumno (
      id_alumno    NUMBER PRIMARY KEY,
      nombre       VARCHAR2(100),
      apellido     VARCHAR2(100),
      email        VARCHAR2(100),
      fecha_nac    DATE,
      modalidad    VARCHAR2(50)
    );
    
Insertar 10000 registros a la tabla alumno:

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

Ejemplo de SQL para crear alumno_m1:

    CREATE TABLE alumno_m1 (
      id_alumno    NUMBER PRIMARY KEY,
      nombre       VARCHAR2(100),
      apellido     VARCHAR2(100),
      email        VARCHAR2(100),
      fecha_nac    DATE,
      modalidad    VARCHAR2(50)
    );

Crear el procedimiento PL/SQL en OracleCopia y ejecuta en SQLPlus (o SQL Developer), parte muy importante donde incluye el *MERGE*:

    CREATE OR REPLACE PROCEDURE sp_sync_alumno_tiempo (
      p_rows_merged OUT NUMBER,
      p_time_ms     OUT NUMBER
    ) AS
      v_start_time NUMBER;
      v_end_time   NUMBER;
    BEGIN
      v_start_time := DBMS_UTILITY.get_time;  -- devuelve centésimas de segundo
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
      p_time_ms := (v_end_time - v_start_time) * 10; -- convierte centésimas a ms
    
      COMMIT;
    EXCEPTION
      WHEN OTHERS THEN
        ROLLBACK;
        RAISE;
    END sp_sync_alumno_tiempo;
    /

Este procedimiento hará el MERGE de los 10,000 registros de alumno a alumno_m1 y devolverá en p_rows_merged cuántas filas se procesaron.

---

## Ejecutar la aplicación

Iniciar el servidor de desarrollo de Laravel:

    php artisan serve

Por defecto arrancará en http://127.0.0.1:8000.

- Registrarse (si no tienes cuenta): abre http://127.0.0.1:8000/register, completa el formulario y haz clic en “Registrarme”.

- Iniciar sesión: ve a http://127.0.0.1:8000/login, ingresa tu email y contraseña.

Sincronizar alumnos: una vez logueado, verás el botón "Actualizar Alumnos" en la pantalla principal. Al hacer clic:

Aparecerá una barra de progreso (simulada) mientras se ejecuta el PL/SQL.

Al terminar, mostrará un mensaje indicando cuántas filas se insertaron o actualizaron y a su vez el tiempo que se tomó en hacer la acción.

---

## Comandos Laravel importantes

Instalar dependencias:

    composer install

Publicar migraciones y ejecutar en Oracle:

    php artisan migrate --database=oracle

Generar clave de aplicación:

    php artisan key:generate

Ejecutar Tinker (para crear usuarios manualmente):

    php artisan tinker --database=oracle
    php artisan tinker

Iniciar servidor local:

    php artisan serve

---

## Notas adicionales!!!

Si cambias el nombre de usuario o contraseña de Oracle, actualiza los valores en .env.

Verifica que la extensión oci8 esté habilitada en tu php.ini:

    extension=oci8

y que la versión de Instant Client sea compatible con Oracle 21c XE.

Para generar o poblar datos de prueba en alumno, puedes ajustar el bloque PL/SQL según necesites.


¡Listo! Con estos pasos, cualquier persona que clone el repositorio podrá configurar, migrar y ejecutar la aplicación en su máquina local sin necesidad de escribir SQL manualmente (salvo para poblar alumno y crear alumno_m1), y probar la funcionalidad de sincronización usando Oracle.
