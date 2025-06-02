<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Proyecto Laravel + Oracle: Sincronización de Alumnos

Este repositorio contiene una pequeña aplicación en **Laravel 12** que utiliza **Oracle 21c XE** (usuario `user01`) para:
- Registro/login de usuarios (`users`).
- Sincronizar (INSERT/UPDATE) 10 000 registros de la tabla `alumno` hacia `alumno_m1` mediante un procedimiento PL/SQL con `MERGE`.
- Mostrar un progress bar al invocar dicha sincronización desde el front-end.

---

## 1. Requisitos

- **PHP 8.1+**  
- **Composer**  
- **Laravel 12**  
- **Oracle 21c XE** instalado localmente  
- Extensión PHP OCI8 (`pdo_oci`) habilitada  
- **Git** (para clonar el repositorio)

---

## 2. Clonar e instalar dependencias

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/tu-repo.git
cd tu-repo

# Instalar dependencias de PHP
composer install
