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
