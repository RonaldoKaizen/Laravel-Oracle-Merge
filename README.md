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

### Requisitos de PHP y OCI8

1. **Instalar Oracle Instant Client**  
   - Descarga la versión correspondiente a tu SO desde [Oracle Instant Client Downloads](https://www.oracle.com/database/technologies/instant-client.html).  
   - Descomprime el ZIP/TAR en una carpeta, por ejemplo:  
     - Windows: `C:\oracle\instantclient_21_1\`  
     - Linux/macOS: `/opt/oracle/instantclient_21_1/`

2. **Agregar Oracle Instant Client al PATH / LD_LIBRARY_PATH**  
   - **Windows (PowerShell o CMD como Administrador):**  
     ```powershell
     setx PATH "C:\oracle\instantclient_21_1;%PATH%"
     ```  

3. **Instalar/ habilitar la extensión OCI8 en PHP**  
   - **Windows**:  
     1. Copia el archivo DLL (`oci8_12c.dll` o similar) desde `instantclient_21_1\php\` (si existe) o instala mediante PECL:  
        ```powershell
        pecl install oci8
        ```  
     2. Edita tu `php.ini` (ej. `C:\php\php.ini`) y agrega o descomenta la línea:  
        ```ini
        extension=oci8
        ```  
   - **Linux/macOS**:  
     1. Instala con PECL (requiere tener los headers de PHP y el Instant Client):  
        ```bash
        sudo pecl install oci8
        ```  
     2. Cuando te pregunte por la ruta, ingresa la ubicación de Instant Client, por ejemplo:  
        ```
        instantclient,/opt/oracle/instantclient_21_1
        ```  
     3. Edita tu `php.ini` (ubicación típica: `/etc/php/8.1/cli/php.ini` o similar) y agrega:  
        ```ini
        extension=oci8.so
        ```  

4. **Verificar que OCI8 esté habilitado**  
   Ejecuta:
   ```bash
   php -m | grep oci8
   # Debe devolver 'oci8'

