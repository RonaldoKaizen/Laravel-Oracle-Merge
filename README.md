<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Proyecto Laravel-Oracle: Sincronización de Alumnos con MERGE

Este repositorio contiene una pequeña aplicación Laravel 12 que se conecta a Oracle 21c XE (usuario `user01`) y permite:
- Registrar y autenticar usuarios (tabla `users`).
- Ejecutar, tras el login, un procedimiento PL/SQL que sincroniza 10 000 registros de la tabla `alumno` a la tabla `alumno_m1` mediante un `MERGE`.
- Mostrar un **progress bar** en el frontend mientras dura la ejecución del procedimiento.
- Devolver, al finalizar, el número total de filas insertadas/actualizadas (y opcionalmente el tiempo invertido).

---
